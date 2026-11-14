<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/TOTP.php';

class AuthController {

    public function index() {
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

            // Si es un Representante intentando entrar por formulario en lugar de OAuth Google/Microsoft
            if ((int)$usuario['id_rol'] === 3) {
                header("Location: index.php?page=login&error=" . urlencode("Las cuentas de Representante deben iniciar sesión con Google o Microsoft."));
                exit();
            }

            $this->iniciarFlujoMfa($usuario);
        }
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

