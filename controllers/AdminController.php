<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Sensor.php';
require_once __DIR__ . '/../models/Contacto.php';

class AdminController {

    public function dashboard() {
        $sensorModel = new Sensor();
        $empresaModel = new Empresa();
        $contactoModel = new Contacto();

        $stats = $sensorModel->obtenerEstadisticasGlobales();
        $empresas = $empresaModel->obtenerTodas();
        $solicitudesPendientes = $empresaModel->obtenerSolicitudesPendientes();
        $alertasRecientes = $sensorModel->obtenerAlertas(null, false, 8);
        $bitacora = $sensorModel->obtenerBitacora(null, 10);
        $mensajesNoLeidos = $contactoModel->obtenerMensajesNoLeidos();
        $totalNoLeidos = $contactoModel->contarNoLeidos();

        require_once 'views/admin/dashboard.php';
    }

    public function empresas() {
        $empresaModel = new Empresa();
        $empresas = $empresaModel->obtenerTodas();
        $solicitudes = $empresaModel->obtenerSolicitudesPendientes();
        require_once 'views/admin/empresas.php';
    }

    public function aprobarEmpresa() {
        $idEmpresa = (int)($_POST['id_empresa'] ?? $_GET['id'] ?? 0);
        $estado = $_POST['estado'] ?? $_GET['estado'] ?? 'aprobada';
        if ($idEmpresa > 0 && in_array($estado, ['aprobada', 'suspendida', 'pendiente'])) {
            $empresaModel = new Empresa();
            $empresaModel->cambiarEstadoEmpresa($idEmpresa, $estado);
            (new Usuario())->registrarBitacora($idEmpresa, $_SESSION['usuario_id'] ?? null, 'ADMIN_EMPRESA', "SuperAdmin cambió estado de empresa #$idEmpresa a $estado");
        }
        $redirect = $_GET['from'] ?? 'admin_empresas';
        header("Location: " . BASE_URL . "/index.php?page=" . $redirect . "&msg=empresa_actualizada");
        exit();
    }

    public function aprobarSolicitudCuenta() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idSolicitud = (int)($_POST['id_solicitud'] ?? 0);
            $correoPersonalizado = trim($_POST['correo_asignado'] ?? '');
            $passwordInicial = trim($_POST['password_temporal'] ?? '');

            if ($idSolicitud > 0) {
                $empresaModel = new Empresa();
                $creado = $empresaModel->aprobarYGenerarCorreoInstitucional(
                    $idSolicitud,
                    $correoPersonalizado !== '' ? $correoPersonalizado : null,
                    $passwordInicial !== '' ? $passwordInicial : null
                );

                if ($creado) {
                    (new Usuario())->registrarBitacora(
                        null,
                        $_SESSION['usuario_id'] ?? null,
                        'PROVISION_CORREO',
                        "Correo institucional generado: {$creado['correo_institucional']} ({$creado['rol']})"
                    );
                }
            }
        }
        $redirect = $_POST['redirect_to'] ?? 'admin_empresas';
        header("Location: " . BASE_URL . "/index.php?page=" . $redirect . "&msg=correo_generado");
        exit();
    }

    public function marcarMensajeLeido() {
        if (isset($_GET['id'])) {
            $contactoModel = new Contacto();
            $contactoModel->marcarComoLeido((int)$_GET['id']);
        }
        header("Location: " . BASE_URL . "/index.php?page=admin");
        exit();
    }

    public function usuarios() {
        $usuarioModel = new Usuario();
        $empresaModel = new Empresa();
        $usuarios = $usuarioModel->obtenerTodos();
        $empresas = $empresaModel->obtenerTodas();
        require_once 'views/admin/usuarios.php';
    }

    public function crearUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuarioModel = new Usuario();

            $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
            $apellidos = htmlspecialchars(trim($_POST['apellidos'] ?? ''));
            $correo = strtolower(filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL));
            $password = $_POST['password'] ?? 'SysMonitor2026!';
            $rol = (int)($_POST['rol'] ?? 1);
            $idEmpresa = !empty($_POST['id_empresa']) ? (int)$_POST['id_empresa'] : null;

            if ($correo && !$usuarioModel->existeCorreo($correo)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $usuarioModel->guardar($nombre, $apellidos, $correo, $hash, $rol, $idEmpresa, 'institucional');
            }

            header("Location: " . BASE_URL . "/index.php?page=admin_users&msg=creado");
            exit();
        }
    }

    public function actualizarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuarioModel = new Usuario();

            $id = (int)($_POST['id_usuario'] ?? 0);
            $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
            $apellidos = htmlspecialchars(trim($_POST['apellidos'] ?? ''));
            $correo = strtolower(filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL));
            $rol = (int)($_POST['rol'] ?? 1);
            $idEmpresa = !empty($_POST['id_empresa']) ? (int)$_POST['id_empresa'] : null;

            if ($id > 0) {
                $usuarioModel->actualizar($id, $nombre, $apellidos, $correo, $rol, $idEmpresa);
            }

            header("Location: " . BASE_URL . "/index.php?page=admin_users&msg=actualizado");
            exit();
        }
    }

    public function eliminarUsuario() {
        if (isset($_GET['id'])) {
            $usuarioModel = new Usuario();
            $usuarioModel->eliminar((int)$_GET['id']);
        }
        header("Location: " . BASE_URL . "/index.php?page=admin_users");
        exit();
    }
}

