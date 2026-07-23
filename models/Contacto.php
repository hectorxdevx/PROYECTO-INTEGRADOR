<?php
require_once 'models/Database.php';

class Contacto {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    public function guardarMensaje($nombre, $correo, $asunto, $mensaje) {
        $stmt = $this->db->prepare("INSERT INTO contactos (nombre, correo, asunto, mensaje) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $correo, $asunto, $mensaje);
        return $stmt->execute();
    }

    public function obtenerMensajesNoLeidos() {
        $stmt = $this->db->prepare("SELECT * FROM contactos WHERE leido = 0 ORDER BY fecha_envio DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mensajes = [];
        while($row = $result->fetch_assoc()) {
            $mensajes[] = $row;
        }
        return $mensajes;
    }
    
    public function contarNoLeidos() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM contactos WHERE leido = 0");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function marcarComoLeido($id) {
        $stmt = $this->db->prepare("UPDATE contactos SET leido = 1 WHERE id_contacto = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
