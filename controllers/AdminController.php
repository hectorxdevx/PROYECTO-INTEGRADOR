<?php
class AdminController {
    public function dashboard() {


        require_once 'views/admin/dashboard.php';
    }


    public function usuarios() {
        // Necesitamos el modelo para obtener los datos reales de la base de datos
        require_once 'models/Usuario.php';
        $usuarioModel = new Usuario();
        
        // Pedimos todos los usuarios a la base de datos
        $usuarios = $usuarioModel->obtenerTodos();
        
        // Pasamos la variable $usuarios a la vista
        require_once 'views/admin/usuarios.php';
    }

    public function crearUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'models/Usuario.php';
            $usuarioModel = new Usuario();
            
            $nombre = $_POST['nombre'] ?? '';
            $apellidos = $_POST['apellidos'] ?? '';
            $correo = $_POST['correo'] ?? '';
            $password = $_POST['password'] ?? '';
            $rol = $_POST['rol'] ?? 1;

            if (!$usuarioModel->existeCorreo($correo)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $usuarioModel->guardar($nombre, $apellidos, $correo, $hash, $rol);
            }
            
            // Volvemos a la página de usuarios
            header("Location: " . BASE_URL . "/index.php?page=admin_users");
            exit();
        }
    }

    public function actualizarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'models/Usuario.php';
            $usuarioModel = new Usuario();
            
            $id = $_POST['id_usuario'] ?? 0;
            $nombre = $_POST['nombre'] ?? '';
            $apellidos = $_POST['apellidos'] ?? '';
            $correo = $_POST['correo'] ?? '';
            $rol = $_POST['rol'] ?? 1;

            if ($id > 0) {
                $usuarioModel->actualizar($id, $nombre, $apellidos, $correo, $rol);
            }
            
            header("Location: " . BASE_URL . "/index.php?page=admin_users");
            exit();
        }
    }

    public function eliminarUsuario() {
        if (isset($_GET['id'])) {
            require_once 'models/Usuario.php';
            $usuarioModel = new Usuario();
            $usuarioModel->eliminar($_GET['id']);
        }
        header("Location: " . BASE_URL . "/index.php?page=admin_users");
        exit();
    }
}
