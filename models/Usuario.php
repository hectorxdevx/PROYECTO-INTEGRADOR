<?php
/*
 * Archivo: Usuario.php
 * Descripción: En este archivo se establecerá el modelo para gestionar los usuarios
 * del sistema. Contendrá métodos para la autenticación (login), registro, actualización
 * de perfiles y la gestión de roles (Administrador, Usuario, etc.).
 * (Aún sin código funcional, solo estructura planeada).
 */
require_once __DIR__ . "/Database.php";

class Usuario {
    private $db;

    public function __construct()
    {
    $database = new Database();
    $this->db = $database->conectar();
    }

    public function guardar(string $nombre, string $apellidos, string $correo, string $contrasenaSegura, int $id_rol = 1) 
    {
        // Permitimos asignar un rol específico, si no se envía, por defecto es 1 (usuario normal)
        $sql = "INSERT INTO users(nombre_usuario, apellidos, correo_electronico, contrasena, id_rol) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ssssi", $nombre, $apellidos, $correo, $contrasenaSegura, $id_rol);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function existeCorreo(string $correo)
    {
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

    public function obtenerPorCorreo(string $correo)
    {
        // Traemos toda la fila (*) del usuario que tenga este correo
        $sql = "SELECT * FROM users WHERE correo_electronico = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;
        
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        // fetch_assoc lo convierte en un arreglo (caja con datos) para usar en PHP
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        return $usuario;
    }

    public function obtenerTodos()
    {
        // Traemos todos los usuarios y juntamos (JOIN) la tabla 'rol' para saber el nombre de su rol
        $sql = "SELECT u.id_usuario, u.nombre_usuario, u.apellidos, u.correo_electronico, u.id_rol, r.nombre_rol 
                FROM users u 
                LEFT JOIN rol r ON u.id_rol = r.id_rol
                ORDER BY u.id_usuario DESC";
        $resultado = $this->db->query($sql);
        
        $usuarios = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $usuarios[] = $fila; // Metemos cada usuario a una lista
            }
        }
        return $usuarios;
    }

    public function eliminar($id)
    {
        // Borramos al usuario que tenga este ID específico
        $sql = "DELETE FROM users WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        
        $stmt->bind_param("i", $id); // La "i" es porque el ID es un número Entero (Integer)
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    public function actualizar($id, $nombre, $apellidos, $correo, $id_rol)
    {
        // Actualizamos los datos del usuario en la base de datos
        $sql = "UPDATE users SET nombre_usuario = ?, apellidos = ?, correo_electronico = ?, id_rol = ? WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        
        // "sssii" significa String, String, String, Integer, Integer
        $stmt->bind_param("sssii", $nombre, $apellidos, $correo, $id_rol, $id);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }
}
?>
