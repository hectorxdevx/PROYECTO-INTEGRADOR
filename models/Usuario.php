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

    public function guardar(string$nombre, string$apellidos, string$correo, string$contrasenaSegura) 
    {
    $sql = "INSERT INTO users(nombre_usuario, apellidos, correo_electronico, contrasena) 
    VALUES (?, ?, ?, ?)";
    $stmt = $this->db->prepare($sql);

    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("ssss", $nombre, $apellidos, $correo, $contrasenaSegura);
    $resultado = $stmt->execute();
    $stmt->close();
    return $resultado;
    }
}

?>
