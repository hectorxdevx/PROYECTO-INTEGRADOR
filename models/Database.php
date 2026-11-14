<?php

require_once __DIR__ . "/../config/config.php";

class Database {
    private $conexion;
    private static $migrated = false;

    public function conectar() {
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->conexion = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($this->conexion->connect_error) {
            // Intentar crear la base de datos si no existe aún
            $tmp = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
            if (!$tmp->connect_error) {
                $tmp->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $tmp->close();
                $this->conexion = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            }
        }

        if ($this->conexion->connect_error) {
            die("Error, no se pudo establecer la conexion: " . $this->conexion->connect_error);
        }

        $this->conexion->set_charset("utf8mb4");

        if (!self::$migrated) {
            self::$migrated = true;
            $this->asegurarEsquemaB2B();
        }

        return $this->conexion;
    }

    private function asegurarEsquemaB2B() {
        // 1. Tabla rol y los 4 roles del modelo de negocio
        $this->conexion->query("CREATE TABLE IF NOT EXISTS rol (
            id_rol TINYINT PRIMARY KEY AUTO_INCREMENT,
            nombre_rol VARCHAR(50) NOT NULL,
            descripcion VARCHAR(150) NULL
        )");

        $this->asegurarColumna('rol', 'descripcion', "VARCHAR(150) NULL");
        $this->conexion->query("UPDATE rol SET nombre_rol = 'operador', descripcion = 'Operador Institucional' WHERE id_rol = 1");
        $this->conexion->query("INSERT IGNORE INTO rol (id_rol, nombre_rol, descripcion) VALUES
            (1, 'operador', 'Operador Institucional - Monitoreo en tiempo real'),
            (2, 'admin', 'Super Administrador - Aprueba empresas y genera correos institucionales'),
            (3, 'representante', 'Representante de Empresa - Cuenta personal Google/Microsoft'),
            (4, 'jefe_ti', 'Jefe de TI Institucional - Monitoreo, racks, umbrales y bitacora')");

        // 2. Tabla users y columnas nuevas de OAuth + MFA + Empresa
        $this->conexion->query("CREATE TABLE IF NOT EXISTS users (
            id_usuario INT PRIMARY KEY AUTO_INCREMENT,
            nombre_usuario VARCHAR(100) NOT NULL,
            apellidos VARCHAR(100) NOT NULL,
            correo_electronico VARCHAR(320) UNIQUE,
            contrasena VARCHAR(256) NOT NULL,
            id_rol TINYINT NOT NULL DEFAULT 1
        )");

        $this->asegurarColumna('users', 'id_empresa', "INT NULL DEFAULT NULL");
        $this->asegurarColumna('users', 'oauth_provider', "VARCHAR(30) NOT NULL DEFAULT 'institucional'");
        $this->asegurarColumna('users', 'oauth_uid', "VARCHAR(128) NULL DEFAULT NULL");
        $this->asegurarColumna('users', 'mfa_secret', "VARCHAR(64) NULL DEFAULT NULL");
        $this->asegurarColumna('users', 'mfa_enabled', "TINYINT(1) NOT NULL DEFAULT 0");
        $this->asegurarColumna('users', 'foto_perfil', "VARCHAR(255) DEFAULT 'default.png'");
        $this->asegurarColumna('users', 'activo', "TINYINT(1) NOT NULL DEFAULT 1");
        $this->asegurarColumna('users', 'fecha_creacion', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

        // 3. Tabla empresas
        $this->conexion->query("CREATE TABLE IF NOT EXISTS empresas (
            id_empresa INT PRIMARY KEY AUTO_INCREMENT,
            id_representante INT NOT NULL,
            nombre_empresa VARCHAR(150) NOT NULL,
            rfc_nit VARCHAR(50) NOT NULL,
            slug_dominio VARCHAR(100) UNIQUE NOT NULL,
            sector VARCHAR(100) DEFAULT 'Infraestructura & DataCenter',
            telefono_contacto VARCHAR(30) NULL,
            estado ENUM('pendiente', 'aprobada', 'suspendida') NOT NULL DEFAULT 'pendiente',
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 4. Tabla solicitudes_cuentas
        $this->conexion->query("CREATE TABLE IF NOT EXISTS solicitudes_cuentas (
            id_solicitud INT PRIMARY KEY AUTO_INCREMENT,
            id_empresa INT NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            apellidos VARCHAR(100) NOT NULL,
            rol_solicitado ENUM('jefe_ti', 'operador') NOT NULL DEFAULT 'operador',
            correo_asignado VARCHAR(320) NULL,
            password_temporal VARCHAR(100) NULL,
            estado ENUM('pendiente', 'aprobada', 'rechazada') NOT NULL DEFAULT 'pendiente',
            id_usuario_creado INT NULL,
            fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 5. Tabla racks
        $this->conexion->query("CREATE TABLE IF NOT EXISTS racks (
            id_rack INT PRIMARY KEY AUTO_INCREMENT,
            id_empresa INT NOT NULL,
            codigo_rack VARCHAR(50) NOT NULL,
            nombre_rack VARCHAR(120) NOT NULL,
            ubicacion VARCHAR(150) DEFAULT 'Site Principal - Sala A',
            api_key_iot VARCHAR(64) UNIQUE NOT NULL,
            temp_max DECIMAL(5,2) NOT NULL DEFAULT 27.00,
            hum_max DECIMAL(5,2) NOT NULL DEFAULT 60.00,
            kw_max DECIMAL(6,2) NOT NULL DEFAULT 2.50,
            estado ENUM('online', 'warning', 'critical', 'offline') NOT NULL DEFAULT 'online',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 6. Tabla lecturas_sensores
        $this->conexion->query("CREATE TABLE IF NOT EXISTS lecturas_sensores (
            id_lectura BIGINT PRIMARY KEY AUTO_INCREMENT,
            id_rack INT NOT NULL,
            temperatura DECIMAL(5,2) NOT NULL,
            humedad DECIMAL(5,2) NOT NULL,
            consumo_kw DECIMAL(6,2) NOT NULL,
            puerta_abierta TINYINT(1) NOT NULL DEFAULT 0,
            voltaje DECIMAL(6,2) DEFAULT 120.00,
            fecha_lectura TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 7. Tabla alertas
        $this->conexion->query("CREATE TABLE IF NOT EXISTS alertas (
            id_alerta INT PRIMARY KEY AUTO_INCREMENT,
            id_empresa INT NOT NULL,
            id_rack INT NOT NULL,
            tipo_alerta VARCHAR(60) NOT NULL,
            mensaje VARCHAR(255) NOT NULL,
            nivel ENUM('info', 'warning', 'critical') NOT NULL DEFAULT 'warning',
            resuelta TINYINT(1) NOT NULL DEFAULT 0,
            fecha_alerta TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 8. Tabla bitacora_accesos
        $this->conexion->query("CREATE TABLE IF NOT EXISTS bitacora_accesos (
            id_evento INT PRIMARY KEY AUTO_INCREMENT,
            id_empresa INT NULL,
            id_usuario INT NULL,
            tipo_evento VARCHAR(60) NOT NULL,
            descripcion VARCHAR(255) NOT NULL,
            ip_origen VARCHAR(45) DEFAULT '127.0.0.1',
            fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 9. Tabla contactos
        $this->conexion->query("CREATE TABLE IF NOT EXISTS contactos (
            id_contacto INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(120) NOT NULL,
            correo VARCHAR(255) NOT NULL,
            asunto VARCHAR(150) NOT NULL,
            mensaje TEXT NOT NULL,
            leido TINYINT(1) NOT NULL DEFAULT 0,
            fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 10. Semilla inicial de demostración B2B si no existe el SuperAdmin institucional
        $this->sembrarDatosIniciales();
    }

    private function asegurarColumna(string $tabla, string $columna, string $definicion) {
        $res = $this->conexion->query("SHOW COLUMNS FROM `$tabla` LIKE '$columna'");
        if ($res && $res->num_rows === 0) {
            $this->conexion->query("ALTER TABLE `$tabla` ADD COLUMN `$columna` $definicion");
        }
    }

    private function sembrarDatosIniciales() {
        // Verificar si existe el SuperAdmin
        $checkAdmin = $this->conexion->query("SELECT id_usuario FROM users WHERE correo_electronico = 'admin@sysmonitor.com' LIMIT 1");
        if ($checkAdmin && $checkAdmin->num_rows === 0) {
            $passHash = password_hash('Admin123!', PASSWORD_BCRYPT);
            // Secret fijo de demo: JBSWY3DPEHPK3PXP
            $this->conexion->query("INSERT INTO users (nombre_usuario, apellidos, correo_electronico, contrasena, id_rol, oauth_provider, mfa_secret, mfa_enabled)
                VALUES ('Super', 'Administrador', 'admin@sysmonitor.com', '$passHash', 2, 'institucional', 'JBSWY3DPEHPK3PXP', 0)");
        }

        // Verificar si existe una empresa demo para pruebas inmediatas en Web, Postman y Flutter
        $checkEmp = $this->conexion->query("SELECT id_empresa FROM empresas LIMIT 1");
        if ($checkEmp && $checkEmp->num_rows === 0) {
            $passRep = password_hash('Rep12345!', PASSWORD_BCRYPT);
            $this->conexion->query("INSERT INTO users (nombre_usuario, apellidos, correo_electronico, contrasena, id_rol, oauth_provider, oauth_uid, mfa_secret, mfa_enabled)
                VALUES ('Carlos', 'Mendoza', 'carlos.mendoza@gmail.com', '$passRep', 3, 'google', 'google-oauth-demo-01', 'JBSWY3DPEHPK3PXP', 0)");
            $idRep = $this->conexion->insert_id ?: 1;

            $this->conexion->query("INSERT INTO empresas (id_representante, nombre_empresa, rfc_nit, slug_dominio, sector, telefono_contacto, estado)
                VALUES ($idRep, 'InfraOps Cloud & DataCenter', 'IOC2601159A1', 'infraops.sysmonitor.com', 'DataCenter & Cloud', '+52 999 123 4567', 'aprobada')");
            $idEmp = $this->conexion->insert_id ?: 1;

            $this->conexion->query("UPDATE users SET id_empresa = $idEmp WHERE id_usuario = $idRep");

            // Crear Jefe de TI y Operador Institucional para InfraOps
            $passInst = password_hash('InfraOps2026!', PASSWORD_BCRYPT);
            $this->conexion->query("INSERT IGNORE INTO users (id_empresa, id_rol, nombre_usuario, apellidos, correo_electronico, contrasena, oauth_provider, mfa_secret, mfa_enabled)
                VALUES
                ($idEmp, 4, 'Alex', 'Vargas (Jefe TI)', 'alex.vargas@infraops.sysmonitor.com', '$passInst', 'institucional', 'JBSWY3DPEHPK3PXP', 0),
                ($idEmp, 1, 'Elena', 'Torres (Operador)', 'elena.torres@infraops.sysmonitor.com', '$passInst', 'institucional', 'JBSWY3DPEHPK3PXP', 0)");

            // Crear 4 Racks con API Keys y lecturas iniciales
            $racksDemo = [
                ['RACK-01', 'Rack 01 - Servidores Principales', 'Site 1 - Pasillo Frío A', 'iot_key_rack01_sysmonitor_2026', 27.0, 55.0, 2.5, 'online', 22.4, 45.0, 1.20, 0],
                ['RACK-02', 'Rack 02 - Clúster Almacenamiento SAN', 'Site 1 - Pasillo Frío A', 'iot_key_rack02_sysmonitor_2026', 26.5, 55.0, 2.2, 'online', 23.8, 42.0, 0.95, 0],
                ['RACK-03', 'Rack 03 - Core Switches & Firewall', 'Site 1 - Telecomunicaciones', 'iot_key_rack03_sysmonitor_2026', 27.0, 60.0, 1.8, 'warning', 28.4, 48.0, 0.65, 0],
                ['RACK-04', 'Rack 04 - Nodos de Respaldo Crítico', 'Site 2 - Bóveda Secundaria', 'iot_key_rack04_sysmonitor_2026', 26.0, 50.0, 2.0, 'critical', 21.5, 40.0, 1.10, 1],
            ];

            foreach ($racksDemo as $r) {
                $stmt = $this->conexion->prepare("INSERT INTO racks (id_empresa, codigo_rack, nombre_rack, ubicacion, api_key_iot, temp_max, hum_max, kw_max, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("issssddds", $idEmp, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6], $r[7]);
                    $stmt->execute();
                    $idRack = $stmt->insert_id;
                    $stmt->close();

                    if ($idRack) {
                        // Insertar lecturas históricas de las últimas horas
                        for ($h = 6; $h >= 0; $h--) {
                            $variacion = ($h % 2 === 0 ? -0.4 : 0.3);
                            $temp = round($r[8] + $variacion, 2);
                            $hum = round($r[9] + ($h % 3), 2);
                            $kw = round($r[10], 2);
                            $puerta = ($h === 0) ? $r[11] : 0;
                            $this->conexion->query("INSERT INTO lecturas_sensores (id_rack, temperatura, humedad, consumo_kw, puerta_abierta, fecha_lectura)
                                VALUES ($idRack, $temp, $hum, $kw, $puerta, DATE_SUB(NOW(), INTERVAL " . ($h * 2) . " HOUR))");
                        }
                    }
                }
            }

            // Insertar alertas iniciales reales
            $this->conexion->query("INSERT INTO alertas (id_empresa, id_rack, tipo_alerta, mensaje, nivel, resuelta)
                SELECT $idEmp, id_rack, 'Temperatura Elevada', 'Rack 03 superó el umbral de 27.0 °C (Actual: 28.4 °C)', 'warning', 0
                FROM racks WHERE codigo_rack = 'RACK-03' LIMIT 1");
            $this->conexion->query("INSERT INTO alertas (id_empresa, id_rack, tipo_alerta, mensaje, nivel, resuelta)
                SELECT $idEmp, id_rack, 'Apertura Física de Puerta', 'Puerta de Rack 04 abierta fuera de ventana de mantenimiento', 'critical', 0
                FROM racks WHERE codigo_rack = 'RACK-04' LIMIT 1");
        }
    }
}
?>

