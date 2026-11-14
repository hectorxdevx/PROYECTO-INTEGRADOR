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
                'POST /api/v1/index.php?endpoint=auth/login' => 'Autenticación institucional (Jefe de TI, Operador, SuperAdmin)',
                'POST /api/v1/index.php?endpoint=auth/oauth' => 'Autenticación OAuth 2.0 Google/Microsoft (Representantes)',
                'POST /api/v1/index.php?endpoint=auth/mfa-verify' => 'Verificación de 6 dígitos Google Authenticator y emisión de JWT',
                'GET  /api/v1/index.php?endpoint=companies' => 'Listar empresas registradas',
                'POST /api/v1/index.php?endpoint=companies' => 'Registrar nueva empresa (Representante)',
                'POST /api/v1/index.php?endpoint=companies/provision-email' => 'Solicitar o generar correo institucional (@empresa.sysmonitor.com)',
                'GET  /api/v1/index.php?endpoint=racks' => 'Obtener racks y sensores IoT en tiempo real',
                'POST /api/v1/index.php?endpoint=racks' => 'Crear rack o actualizar umbrales de alerta (Jefe de TI)',
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
                'empresa' => $usuario['nombre_empresa'] ?? 'SysMonitor Global'
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
                'correo' => $usuario['correo_electronico'],
                'rol' => 'representante',
                'id_rol' => 3
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
        $jwtFinal = JwtHelper::generarToken([
            'id_usuario' => (int)$usuario['id_usuario'],
            'id_empresa' => $usuario['id_empresa'] ? (int)$usuario['id_empresa'] : 1,
            'id_rol' => (int)$usuario['id_rol'],
            'rol' => $usuario['nombre_rol'] ?? 'operador',
            'correo' => $usuario['correo_electronico'],
            'mfa_verified' => true
        ]);

        $usuarioModel->registrarBitacora(
            $usuario['id_empresa'] ? (int)$usuario['id_empresa'] : null,
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
                'id_empresa' => $usuario['id_empresa'] ? (int)$usuario['id_empresa'] : 1,
                'nombre' => $usuario['nombre_usuario'] . ' ' . $usuario['apellidos'],
                'correo' => $usuario['correo_electronico'],
                'id_rol' => (int)$usuario['id_rol'],
                'rol' => $usuario['nombre_rol'] ?? 'operador',
                'empresa' => $usuario['nombre_empresa'] ?? 'InfraOps Cloud & DataCenter'
            ]
        ]);
        break;

    // ----------------------------------------------------------
    // 5. EMPRESAS (LISTAR Y REGISTRAR POR REPRESENTANTE)
    // ----------------------------------------------------------
    case 'companies':
        $auth = requireJwtAuth();
        $empresaModel = new Empresa();

        if ($method === 'GET') {
            $empresas = $empresaModel->obtenerTodas();
            jsonResponse(200, ['ok' => true, 'total' => count($empresas), 'empresas' => $empresas]);
        }

        if ($method === 'POST') {
            $nombreEmpresa = trim($body['nombre_empresa'] ?? '');
            $rfcNit = strtoupper(trim($body['rfc_nit'] ?? ''));
            $slugDominio = strtolower(trim($body['slug_dominio'] ?? ''));
            $sector = trim($body['sector'] ?? 'DataCenter & Cloud');
            $telefono = trim($body['telefono'] ?? '+52 999 000 0000');

            if (!$nombreEmpresa || !$rfcNit || !$slugDominio) {
                jsonResponse(400, ['ok' => false, 'error' => 'Faltan campos requeridos: nombre_empresa, rfc_nit, slug_dominio.']);
            }

            $idEmpresa = $empresaModel->registrarEmpresa((int)$auth['id_usuario'], $nombreEmpresa, $rfcNit, $slugDominio, $sector, $telefono);
            if (!$idEmpresa) {
                jsonResponse(409, ['ok' => false, 'error' => 'No se pudo registrar la empresa (el dominio podría estar en uso).']);
            }

            jsonResponse(201, [
                'ok' => true,
                'message' => 'Empresa registrada exitosamente. Pendiente de aprobación por SuperAdmin.',
                'id_empresa' => $idEmpresa,
                'dominio_institucional' => $slugDominio . '.' . INSTITUTIONAL_BASE_DOMAIN
            ]);
        }
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
                'message' => 'Correo institucional creado con éxito.',
                'credenciales_institucionales' => $creado
            ]);
        }

        // Si el Representante solicita una nueva cuenta para su Jefe de TI u Operador
        $idEmpresa = (int)($body['id_empresa'] ?? $auth['id_empresa'] ?? 1);
        $nombre = trim($body['nombre'] ?? '');
        $apellidos = trim($body['apellidos'] ?? '');
        $rolSolicitado = ($body['rol_solicitado'] ?? 'operador') === 'jefe_ti' ? 'jefe_ti' : 'operador';

        if (!$nombre || !$apellidos) {
            jsonResponse(400, ['ok' => false, 'error' => 'Debes indicar nombre y apellidos del colaborador.']);
        }

        $empresaModel->crearSolicitudCuenta($idEmpresa, $nombre, $apellidos, $rolSolicitado);
        jsonResponse(201, [
            'ok' => true,
            'message' => 'Solicitud de correo institucional registrada correctamente.',
            'rol_solicitado' => $rolSolicitado
        ]);
        break;

    // ----------------------------------------------------------
    // 7. RACKS IOT Y UMBRALES (CONSULTA Y GESTIÓN POR JEFE DE TI)
    // ----------------------------------------------------------
    case 'racks':
        $auth = requireJwtAuth();
        $sensorModel = new Sensor();
        $idEmpresa = isset($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : (int)($auth['id_empresa'] ?? 1);

        if ($method === 'GET') {
            $racks = $sensorModel->obtenerRacksConUltimaLectura($idEmpresa);
            jsonResponse(200, [
                'ok' => true,
                'id_empresa' => $idEmpresa,
                'total_racks' => count($racks),
                'racks' => $racks
            ]);
        }

        if ($method === 'POST') {
            if (!in_array((int)$auth['id_rol'], [2, 4])) {
                jsonResponse(403, ['ok' => false, 'error' => 'Solo el Jefe de TI o SuperAdmin pueden crear racks o modificar umbrales.']);
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
            $idRack = isset($_GET['id_rack']) ? (int)$_GET['id_rack'] : null;
            $historial = $sensorModel->obtenerHistorialLecturas((int)($auth['id_empresa'] ?? 1), $idRack, 20);
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
        $alertas = $sensorModel->obtenerAlertas((int)($auth['id_empresa'] ?? 1), false, 25);
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
        $idAlerta = (int)($body['id_alerta'] ?? 0);
        $ok = (new Sensor())->resolverAlerta($idAlerta, (int)($auth['id_empresa'] ?? 1));
        jsonResponse(200, ['ok' => $ok, 'message' => 'Alerta marcada como resuelta.']);
        break;

    default:
        jsonResponse(404, [
            'ok' => false,
            'error' => "Endpoint '$endpoint' no encontrado en /api/v1/.",
            'ayuda' => 'Consulta GET /api/v1/index.php?endpoint=status para ver el catálogo completo.'
        ]);
}
