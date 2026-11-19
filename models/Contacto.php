<?php
require_once 'models/Database.php';

class Contacto {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    public function guardarMensaje($nombre, $correo, $asunto, $mensaje, $ipOrigen = null) {
        $ip = $ipOrigen ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $stmt = $this->db->prepare("INSERT INTO contactos (nombre, correo, asunto, mensaje, ip_origen) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("sssss", $nombre, $correo, $asunto, $mensaje, $ip);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
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
