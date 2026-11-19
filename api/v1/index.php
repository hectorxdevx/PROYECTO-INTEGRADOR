<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/Empresa.php';
require_once __DIR__ . '/../../models/MailerService.php';
require_once __DIR__ . '/../../models/Sensor.php';
require_once __DIR__ . '/../../models/TOTP.php';
require_once __DIR__ . '/../../models/JwtHelper.php';

function jsonResponse(int $statusCode, array $data): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

function getJsonBody(): array {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return $_POST ?: [];
}

function requireJwtAuth(): array {
    $token = JwtHelper::obtenerBearerToken();
    if (!$token) {
        jsonResponse(401, [
            'ok' => false,
            'error' => 'No se proporcionó token Bearer JWT en la cabecera Authorization.'
        ]);
    }
    $payload = JwtHelper::validarToken($token);
    if (!$payload || empty($payload['mfa_verified'])) {
        jsonResponse(401, [
            'ok' => false,
            'error' => 'Token JWT inválido, expirado o sin verificación MFA (Google Authenticator).'
        ]);
    }
    return $payload;
}

function resolverIdEmpresaUsuario(array $auth, array $body = [], ?Empresa $empresaModel = null): ?int {
    $idRol = (int)($auth['id_rol'] ?? 0);
    if ($idRol === 2) {
        if (isset($_GET['id_empresa']) && $_GET['id_empresa'] !== '') {
            return (int)$_GET['id_empresa'];
        }
        if (!empty($body['id_empresa'])) {
            return (int)$body['id_empresa'];
        }
    }
    $usuarioModel = new Usuario();
    $userDb = $usuarioModel->obtenerPorId((int)$auth['id_usuario']);
    if ($userDb && !empty($userDb['id_empresa'])) {
        return (int)$userDb['id_empresa'];
    }
    if ($empresaModel !== null) {
        $emp = $empresaModel->obtenerPorRepresentante((int)$auth['id_usuario']);
        if ($emp && !empty($emp['id_empresa'])) {
            return (int)$emp['id_empresa'];
        }
    }
    if (!empty($auth['id_empresa'])) {
        return (int)$auth['id_empresa'];
    }
    return $idRol === 3 ? null : 1;
}

// Determinar el endpoint solicitado (soporta ?endpoint=racks o /api/v1/racks)
$endpoint = $_GET['endpoint'] ?? '';
if (empty($endpoint)) {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (preg_match('#/api/v1/(.+)$#', $uriPath, $matches)) {
        $endpoint = trim($matches[1], '/');
    }
}
$endpoint = trim($endpoint, '/');
if ($endpoint === 'index.php' || $endpoint === '') {
    $endpoint = 'status';
}

$method = $_SERVER['REQUEST_METHOD'];
$body = getJsonBody();

switch ($endpoint) {

    // ----------------------------------------------------------
    // 1. HEALTHCHECK & DOCUMENTACIÓN RÁPIDA DE ENDPOINTS
    // ----------------------------------------------------------
    case 'status':
        jsonResponse(200, [
            'ok' => true,
            'service' => 'SysMonitor IoT REST API v1',
            'security_model' => 'Zero-Trust B2B (OAuth 2.0 + Google Authenticator TOTP + JWT + IoT API Keys)',
            'timestamp' => date('c'),
            'endpoints_disponibles' => [
                'POST /api/v1/index.php?endpoint=auth/login' => 'Autenticación institucional (Jefe de TI, Operador, SuperAdmin, Representante)',
                'POST /api/v1/index.php?endpoint=auth/oauth' => 'Autenticación OAuth 2.0 Google/Microsoft (Representantes)',
                'POST /api/v1/index.php?endpoint=auth/register-company' => 'Registro unificado de Representante + Empresa + Plan B2B + Jefe de TI',
                'POST /api/v1/index.php?endpoint=auth/mfa-verify' => 'Verificación de 6 dígitos Google Authenticator y emisión de JWT',
                'GET  /api/v1/index.php?endpoint=companies' => 'Listar empresas registradas (?mine=1 para resumen del representante y correos SMTP)',
                'POST /api/v1/index.php?endpoint=companies' => 'Registrar nueva empresa con Plan B2B (Representante)',
                'POST /api/v1/index.php?endpoint=companies/upgrade' => 'Cambiar plan B2B de la empresa (starter, corporate, enterprise)',
                'POST /api/v1/index.php?endpoint=companies/provision-email' => 'Solicitar o generar correo institucional (@empresa.sysmonitor.com)',
                'GET  /api/v1/index.php?endpoint=racks' => 'Obtener racks y sensores IoT en tiempo real',
                'POST /api/v1/index.php?endpoint=racks' => 'Crear rack o actualizar umbrales de alerta (Jefe de TI / Representante)',
                'GET  /api/v1/index.php?endpoint=telemetry' => 'Historial de lecturas de sensores',
                'POST /api/v1/index.php?endpoint=telemetry' => 'Enviar lectura desde placa IoT (ESP32) o Postman',
                'GET  /api/v1/index.php?endpoint=alerts' => 'Listar alertas críticas y advertencias',
                'POST /api/v1/index.php?endpoint=alerts/resolve' => 'Marcar alerta como atendida'
            ]
        ]);
        break;

    // ----------------------------------------------------------
    // 2. LOGIN INSTITUCIONAL (PASO 1 ANTES DE MFA)
    // ----------------------------------------------------------
    case 'auth/login':
        if ($method !== 'POST') {
            jsonResponse(405, ['ok' => false, 'error' => 'Método no permitido. Usa POST.']);
        }
        $correo = strtolower(trim($body['correo'] ?? $body['email'] ?? ''));
        $contrasena = trim($body['contrasena'] ?? $body['password'] ?? '');

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorCorreo($correo);

        if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
            jsonResponse(401, [
                'ok' => false,
                'error' => 'Correo institucional o contraseña incorrectos.'
            ]);
        }

        if (empty($usuario['mfa_secret'])) {
            $secreto = TOTP::generarSecreto();
            $usuarioModel->guardarSecretoMfa((int)$usuario['id_usuario'], $secreto, 0);
            $usuario['mfa_secret'] = $secreto;
        }

        $tempToken = JwtHelper::generarToken([
            'id_usuario' => (int)$usuario['id_usuario'],
            'correo' => $usuario['correo_electronico'],
            'mfa_pending' => true
        ], 600);

        jsonResponse(200, [
            'ok' => true,
            'status' => 'MFA_REQUIRED',
            'message' => 'Credenciales primarias válidas. Ingresa el código de 6 dígitos de Google Authenticator en auth/mfa-verify.',
            'temp_token' => $tempToken,
            'mfa_setup_qr_url' => TOTP::obtenerQrImageUrl($usuario['correo_electronico'], $usuario['mfa_secret'], MFA_ISSUER),
            'codigo_totp_actual_para_pruebas_postman' => TOTP::obtenerCodigo($usuario['mfa_secret']),
            'usuario' => [
                'id_usuario' => (int)$usuario['id_usuario'],
                'nombre' => $usuario['nombre_usuario'] . ' ' . $usuario['apellidos'],
                'correo' => $usuario['correo_electronico'],
                'rol' => $usuario['nombre_rol'] ?? 'operador',
                'id_rol' => (int)$usuario['id_rol'],
                'id_empresa' => $usuario['id_empresa'] ? (int)$usuario['id_empresa'] : null,
                'empresa' => $usuario['nombre_empresa'] ?? 'SysMonitor Global',
                'slug_dominio' => $usuario['slug_dominio'] ?? null
            ]
        ]);
        break;

    // ----------------------------------------------------------
    // 3. LOGIN OAUTH 2.0 GOOGLE / MICROSOFT PARA REPRESENTANTES
    // ----------------------------------------------------------
    case 'auth/oauth':
        if ($method !== 'POST') {
            jsonResponse(405, ['ok' => false, 'error' => 'Método no permitido. Usa POST.']);
        }
        $provider = ($body['provider'] ?? 'google') === 'microsoft' ? 'microsoft' : 'google';
        $correo = strtolower(trim($body['correo'] ?? ''));
        $nombre = trim($body['nombre'] ?? 'Representante');
        $apellidos = trim($body['apellidos'] ?? 'Corporativo');

        if (empty($correo)) {
            jsonResponse(400, ['ok' => false, 'error' => 'El campo correo es obligatorio.']);
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerOCrearRepresentanteOAuth($provider, $provider . '_' . md5($correo), $nombre, $apellidos, $correo);

        $tempToken = JwtHelper::generarToken([
            'id_usuario' => (int)$usuario['id_usuario'],
            'correo' => $usuario['correo_electronico'],
            'mfa_pending' => true
        ], 600);

        jsonResponse(200, [
            'ok' => true,
            'status' => 'MFA_REQUIRED',
            'provider' => $provider,
            'temp_token' => $tempToken,
            'mfa_setup_qr_url' => TOTP::obtenerQrImageUrl($usuario['correo_electronico'], $usuario['mfa_secret'], MFA_ISSUER),
            'codigo_totp_actual_para_pruebas_postman' => TOTP::obtenerCodigo($usuario['mfa_secret']),
            'usuario' => [
                'id_usuario' => (int)$usuario['id_usuario'],
                'id_empresa' => $usuario['id_empresa'] ? (int)$usuario['id_empresa'] : null,
                'nombre' => $usuario['nombre_usuario'] . ' ' . $usuario['apellidos'],
                'correo' => $usuario['correo_electronico'],
                'rol' => 'representante',
                'id_rol' => 3,
                'empresa' => $usuario['nombre_empresa'] ?? 'Sin empresa asignada'
            ]
        ]);
        break;

    // ----------------------------------------------------------
    // 3B. REGISTRO UNIFICADO: REPRESENTANTE + EMPRESA + PLAN B2B + JEFE TI
    // ----------------------------------------------------------
    case 'auth/register-company':
        if ($method !== 'POST') {
            jsonResponse(405, ['ok' => false, 'error' => 'Método no permitido. Usa POST.']);
        }

        $nombre = trim((string)($body['nombre'] ?? ''));
        $apellidos = trim((string)($body['apellidos'] ?? ''));
        $correo = strtolower(trim((string)($body['correo'] ?? $body['email'] ?? '')));
        $contrasena = trim((string)($body['contrasena'] ?? $body['password'] ?? ''));
        $nombreEmpresa = trim((string)($body['nombre_empresa'] ?? ''));
        $rfcNit = strtoupper(trim((string)($body['rfc_nit'] ?? '')));
        $slugDominio = strtolower(trim((string)($body['slug_dominio'] ?? '')));
        $sector = trim((string)($body['sector'] ?? 'DataCenter & Cloud'));
        if ($sector === '') {
            $sector = 'DataCenter & Cloud';
        }
        $telefono = trim((string)($body['telefono'] ?? ''));
        $planSuscripcion = strtolower(trim((string)($body['plan_suscripcion'] ?? $body['plan'] ?? 'corporate')));

        $planesValidos = defined('B2B_PLANS') ? array_keys(B2B_PLANS) : ['starter', 'corporate', 'enterprise'];

        if ($nombre === '' || $apellidos === '' || $correo === '' || $contrasena === '' || $nombreEmpresa === '' || $rfcNit === '' || $slugDominio === '' || $telefono === '') {
            jsonResponse(400, [
                'ok' => false,
                'error' => 'Todos los campos del representante y de la empresa son obligatorios.'
            ]);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(400, ['ok' => false, 'error' => 'El correo electrónico proporcionado no es válido.']);
        }

        if (strlen($contrasena) < 8) {
            jsonResponse(400, ['ok' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres.']);
        }

        if (!in_array($planSuscripcion, $planesValidos, true)) {
            jsonResponse(400, [
                'ok' => false,
                'error' => 'Plan de suscripción inválido. Valores permitidos: starter, corporate, enterprise.'
            ]);
        }

        $usuarioModel = new Usuario();
        if ($usuarioModel->existeCorreo($correo)) {
            jsonResponse(409, ['ok' => false, 'error' => 'Ya existe una cuenta registrada con ese correo electrónico.']);
        }

        $passwordHash = password_hash($contrasena, PASSWORD_BCRYPT);
        $creadoRep = $usuarioModel->guardar($nombre, $apellidos, $correo, $passwordHash, 3, null, 'institucional', null);
        if (!$creadoRep) {
            jsonResponse(500, ['ok' => false, 'error' => 'No fue posible crear la cuenta del representante.']);
        }

        $usuario = $usuarioModel->obtenerPorCorreo($correo);
        if (!$usuario) {
            jsonResponse(500, ['ok' => false, 'error' => 'No fue posible recuperar la cuenta recién creada.']);
        }

        $empresaModel = new Empresa();
        $registroEmpresa = $empresaModel->registrarEmpresa(
            (int)$usuario['id_usuario'],
            $nombreEmpresa,
            $rfcNit,
            $slugDominio,
            $sector,
            $telefono,
            $planSuscripcion,
            $correo
        );

        if (!$registroEmpresa || !is_array($registroEmpresa)) {
            $usuarioModel->eliminar((int)$usuario['id_usuario']);
            jsonResponse(409, [
                'ok' => false,
                'error' => 'No se pudo registrar la empresa (el subdominio corporativo podría estar en uso).'
            ]);
        }

        $usuarioActualizado = $usuarioModel->obtenerPorId((int)$usuario['id_usuario']) ?: $usuario;
        $usuarioModel->registrarBitacora(
            (int)$registroEmpresa['id_empresa'],
            (int)$usuarioActualizado['id_usuario'],
            'API_REGISTRO_UNIFICADO',
            "Registro B2B unificado: {$nombreEmpresa} (Plan: {$planSuscripcion})"
        );

        $tempToken = JwtHelper::generarToken([
            'id_usuario' => (int)$usuarioActualizado['id_usuario'],
            'correo' => $usuarioActualizado['correo_electronico'],
            'mfa_pending' => true
        ], 600);

        jsonResponse(201, [
            'ok' => true,
            'status' => 'MFA_REQUIRED',
            'message' => 'Empresa activada con Rack-01, Jefe de TI inicial y credenciales enviadas por SMTP. Verifica MFA para acceder.',
            'temp_token' => $tempToken,
            'codigo_totp_actual_para_pruebas_postman' => TOTP::obtenerCodigo($usuarioActualizado['mfa_secret']),
            'mfa_setup_qr_url' => TOTP::obtenerQrImageUrl($usuarioActualizado['correo_electronico'], $usuarioActualizado['mfa_secret'], MFA_ISSUER),
            'mfa_secret' => $usuarioActualizado['mfa_secret'],
            'empresa' => [
                'id_empresa' => (int)$registroEmpresa['id_empresa'],
                'nombre_empresa' => $registroEmpresa['nombre_empresa'],
                'dominio_institucional' => $registroEmpresa['dominio_institucional'],
                'plan' => $registroEmpresa['plan'],
                'plan_suscripcion' => $registroEmpresa['plan'],
                'correo_enviado_a' => $registroEmpresa['correo_enviado_a'] ?? $correo
            ],
            'jefe_ti_credenciales' => $registroEmpresa['jefe_ti_credenciales'] ?? null,
            'usuario' => [
                'id_usuario' => (int)$usuarioActualizado['id_usuario'],
                'id_empresa' => (int)$registroEmpresa['id_empresa'],
                'nombre' => $usuarioActualizado['nombre_usuario'] . ' ' . $usuarioActualizado['apellidos'],
                'correo' => $usuarioActualizado['correo_electronico'],
                'rol' => $usuarioActualizado['nombre_rol'] ?? 'representante',
                'id_rol' => (int)$usuarioActualizado['id_rol'],
                'empresa' => $registroEmpresa['nombre_empresa'],
                'slug_dominio' => $registroEmpresa['dominio_institucional']
            ]
        ]);
        break;

    // ----------------------------------------------------------
    // 4. VERIFICACIÓN DE 6 DÍGITOS GOOGLE AUTHENTICATOR -> EMITE JWT
    // ----------------------------------------------------------
    case 'auth/mfa-verify':
        if ($method !== 'POST') {
            jsonResponse(405, ['ok' => false, 'error' => 'Método no permitido. Usa POST.']);
        }
        $codigoMfa = trim((string)($body['codigo_mfa'] ?? $body['otp'] ?? ''));
        $tempToken = trim($body['temp_token'] ?? '');
        $correoDirecto = strtolower(trim($body['correo'] ?? ''));

        $usuarioModel = new Usuario();
        $usuario = null;

        if (!empty($tempToken)) {
            $decoded = JwtHelper::validarToken($tempToken);
            if ($decoded && !empty($decoded['id_usuario'])) {
                $usuario = $usuarioModel->obtenerPorId((int)$decoded['id_usuario']);
            }
        } elseif (!empty($correoDirecto)) {
            $usuario = $usuarioModel->obtenerPorCorreo($correoDirecto);
        }

        if (!$usuario) {
            jsonResponse(401, ['ok' => false, 'error' => 'Sesión pre-MFA no encontrada. Ejecuta primero auth/login.']);
        }

        if (!TOTP::verificarCodigo($usuario['mfa_secret'], $codigoMfa)) {
            jsonResponse(401, [
                'ok' => false,
                'error' => 'Código de Google Authenticator inválido o expirado.',
                'hint_codigo_actual_demo' => TOTP::obtenerCodigo($usuario['mfa_secret'])
            ]);
        }

        $usuarioModel->activarMfa((int)$usuario['id_usuario']);
        $idEmpresaUsuario = $usuario['id_empresa']
            ? (int)$usuario['id_empresa']
            : ((int)$usuario['id_rol'] === 3 ? null : 1);

        $jwtFinal = JwtHelper::generarToken([
            'id_usuario' => (int)$usuario['id_usuario'],
            'id_empresa' => $idEmpresaUsuario,
            'id_rol' => (int)$usuario['id_rol'],
            'rol' => $usuario['nombre_rol'] ?? 'operador',
            'correo' => $usuario['correo_electronico'],
            'mfa_verified' => true
        ]);

        $usuarioModel->registrarBitacora(
            $idEmpresaUsuario,
            (int)$usuario['id_usuario'],
            'API_MFA_OK',
            "Token JWT emitido tras validar Google Authenticator"
        );

        jsonResponse(200, [
            'ok' => true,
            'message' => 'Autenticación Multifactor exitosa.',
            'token' => $jwtFinal,
            'token_type' => 'Bearer',
            'expires_in' => JWT_EXPIRE_SECONDS,
            'usuario' => [
                'id_usuario' => (int)$usuario['id_usuario'],
                'id_empresa' => $idEmpresaUsuario,
                'nombre' => $usuario['nombre_usuario'] . ' ' . $usuario['apellidos'],
                'correo' => $usuario['correo_electronico'],
                'id_rol' => (int)$usuario['id_rol'],
                'rol' => $usuario['nombre_rol'] ?? 'operador',
                'empresa' => $usuario['nombre_empresa'] ?? ($idEmpresaUsuario ? 'InfraOps Cloud & DataCenter' : 'Sin empresa asignada'),
                'slug_dominio' => $usuario['slug_dominio'] ?? null
            ]
        ]);
        break;

    // ----------------------------------------------------------
    // 5. EMPRESAS (LISTAR, RESUMEN B2B, ALTA Y CAMBIO DE PLAN)
    // ----------------------------------------------------------
    case 'companies':
        $auth = requireJwtAuth();
        $empresaModel = new Empresa();
        $mailerService = new MailerService();

        if ($method === 'GET') {
            if (!empty($_GET['mine'])) {
                $empresa = $empresaModel->obtenerPorRepresentante((int)$auth['id_usuario']);
                if (!$empresa) {
                    $idEmpresaUsuario = resolverIdEmpresaUsuario($auth, [], $empresaModel);
                    if ($idEmpresaUsuario) {
                        $empresa = $empresaModel->obtenerPorId($idEmpresaUsuario);
                    }
                }
                if (!$empresa) {
                    jsonResponse(200, [
                        'ok' => true,
                        'empresa' => null,
                        'uso_plan' => null,
                        'solicitudes' => [],
                        'correos_enviados' => [],
                        'planes_disponibles' => defined('B2B_PLANS') ? B2B_PLANS : []
                    ]);
                }

                $idEmp = (int)$empresa['id_empresa'];
                jsonResponse(200, [
                    'ok' => true,
                    'empresa' => $empresa,
                    'uso_plan' => $empresaModel->obtenerUsoPlan($idEmp),
                    'solicitudes' => $empresaModel->obtenerSolicitudesPorEmpresa($idEmp),
                    'correos_enviados' => $mailerService->obtenerCorreos($idEmp, 25),
                    'planes_disponibles' => defined('B2B_PLANS') ? B2B_PLANS : []
                ]);
            }

            $empresas = $empresaModel->obtenerTodas();
            jsonResponse(200, [
                'ok' => true,
                'total' => count($empresas),
                'empresas' => $empresas,
                'correos_enviados' => $mailerService->obtenerCorreos(null, 30),
                'planes_disponibles' => defined('B2B_PLANS') ? B2B_PLANS : []
            ]);
        }

        if ($method === 'POST') {
            $nombreEmpresa = trim($body['nombre_empresa'] ?? '');
            $rfcNit = strtoupper(trim($body['rfc_nit'] ?? ''));
            $slugDominio = strtolower(trim($body['slug_dominio'] ?? ''));
            $sector = trim($body['sector'] ?? 'DataCenter & Cloud') ?: 'DataCenter & Cloud';
            $telefono = trim($body['telefono'] ?? '+52 999 000 0000') ?: '+52 999 000 0000';
            $plan = strtolower(trim($body['plan_suscripcion'] ?? $body['plan'] ?? 'corporate'));
            $correoNotif = !empty($body['correo_notificaciones'])
                ? strtolower(trim($body['correo_notificaciones']))
                : ($auth['correo'] ?? null);

            $planesValidos = defined('B2B_PLANS') ? array_keys(B2B_PLANS) : ['starter', 'corporate', 'enterprise'];
            if (!in_array($plan, $planesValidos, true)) {
                jsonResponse(400, ['ok' => false, 'error' => 'Plan B2B no válido. Usa starter, corporate o enterprise.']);
            }

            if (!$nombreEmpresa || !$rfcNit || !$slugDominio) {
                jsonResponse(400, ['ok' => false, 'error' => 'Faltan campos requeridos: nombre_empresa, rfc_nit, slug_dominio.']);
            }

            $registro = $empresaModel->registrarEmpresa(
                (int)$auth['id_usuario'],
                $nombreEmpresa,
                $rfcNit,
                $slugDominio,
                $sector,
                $telefono,
                $plan,
                $correoNotif
            );
            if (!$registro || !is_array($registro)) {
                jsonResponse(409, ['ok' => false, 'error' => 'No se pudo registrar la empresa (el dominio podría estar en uso).']);
            }

            $idNuevaEmpresa = (int)$registro['id_empresa'];
            (new Usuario())->registrarBitacora(
                $idNuevaEmpresa,
                (int)$auth['id_usuario'],
                'API_ALTA_EMPRESA',
                "Registró la empresa {$nombreEmpresa} con Plan {$plan}"
            );

            $tokenActualizado = JwtHelper::generarToken([
                'id_usuario' => (int)$auth['id_usuario'],
                'id_empresa' => $idNuevaEmpresa,
                'id_rol' => (int)($auth['id_rol'] ?? 3),
                'rol' => $auth['rol'] ?? 'representante',
                'correo' => $auth['correo'] ?? '',
                'mfa_verified' => true
            ]);

            jsonResponse(201, [
                'ok' => true,
                'message' => 'Empresa registrada y activada con Rack-01 y Jefe de TI aprovisionados.',
                'id_empresa' => $idNuevaEmpresa,
                'nombre_empresa' => $registro['nombre_empresa'],
                'dominio_institucional' => $registro['dominio_institucional'],
                'plan' => $registro['plan'],
                'correo_enviado_a' => $registro['correo_enviado_a'] ?? $correoNotif,
                'jefe_ti_credenciales' => $registro['jefe_ti_credenciales'] ?? null,
                'uso_plan' => $empresaModel->obtenerUsoPlan($idNuevaEmpresa),
                'token' => $tokenActualizado
            ]);
        }
        break;

    // ----------------------------------------------------------
    // 5B. CAMBIO / UPGRADE DE PLAN B2B
    // ----------------------------------------------------------
    case 'companies/upgrade':
        $auth = requireJwtAuth();
        if ($method !== 'POST') {
            jsonResponse(405, ['ok' => false, 'error' => 'Usa POST.']);
        }
        if (!in_array((int)($auth['id_rol'] ?? 0), [2, 3, 4], true)) {
            jsonResponse(403, ['ok' => false, 'error' => 'No tienes permisos para cambiar el plan B2B.']);
        }

        $nuevoPlan = strtolower(trim((string)($body['plan_suscripcion'] ?? $body['nuevo_plan'] ?? $body['plan'] ?? '')));
        $planesValidos = defined('B2B_PLANS') ? array_keys(B2B_PLANS) : ['starter', 'corporate', 'enterprise'];
        if (!in_array($nuevoPlan, $planesValidos, true)) {
            jsonResponse(400, ['ok' => false, 'error' => 'Plan B2B no válido. Usa starter, corporate o enterprise.']);
        }

        $empresaModel = new Empresa();
        $idEmpresa = resolverIdEmpresaUsuario($auth, $body, $empresaModel);
        if (!$idEmpresa) {
            jsonResponse(404, ['ok' => false, 'error' => 'No hay una empresa vinculada a tu cuenta.']);
        }

        if (!$empresaModel->cambiarPlanEmpresa($idEmpresa, $nuevoPlan)) {
            jsonResponse(400, ['ok' => false, 'error' => 'No fue posible actualizar el plan B2B de la empresa.']);
        }

        $usoPlan = $empresaModel->obtenerUsoPlan($idEmpresa);
        (new Usuario())->registrarBitacora($idEmpresa, (int)$auth['id_usuario'], 'CAMBIO_PLAN_B2B', "Plan B2B actualizado a {$nuevoPlan}");

        jsonResponse(200, [
            'ok' => true,
            'message' => 'Plan B2B actualizado a ' . ($usoPlan['nombre_plan'] ?? ucfirst($nuevoPlan)) . '.',
            'plan_suscripcion' => $nuevoPlan,
            'uso_plan' => $usoPlan
        ]);
        break;

    // ----------------------------------------------------------
    // 6. SOLICITAR O EMITIR CORREOS INSTITUCIONALES (@empresa.sysmonitor.com)
    // ----------------------------------------------------------
    case 'companies/provision-email':
        $auth = requireJwtAuth();
        if ($method !== 'POST') {
            jsonResponse(405, ['ok' => false, 'error' => 'Usa POST.']);
        }

        $empresaModel = new Empresa();
        $mailerService = new MailerService();
        // Si se envía id_solicitud y es SuperAdmin, aprueba y genera el correo institucional
        if (!empty($body['id_solicitud'])) {
            $creado = $empresaModel->aprobarYGenerarCorreoInstitucional(
                (int)$body['id_solicitud'],
                $body['correo_personalizado'] ?? null,
                $body['password_temporal'] ?? null
            );
            if (!$creado) {
                jsonResponse(400, ['ok' => false, 'error' => 'No fue posible emitir el correo institucional.']);
            }
            jsonResponse(201, [
                'ok' => true,
                'message' => 'Correo institucional creado y notificado por SMTP.',
                'credenciales_institucionales' => $creado
            ]);
        }

        // Si el Representante o Jefe de TI solicita una nueva cuenta para su equipo
        $idEmpresa = !empty($body['id_empresa']) && (int)($auth['id_rol'] ?? 0) === 2
            ? (int)$body['id_empresa']
            : (resolverIdEmpresaUsuario($auth, $body, $empresaModel) ?? 1);

        $nombre = trim($body['nombre'] ?? '');
        $apellidos = trim($body['apellidos'] ?? '');
        $rolSolicitado = ($body['rol_solicitado'] ?? 'operador') === 'jefe_ti' ? 'jefe_ti' : 'operador';

        if (!$nombre || !$apellidos) {
            jsonResponse(400, ['ok' => false, 'error' => 'Debes indicar nombre y apellidos del colaborador.']);
        }

        if (method_exists($empresaModel, 'puedeSolicitarCuenta') && !$empresaModel->puedeSolicitarCuenta($idEmpresa)) {
            jsonResponse(403, [
                'ok' => false,
                'error' => 'Has alcanzado el límite de cuentas institucionales de tu Plan B2B. Actualiza tu plan para solicitar más.'
            ]);
        }

        $okSolicitud = $empresaModel->crearSolicitudCuenta($idEmpresa, $nombre, $apellidos, $rolSolicitado);
        if (!$okSolicitud) {
            jsonResponse(500, ['ok' => false, 'error' => 'No se pudo registrar la solicitud de correo institucional.']);
        }

        jsonResponse(201, [
            'ok' => true,
            'message' => 'Solicitud de correo institucional registrada correctamente.',
            'rol_solicitado' => $rolSolicitado,
            'solicitudes' => $empresaModel->obtenerSolicitudesPorEmpresa($idEmpresa),
            'correos_enviados' => $mailerService->obtenerCorreos($idEmpresa, 25),
            'uso_plan' => $empresaModel->obtenerUsoPlan($idEmpresa)
        ]);
        break;

    // ----------------------------------------------------------
    // 7. RACKS IOT Y UMBRALES (CONSULTA Y GESTIÓN POR JEFE DE TI / REPRESENTANTE)
    // ----------------------------------------------------------
    case 'racks':
        $auth = requireJwtAuth();
        $sensorModel = new Sensor();
        $empresaModel = new Empresa();
        $idEmpresa = resolverIdEmpresaUsuario($auth, $body, $empresaModel);

        if ($method === 'GET') {
            $racks = $idEmpresa ? $sensorModel->obtenerRacksConUltimaLectura($idEmpresa) : [];
            jsonResponse(200, [
                'ok' => true,
                'id_empresa' => $idEmpresa,
                'total_racks' => count($racks),
                'racks' => $racks
            ]);
        }

        if ($method === 'POST') {
            if (!in_array((int)$auth['id_rol'], [2, 3, 4])) {
                jsonResponse(403, ['ok' => false, 'error' => 'Solo el Jefe de TI, Representante o SuperAdmin pueden crear racks o modificar umbrales.']);
            }
            if (!$idEmpresa) {
                jsonResponse(400, ['ok' => false, 'error' => 'Debes registrar una empresa antes de crear racks IoT.']);
            }

            if (!empty($body['id_rack'])) {
                $ok = $sensorModel->actualizarUmbralesRack(
                    (int)$body['id_rack'],
                    $idEmpresa,
                    trim($body['nombre_rack'] ?? 'Rack Actualizado'),
                    trim($body['ubicacion'] ?? 'Site Principal'),
                    (float)($body['temp_max'] ?? 27.0),
                    (float)($body['hum_max'] ?? 60.0),
                    (float)($body['kw_max'] ?? 2.5)
                );
                jsonResponse(200, ['ok' => $ok, 'message' => 'Umbrales del rack actualizados correctamente.']);
            }

            if (method_exists($empresaModel, 'puedeCrearRack') && !$empresaModel->puedeCrearRack($idEmpresa)) {
                jsonResponse(403, [
                    'ok' => false,
                    'error' => 'Límite de racks alcanzado para tu plan B2B actual. Mejora tu plan para agregar más racks.'
                ]);
            }

            $idNuevoRack = $sensorModel->crearRack(
                $idEmpresa,
                strtoupper(trim($body['codigo_rack'] ?? 'RACK-API')),
                trim($body['nombre_rack'] ?? 'Rack Creado por API'),
                trim($body['ubicacion'] ?? 'Site Principal'),
                (float)($body['temp_max'] ?? 27.0),
                (float)($body['hum_max'] ?? 60.0),
                (float)($body['kw_max'] ?? 2.5)
            );
            jsonResponse(201, ['ok' => true, 'id_rack' => $idNuevoRack, 'message' => 'Nuevo Rack IoT registrado.']);
        }
        break;

    // ----------------------------------------------------------
    // 8. TELEMETRÍA IOT (CONSULTA E INGESTA DESDE SENSORES / POSTMAN)
    // ----------------------------------------------------------
    case 'telemetry':
        $sensorModel = new Sensor();

        if ($method === 'GET') {
            $auth = requireJwtAuth();
            $idEmpresa = resolverIdEmpresaUsuario($auth, [], new Empresa());
            $idRack = isset($_GET['id_rack']) ? (int)$_GET['id_rack'] : null;
            $historial = $idEmpresa ? $sensorModel->obtenerHistorialLecturas($idEmpresa, $idRack, 20) : [];
            jsonResponse(200, [
                'ok' => true,
                'total_lecturas' => count($historial),
                'historial' => $historial
            ]);
        }

        if ($method === 'POST') {
            // Permite autenticar con X-API-Key del Rack IoT (ESP32) O con Bearer JWT
            $headers = function_exists('getallheaders') ? getallheaders() : [];
            $iotApiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? ($_SERVER['HTTP_X_API_KEY'] ?? ($body['api_key_iot'] ?? ''));
            $idRack = (int)($body['id_rack'] ?? 0);

            if (!empty($iotApiKey)) {
                $rack = $sensorModel->obtenerRackPorApiKey($iotApiKey);
                if (!$rack) {
                    jsonResponse(401, ['ok' => false, 'error' => 'X-API-Key de sensor IoT no válida.']);
                }
                $idRack = (int)$rack['id_rack'];
            } else {
                requireJwtAuth();
                if ($idRack <= 0) $idRack = 1;
            }

            $temperatura = (float)($body['temperatura'] ?? 22.5);
            $humedad = (float)($body['humedad'] ?? 45.0);
            $consumoKw = (float)($body['consumo_kw'] ?? 1.15);
            $puertaAbierta = !empty($body['puerta_abierta']) ? 1 : 0;
            $voltaje = (float)($body['voltaje'] ?? 120.0);

            $resultado = $sensorModel->registrarLectura($idRack, $temperatura, $humedad, $consumoKw, $puertaAbierta, $voltaje);
            if (!$resultado) {
                jsonResponse(404, ['ok' => false, 'error' => 'El Rack indicado no existe.']);
            }

            jsonResponse(201, [
                'ok' => true,
                'message' => 'Lectura de telemetría IoT almacenada y evaluada.',
                'resultado' => $resultado
            ]);
        }
        break;

    // ----------------------------------------------------------
    // 9. ALERTAS DE INFRAESTRUCTURA
    // ----------------------------------------------------------
    case 'alerts':
        $auth = requireJwtAuth();
        $sensorModel = new Sensor();
        $idEmpresa = resolverIdEmpresaUsuario($auth, [], new Empresa());
        $alertas = $idEmpresa ? $sensorModel->obtenerAlertas($idEmpresa, false, 25) : [];
        jsonResponse(200, [
            'ok' => true,
            'total' => count($alertas),
            'alertas' => $alertas
        ]);
        break;

    case 'alerts/resolve':
        $auth = requireJwtAuth();
        if ($method !== 'POST') {
            jsonResponse(405, ['ok' => false, 'error' => 'Usa POST.']);
        }
        $idEmpresa = resolverIdEmpresaUsuario($auth, $body, new Empresa());
        $idAlerta = (int)($body['id_alerta'] ?? 0);
        $ok = $idEmpresa ? (new Sensor())->resolverAlerta($idAlerta, $idEmpresa) : false;
        jsonResponse(200, ['ok' => $ok, 'message' => 'Alerta marcada como resuelta.']);
        break;

    default:
        jsonResponse(404, [
            'ok' => false,
            'error' => "Endpoint '$endpoint' no encontrado en /api/v1/.",
            'ayuda' => 'Consulta GET /api/v1/index.php?endpoint=status para ver el catálogo completo.'
        ]);
}
