<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ruta base del proyecto
define('BASE_URL', 'http://localhost/PROYECTO-INTEGRADOR');

// Configuración de la base de datos (MariaDB)
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'mariadb');
define('DB_NAME', 'server_monitor');
define('DB_PORT', 3307);

// ==========================================================
// SEGURIDAD EXTREMA: JWT, MFA (Google Authenticator) y OAuth 2.0
// ==========================================================
define('JWT_SECRET', 'sysmonitor_iot_extreme_security_jwt_key_2026_!#987');
define('JWT_EXPIRE_SECONDS', 86400); // 24 horas
define('MFA_ISSUER', 'SysMonitor IoT');
define('INSTITUTIONAL_BASE_DOMAIN', 'sysmonitor.com');

// Credenciales OAuth 2.0 (Google Cloud Console / Microsoft Entra ID)
// Si están en 'SANDBOX_MODE', el sistema habilita el selector OAuth simulado para pruebas locales inmediatas.
define('OAUTH_SANDBOX_MODE', true);

define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', BASE_URL . '/index.php?page=oauth_callback&provider=google');

define('MICROSOFT_CLIENT_ID', getenv('MICROSOFT_CLIENT_ID') ?: 'YOUR_MICROSOFT_CLIENT_ID');
define('MICROSOFT_CLIENT_SECRET', getenv('MICROSOFT_CLIENT_SECRET') ?: 'YOUR_MICROSOFT_CLIENT_SECRET');
define('MICROSOFT_REDIRECT_URI', BASE_URL . '/index.php?page=oauth_callback&provider=microsoft');

// ==========================================================
// CARGA SEGURA DE VARIABLES LOCALES (.env)
// ==========================================================
$__envFile = __DIR__ . '/../.env';
if (is_file($__envFile) && is_readable($__envFile)) {
    $__lines = @file($__envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($__lines)) {
        foreach ($__lines as $__line) {
            $__trimmed = trim($__line);
            if ($__trimmed === '' || $__trimmed[0] === '#' || $__trimmed[0] === ';') {
                continue;
            }
            if (str_starts_with($__trimmed, 'export ')) {
                $__trimmed = trim(substr($__trimmed, 7));
            }
            $__eqPos = strpos($__trimmed, '=');
            if ($__eqPos === false) {
                continue;
            }
            $__key = trim(substr($__trimmed, 0, $__eqPos));
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $__key)) {
                continue;
            }
            $__val = trim(substr($__trimmed, $__eqPos + 1));
            $__len = strlen($__val);
            if ($__len >= 2 && (($__val[0] === '"' && $__val[$__len - 1] === '"') || ($__val[0] === "'" && $__val[$__len - 1] === "'"))) {
                $__quote = $__val[0];
                $__val = substr($__val, 1, -1);
                if ($__quote === '"') {
                    $__val = str_replace(['\"', '\\n', '\\r'], ['"', "\n", "\r"], $__val);
                }
            } else {
                $__val = trim((string)preg_replace('/\s+#.*$/', '', $__val));
            }
            if (!array_key_exists($__key, $_ENV)) {
                $_ENV[$__key] = $__val;
            }
            if (!array_key_exists($__key, $_SERVER)) {
                $_SERVER[$__key] = $__val;
            }
        }
    }
}
unset($__envFile, $__lines, $__line, $__trimmed, $__eqPos, $__key, $__val, $__len, $__quote);

$__envOrDefault = static function (string $key, string $default = ''): string {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string)$_ENV[$key];
    }
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return (string)$val;
    }
    return $default;
};

// ==========================================================
// CONFIGURACIÓN SMTP (CORREO CORPORATIVO Y CONTACTO WEB)
// ==========================================================
define('SMTP_HOST', $__envOrDefault('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int)$__envOrDefault('SMTP_PORT', '587'));
define('SMTP_USER', $__envOrDefault('SMTP_USER', ''));
define('SMTP_PASS', $__envOrDefault('SMTP_PASS', ''));
define('SMTP_FROM_EMAIL', $__envOrDefault('SMTP_FROM_EMAIL', SMTP_USER !== '' ? SMTP_USER : 'no-reply@sysmonitor.com'));
define('SMTP_FROM_NAME', $__envOrDefault('SMTP_FROM_NAME', 'SysMonitor IoT Seguridad'));
define('SMTP_ADMIN_EMAIL', $__envOrDefault('SMTP_ADMIN_EMAIL', 'admin@sysmonitor.com'));
define('SMTP_ENABLED', (trim(SMTP_USER) !== '' && trim(SMTP_PASS) !== ''));
unset($__envOrDefault);

// ==========================================================
// PLANES B2B SYSMONITOR IOT
// ==========================================================
define('B2B_PLANS', [
    'starter' => [
        'nombre' => 'Starter',
        'precio' => 49.00,
        'max_racks' => 2,
        'max_cuentas' => 3,
        'descripcion' => 'Pequeñas empresas / 1 site'
    ],
    'corporate' => [
        'nombre' => 'Corporate',
        'precio' => 149.00,
        'max_racks' => 10,
        'max_cuentas' => 15,
        'descripcion' => 'Empresas medianas / multi-rack'
    ],
    'enterprise' => [
        'nombre' => 'Enterprise',
        'precio' => 399.00,
        'max_racks' => 999,
        'max_cuentas' => 999,
        'descripcion' => 'DataCenters / cuentas ilimitadas'
    ]
]);

