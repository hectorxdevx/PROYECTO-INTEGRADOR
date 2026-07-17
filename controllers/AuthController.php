<?php
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {

    public function index() {
        require_once 'views/auth/login.php';
    }

    public function registrar() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $nombre = trim($_POST['nombre_usuario']);
            $apellidos = trim($_POST['apellidos']);
            $correo = trim($_POST['correo']);
            $contrasena = trim($_POST['contrasena']);
            $contrasenaSegura = password_hash($contrasena, PASSWORD_BCRYPT);

            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->guardar($nombre, $apellidos, $correo, $contrasenaSegura);
            if ($resultado) {
                header("Location: index.php?controller=auth&action=index&registro=exitoso");
                exit();
            } else {
                echo "Hubo un error al registrarte, intentalo de nuevo.";
            }
        }
    }
}

?>
