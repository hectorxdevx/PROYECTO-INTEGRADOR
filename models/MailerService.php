<?php
require_once __DIR__ . '/Database.php';

class MailerService {
    private ?mysqli $db = null;

    public function __construct(?mysqli $conexion = null) {
        if ($conexion !== null) {
            $this->db = $conexion;
        } else {
            $this->db = (new Database())->conectar();
        }
    }

    /**
     * Limpia cabeceras de correo para neutralizar ataques de CRLF / Header Injection.
     */
    public static function sanitizarCabecera(string $valor): string {
        $limpio = preg_replace('/%(?:0[ad0]|00)/i', ' ', $valor);
        $limpio = preg_replace('/[\x00-\x1F\x7F]+/', ' ', (string)$limpio);
        $limpio = preg_replace('/\b(?:bcc|cc|content-type|mime-version|content-transfer-encoding|reply-to|x-mailer)\s*:/i', '', (string)$limpio);
        return trim((string)preg_replace('/\s{2,}/', ' ', (string)$limpio));
    }

    /**
     * Valida solicitudes del formulario de contacto web contra honeypot, XSS, enlaces masivos y rate limit por IP.
     */
    public function verificarFiltroSpam(string $ip, string $correo, string $mensaje, string $honeypot = ''): array {
        if (trim($honeypot) !== '') {
            return [
                'ok' => false,
                'motivo' => 'Solicitud bloqueada por protección anti-bot (honeypot).'
            ];
        }

        $correoTrim = trim($correo);
        $correoLimpio = self::sanitizarCabecera($correoTrim);
        if ($correoTrim === '' || $correoLimpio !== $correoTrim || !filter_var($correoLimpio, FILTER_VALIDATE_EMAIL)) {
            return [
                'ok' => false,
                'motivo' => 'El correo electrónico proporcionado no es válido.'
            ];
        }

        $mensajeTrim = trim($mensaje);
        $longitud = function_exists('mb_strlen') ? mb_strlen($mensajeTrim, 'UTF-8') : strlen($mensajeTrim);
        if ($longitud < 3) {
            return [
                'ok' => false,
                'motivo' => 'El mensaje es demasiado corto o está vacío.'
            ];
        }
        if ($longitud > 4000) {
            return [
                'ok' => false,
                'motivo' => 'El mensaje excede el máximo permitido (4000 caracteres).'
            ];
        }

        // Bloquear payloads XSS / HTML malicioso
        $patronesXss = [
            '/<\s*(?:script|iframe|object|embed|svg|style|link|meta|form)\b/i',
            '/\bon(?:error|load|click|mouseover|focus|submit|input|change)\s*=/i',
            '/(?:javascript|vbscript)\s*:|data\s*:\s*text\/html/i',
            '/<\?php/i'
        ];
        foreach ($patronesXss as $patron) {
            if (preg_match($patron, $mensajeTrim)) {
                return [
                    'ok' => false,
                    'motivo' => 'Mensaje bloqueado por filtro WAF (etiquetas o scripts no permitidos).'
                ];
            }
        }

        // Máximo 3 URLs permitidas en el cuerpo del mensaje
        if (preg_match_all('/\b(?:https?:\/\/|ftp:\/\/|www\.)[^\s<>"\']+/i', $mensajeTrim, $matches)) {
            if (count($matches[0]) > 3) {
                return [
                    'ok' => false,
                    'motivo' => 'Mensaje bloqueado por exceso de enlaces externos (máximo 3 URLs).'
                ];
            }
        }

        // Rate limit en BD: máximo 3 envíos contacto_web desde la misma IP en los últimos 15 minutos
        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) AS total
                 FROM correos_enviados
                 WHERE ip_origen = ?
                   AND tipo_correo = 'contacto_web'
                   AND estado_smtp <> 'bloqueado_filtro'
                   AND fecha_envio >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
            );
            if ($stmt) {
                $stmt->bind_param("s", $ip);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ((int)($row['total'] ?? 0) >= 3) {
                    return [
                        'ok' => false,
                        'motivo' => 'Límite de envíos alcanzado (máximo 3 mensajes cada 15 minutos por IP).'
                    ];
                }
            }
        }

        return ['ok' => true, 'motivo' => 'OK'];
    }

    /**
     * Envía un correo por SMTP nativo (si SMTP_ENABLED está activo) o lo registra en cola local auditable.
     */
    public function enviarCorreo(
        string $destinatario,
        string $asunto,
        string $cuerpoHtml,
        string $tipoCorreo = 'sistema',
        ?int $idEmpresa = null,
        ?string $replyTo = null
    ): array {
        $destinatarioLimpio = self::sanitizarCabecera($destinatario);
        $asuntoLimpio = mb_substr(self::sanitizarCabecera($asunto), 0, 255, 'UTF-8');
        $replyToLimpio = null;
        if ($replyTo !== null && trim($replyTo) !== '') {
            $candidato = self::sanitizarCabecera($replyTo);
            if (filter_var($candidato, FILTER_VALIDATE_EMAIL)) {
                $replyToLimpio = $candidato;
            }
        }

        if (!filter_var($destinatarioLimpio, FILTER_VALIDATE_EMAIL)) {
            return [
                'ok' => false,
                'id_correo' => 0,
                'estado_smtp' => 'error_validacion',
                'mensaje' => 'El correo destinatario no es válido.'
            ];
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $estadoSmtp = 'cola_local_auditable';
        $detalle = 'SMTP sin credenciales en .env; correo almacenado en cola local auditable.';

        $smtpHabilitado = defined('SMTP_ENABLED') && SMTP_ENABLED === true
            && defined('SMTP_USER') && trim((string)SMTP_USER) !== ''
            && defined('SMTP_PASS') && trim((string)SMTP_PASS) !== '';

        if ($smtpHabilitado) {
            $resSmtp = $this->enviarPorSocketSmtp($destinatarioLimpio, $asuntoLimpio, $cuerpoHtml, $replyToLimpio);
            if ($resSmtp['ok']) {
                $estadoSmtp = 'enviado_smtp';
            } else {
                $estadoSmtp = 'cola_local_auditable';
            }
            $detalle = $resSmtp['detalle'];
        }

        $idCorreo = $this->registrarCorreoEnBd(
            $idEmpresa,
            $destinatarioLimpio,
            $replyToLimpio,
            $asuntoLimpio,
            $cuerpoHtml,
            $tipoCorreo,
            $estadoSmtp,
            $detalle,
            $ip
        );

        return [
            'ok' => true,
            'id_correo' => $idCorreo,
            'estado_smtp' => $estadoSmtp,
            'detalle' => $detalle,
            'mensaje' => $estadoSmtp === 'enviado_smtp'
                ? 'Correo enviado exitosamente vía SMTP.'
                : 'Correo guardado en cola local auditable.'
        ];
    }

    /**
     * Genera y envía el correo institucional corporativo (Minimalist Blue) con credenciales + secreto MFA.
     */
    public function enviarCredencialesInstitucionales(array $datosEmpresa, array $credenciales, string $correoDestinoRepresentante): array {
        $idEmpresa = isset($datosEmpresa['id_empresa']) ? (int)$datosEmpresa['id_empresa'] : null;
        $nombreEmpresa = (string)($datosEmpresa['nombre_empresa'] ?? 'Tu Organización');
        $dominio = (string)($datosEmpresa['slug_dominio'] ?? $datosEmpresa['dominio_institucional'] ?? INSTITUTIONAL_BASE_DOMAIN);

        $planKey = strtolower((string)($datosEmpresa['plan_suscripcion'] ?? $datosEmpresa['plan'] ?? 'corporate'));
        $planes = defined('B2B_PLANS') ? B2B_PLANS : [];
        $planInfo = $planes[$planKey] ?? [
            'nombre' => ucfirst($planKey),
            'precio' => 149.00,
            'max_racks' => 10,
            'max_cuentas' => 15,
        ];

        $rolRaw = (string)($credenciales['rol'] ?? 'operador');
        $rolLegible = ($rolRaw === 'jefe_ti') ? 'Jefe de TI Institucional' : 'Operador Institucional';
        $correoInst = (string)($credenciales['correo_institucional'] ?? '');
        $passwordTemp = (string)($credenciales['password_temporal'] ?? '');
        $mfaSecret = (string)($credenciales['mfa_secret'] ?? '');

        $esc = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        $asunto = "SysMonitor IoT | Credenciales Institucionales ({$rolLegible}) - {$nombreEmpresa}";
        $cuerpoHtml = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>'
            . '<body style="margin:0;padding:24px;background-color:#F8FAFC;font-family:\'Segoe UI\',Arial,sans-serif;color:#0F172A;">'
            . '<div style="max-width:620px;margin:0 auto;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:16px;overflow:hidden;">'
            . '<div style="background:linear-gradient(135deg,#1D4ED8,#2563EB);padding:28px 32px;color:#FFFFFF;">'
            . '<div style="font-size:12px;letter-spacing:1.2px;text-transform:uppercase;opacity:0.85;">SysMonitor IoT · Seguridad Extrema B2B</div>'
            . '<h1 style="margin:8px 0 0;font-size:22px;font-weight:700;">Credenciales Institucionales Emitidas</h1>'
            . '</div>'
            . '<div style="padding:28px 32px;">'
            . '<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">'
            . 'Se ha aprovisionado una nueva cuenta institucional activa para <strong>' . $esc($nombreEmpresa) . '</strong> '
            . '(<span style="color:#1D4ED8;font-weight:600;">' . $esc($dominio) . '</span>).'
            . '</p>'
            . '<div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:12px;padding:16px 20px;margin-bottom:20px;">'
            . '<div style="font-size:12px;text-transform:uppercase;color:#1E40AF;font-weight:700;margin-bottom:6px;">Plan B2B Activo</div>'
            . '<div style="font-size:15px;font-weight:700;color:#0F172A;">Plan ' . $esc((string)$planInfo['nombre'])
            . ' · $' . number_format((float)$planInfo['precio'], 2) . ' USD/mes</div>'
            . '<div style="font-size:13px;color:#475569;margin-top:4px;">Capacidad: hasta ' . (int)$planInfo['max_racks']
            . ' racks IoT y ' . (int)$planInfo['max_cuentas'] . ' cuentas institucionales.</div>'
            . '</div>'
            . '<table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:20px;border:1px solid #E2E8F0;border-radius:12px;">'
            . '<tr><td style="padding:12px 16px;border-bottom:1px solid #E2E8F0;color:#64748B;font-size:13px;width:42%;">Rol Asignado</td>'
            . '<td style="padding:12px 16px;border-bottom:1px solid #E2E8F0;font-weight:600;color:#0F172A;">' . $esc($rolLegible) . '</td></tr>'
            . '<tr><td style="padding:12px 16px;border-bottom:1px solid #E2E8F0;color:#64748B;font-size:13px;">Correo Institucional</td>'
            . '<td style="padding:12px 16px;border-bottom:1px solid #E2E8F0;font-family:Consolas,monospace;font-weight:700;color:#1D4ED8;">' . $esc($correoInst) . '</td></tr>'
            . '<tr><td style="padding:12px 16px;border-bottom:1px solid #E2E8F0;color:#64748B;font-size:13px;">Contraseña Temporal</td>'
            . '<td style="padding:12px 16px;border-bottom:1px solid #E2E8F0;font-family:Consolas,monospace;font-weight:700;color:#0F172A;">' . $esc($passwordTemp)
            . ' <span style="display:block;font-family:\'Segoe UI\',Arial,sans-serif;font-weight:400;font-size:12px;color:#DC2626;margin-top:2px;">Uso para primer inicio de sesión (cambio recomendado).</span></td></tr>'
            . '<tr><td style="padding:12px 16px;color:#64748B;font-size:13px;">Secreto MFA TOTP (Base32)</td>'
            . '<td style="padding:12px 16px;font-family:Consolas,monospace;font-weight:700;letter-spacing:1px;color:#0F172A;">' . $esc($mfaSecret) . '</td></tr>'
            . '</table>'
            . '<div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:16px 20px;">'
            . '<div style="font-size:13px;font-weight:700;color:#0F172A;margin-bottom:8px;">Configuración de Segundo Factor (MFA Obligatorio)</div>'
            . '<ol style="margin:0;padding-left:18px;font-size:13px;line-height:1.6;color:#475569;">'
            . '<li>Abre <strong>Google Authenticator</strong> o <strong>Microsoft Authenticator</strong> en tu teléfono.</li>'
            . '<li>Selecciona <em>Agregar cuenta</em> &rarr; <em>Ingresar clave de configuración</em>.</li>'
            . '<li>Introduce la clave Base32 <strong>' . $esc($mfaSecret) . '</strong> en modo <em>Basado en tiempo (TOTP)</em>.</li>'
            . '<li>Inicia sesión en SysMonitor IoT con el correo institucional y valida el código dinámico de 6 dígitos.</li>'
            . '</ol>'
            . '</div>'
            . '<p style="margin:20px 0 0;font-size:12px;color:#94A3B8;text-align:center;">Mensaje automático de infraestructura · SysMonitor IoT</p>'
            . '</div></div></body></html>';

        return $this->enviarCorreo(
            $correoDestinoRepresentante,
            $asunto,
            $cuerpoHtml,
            'credenciales_ti',
            $idEmpresa
        );
    }

    /**
     * Evalúa el filtro anti-spam/WAF para el formulario de contacto web y envía al administrador o bloquea.
     */
    public function enviarDudaContactoWeb(
        string $nombre,
        string $correoVisitante,
        string $asunto,
        string $mensaje,
        string $honeypot = ''
    ): array {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $nombreLimpio = self::sanitizarCabecera($nombre);
        $correoLimpio = self::sanitizarCabecera($correoVisitante);
        $asuntoLimpio = self::sanitizarCabecera($asunto);
        $adminEmail = defined('SMTP_ADMIN_EMAIL') && SMTP_ADMIN_EMAIL !== '' ? SMTP_ADMIN_EMAIL : 'admin@sysmonitor.com';

        $filtro = $this->verificarFiltroSpam($ip, $correoVisitante, $mensaje, $honeypot);
        if ($filtro['ok'] && ($nombreLimpio === '' || $asuntoLimpio === '')) {
            $filtro = [
                'ok' => false,
                'motivo' => 'El nombre y el asunto son obligatorios.'
            ];
        }

        if (!$filtro['ok']) {
            $esc = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
            $cuerpoBloqueo = '<div style="font-family:Arial,sans-serif;padding:16px;background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;">'
                . '<h3 style="margin:0 0 8px;color:#991B1B;">Intento de contacto bloqueado por WAF / Anti-Spam</h3>'
                . '<p style="margin:4px 0;"><strong>Motivo:</strong> ' . $esc($filtro['motivo']) . '</p>'
                . '<p style="margin:4px 0;"><strong>IP:</strong> ' . $esc($ip) . '</p>'
                . '<p style="margin:4px 0;"><strong>Correo ingresado:</strong> ' . $esc($correoLimpio) . '</p>'
                . '<pre style="background:#FFFFFF;padding:10px;border-radius:6px;white-space:pre-wrap;">' . $esc(mb_substr($mensaje, 0, 1000, 'UTF-8')) . '</pre>'
                . '</div>';

            $idBloqueo = $this->registrarCorreoEnBd(
                null,
                $adminEmail,
                mb_substr($correoLimpio, 0, 320, 'UTF-8'),
                mb_substr('[BLOQUEADO] ' . ($asuntoLimpio ?: 'Contacto web'), 0, 255, 'UTF-8'),
                $cuerpoBloqueo,
                'contacto_web',
                'bloqueado_filtro',
                mb_substr($filtro['motivo'], 0, 255, 'UTF-8'),
                $ip
            );

            return [
                'ok' => false,
                'id_correo' => $idBloqueo,
                'estado_smtp' => 'bloqueado_filtro',
                'motivo' => $filtro['motivo']
            ];
        }

        $esc = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $cuerpoHtml = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>'
            . '<body style="margin:0;padding:24px;background:#F8FAFC;font-family:\'Segoe UI\',Arial,sans-serif;color:#0F172A;">'
            . '<div style="max-width:600px;margin:0 auto;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:16px;overflow:hidden;">'
            . '<div style="background:linear-gradient(135deg,#1D4ED8,#2563EB);padding:22px 28px;color:#FFFFFF;">'
            . '<div style="font-size:12px;text-transform:uppercase;letter-spacing:1px;opacity:0.85;">Buzón Web B2B · SysMonitor IoT</div>'
            . '<h2 style="margin:6px 0 0;font-size:20px;">Nueva Consulta Corporativa</h2>'
            . '</div>'
            . '<div style="padding:24px 28px;">'
            . '<p style="margin:0 0 8px;font-size:14px;"><strong>Remitente:</strong> ' . $esc($nombreLimpio) . '</p>'
            . '<p style="margin:0 0 8px;font-size:14px;"><strong>Correo (Reply-To):</strong> <span style="color:#1D4ED8;">' . $esc($correoLimpio) . '</span></p>'
            . '<p style="margin:0 0 8px;font-size:14px;"><strong>Asunto:</strong> ' . $esc($asuntoLimpio) . '</p>'
            . '<p style="margin:0 0 16px;font-size:12px;color:#64748B;"><strong>IP de origen:</strong> ' . $esc($ip) . '</p>'
            . '<div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:16px;font-size:14px;line-height:1.6;color:#334155;">'
            . nl2br($esc(trim($mensaje)))
            . '</div>'
            . '</div></div></body></html>';

        return $this->enviarCorreo(
            $adminEmail,
            '[Contacto Web] ' . $asuntoLimpio,
            $cuerpoHtml,
            'contacto_web',
            null,
            $correoLimpio
        );
    }

    /**
     * Obtiene los correos recientes registrados en correos_enviados (filtrados opcionalmente por empresa).
     */
    public function obtenerCorreos(?int $idEmpresa = null, int $limite = 25): array {
        if (!$this->db instanceof mysqli) {
            return [];
        }
        $limite = max(1, min(200, $limite));

        if ($idEmpresa !== null && $idEmpresa > 0) {
            $stmt = $this->db->prepare(
                "SELECT c.*, e.nombre_empresa
                 FROM correos_enviados c
                 LEFT JOIN empresas e ON c.id_empresa = e.id_empresa
                 WHERE c.id_empresa = ?
                 ORDER BY c.fecha_envio DESC, c.id_correo DESC
                 LIMIT ?"
            );
            if (!$stmt) return [];
            $stmt->bind_param("ii", $idEmpresa, $limite);
        } else {
            $stmt = $this->db->prepare(
                "SELECT c.*, e.nombre_empresa
                 FROM correos_enviados c
                 LEFT JOIN empresas e ON c.id_empresa = e.id_empresa
                 ORDER BY c.fecha_envio DESC, c.id_correo DESC
                 LIMIT ?"
            );
            if (!$stmt) return [];
            $stmt->bind_param("i", $limite);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $lista = [];
        while ($row = $res->fetch_assoc()) {
            $lista[] = $row;
        }
        $stmt->close();
        return $lista;
    }

    private function registrarCorreoEnBd(
        ?int $idEmpresa,
        string $destinatario,
        ?string $replyTo,
        string $asunto,
        string $cuerpoHtml,
        string $tipoCorreo,
        string $estadoSmtp,
        ?string $detalleFiltro,
        string $ipOrigen
    ): int {
        if (!$this->db instanceof mysqli) {
            return 0;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO correos_enviados
             (id_empresa, destinatario, remitente_reply, asunto, cuerpo_html, tipo_correo, estado_smtp, detalle_filtro, ip_origen)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param(
            "issssssss",
            $idEmpresa,
            $destinatario,
            $replyTo,
            $asunto,
            $cuerpoHtml,
            $tipoCorreo,
            $estadoSmtp,
            $detalleFiltro,
            $ipOrigen
        );
        $ok = $stmt->execute();
        $idCorreo = $ok ? (int)$stmt->insert_id : 0;
        $stmt->close();
        return $idCorreo;
    }

    /**
     * Cliente SMTP nativo sobre stream_socket_client con soporte SSL (465) y STARTTLS (587) + AUTH LOGIN.
     */
    private function enviarPorSocketSmtp(string $destinatario, string $asunto, string $cuerpoHtml, ?string $replyTo = null): array {
        $host = self::sanitizarCabecera((string)(defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com'));
        $port = (int)(defined('SMTP_PORT') ? SMTP_PORT : 587);
        $user = self::sanitizarCabecera((string)SMTP_USER);
        $pass = str_replace(["\r", "\n", "\0"], '', (string)SMTP_PASS);
        $fromEmail = self::sanitizarCabecera((string)(defined('SMTP_FROM_EMAIL') && SMTP_FROM_EMAIL !== '' ? SMTP_FROM_EMAIL : $user));
        $fromName = self::sanitizarCabecera((string)(defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'SysMonitor IoT Seguridad'));

        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = $user;
        }

        $remote = ($port === 465) ? "ssl://{$host}:{$port}" : "tcp://{$host}:{$port}";
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            ]
        ]);

        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($remote, $errno, $errstr, 4, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            return [
                'ok' => false,
                'detalle' => mb_substr("Conexión SMTP fallida ({$host}:{$port}): {$errstr} (#{$errno}). En cola local.", 0, 255, 'UTF-8')
            ];
        }

        stream_set_timeout($socket, 4);

        try {
            $greeting = $this->leerRespuestaSmtp($socket);
            if ($greeting['code'] !== 220) {
                throw new RuntimeException('Saludo SMTP inesperado: ' . $greeting['text']);
            }

            $ehloHost = preg_replace('/[^a-zA-Z0-9.\-]/', '', (string)($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
            $this->comandoSmtp($socket, "EHLO {$ehloHost}", [250]);

            if ($port !== 465) {
                $this->comandoSmtp($socket, "STARTTLS", [220]);
                $cryptoOk = @stream_socket_enable_crypto(
                    $socket,
                    true,
                    STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT
                );
                if (!$cryptoOk) {
                    throw new RuntimeException('No se pudo negociar cifrado STARTTLS.');
                }
                $this->comandoSmtp($socket, "EHLO {$ehloHost}", [250]);
            }

            $this->comandoSmtp($socket, "AUTH LOGIN", [334]);
            $this->comandoSmtp($socket, base64_encode($user), [334]);
            $this->comandoSmtp($socket, base64_encode($pass), [235]);
            $this->comandoSmtp($socket, "MAIL FROM:<{$fromEmail}>", [250]);
            $this->comandoSmtp($socket, "RCPT TO:<{$destinatario}>", [250, 251]);
            $this->comandoSmtp($socket, "DATA", [354]);

            $headers = [
                'Date: ' . date('r'),
                'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>',
                'To: <' . $destinatario . '>',
            ];
            if ($replyTo !== null && $replyTo !== '') {
                $headers[] = 'Reply-To: <' . $replyTo . '>';
            }
            $headers[] = 'Subject: =?UTF-8?B?' . base64_encode($asunto) . '?=';
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: base64';

            $payload = implode("\r\n", $headers)
                . "\r\n\r\n"
                . chunk_split(base64_encode($cuerpoHtml))
                . "\r\n.\r\n";

            $written = @fwrite($socket, $payload);
            if ($written === false) {
                throw new RuntimeException('Error escribiendo payload SMTP.');
            }
            $dataResp = $this->leerRespuestaSmtp($socket);
            if ($dataResp['code'] !== 250) {
                throw new RuntimeException('Rechazo SMTP en DATA: ' . $dataResp['text']);
            }

            @fwrite($socket, "QUIT\r\n");
            @fclose($socket);

            return [
                'ok' => true,
                'detalle' => "Enviado exitosamente vía SMTP ({$host}:{$port})."
            ];
        } catch (Throwable $e) {
            @fclose($socket);
            return [
                'ok' => false,
                'detalle' => mb_substr('Fallback cola local SMTP: ' . $e->getMessage(), 0, 255, 'UTF-8')
            ];
        }
    }

    private function comandoSmtp($socket, string $comando, array $codigosEsperados): array {
        $written = @fwrite($socket, $comando . "\r\n");
        if ($written === false) {
            throw new RuntimeException('Error enviando comando SMTP.');
        }
        $resp = $this->leerRespuestaSmtp($socket);
        if (!in_array($resp['code'], $codigosEsperados, true)) {
            throw new RuntimeException('Respuesta SMTP inesperada (' . $resp['code'] . '): ' . $resp['text']);
        }
        return $resp;
    }

    private function leerRespuestaSmtp($socket): array {
        $texto = '';
        $codigo = 0;
        while (!feof($socket)) {
            $linea = @fgets($socket, 515);
            if ($linea === false) {
                $meta = stream_get_meta_data($socket);
                if (!empty($meta['timed_out'])) {
                    throw new RuntimeException('Timeout leyendo respuesta SMTP.');
                }
                break;
            }
            $texto .= $linea;
            if (strlen($linea) >= 4 && substr($linea, 3, 1) === ' ') {
                $codigo = (int)substr($linea, 0, 3);
                break;
            }
            if (strlen($linea) < 4) {
                $codigo = (int)substr($linea, 0, 3);
                break;
            }
        }
        return ['code' => $codigo, 'text' => trim($texto)];
    }
}
