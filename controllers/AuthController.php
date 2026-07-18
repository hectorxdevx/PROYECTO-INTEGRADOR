<?php
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {

    public function index() {
        require_once 'views/auth/login.php';
    }

    public function registrar() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // Limpiamos la basura o código malicioso que puedan escribir
            $nombre = htmlspecialchars(trim($_POST['nombre_usuario']));
            $apellidos = htmlspecialchars(trim($_POST['apellidos']));
            $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
            $contrasena = trim($_POST['contrasena']);

            // 1. Validar el dominio del correo por si hicieron trampa en el navegador
            $dominios_permitidos = ['gmail.com', 'hotmail.com'];
            $partes_correo = explode('@', $correo);
            $dominio = end($partes_correo);

            if (!in_array($dominio, $dominios_permitidos)) {
                header("Location: index.php?page=login&error=" . urlencode("Solo se permiten correos de @gmail.com o @hotmail.com"));
                exit();
            }

            // 2. Revisar la longitud de la contraseña de nuevo
            if (strlen($contrasena) < 8) {
                header("Location: index.php?page=login&error=" . urlencode("La contraseña debe tener al menos 8 caracteres"));
                exit();
            }

            $usuarioModel = new Usuario();

            // 3. Revisar si el correo ya existe en nuestra base de datos
            if ($usuarioModel->existeCorreo($correo)) {
                header("Location: index.php?page=login&error=" . urlencode("El correo ya está registrado"));
                exit();
            }

            // Encriptamos la contraseña y guardamos
            $contrasenaSegura = password_hash($contrasena, PASSWORD_BCRYPT);
            $resultado = $usuarioModel->guardar($nombre, $apellidos, $correo, $contrasenaSegura);
            
            if ($resultado) {
                // Lo mandamos de regreso con un mensaje de éxito
                header("Location: index.php?page=login&registro=exitoso");
                exit();
            } else {
                // Lo mandamos de regreso con un error
                header("Location: index.php?page=login&error=" . urlencode("Hubo un error al registrarte, intentalo de nuevo."));
                exit();
            }
        }
    }
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $correo = filter_var(trim($_POST['correo_electronico']), FILTER_SANITIZE_EMAIL);
            $contrasena = trim($_POST['contrasena']);

            $usuarioModel = new Usuario();
            // Buscamos a la persona en la base de datos usando su correo
            $usuario = $usuarioModel->obtenerPorCorreo($correo);

            // Si existe la persona, y su contraseña coincide con la que tenemos guardada
            if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
                // Le ponemos un sello en la mano (iniciar sesión)
                session_start();
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                $_SESSION['rol'] = $usuario['id_rol']; // Guardamos su nivel de acceso
                
                // Le damos paso libre a la fiesta dependiendo de si es jefe o usuario normal
                if ($usuario['id_rol'] == 2) {
                    header("Location: index.php?page=admin");
                } else {
                    header("Location: index.php?page=user");
                }
                exit();
            } else {
                // Si se equivocó en correo o contraseña, lo devolvemos con error
                header("Location: index.php?page=login&error=" . urlencode("Correo o contraseña incorrectos"));
                exit();
            }
        }
    }
}
?>
