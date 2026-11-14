<?php
require_once __DIR__ . '/../config/config.php';

/**
 * JwtHelper - Generación y validación de JSON Web Tokens (HS256) para la API REST y App Móvil Flutter.
 */
class JwtHelper {

    public static function generarToken(array $payload, int $expiracionSegundos = JWT_EXPIRE_SECONDS): string {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + $expiracionSegundos;

        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function validarToken(string $token): ?array {
        $partes = explode('.', $token);
        if (count($partes) !== 3) {
            return null;
        }

        [$header64, $payload64, $signature64] = $partes;
        $firmaEsperada = self::base64UrlEncode(
            hash_hmac('sha256', $header64 . "." . $payload64, JWT_SECRET, true)
        );

        if (!hash_equals($firmaEsperada, $signature64)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payload64), true);
        if (!is_array($payload)) {
            return null;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    public static function obtenerBearerToken(): ?string {
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
?>
