<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/TOTP.php';
require_once __DIR__ . '/MailerService.php';

class Empresa {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    private function resolverPlan(string $plan): array {
        $planes = defined('B2B_PLANS') ? B2B_PLANS : [
            'starter' => ['nombre' => 'Starter', 'precio' => 49.00, 'max_racks' => 2, 'max_cuentas' => 3],
            'corporate' => ['nombre' => 'Corporate', 'precio' => 149.00, 'max_racks' => 10, 'max_cuentas' => 15],
            'enterprise' => ['nombre' => 'Enterprise', 'precio' => 399.00, 'max_racks' => 999, 'max_cuentas' => 999],
        ];
        $planKey = strtolower(trim($plan));
        if (!isset($planes[$planKey])) {
            $planKey = 'corporate';
        }
        $info = $planes[$planKey];
        $info['key'] = $planKey;
        return $info;
    }

    private function asegurarRackInicial(int $idEmpresa): void {
        $stmtCheck = $this->db->prepare("SELECT id_rack FROM racks WHERE id_empresa = ? LIMIT 1");
        if (!$stmtCheck) return;
        $stmtCheck->bind_param("i", $idEmpresa);
        $stmtCheck->execute();
        $existe = $stmtCheck->get_result()->num_rows > 0;
        $stmtCheck->close();

        if ($existe) return;

        $apiKey = 'iot_key_emp' . $idEmpresa . '_' . bin2hex(random_bytes(6));
        $stmtRack = $this->db->prepare("INSERT INTO racks (id_empresa, codigo_rack, nombre_rack, ubicacion, api_key_iot)
            VALUES (?, 'RACK-01', 'Rack 01 - Nodo Principal', 'Site Principal', ?)");
        if ($stmtRack) {
            $stmtRack->bind_param("is", $idEmpresa, $apiKey);
            $stmtRack->execute();
            $idRack = $stmtRack->insert_id;
            $stmtRack->close();

            if ($idRack) {
                $this->db->query("INSERT INTO lecturas_sensores (id_rack, temperatura, humedad, consumo_kw, puerta_abierta)
                    VALUES ($idRack, 22.0, 45.0, 1.15, 0)");
            }
        }
    }

    /**
     * Registra una nueva empresa en modo activo inmediato con plan B2B, Rack inicial y Jefe de TI aprovisionado.
     */
    public function registrarEmpresa(
        int $idRepresentante,
        string $nombreEmpresa,
        string $rfcNit,
        string $slugDominio,
        string $sector,
        string $telefono,
        string $plan = 'corporate',
        ?string $correoNotificaciones = null
    ): array|false {
        $baseSlug = str_ireplace('.' . INSTITUTIONAL_BASE_DOMAIN, '', trim($slugDominio));
        $slugLimpio = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '', $baseSlug));
        if (empty($slugLimpio)) {
            $slugLimpio = 'emp' . time();
        }
        $dominioCompleto = $slugLimpio . '.' . INSTITUTIONAL_BASE_DOMAIN;

        $planInfo = $this->resolverPlan($plan);
        $planKey = $planInfo['key'];
        $maxRacks = (int)$planInfo['max_racks'];
        $maxCuentas = (int)$planInfo['max_cuentas'];
        $costoMensual = (float)$planInfo['precio'];

        $correoDestino = null;
        if ($correoNotificaciones !== null && filter_var(trim($correoNotificaciones), FILTER_VALIDATE_EMAIL)) {
            $correoDestino = strtolower(trim($correoNotificaciones));
        } else {
            $stmtRep = $this->db->prepare("SELECT correo_electronico FROM users WHERE id_usuario = ? LIMIT 1");
            if ($stmtRep) {
                $stmtRep->bind_param("i", $idRepresentante);
                $stmtRep->execute();
                $repRow = $stmtRep->get_result()->fetch_assoc();
                $stmtRep->close();
                if (!empty($repRow['correo_electronico'])) {
                    $correoDestino = $repRow['correo_electronico'];
                }
            }
        }

        $sql = "INSERT INTO empresas (
                    id_representante, nombre_empresa, rfc_nit, slug_dominio, sector,
                    telefono_contacto, estado, plan_suscripcion, max_racks, max_cuentas,
                    costo_mensual, correo_notificaciones
                ) VALUES (?, ?, ?, ?, ?, ?, 'aprobada', ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param(
            "issssssiids",
            $idRepresentante,
            $nombreEmpresa,
            $rfcNit,
            $dominioCompleto,
            $sector,
            $telefono,
            $planKey,
            $maxRacks,
            $maxCuentas,
            $costoMensual,
            $correoDestino
        );
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $idEmpresa = (int)$stmt->insert_id;
        $stmt->close();

        // Vincular al representante con su empresa recién creada
        $stmtUser = $this->db->prepare("UPDATE users SET id_empresa = ? WHERE id_usuario = ?");
        if ($stmtUser) {
            $stmtUser->bind_param("ii", $idEmpresa, $idRepresentante);
            $stmtUser->execute();
            $stmtUser->close();
        }

        // Crear RACK-01 inicial + lectura base
        $this->asegurarRackInicial($idEmpresa);

        // Auto-aprovisionar primera cuenta de Jefe de TI y enviar credenciales SMTP
        $idSolicitud = $this->crearSolicitudCuentaInterna($idEmpresa, 'Jefe', 'TI', 'jefe_ti');
        $creado = $idSolicitud ? $this->aprobarYGenerarCorreoInstitucional($idSolicitud) : null;

        return [
            'id_empresa' => $idEmpresa,
            'nombre_empresa' => $nombreEmpresa,
            'dominio_institucional' => $dominioCompleto,
            'plan' => $planKey,
            'jefe_ti_credenciales' => $creado,
            'correo_enviado_a' => $correoDestino
        ];
    }

    public function obtenerPorRepresentante(int $idRepresentante): ?array {
        $stmt = $this->db->prepare("SELECT * FROM empresas WHERE id_representante = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $idRepresentante);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res ?: null;
    }

    public function obtenerPorId(int $idEmpresa): ?array {
        $stmt = $this->db->prepare("SELECT e.*, u.nombre_usuario AS rep_nombre, u.apellidos AS rep_apellidos, u.correo_electronico AS rep_correo
                                    FROM empresas e
                                    LEFT JOIN users u ON e.id_representante = u.id_usuario
                                    WHERE e.id_empresa = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $idEmpresa);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res ?: null;
    }

    public function obtenerTodas(): array {
        $sql = "SELECT e.*, u.nombre_usuario AS rep_nombre, u.apellidos AS rep_apellidos, u.correo_electronico AS rep_correo,
                       (SELECT COUNT(*) FROM users usr WHERE usr.id_empresa = e.id_empresa AND usr.id_rol IN (1,4)) AS total_personal,
                       (SELECT COUNT(*) FROM racks r WHERE r.id_empresa = e.id_empresa) AS total_racks
                FROM empresas e
                LEFT JOIN users u ON e.id_representante = u.id_usuario
                ORDER BY e.fecha_registro DESC";
        $res = $this->db->query($sql);
        $lista = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $lista[] = $row;
            }
        }
        return $lista;
    }

    public function cambiarEstadoEmpresa(int $idEmpresa, string $nuevoEstado): bool {
        $stmt = $this->db->prepare("UPDATE empresas SET estado = ? WHERE id_empresa = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $nuevoEstado, $idEmpresa);
        $ok = $stmt->execute();
        $stmt->close();

        // Si se aprueba y no tiene racks aún, crear un Rack 01 inicial para la empresa
        if ($ok && $nuevoEstado === 'aprobada') {
            $this->asegurarRackInicial($idEmpresa);
        }

        return $ok;
    }

    /**
     * El Representante solicita una cuenta institucional (para su Jefe de TI o un Operador).
     */
    public function crearSolicitudCuenta(int $idEmpresa, string $nombre, string $apellidos, string $rolSolicitado): bool {
        return $this->crearSolicitudCuentaInterna($idEmpresa, $nombre, $apellidos, $rolSolicitado) !== false;
    }

    private function crearSolicitudCuentaInterna(int $idEmpresa, string $nombre, string $apellidos, string $rolSolicitado): int|false {
        $rolSolicitado = ($rolSolicitado === 'jefe_ti') ? 'jefe_ti' : 'operador';
        $stmt = $this->db->prepare("INSERT INTO solicitudes_cuentas (id_empresa, nombre, apellidos, rol_solicitado, estado)
                                    VALUES (?, ?, ?, ?, 'pendiente')");
        if (!$stmt) return false;
        $stmt->bind_param("isss", $idEmpresa, $nombre, $apellidos, $rolSolicitado);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $idSolicitud = (int)$stmt->insert_id;
        $stmt->close();
        return $idSolicitud > 0 ? $idSolicitud : false;
    }

    public function obtenerSolicitudesPorEmpresa(int $idEmpresa): array {
        $stmt = $this->db->prepare("SELECT * FROM solicitudes_cuentas WHERE id_empresa = ? ORDER BY fecha_solicitud DESC");
        if (!$stmt) return [];
        $stmt->bind_param("i", $idEmpresa);
        $stmt->execute();
        $res = $stmt->get_result();
        $lista = [];
        while ($row = $res->fetch_assoc()) {
            $lista[] = $row;
        }
        $stmt->close();
        return $lista;
    }

    public function obtenerSolicitudesPendientes(): array {
        $sql = "SELECT s.*, e.nombre_empresa, e.slug_dominio
                FROM solicitudes_cuentas s
                INNER JOIN empresas e ON s.id_empresa = e.id_empresa
                ORDER BY FIELD(s.estado, 'pendiente', 'aprobada', 'rechazada'), s.fecha_solicitud DESC";
        $res = $this->db->query($sql);
        $lista = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $lista[] = $row;
            }
        }
        return $lista;
    }

    /**
     * El SuperAdmin (o el alta inmediata) aprueba una solicitud, genera el correo institucional,
     * marca cambio_password_requerido = 1 y envía credenciales + TOTP vía MailerService.
     */
    public function aprobarYGenerarCorreoInstitucional(int $idSolicitud, ?string $correoPersonalizado = null, ?string $passwordInicial = null): ?array {
        $stmt = $this->db->prepare("SELECT s.*, e.slug_dominio, e.nombre_empresa, e.plan_suscripcion,
                                           e.correo_notificaciones, u.correo_electronico AS rep_correo
                                    FROM solicitudes_cuentas s
                                    INNER JOIN empresas e ON s.id_empresa = e.id_empresa
                                    LEFT JOIN users u ON e.id_representante = u.id_usuario
                                    WHERE s.id_solicitud = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $idSolicitud);
        $stmt->execute();
        $solicitud = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$solicitud) return null;

        // Construir correo institucional tipo universidad: nombre.apellido@empresa.sysmonitor.com
        if (empty($correoPersonalizado)) {
            $primerNombre = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', trim($solicitud['nombre']))[0] ?? ''));
            $primerApellido = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', trim($solicitud['apellidos']))[0] ?? ''));
            if ($primerNombre === '') $primerNombre = 'usuario';
            if ($primerApellido === '') $primerApellido = 'ti';
            $baseUser = $primerNombre . '.' . $primerApellido;
            $correoInstitucional = $baseUser . '@' . $solicitud['slug_dominio'];

            // Verificar colisión
            $contador = 1;
            while ($this->existeCorreoEnUsers($correoInstitucional)) {
                $correoInstitucional = $baseUser . $contador . '@' . $solicitud['slug_dominio'];
                $contador++;
            }
        } else {
            $correoInstitucional = strtolower(trim($correoPersonalizado));
            if ($this->existeCorreoEnUsers($correoInstitucional)) {
                return null;
            }
        }

        if (empty($passwordInicial)) {
            $prefijoNombre = preg_replace('/[^a-zA-Z]/', '', $solicitud['nombre']);
            if ($prefijoNombre === '') $prefijoNombre = 'Sys';
            $passwordInicial = 'Sys' . ucfirst(substr($prefijoNombre, 0, 3)) . random_int(1000, 9999) . '!';
        }

        $hash = password_hash($passwordInicial, PASSWORD_BCRYPT);
        $idRol = ($solicitud['rol_solicitado'] === 'jefe_ti') ? 4 : 1;
        $mfaSecret = TOTP::generarSecreto();
        $idEmpresa = (int)$solicitud['id_empresa'];

        $stmtUser = $this->db->prepare("INSERT INTO users (id_empresa, id_rol, nombre_usuario, apellidos, correo_electronico, contrasena, oauth_provider, mfa_secret, mfa_enabled, cambio_password_requerido)
                                        VALUES (?, ?, ?, ?, ?, ?, 'institucional', ?, 0, 1)");
        if (!$stmtUser) return null;
        $stmtUser->bind_param("iisssss", $idEmpresa, $idRol, $solicitud['nombre'], $solicitud['apellidos'], $correoInstitucional, $hash, $mfaSecret);
        if (!$stmtUser->execute()) {
            $stmtUser->close();
            return null;
        }
        $idNuevoUsuario = (int)$stmtUser->insert_id;
        $stmtUser->close();

        // Actualizar la solicitud con el correo asignado y la contraseña temporal
        $stmtUpd = $this->db->prepare("UPDATE solicitudes_cuentas
                                       SET correo_asignado = ?, password_temporal = ?, estado = 'aprobada', id_usuario_creado = ?
                                       WHERE id_solicitud = ?");
        if ($stmtUpd) {
            $stmtUpd->bind_param("ssii", $correoInstitucional, $passwordInicial, $idNuevoUsuario, $idSolicitud);
            $stmtUpd->execute();
            $stmtUpd->close();
        }

        $correoDestinoRep = !empty($solicitud['correo_notificaciones'])
            ? (string)$solicitud['correo_notificaciones']
            : (string)($solicitud['rep_correo'] ?? '');

        $smtpResultado = null;
        if ($correoDestinoRep !== '') {
            $mailer = new MailerService();
            $smtpResultado = $mailer->enviarCredencialesInstitucionales(
                [
                    'id_empresa' => $idEmpresa,
                    'nombre_empresa' => $solicitud['nombre_empresa'],
                    'slug_dominio' => $solicitud['slug_dominio'],
                    'plan_suscripcion' => $solicitud['plan_suscripcion'] ?? 'corporate',
                ],
                [
                    'id_usuario' => $idNuevoUsuario,
                    'correo_institucional' => $correoInstitucional,
                    'password_temporal' => $passwordInicial,
                    'rol' => $solicitud['rol_solicitado'],
                    'mfa_secret' => $mfaSecret,
                ],
                $correoDestinoRep
            );
        }

        return [
            'id_usuario' => $idNuevoUsuario,
            'correo_institucional' => $correoInstitucional,
            'password_temporal' => $passwordInicial,
            'rol' => $solicitud['rol_solicitado'],
            'mfa_secret' => $mfaSecret,
            'smtp' => $smtpResultado
        ];
    }

    public function puedeCrearRack(int $idEmpresa): bool {
        $uso = $this->obtenerUsoPlan($idEmpresa);
        return $uso['racks_usados'] < $uso['max_racks'];
    }

    public function puedeSolicitarCuenta(int $idEmpresa): bool {
        $uso = $this->obtenerUsoPlan($idEmpresa);
        $pendientes = 0;
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM solicitudes_cuentas WHERE id_empresa = ? AND estado = 'pendiente'");
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $pendientes = (int)($row['total'] ?? 0);
            $stmt->close();
        }
        return ($uso['cuentas_usadas'] + $pendientes) < $uso['max_cuentas'];
    }

    public function cambiarPlanEmpresa(int $idEmpresa, string $nuevoPlan): bool {
        if ($idEmpresa <= 0) return false;
        $planes = defined('B2B_PLANS') ? B2B_PLANS : [];
        $planKey = strtolower(trim($nuevoPlan));
        if (!isset($planes[$planKey])) {
            return false;
        }
        $planInfo = $planes[$planKey];
        $maxRacks = (int)$planInfo['max_racks'];
        $maxCuentas = (int)$planInfo['max_cuentas'];
        $costoMensual = (float)$planInfo['precio'];

        $stmt = $this->db->prepare("UPDATE empresas SET plan_suscripcion = ?, max_racks = ?, max_cuentas = ?, costo_mensual = ? WHERE id_empresa = ?");
        if (!$stmt) return false;
        $stmt->bind_param("siidi", $planKey, $maxRacks, $maxCuentas, $costoMensual, $idEmpresa);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function obtenerUsoPlan(int $idEmpresa): array {
        $stmt = $this->db->prepare(
            "SELECT e.plan_suscripcion, e.max_racks, e.max_cuentas, e.costo_mensual, e.estado,
                    (SELECT COUNT(*) FROM racks r WHERE r.id_empresa = e.id_empresa) AS racks_usados,
                    (SELECT COUNT(*) FROM users u WHERE u.id_empresa = e.id_empresa AND u.id_rol IN (1, 4)) AS cuentas_usadas
             FROM empresas e
             WHERE e.id_empresa = ? LIMIT 1"
        );
        if (!$stmt) {
            return [
                'plan_suscripcion' => 'corporate',
                'nombre_plan' => 'Corporate',
                'costo_mensual' => 149.00,
                'racks_usados' => 0,
                'max_racks' => 10,
                'cuentas_usadas' => 0,
                'max_cuentas' => 15,
                'estado' => 'pendiente'
            ];
        }
        $stmt->bind_param("i", $idEmpresa);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return [
                'plan_suscripcion' => 'corporate',
                'nombre_plan' => 'Corporate',
                'costo_mensual' => 149.00,
                'racks_usados' => 0,
                'max_racks' => 10,
                'cuentas_usadas' => 0,
                'max_cuentas' => 15,
                'estado' => 'pendiente'
            ];
        }

        $planKey = strtolower((string)($row['plan_suscripcion'] ?? 'corporate'));
        $planInfo = $this->resolverPlan($planKey);

        return [
            'plan_suscripcion' => $planInfo['key'],
            'nombre_plan' => (string)$planInfo['nombre'],
            'costo_mensual' => (float)$row['costo_mensual'],
            'racks_usados' => (int)$row['racks_usados'],
            'max_racks' => (int)$row['max_racks'],
            'cuentas_usadas' => (int)$row['cuentas_usadas'],
            'max_cuentas' => (int)$row['max_cuentas'],
            'estado' => (string)$row['estado']
        ];
    }

    private function existeCorreoEnUsers(string $correo): bool {
        $stmt = $this->db->prepare("SELECT id_usuario FROM users WHERE correo_electronico = ? LIMIT 1");
        if (!$stmt) return false;
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $existe;
    }
}
?>
