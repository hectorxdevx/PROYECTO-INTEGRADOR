<?php
class HomeController {
    public function index() {
        // Carga la vista principal (Landing Page)
        require_once 'views/home/index.php';
    }

    public function procesarContacto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'models/Contacto.php';
            $contactoModel = new Contacto();
            
            $nombre = $_POST['nombre'] ?? '';
            $correo = $_POST['correo'] ?? '';
            $asunto = $_POST['asunto'] ?? '';
            $mensaje = $_POST['mensaje'] ?? '';

            if(!empty($nombre) && !empty($correo) && !empty($mensaje)) {
                $contactoModel->guardarMensaje($nombre, $correo, $asunto, $mensaje);
            }
            
            header("Location: " . BASE_URL . "/index.php?success=contacto#contacto");
            exit();
        }
    }
}
