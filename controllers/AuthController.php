<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/TOTP.php';

class AuthController {

    public function index() {
        $tab = $_GET['tab'] ?? 'login';
        $activeTab = in_array($tab, ['registro', 'representante'], true) ? 'representante' : 'login';
        require_once 'views/auth/login.php';
    }

    /**
     * Inicio de sesión exclusivo para Cuentas Institucionales (Jefe de TI, Operador y SuperAdmin)
     * con el correo proporcionado por la plataforma + contraseña, seguido de MFA obligatorio.
     */
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $correo = strtolower(filter_var(trim($_POST['correo_electronico'] ?? ''), FILTER_SANITIZE_EMAIL));
            $contrasena = trim($_POST['contrasena'] ?? '');

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->obtenerPorCorreo($correo);

            if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
                header("Location: index.php?page=login&error=" . urlencode("Credenciales institucionales inválidas."));
                exit();
            }

            // Si es un Representante registrado exclusivamente vía OAuth intentando entrar por formulario
            if ((int)$usuario['id_rol'] === 3 && ($usuario['oauth_provider'] ?? 'institucional') !== 'institucional') {
                header("Location: index.php?page=login&error=" . urlencode("Las cuentas vinculadas con Google o Microsoft deben iniciar sesión mediante OAuth."));
                exit();
            }

            $this->iniciarFlujoMfa($usuario);
        }
    }

    /**
     * Registro unificado B2B: crea la cuenta del Representante (con contraseña Bcrypt),
     * da de alta la Empresa activa con su Plan B2B, aprovisiona automáticamente al primer Jefe de TI
     * enviando sus credenciales por SMTP, e inicia el flujo obligatorio de MFA TOTP.
     */
    public function registroUnificado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=login&tab=registro");
            exit();
        }

        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
        $apellidos = htmlspecialchars(trim($_POST['apellidos'] ?? ''));
        $correo = strtolower(filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL));
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmar = $_POST['confirmar_contrasena'] ?? '';
        $nombreEmpresa = htmlspecialchars(trim($_POST['nombre_empresa'] ?? ''));
        $rfcNit = strtoupper(htmlspecialchars(trim($_POST['rfc_nit'] ?? '')));
        $slugDominio = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '', trim($_POST['slug_dominio'] ?? '')));
        $sector = htmlspecialchars(trim($_POST['sector'] ?? 'DataCenter & Cloud'));
        $telefono = htmlspecialchars(trim($_POST['telefono'] ?? ''));
        $plan = trim($_POST['plan_suscripcion'] ?? 'corporate');

        $planesValidos = defined('B2B_PLANS') ? array_keys(B2B_PLANS) : ['starter', 'corporate', 'enterprise'];
        if (!in_array($plan, $planesValidos, true)) {
            $plan = 'corporate';
        }

        if ($nombre === '' || $apellidos === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            header("Location: index.php?page=login&tab=registro&error=" . urlencode("Ingresa tu nombre, apellidos y un correo electrónico válido."));
            exit();
        }

        if (strlen($contrasena) < 8) {
            header("Location: index.php?page=login&tab=registro&error=" . urlencode("La contraseña debe tener al menos 8 caracteres."));
            exit();
        }

        if ($contrasena !== $confirmar) {
            header("Location: index.php?page=login&tab=registro&error=" . urlencode("Las contraseñas no coinciden."));
            exit();
        }

        if ($nombreEmpresa === '' || $rfcNit === '' || $slugDominio === '' || $telefono === '') {
            header("Location: index.php?page=login&tab=registro&error=" . urlencode("Completa todos los datos de tu empresa y subdominio institucional."));
            exit();
        }

        $usuarioModel = new Usuario();
        if ($usuarioModel->existeCorreo($correo)) {
            header("Location: index.php?page=login&tab=registro&error=" . urlencode("El correo electrónico ya se encuentra registrado."));
            exit();
        }

        $hash = password_hash($contrasena, PASSWORD_BCRYPT);
        $guardado = $usuarioModel->guardar($nombre, $apellidos, $correo, $hash, 3, null, 'institucional');
        $usuario = $guardado ? $usuarioModel->obtenerPorCorreo($correo) : null;

        if (!$usuario) {
            header("Location: index.php?page=login&tab=registro&error=" . urlencode("No fue posible registrar tu cuenta de representante."));
            exit();
        }

        $empresaModel = new Empresa();
        $registroEmpresa = $empresaModel->registrarEmpresa(
            (int)$usuario['id_usuario'],
            $nombreEmpresa,
            $rfcNit,
            $slugDominio,
            $sector !== '' ? $sector : 'DataCenter & Cloud',
            $telefono,
            $plan,
            $correo
        );

        if (!is_array($registroEmpresa) || empty($registroEmpresa['id_empresa'])) {
            $usuarioModel->eliminar((int)$usuario['id_usuario']);
            header("Location: index.php?page=login&tab=registro&error=" . urlencode("No se pudo registrar la empresa. Verifica que el subdominio institucional no esté en uso."));
            exit();
        }

        if (!empty($registroEmpresa['jefe_ti_credenciales'])) {
            $_SESSION['recien_creado_jefe_ti'] = $registroEmpresa['jefe_ti_credenciales'];
            $_SESSION['recien_creado_empresa_meta'] = [
                'nombre_empresa' => $registroEmpresa['nombre_empresa'] ?? $nombreEmpresa,
                'dominio_institucional' => $registroEmpresa['dominio_institucional'] ?? ($slugDominio . '.' . INSTITUTIONAL_BASE_DOMAIN),
                'plan' => $registroEmpresa['plan'] ?? $plan,
                'correo_enviado_a' => $registroEmpresa['correo_enviado_a'] ?? $correo,
            ];
        }

        $usuario = $usuarioModel->obtenerPorId((int)$usuario['id_usuario']) ?: $usuario;
        $usuarioModel->registrarBitacora(
            (int)$registroEmpresa['id_empresa'],
            (int)$usuario['id_usuario'],
            'REGISTRO_UNIFICADO',
            "Registro unificado Representante + Empresa: $nombreEmpresa (Plan: $plan)"
        );

        $this->iniciarFlujoMfa($usuario);
    }

    /**
     * Inicia el flujo OAuth 2.0 con Google o Microsoft para Representantes de Empresa (Cuenta Personal).
     */
    public function oauthLogin() {
        $provider = ($_GET['provider'] ?? 'google') === 'microsoft' ? 'microsoft' : 'google';

        // Si las credenciales reales están configuradas y OAUTH_SANDBOX_MODE es false, redirigir al proveedor oficial
        if (!OAUTH_SANDBOX_MODE) {
            if ($provider === 'google') {
                $params = http_build_query([
                    'client_id' => GOOGLE_CLIENT_ID,
                    'redirect_uri' => GOOGLE_REDIRECT_URI,
                    'response_type' => 'code',
                    'scope' => 'openid email profile',
                    'access_type' => 'online',
                    'prompt' => 'select_account'
                ]);
                header("Location: https://accounts.google.com/o/oauth2/v2/auth?" . $params);
                exit();
            } else {
                $params = http_build_query([
                    'client_id' => MICROSOFT_CLIENT_ID,
                    'redirect_uri' => MICROSOFT_REDIRECT_URI,
                    'response_type' => 'code',
                    'scope' => 'openid email profile User.Read',
                    'response_mode' => 'query'
                ]);
                header("Location: https://login.microsoftonline.com/common/oauth2/v2.0/authorize?" . $params);
                exit();
            }
        }

        // En modo desarrollo/sandbox o desde el modal OAuth de login.php
        require_once 'views/auth/login.php';
    }

    /**
     * Callback OAuth 2.0 (Google / Microsoft) que crea o recupera la cuenta personal del Representante
     * y exige validación de segundo factor con Google Authenticator (TOTP).
     */
    public function oauthCallback() {
        $provider = ($_POST['provider'] ?? $_GET['provider'] ?? 'google') === 'microsoft' ? 'microsoft' : 'google';
        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? 'Carlos'));
        $apellidos = htmlspecialchars(trim($_POST['apellidos'] ?? 'Mendoza'));
        $correo = strtolower(filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL));

        if (empty($correo)) {
            header("Location: index.php?page=login&error=" . urlencode("Debes proporcionar un correo válido de Google o Microsoft."));
            exit();
        }

        $oauthUid = $provider . '_' . md5($correo);
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerOCrearRepresentanteOAuth($provider, $oauthUid, $nombre, $apellidos, $correo);

        if (!$usuario) {
            header("Location: index.php?page=login&error=" . urlencode("No fue posible autenticar la cuenta con " . ucfirst($provider)));
            exit();
        }

        $usuarioModel->registrarBitacora(
            $usuario['id_empresa'] ? (int)$usuario['id_empresa'] : null,
            (int)$usuario['id_usuario'],
            'OAUTH_LOGIN',
            "Autenticación primaria con " . strtoupper($provider) . " ({$correo})"
        );

        $this->iniciarFlujoMfa($usuario);
    }

    /**
     * Paso intermedio de Seguridad Extrema: almacena sesión pre-MFA y envía a configurar QR o verificar 6 dígitos.
     */
    private function iniciarFlujoMfa(array $usuario): void {
        if (empty($usuario['mfa_secret'])) {
            $nuevoSecreto = TOTP::generarSecreto();
            (new Usuario())->guardarSecretoMfa((int)$usuario['id_usuario'], $nuevoSecreto, 0);
            $usuario['mfa_secret'] = $nuevoSecreto;
            $usuario['mfa_enabled'] = 0;
        }

        $_SESSION['pre_mfa_user'] = [
            'id_usuario' => (int)$usuario['id_usuario'],
            'id_empresa' => $usuario['id_empresa'] ? (int)$usuario['id_empresa'] : null,
            'id_rol' => (int)$usuario['id_rol'],
            'nombre_usuario' => $usuario['nombre_usuario'],
            'apellidos' => $usuario['apellidos'],
            'correo_electronico' => $usuario['correo_electronico'],
            'foto_perfil' => $usuario['foto_perfil'] ?? 'default.png',
            'mfa_secret' => $usuario['mfa_secret'],
            'mfa_enabled' => (int)$usuario['mfa_enabled'],
            'oauth_provider' => $usuario['oauth_provider'] ?? 'institucional'
        ];

        if ((int)$usuario['mfa_enabled'] === 0) {
            header("Location: index.php?page=mfa_setup");
        } else {
            header("Location: index.php?page=mfa_verify");
        }
        exit();
    }

    /**
     * Muestra la pantalla de vinculación inicial con Google Authenticator (Código QR + clave secreta).
     */
    public function mfaSetup() {
        if (!isset($_SESSION['pre_mfa_user'])) {
            header("Location: index.php?page=login");
            exit();
        }
        $preUser = $_SESSION['pre_mfa_user'];
        $qrUrl = TOTP::obtenerQrImageUrl($preUser['correo_electronico'], $preUser['mfa_secret'], MFA_ISSUER);
        $codigoActualDemo = TOTP::obtenerCodigo($preUser['mfa_secret']);
        require_once 'views/auth/mfa_setup.php';
    }

    /**
     * Muestra y procesa la verificación del código de 6 dígitos de Google Authenticator.
     */
    public function mfaVerify() {
        if (!isset($_SESSION['pre_mfa_user'])) {
            header("Location: index.php?page=login");
            exit();
        }

        $preUser = $_SESSION['pre_mfa_user'];
        $codigoActualDemo = TOTP::obtenerCodigo($preUser['mfa_secret']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo_mfa'] ?? '');
            if (TOTP::verificarCodigo($preUser['mfa_secret'], $codigo)) {
                $usuarioModel = new Usuario();
                $usuarioModel->activarMfa((int)$preUser['id_usuario']);

                // Promover sesión completa autenticada con MFA
                $_SESSION['usuario_id'] = (int)$preUser['id_usuario'];
                $_SESSION['id_empresa'] = $preUser['id_empresa'];
                $_SESSION['nombre_usuario'] = $preUser['nombre_usuario'];
                $_SESSION['apellidos'] = $preUser['apellidos'];
                $_SESSION['correo_electronico'] = $preUser['correo_electronico'];
                $_SESSION['rol'] = (int)$preUser['id_rol'];
                $_SESSION['foto_perfil'] = $preUser['foto_perfil'];
                $_SESSION['mfa_verified'] = true;
                unset($_SESSION['pre_mfa_user']);

                $usuarioModel->registrarBitacora(
                    $_SESSION['id_empresa'],
                    $_SESSION['usuario_id'],
                    'MFA_VERIFIED',
                    "Acceso verificado con Google Authenticator (TOTP 6 dígitos)"
                );

                // Redirigir según el rol del nuevo modelo de negocio
                if ($_SESSION['rol'] === 2) {
                    header("Location: index.php?page=admin");
                } elseif ($_SESSION['rol'] === 3) {
                    header("Location: index.php?page=representante");
                } else {
                    // Rol 4 (Jefe de TI) y Rol 1 (Operador)
                    header("Location: index.php?page=user");
                }
                exit();
            } else {
                $errorMfa = "El código de 6 dígitos de Google Authenticator es incorrecto o ha expirado.";
                if (isset($_POST['from_setup'])) {
                    $qrUrl = TOTP::obtenerQrImageUrl($preUser['correo_electronico'], $preUser['mfa_secret'], MFA_ISSUER);
                    require_once 'views/auth/mfa_setup.php';
                    return;
                }
            }
        }

        require_once 'views/auth/mfa_verify.php';
    }

    public function logout() {
        if (isset($_SESSION['usuario_id'])) {
            (new Usuario())->registrarBitacora(
                $_SESSION['id_empresa'] ?? null,
                (int)$_SESSION['usuario_id'],
                'LOGOUT',
                "Cierre de sesión seguro"
            );
        }
        session_unset();
        session_destroy();
        header("Location: index.php?page=login");
        exit();
    }
}
?>

