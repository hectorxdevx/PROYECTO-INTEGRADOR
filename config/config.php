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

