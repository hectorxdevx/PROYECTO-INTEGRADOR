CREATE DATABASE IF NOT EXISTS server_monitor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE server_monitor;

-- ==========================================================
-- 1. TABLA DE ROLES (Modelo de Negocio de 4 Niveles)
-- ==========================================================
CREATE TABLE IF NOT EXISTS rol (
    id_rol TINYINT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(150) NULL
);

INSERT IGNORE INTO rol (id_rol, nombre_rol, descripcion) VALUES
(1, 'operador', 'Operador Institucional - Monitoreo en tiempo real de racks y sensores'),
(2, 'admin', 'Super Administrador - Aprueba empresas y genera correos institucionales'),
(3, 'representante', 'Representante de Empresa - Cuenta personal (Google/Microsoft) para dar de alta su institucion'),
(4, 'jefe_ti', 'Jefe de TI Institucional - Monitoreo, gestion de racks, umbrales y bitacora');

-- ==========================================================
-- 2. TABLA DE USUARIOS (Soporte OAuth 2.0 + Google Authenticator TOTP)
-- ==========================================================
CREATE TABLE IF NOT EXISTS users (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT NULL,
    id_rol TINYINT NOT NULL DEFAULT 1,
    nombre_usuario VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo_electronico VARCHAR(320) UNIQUE NOT NULL,
    contrasena VARCHAR(256) NOT NULL,
    oauth_provider ENUM('institucional', 'google', 'microsoft') NOT NULL DEFAULT 'institucional',
    oauth_uid VARCHAR(128) NULL,
    mfa_secret VARCHAR(64) NULL,
    mfa_enabled TINYINT(1) NOT NULL DEFAULT 0,
    foto_perfil VARCHAR(255) DEFAULT 'default.png',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rol FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);

-- ==========================================================
-- 3. TABLA DE EMPRESAS / INSTITUCIONES
-- ==========================================================
CREATE TABLE IF NOT EXISTS empresas (
    id_empresa INT PRIMARY KEY AUTO_INCREMENT,
    id_representante INT NOT NULL,
    nombre_empresa VARCHAR(150) NOT NULL,
    rfc_nit VARCHAR(50) NOT NULL,
    slug_dominio VARCHAR(100) UNIQUE NOT NULL,
    sector VARCHAR(100) DEFAULT 'Infraestructura & DataCenter',
    telefono_contacto VARCHAR(30) NULL,
    estado ENUM('pendiente', 'aprobada', 'suspendida') NOT NULL DEFAULT 'pendiente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_empresa_rep FOREIGN KEY (id_representante) REFERENCES users(id_usuario) ON DELETE CASCADE
);

-- ==========================================================
-- 4. SOLICITUDES DE CORREOS INSTITUCIONALES (Jefe de TI y Operadores)
-- ==========================================================
CREATE TABLE IF NOT EXISTS solicitudes_cuentas (
    id_solicitud INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    rol_solicitado ENUM('jefe_ti', 'operador') NOT NULL DEFAULT 'operador',
    correo_asignado VARCHAR(320) NULL,
    password_temporal VARCHAR(100) NULL,
    estado ENUM('pendiente', 'aprobada', 'rechazada') NOT NULL DEFAULT 'pendiente',
    id_usuario_creado INT NULL,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitud_empresa FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE
);

-- ==========================================================
-- 5. TABLA DE RACKS IOT POR EMPRESA
-- ==========================================================
CREATE TABLE IF NOT EXISTS racks (
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
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rack_empresa FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE
);

-- ==========================================================
-- 6. LECTURAS DE TELEMETRÍA IOT (Sensores)
-- ==========================================================
CREATE TABLE IF NOT EXISTS lecturas_sensores (
    id_lectura BIGINT PRIMARY KEY AUTO_INCREMENT,
    id_rack INT NOT NULL,
    temperatura DECIMAL(5,2) NOT NULL,
    humedad DECIMAL(5,2) NOT NULL,
    consumo_kw DECIMAL(6,2) NOT NULL,
    puerta_abierta TINYINT(1) NOT NULL DEFAULT 0,
    voltaje DECIMAL(6,2) DEFAULT 120.00,
    fecha_lectura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lectura_rack FOREIGN KEY (id_rack) REFERENCES racks(id_rack) ON DELETE CASCADE
);

-- ==========================================================
-- 7. ALERTAS AUTOMÁTICAS DE INFRAESTRUCTURA
-- ==========================================================
CREATE TABLE IF NOT EXISTS alertas (
    id_alerta INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT NOT NULL,
    id_rack INT NOT NULL,
    tipo_alerta VARCHAR(60) NOT NULL,
    mensaje VARCHAR(255) NOT NULL,
    nivel ENUM('info', 'warning', 'critical') NOT NULL DEFAULT 'warning',
    resuelta TINYINT(1) NOT NULL DEFAULT 0,
    fecha_alerta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alerta_empresa FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE,
    CONSTRAINT fk_alerta_rack FOREIGN KEY (id_rack) REFERENCES racks(id_rack) ON DELETE CASCADE
);

-- ==========================================================
-- 8. BITÁCORA DE AUDITORÍA Y SEGURIDAD EXTREMA
-- ==========================================================
CREATE TABLE IF NOT EXISTS bitacora_accesos (
    id_evento INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT NULL,
    id_usuario INT NULL,
    tipo_evento VARCHAR(60) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    ip_origen VARCHAR(45) DEFAULT '127.0.0.1',
    fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- 9. BUZÓN DE CONTACTO (LANDING PAGE)
-- ==========================================================
CREATE TABLE IF NOT EXISTS contactos (
    id_contacto INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(255) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    leido TINYINT(1) NOT NULL DEFAULT 0,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

