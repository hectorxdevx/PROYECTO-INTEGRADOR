<?php
require_once __DIR__ . '/../models/Contacto.php';
require_once __DIR__ . '/../models/MailerService.php';

class HomeController {
    public function index() {
        require_once 'views/home/index.php';
    }

    public function procesarContacto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombreRaw = trim($_POST['nombre'] ?? '');
            $correoRaw = trim($_POST['correo'] ?? '');
            $asuntoRaw = trim($_POST['asunto'] ?? '');
            $mensajeRaw = trim($_POST['mensaje'] ?? '');
            $honeypot = trim($_POST['website_hp'] ?? '');

            $mailer = new MailerService();
            $resultadoEnvio = $mailer->enviarDudaContactoWeb($nombreRaw, $correoRaw, $asuntoRaw, $mensajeRaw, $honeypot);

            if (empty($resultadoEnvio['ok'])) {
                $motivo = $resultadoEnvio['motivo'] ?? 'Tu mensaje fue rechazado por el filtro de seguridad.';
                header("Location: " . BASE_URL . "/index.php?error=" . urlencode($motivo) . "#contacto");
                exit();
            }

            $nombre = htmlspecialchars(MailerService::sanitizarCabecera($nombreRaw), ENT_QUOTES, 'UTF-8');
            $correo = filter_var(MailerService::sanitizarCabecera($correoRaw), FILTER_SANITIZE_EMAIL);
            $asunto = htmlspecialchars(MailerService::sanitizarCabecera($asuntoRaw), ENT_QUOTES, 'UTF-8');
            $mensaje = htmlspecialchars($mensajeRaw, ENT_QUOTES, 'UTF-8');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $contactoModel = new Contacto();
            $contactoModel->guardarMensaje($nombre, $correo, $asunto, $mensaje, $ip);

            header("Location: " . BASE_URL . "/index.php?success=contacto_smtp#contacto");
            exit();
        }
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}

