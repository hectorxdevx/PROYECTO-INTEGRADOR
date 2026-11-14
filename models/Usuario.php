<?php
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/TOTP.php";

class Usuario {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->conectar();
    }

    public function guardar(
        string $nombre,
        string $apellidos,
        string $correo,
        string $contrasenaSegura,
        int $id_rol = 1,
        ?int $id_empresa = null,
        string $oauth_provider = 'institucional',
        ?string $oauth_uid = null
    ) {
        $mfaSecret = TOTP::generarSecreto();
        $sql = "INSERT INTO users (id_empresa, id_rol, nombre_usuario, apellidos, correo_electronico, contrasena, oauth_provider, oauth_uid, mfa_secret, mfa_enabled)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("iisssssss", $id_empresa, $id_rol, $nombre, $apellidos, $correo, $contrasenaSegura, $oauth_provider, $oauth_uid, $mfaSecret);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Obtiene o registra automáticamente a un Representante (id_rol = 3) autenticado con Google o Microsoft OAuth 2.0.
     */
    public function obtenerOCrearRepresentanteOAuth(string $provider, string $oauthUid, string $nombre, string $apellidos, string $correo): ?array {
        $usuario = $this->obtenerPorCorreo($correo);
        if ($usuario) {
            // Si no tenía mfa_secret aún, generarlo
            if (empty($usuario['mfa_secret'])) {
                $nuevoSecreto = TOTP::generarSecreto();
                $this->guardarSecretoMfa((int)$usuario['id_usuario'], $nuevoSecreto, 0);
                $usuario['mfa_secret'] = $nuevoSecreto;
            }
            return $usuario;
        }

        $mfaSecret = TOTP::generarSecreto();
        $randomPass = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        $idRolRepresentante = 3;

        $sql = "INSERT INTO users (id_rol, nombre_usuario, apellidos, correo_electronico, contrasena, oauth_provider, oauth_uid, mfa_secret, mfa_enabled)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("isssssss", $idRolRepresentante, $nombre, $apellidos, $correo, $randomPass, $provider, $oauthUid, $mfaSecret);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }
        $stmt->close();

        return $this->obtenerPorCorreo($correo);
    }

    public function guardarSecretoMfa(int $idUsuario, string $secreto, int $habilitado = 1): bool {
        $stmt = $this->db->prepare("UPDATE users SET mfa_secret = ?, mfa_enabled = ? WHERE id_usuario = ?");
        if (!$stmt) return false;
        $stmt->bind_param("sii", $secreto, $habilitado, $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function activarMfa(int $idUsuario): bool {
        $stmt = $this->db->prepare("UPDATE users SET mfa_enabled = 1 WHERE id_usuario = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function existeCorreo(string $correo): bool {
        $sql = "SELECT id_usuario FROM users WHERE correo_electronico = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $existe = $resultado->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    public function obtenerPorCorreo(string $correo): ?array {
        $sql = "SELECT u.*, r.nombre_rol, e.nombre_empresa, e.slug_dominio, e.estado AS estado_empresa
                FROM users u
                LEFT JOIN rol r ON u.id_rol = r.id_rol
                LEFT JOIN empresas e ON u.id_empresa = e.id_empresa
                WHERE u.correo_electronico = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        return $usuario ?: null;
    }

    public function obtenerPorId(int $idUsuario): ?array {
        $sql = "SELECT u.*, r.nombre_rol, e.nombre_empresa, e.slug_dominio, e.estado AS estado_empresa
                FROM users u
                LEFT JOIN rol r ON u.id_rol = r.id_rol
                LEFT JOIN empresas e ON u.id_empresa = e.id_empresa
                WHERE u.id_usuario = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        return $usuario ?: null;
    }

    public function obtenerTodos(): array {
        $sql = "SELECT u.id_usuario, u.id_empresa, u.nombre_usuario, u.apellidos, u.correo_electronico,
                       u.id_rol, u.oauth_provider, u.mfa_enabled, u.mfa_secret, u.foto_perfil, u.fecha_creacion,
                       r.nombre_rol, e.nombre_empresa, e.slug_dominio
                FROM users u
                LEFT JOIN rol r ON u.id_rol = r.id_rol
                LEFT JOIN empresas e ON u.id_empresa = e.id_empresa
                ORDER BY u.id_usuario DESC";
        $resultado = $this->db->query($sql);

        $usuarios = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $usuarios[] = $fila;
            }
        }
        return $usuarios;
    }

    public function obtenerPorEmpresa(int $idEmpresa): array {
        $sql = "SELECT u.id_usuario, u.nombre_usuario, u.apellidos, u.correo_electronico, u.id_rol,
                       u.oauth_provider, u.mfa_enabled, u.fecha_creacion, r.nombre_rol
                FROM users u
                LEFT JOIN rol r ON u.id_rol = r.id_rol
                WHERE u.id_empresa = ?
                ORDER BY u.id_rol DESC, u.id_usuario ASC";
        $stmt = $this->db->prepare($sql);
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

    public function eliminar($id): bool {
        $sql = "DELETE FROM users WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    public function actualizar($id, $nombre, $apellidos, $correo, $id_rol, $id_empresa = null): bool {
        $sql = "UPDATE users SET nombre_usuario = ?, apellidos = ?, correo_electronico = ?, id_rol = ?, id_empresa = ? WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $idEmpVal = !empty($id_empresa) ? (int)$id_empresa : null;
        $stmt->bind_param("sssiii", $nombre, $apellidos, $correo, $id_rol, $idEmpVal, $id);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    public function actualizarPerfil(int $idUsuario, string $nombre, string $apellidos, ?string $nuevaContrasenaHash = null): bool {
        if ($nuevaContrasenaHash) {
            $stmt = $this->db->prepare("UPDATE users SET nombre_usuario = ?, apellidos = ?, contrasena = ? WHERE id_usuario = ?");
            if (!$stmt) return false;
            $stmt->bind_param("sssi", $nombre, $apellidos, $nuevaContrasenaHash, $idUsuario);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET nombre_usuario = ?, apellidos = ? WHERE id_usuario = ?");
            if (!$stmt) return false;
            $stmt->bind_param("ssi", $nombre, $apellidos, $idUsuario);
        }
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function actualizarFoto(int $idUsuario, string $nombreArchivo): bool {
        $stmt = $this->db->prepare("UPDATE users SET foto_perfil = ? WHERE id_usuario = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $nombreArchivo, $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function registrarBitacora(?int $idEmpresa, ?int $idUsuario, string $tipoEvento, string $descripcion): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $this->db->prepare("INSERT INTO bitacora_accesos (id_empresa, id_usuario, tipo_evento, descripcion, ip_origen) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iisss", $idEmpresa, $idUsuario, $tipoEvento, $descripcion, $ip);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>

