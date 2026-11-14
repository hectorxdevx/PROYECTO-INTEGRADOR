<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/TOTP.php';

class Empresa {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    /**
     * Registra una nueva empresa por parte de un Representante (cuenta personal).
     */
    public function registrarEmpresa(int $idRepresentante, string $nombreEmpresa, string $rfcNit, string $slugDominio, string $sector, string $telefono): int|false {
        $slugLimpio = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '', $slugDominio));
        if (empty($slugLimpio)) {
            $slugLimpio = 'emp' . time();
        }
        $dominioCompleto = $slugLimpio . '.' . INSTITUTIONAL_BASE_DOMAIN;

        $sql = "INSERT INTO empresas (id_representante, nombre_empresa, rfc_nit, slug_dominio, sector, telefono_contacto, estado)
                VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("isssss", $idRepresentante, $nombreEmpresa, $rfcNit, $dominioCompleto, $sector, $telefono);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $idEmpresa = $stmt->insert_id;
        $stmt->close();

        // Vincular al representante con su empresa recién creada
        $stmtUser = $this->db->prepare("UPDATE users SET id_empresa = ? WHERE id_usuario = ?");
        if ($stmtUser) {
            $stmtUser->bind_param("ii", $idEmpresa, $idRepresentante);
            $stmtUser->execute();
            $stmtUser->close();
        }

        return $idEmpresa;
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
            $check = $this->db->query("SELECT id_rack FROM racks WHERE id_empresa = $idEmpresa LIMIT 1");
            if ($check && $check->num_rows === 0) {
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
        }

        return $ok;
    }

    /**
     * El Representante solicita una cuenta institucional (para su Jefe de TI o un Operador).
     */
    public function crearSolicitudCuenta(int $idEmpresa, string $nombre, string $apellidos, string $rolSolicitado): bool {
        $rolSolicitado = ($rolSolicitado === 'jefe_ti') ? 'jefe_ti' : 'operador';
        $stmt = $this->db->prepare("INSERT INTO solicitudes_cuentas (id_empresa, nombre, apellidos, rol_solicitado, estado)
                                    VALUES (?, ?, ?, ?, 'pendiente')");
        if (!$stmt) return false;
        $stmt->bind_param("isss", $idEmpresa, $nombre, $apellidos, $rolSolicitado);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
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
     * El SuperAdmin aprueba una solicitud y el sistema genera automáticamente el correo institucional
     * (como las universidades con los correos institucionales) y su contraseña inicial + secreto MFA.
     */
    public function aprobarYGenerarCorreoInstitucional(int $idSolicitud, ?string $correoPersonalizado = null, ?string $passwordInicial = null): ?array {
        $stmt = $this->db->prepare("SELECT s.*, e.slug_dominio, e.nombre_empresa
                                    FROM solicitudes_cuentas s
                                    INNER JOIN empresas e ON s.id_empresa = e.id_empresa
                                    WHERE s.id_solicitud = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $idSolicitud);
        $stmt->execute();
        $solicitud = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$solicitud) return null;

        // Construir correo institucional tipo universidad: nombre.apellido@empresa.sysmonitor.com
        if (empty($correoPersonalizado)) {
            $primerNombre = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', trim($solicitud['nombre']))[0]));
            $primerApellido = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', trim($solicitud['apellidos']))[0]));
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
        }

        if (empty($passwordInicial)) {
            $passwordInicial = 'Sys' . ucfirst(substr(preg_replace('/[^a-zA-Z]/', '', $solicitud['nombre']), 0, 3)) . random_int(1000, 9999) . '!';
        }

        $hash = password_hash($passwordInicial, PASSWORD_BCRYPT);
        $idRol = ($solicitud['rol_solicitado'] === 'jefe_ti') ? 4 : 1;
        $mfaSecret = TOTP::generarSecreto();
        $idEmpresa = (int)$solicitud['id_empresa'];

        $stmtUser = $this->db->prepare("INSERT INTO users (id_empresa, id_rol, nombre_usuario, apellidos, correo_electronico, contrasena, oauth_provider, mfa_secret, mfa_enabled)
                                        VALUES (?, ?, ?, ?, ?, ?, 'institucional', ?, 0)");
        if (!$stmtUser) return null;
        $stmtUser->bind_param("iisssss", $idEmpresa, $idRol, $solicitud['nombre'], $solicitud['apellidos'], $correoInstitucional, $hash, $mfaSecret);
        if (!$stmtUser->execute()) {
            $stmtUser->close();
            return null;
        }
        $idNuevoUsuario = $stmtUser->insert_id;
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

        return [
            'id_usuario' => $idNuevoUsuario,
            'correo_institucional' => $correoInstitucional,
            'password_temporal' => $passwordInicial,
            'rol' => $solicitud['rol_solicitado'],
            'mfa_secret' => $mfaSecret
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
