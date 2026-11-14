<?php
require_once 'config/config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    // --- AUTENTICACIÓN (OAuth Google/Microsoft + Login Institucional + Google Authenticator MFA) ---
    case 'login':
        require_once 'controllers/AuthController.php';
        (new AuthController())->index();
        break;
    case 'autenticar':
        require_once 'controllers/AuthController.php';
        (new AuthController())->login();
        break;
    case 'oauth_login':
        require_once 'controllers/AuthController.php';
        (new AuthController())->oauthLogin();
        break;
    case 'oauth_callback':
        require_once 'controllers/AuthController.php';
        (new AuthController())->oauthCallback();
        break;
    case 'mfa_setup':
        require_once 'controllers/AuthController.php';
        (new AuthController())->mfaSetup();
        break;
    case 'mfa_verify':
        require_once 'controllers/AuthController.php';
        (new AuthController())->mfaVerify();
        break;
    case 'logout':
        require_once 'controllers/AuthController.php';
        (new AuthController())->logout();
        break;

    // --- PANEL REPRESENTANTE (CUENTA PERSONAL GOOGLE/MICROSOFT) ---
    case 'representante':
        require_once 'controllers/RepresentanteController.php';
        (new RepresentanteController())->dashboard();
        break;
    case 'rep_registrar_empresa':
        require_once 'controllers/RepresentanteController.php';
        (new RepresentanteController())->registrarEmpresa();
        break;
    case 'rep_solicitar_cuenta':
        require_once 'controllers/RepresentanteController.php';
        (new RepresentanteController())->solicitarCuenta();
        break;

    // --- PANEL SUPER ADMINISTRADOR ---
    case 'admin':
        require_once 'controllers/AdminController.php';
        (new AdminController())->dashboard();
        break;
    case 'admin_empresas':
        require_once 'controllers/AdminController.php';
        (new AdminController())->empresas();
        break;
    case 'admin_aprobar_empresa':
        require_once 'controllers/AdminController.php';
        (new AdminController())->aprobarEmpresa();
        break;
    case 'admin_aprobar_solicitud':
        require_once 'controllers/AdminController.php';
        (new AdminController())->aprobarSolicitudCuenta();
        break;
    case 'admin_mensaje_leido':
        require_once 'controllers/AdminController.php';
        (new AdminController())->marcarMensajeLeido();
        break;
    case 'admin_users':
        require_once 'controllers/AdminController.php';
        (new AdminController())->usuarios();
        break;
    case 'admin_users_crear':
        require_once 'controllers/AdminController.php';
        (new AdminController())->crearUsuario();
        break;
    case 'admin_users_editar':
        require_once 'controllers/AdminController.php';
        (new AdminController())->actualizarUsuario();
        break;
    case 'admin_users_eliminar':
        require_once 'controllers/AdminController.php';
        (new AdminController())->eliminarUsuario();
        break;

    // --- PANEL MONITOREO INSTITUCIONAL (JEFE DE TI Y OPERADOR) ---
    case 'user':
        require_once 'controllers/UserController.php';
        (new UserController())->dashboard();
        break;
    case 'user_crear_rack':
        require_once 'controllers/UserController.php';
        (new UserController())->crearRack();
        break;
    case 'user_actualizar_rack':
        require_once 'controllers/UserController.php';
        (new UserController())->actualizarRack();
        break;
    case 'user_resolver_alerta':
        require_once 'controllers/UserController.php';
        (new UserController())->resolverAlerta();
        break;
    case 'user_simular_lectura':
        require_once 'controllers/UserController.php';
        (new UserController())->simularLectura();
        break;
    case 'perfil':
        require_once 'controllers/UserController.php';
        (new UserController())->perfil();
        break;
    case 'perfil_actualizar':
        require_once 'controllers/UserController.php';
        (new UserController())->actualizarPerfil();
        break;
    case 'upload_photo':
        require_once 'controllers/UserController.php';
        (new UserController())->subirFoto();
        break;

    // --- LANDING PAGE & CONTACTO ---
    case 'procesar_contacto':
        require_once 'controllers/HomeController.php';
        (new HomeController())->procesarContacto();
        break;
    case 'home':
    default:
        require_once 'controllers/HomeController.php';
        (new HomeController())->index();
        break;
}

