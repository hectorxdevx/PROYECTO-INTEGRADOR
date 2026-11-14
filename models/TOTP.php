<?php
/**
 * TOTP (RFC 6238) - Implementación nativa compatible con Google Authenticator y Microsoft Authenticator.
 */
class TOTP {
    private static $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Genera una llave secreta en formato Base32 de 16 caracteres (80 bits).
     */
    public static function generarSecreto(int $longitud = 16): string {
        $secreto = '';
        $max = strlen(self::$base32Chars) - 1;
        for ($i = 0; $i < $longitud; $i++) {
            $secreto .= self::$base32Chars[random_int(0, $max)];
        }
        return $secreto;
    }

    /**
     * Genera la URI otpauth:// estándar para escanear en Google Authenticator.
     */
    public static function obtenerOtpAuthUrl(string $correo, string $secreto, string $emisor = 'SysMonitor IoT'): string {
        $etiqueta = rawurlencode($emisor . ':' . $correo);
        $params = http_build_query([
            'secret' => $secreto,
            'issuer' => $emisor,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30
        ]);
        return "otpauth://totp/{$etiqueta}?{$params}";
    }

    /**
     * Genera la URL de imagen del código QR lista para mostrar en pantalla.
     */
    public static function obtenerQrImageUrl(string $correo, string $secreto, string $emisor = 'SysMonitor IoT'): string {
        $otpUrl = self::obtenerOtpAuthUrl($correo, $secreto, $emisor);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=10&data=' . urlencode($otpUrl);
    }

    /**
     * Calcula el código de 6 dígitos para un instante de tiempo dado.
     */
    public static function obtenerCodigo(string $secreto, ?int $timeSlice = null): string {
        if ($timeSlice === null) {
            $timeSlice = (int)floor(time() / 30);
        }

        $secretKey = self::base32Decode($secreto);
        // Empaquetar timeSlice en binario de 8 bytes big-endian
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        $hm = hash_hmac('SHA1', $time, $secretKey, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashpart = substr($hm, $offset, 4);

        $value = unpack('N', $hashpart)[1];
        $value = $value & 0x7FFFFFFF;

        $modulo = 10 ** 6;
        return str_pad((string)($value % $modulo), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica si el código ingresado por el usuario coincide con el de Google Authenticator
     * (incluye una ventana de ±1 intervalo de 30s para compensar desfase de reloj).
     */
    public static function verificarCodigo(string $secreto, string $codigoUsuario, int $discrepancia = 1): bool {
        $codigoLimpio = preg_replace('/\s+/', '', trim($codigoUsuario));
        if (strlen($codigoLimpio) !== 6 || !ctype_digit($codigoLimpio)) {
            return false;
        }

        $currentTimeSlice = (int)floor(time() / 30);
        for ($i = -$discrepancia; $i <= $discrepancia; $i++) {
            $calculado = self::obtenerCodigo($secreto, $currentTimeSlice + $i);
            if (hash_equals($calculado, $codigoLimpio)) {
                return true;
            }
        }
        return false;
    }

    private static function base32Decode(string $secreto): string {
        $secreto = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secreto));
        if (empty($secreto)) {
            return '';
        }

        $buffer = 0;
        $bitsLeft = 0;
        $result = '';

        for ($i = 0, $len = strlen($secreto); $i < $len; $i++) {
            $val = strpos(self::$base32Chars, $secreto[$i]);
            if ($val === false) continue;

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $result .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }
        return $result;
    }
}
?>
