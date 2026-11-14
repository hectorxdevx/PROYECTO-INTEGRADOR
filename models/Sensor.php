<?php
require_once __DIR__ . '/Database.php';

class Sensor {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    /**
     * Obtiene los Racks de una empresa (o todos si $idEmpresa es null) con su última lectura IoT y métricas del día.
     */
    public function obtenerRacksConUltimaLectura(?int $idEmpresa = null): array {
        $where = $idEmpresa ? "WHERE r.id_empresa = " . (int)$idEmpresa : "";
        $sql = "SELECT r.*, e.nombre_empresa
                FROM racks r
                LEFT JOIN empresas e ON r.id_empresa = e.id_empresa
                $where
                ORDER BY r.codigo_rack ASC, r.id_rack ASC";

        $res = $this->db->query($sql);
        $racks = [];
        if ($res) {
            while ($rack = $res->fetch_assoc()) {
                $idRack = (int)$rack['id_rack'];
                // Última lectura
                $qUlt = $this->db->query("SELECT * FROM lecturas_sensores WHERE id_rack = $idRack ORDER BY fecha_lectura DESC, id_lectura DESC LIMIT 1");
                $ultima = ($qUlt && $qUlt->num_rows > 0) ? $qUlt->fetch_assoc() : [
                    'temperatura' => 22.0,
                    'humedad' => 45.0,
                    'consumo_kw' => 1.10,
                    'puerta_abierta' => 0,
                    'voltaje' => 120.0,
                    'fecha_lectura' => date('Y-m-d H:i:s')
                ];

                // Resumen estadístico (Mín, Promedio, Máx)
                $qStats = $this->db->query("SELECT
                    ROUND(MIN(temperatura),1) AS temp_min, ROUND(AVG(temperatura),1) AS temp_avg, ROUND(MAX(temperatura),1) AS temp_max_dia,
                    ROUND(MIN(humedad),1) AS hum_min, ROUND(AVG(humedad),1) AS hum_avg, ROUND(MAX(humedad),1) AS hum_max_dia,
                    ROUND(MIN(consumo_kw),2) AS kw_min, ROUND(AVG(consumo_kw),2) AS kw_avg, ROUND(MAX(consumo_kw),2) AS kw_max_dia
                    FROM lecturas_sensores WHERE id_rack = $idRack");
                $stats = ($qStats && $qStats->num_rows > 0) ? $qStats->fetch_assoc() : [];

                $rack['lectura'] = $ultima;
                $rack['resumen_dia'] = $stats;
                $racks[] = $rack;
            }
        }
        return $racks;
    }

    public function obtenerRackPorId(int $idRack): ?array {
        $stmt = $this->db->prepare("SELECT * FROM racks WHERE id_rack = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $idRack);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res ?: null;
    }

    public function obtenerRackPorApiKey(string $apiKey): ?array {
        $stmt = $this->db->prepare("SELECT * FROM racks WHERE api_key_iot = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res ?: null;
    }

    /**
     * Permite al Jefe de TI o SuperAdmin dar de alta un nuevo Rack en su empresa.
     */
    public function crearRack(int $idEmpresa, string $codigoRack, string $nombreRack, string $ubicacion, float $tempMax = 27.0, float $humMax = 60.0, float $kwMax = 2.5): int|false {
        $apiKey = 'iot_key_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $codigoRack)) . '_' . bin2hex(random_bytes(5));
        $stmt = $this->db->prepare("INSERT INTO racks (id_empresa, codigo_rack, nombre_rack, ubicacion, api_key_iot, temp_max, hum_max, kw_max, estado)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'online')");
        if (!$stmt) return false;
        $stmt->bind_param("issssddd", $idEmpresa, $codigoRack, $nombreRack, $ubicacion, $apiKey, $tempMax, $humMax, $kwMax);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $idRack = $stmt->insert_id;
        $stmt->close();

        // Lectura inicial por defecto
        $this->registrarLectura($idRack, 22.0, 44.0, 1.05, 0);
        return $idRack;
    }

    /**
     * Permite al Jefe de TI actualizar umbrales de alerta y datos de un Rack.
     */
    public function actualizarUmbralesRack(int $idRack, int $idEmpresa, string $nombreRack, string $ubicacion, float $tempMax, float $humMax, float $kwMax): bool {
        $stmt = $this->db->prepare("UPDATE racks SET nombre_rack = ?, ubicacion = ?, temp_max = ?, hum_max = ?, kw_max = ?
                                    WHERE id_rack = ? AND id_empresa = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ssdddii", $nombreRack, $ubicacion, $tempMax, $humMax, $kwMax, $idRack, $idEmpresa);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Registra una nueva lectura de telemetría IoT y evalúa automáticamente si se violaron los umbrales de seguridad.
     */
    public function registrarLectura(int $idRack, float $temperatura, float $humedad, float $consumoKw, int $puertaAbierta, float $voltaje = 120.0): ?array {
        $rack = $this->obtenerRackPorId($idRack);
        if (!$rack) return null;

        $stmt = $this->db->prepare("INSERT INTO lecturas_sensores (id_rack, temperatura, humedad, consumo_kw, puerta_abierta, voltaje)
                                    VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) return null;
        $stmt->bind_param("idddid", $idRack, $temperatura, $humedad, $consumoKw, $puertaAbierta, $voltaje);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }
        $idLectura = $stmt->insert_id;
        $stmt->close();

        // Evaluar umbrales y disparar alertas si corresponde
        $alertasGeneradas = [];
        $nuevoEstado = 'online';
        $idEmpresa = (int)$rack['id_empresa'];

        if ($puertaAbierta === 1) {
            $nuevoEstado = 'critical';
            $msg = "Puerta física ABIERTA en {$rack['nombre_rack']} ({$rack['ubicacion']})";
            $this->crearAlerta($idEmpresa, $idRack, 'Apertura Física de Rack', $msg, 'critical');
            $alertasGeneradas[] = $msg;
        }

        if ($temperatura > (float)$rack['temp_max']) {
            if ($nuevoEstado !== 'critical') {
                $nuevoEstado = ($temperatura > (float)$rack['temp_max'] + 3) ? 'critical' : 'warning';
            }
            $nivel = ($temperatura > (float)$rack['temp_max'] + 3) ? 'critical' : 'warning';
            $msg = "Temperatura elevada en {$rack['nombre_rack']}: {$temperatura} °C (Máx: {$rack['temp_max']} °C)";
            $this->crearAlerta($idEmpresa, $idRack, 'Sobrecalentamiento Térmico', $msg, $nivel);
            $alertasGeneradas[] = $msg;
        }

        if ($humedad > (float)$rack['hum_max']) {
            if ($nuevoEstado === 'online') $nuevoEstado = 'warning';
            $msg = "Humedad fuera de rango en {$rack['nombre_rack']}: {$humedad}% (Máx: {$rack['hum_max']}%)";
            $this->crearAlerta($idEmpresa, $idRack, 'Humedad Crítica', $msg, 'warning');
            $alertasGeneradas[] = $msg;
        }

        if ($consumoKw > (float)$rack['kw_max']) {
            if ($nuevoEstado === 'online') $nuevoEstado = 'warning';
            $msg = "Sobrecarga energética en {$rack['nombre_rack']}: {$consumoKw} kW (Máx: {$rack['kw_max']} kW)";
            $this->crearAlerta($idEmpresa, $idRack, 'Exceso de Consumo Eléctrico', $msg, 'warning');
            $alertasGeneradas[] = $msg;
        }

        $this->db->query("UPDATE racks SET estado = '$nuevoEstado' WHERE id_rack = $idRack");

        return [
            'id_lectura' => $idLectura,
            'id_rack' => $idRack,
            'estado_rack' => $nuevoEstado,
            'alertas_disparadas' => $alertasGeneradas
        ];
    }

    public function crearAlerta(int $idEmpresa, int $idRack, string $tipo, string $mensaje, string $nivel = 'warning'): bool {
        $stmt = $this->db->prepare("INSERT INTO alertas (id_empresa, id_rack, tipo_alerta, mensaje, nivel, resuelta)
                                    VALUES (?, ?, ?, ?, ?, 0)");
        if (!$stmt) return false;
        $stmt->bind_param("iisss", $idEmpresa, $idRack, $tipo, $mensaje, $nivel);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function obtenerAlertas(?int $idEmpresa = null, bool $soloActivas = false, int $limite = 20): array {
        $condiciones = [];
        if ($idEmpresa !== null) {
            $condiciones[] = "a.id_empresa = " . (int)$idEmpresa;
        }
        if ($soloActivas) {
            $condiciones[] = "a.resuelta = 0";
        }
        $where = count($condiciones) > 0 ? "WHERE " . implode(" AND ", $condiciones) : "";

        $sql = "SELECT a.*, r.codigo_rack, r.nombre_rack, e.nombre_empresa
                FROM alertas a
                INNER JOIN racks r ON a.id_rack = r.id_rack
                LEFT JOIN empresas e ON a.id_empresa = e.id_empresa
                $where
                ORDER BY a.resuelta ASC, a.fecha_alerta DESC
                LIMIT " . (int)$limite;
        $res = $this->db->query($sql);
        $lista = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $lista[] = $row;
            }
        }
        return $lista;
    }

    public function resolverAlerta(int $idAlerta, ?int $idEmpresa = null): bool {
        $sql = "UPDATE alertas SET resuelta = 1 WHERE id_alerta = ?" . ($idEmpresa ? " AND id_empresa = " . (int)$idEmpresa : "");
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $idAlerta);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function obtenerHistorialLecturas(?int $idEmpresa = null, ?int $idRack = null, int $limite = 12): array {
        $cond = [];
        if ($idEmpresa !== null) $cond[] = "r.id_empresa = " . (int)$idEmpresa;
        if ($idRack !== null) $cond[] = "l.id_rack = " . (int)$idRack;
        $where = count($cond) > 0 ? "WHERE " . implode(" AND ", $cond) : "";

        $sql = "SELECT l.*, r.codigo_rack, r.nombre_rack
                FROM lecturas_sensores l
                INNER JOIN racks r ON l.id_rack = r.id_rack
                $where
                ORDER BY l.fecha_lectura DESC, l.id_lectura DESC
                LIMIT " . (int)$limite;
        $res = $this->db->query($sql);
        $datos = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $datos[] = $row;
            }
        }
        return array_reverse($datos);
    }

    public function obtenerBitacora(?int $idEmpresa = null, int $limite = 20): array {
        $where = $idEmpresa !== null ? "WHERE b.id_empresa = " . (int)$idEmpresa : "";
        $sql = "SELECT b.*, u.nombre_usuario, u.apellidos, u.correo_electronico, e.nombre_empresa
                FROM bitacora_accesos b
                LEFT JOIN users u ON b.id_usuario = u.id_usuario
                LEFT JOIN empresas e ON b.id_empresa = e.id_empresa
                $where
                ORDER BY b.fecha_evento DESC
                LIMIT " . (int)$limite;
        $res = $this->db->query($sql);
        $eventos = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $eventos[] = $row;
            }
        }
        return $eventos;
    }

    public function obtenerEstadisticasGlobales(): array {
        $stats = [
            'total_usuarios' => 0,
            'usuarios_mfa' => 0,
            'total_empresas' => 0,
            'empresas_pendientes' => 0,
            'solicitudes_pendientes' => 0,
            'total_racks' => 0,
            'alertas_activas' => 0
        ];

        $q1 = $this->db->query("SELECT COUNT(*) AS c, SUM(CASE WHEN mfa_enabled = 1 THEN 1 ELSE 0 END) AS mfa FROM users");
        if ($q1 && $r = $q1->fetch_assoc()) {
            $stats['total_usuarios'] = (int)$r['c'];
            $stats['usuarios_mfa'] = (int)$r['mfa'];
        }

        $q2 = $this->db->query("SELECT COUNT(*) AS c, SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS pend FROM empresas");
        if ($q2 && $r = $q2->fetch_assoc()) {
            $stats['total_empresas'] = (int)$r['c'];
            $stats['empresas_pendientes'] = (int)$r['pend'];
        }

        $q3 = $this->db->query("SELECT COUNT(*) AS c FROM solicitudes_cuentas WHERE estado = 'pendiente'");
        if ($q3 && $r = $q3->fetch_assoc()) {
            $stats['solicitudes_pendientes'] = (int)$r['c'];
        }

        $q4 = $this->db->query("SELECT COUNT(*) AS c FROM racks");
        if ($q4 && $r = $q4->fetch_assoc()) {
            $stats['total_racks'] = (int)$r['c'];
        }

        $q5 = $this->db->query("SELECT COUNT(*) AS c FROM alertas WHERE resuelta = 0");
        if ($q5 && $r = $q5->fetch_assoc()) {
            $stats['alertas_activas'] = (int)$r['c'];
        }

        return $stats;
    }
}
?>

