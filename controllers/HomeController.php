<?php
require_once __DIR__ . '/../models/Contacto.php';

class HomeController {
    public function index() {
        require_once 'views/home/index.php';
    }

    public function procesarContacto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
            $correo = filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
            $asunto = htmlspecialchars(trim($_POST['asunto'] ?? ''));
            $mensaje = htmlspecialchars(trim($_POST['mensaje'] ?? ''));

            if ($nombre && $correo && $asunto && $mensaje) {
                $contactoModel = new Contacto();
                $contactoModel->guardarMensaje($nombre, $correo, $asunto, $mensaje);
            }
            header("Location: " . BASE_URL . "/index.php?success=contacto#contacto");
            exit();
        }
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}

