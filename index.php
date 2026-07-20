<?php
require_once 'config/config.php';

// Verificacion de página para redireccionar al usuario (por defecto 'home')
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Enrutador sencillo
switch ($page) {
    case 'login':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->index();
        break;
    case 'registrar':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->registrar(); 
        break;
        
    case 'autenticar':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;
        
    case 'admin':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->dashboard();
        break;
        
    case 'admin_users':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->usuarios();
        break;

    case 'admin_mensaje_leido':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->marcarMensajeLeido();
        break;
        
    case 'admin_users_crear':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->crearUsuario();
        break;

    case 'admin_users_editar':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->actualizarUsuario();
        break;

    case 'admin_users_eliminar':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->eliminarUsuario();
        break;

        
    case 'user':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        $controller->dashboard();
        break;
        
    case 'perfil':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        $controller->perfil();
        break;
        
    case 'procesar_contacto':
        require_once 'controllers/HomeController.php';
        $controller = new HomeController();
        $controller->procesarContacto();
        break;

    case 'home':
    default:
        require_once 'controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
}
