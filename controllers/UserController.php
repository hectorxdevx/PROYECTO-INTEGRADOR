<?php
require_once __DIR__ . '/../models/Sensor.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/TOTP.php';

class UserController {

    public function dashboard() {
        $sensorModel = new Sensor();
        $usuarioModel = new Usuario();
        $empresaModel = new Empresa();

        $idEmpresa = $_SESSION['id_empresa'] ?? null;
        $rolUsuario = (int)($_SESSION['rol'] ?? 1); // 4 = Jefe de TI, 1 = Operador
        $esJefeTi = ($rolUsuario === 4 || $rolUsuario === 2);

        // Si no hay sesión activa en demo local, cargar la empresa 1 por defecto para evitar pantallas vacías
        if (!$idEmpresa) {
            $todasEmp = $empresaModel->obtenerTodas();
            if (!empty($todasEmp)) {
                $idEmpresa = (int)$todasEmp[0]['id_empresa'];
            }
        }

        $empresaActual = $idEmpresa ? $empresaModel->obtenerPorId((int)$idEmpresa) : null;
        $racks = $sensorModel->obtenerRacksConUltimaLectura($idEmpresa ? (int)$idEmpresa : null);
        $alertas = $sensorModel->obtenerAlertas($idEmpresa ? (int)$idEmpresa : null, false, 10);
        $historial24h = $sensorModel->obtenerHistorialLecturas($idEmpresa ? (int)$idEmpresa : null, null, 12);
        $equipoEmpresa = ($esJefeTi && $idEmpresa) ? $usuarioModel->obtenerPorEmpresa((int)$idEmpresa) : [];
        $bitacoraEmpresa = ($esJefeTi && $idEmpresa) ? $sensorModel->obtenerBitacora((int)$idEmpresa, 12) : [];

        require_once 'views/user/dashboard.php';
    }

    /**
     * Exclusivo para Jefe de TI (rol 4): Dar de alta un nuevo Rack IoT en su empresa.
     */
    public function crearRack() {
        $rolUsuario = (int)($_SESSION['rol'] ?? 1);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($rolUsuario === 4 || $rolUsuario === 2)) {
            $idEmpresa = (int)($_SESSION['id_empresa'] ?? $_POST['id_empresa'] ?? 1);
            $codigoRack = strtoupper(htmlspecialchars(trim($_POST['codigo_rack'] ?? 'RACK-NEW')));
            $nombreRack = htmlspecialchars(trim($_POST['nombre_rack'] ?? 'Nuevo Rack'));
            $ubicacion = htmlspecialchars(trim($_POST['ubicacion'] ?? 'Site Principal'));
            $tempMax = (float)($_POST['temp_max'] ?? 27.0);
            $humMax = (float)($_POST['hum_max'] ?? 60.0);
            $kwMax = (float)($_POST['kw_max'] ?? 2.5);

            $sensorModel = new Sensor();
            $sensorModel->crearRack($idEmpresa, $codigoRack, $nombreRack, $ubicacion, $tempMax, $humMax, $kwMax);
            (new Usuario())->registrarBitacora($idEmpresa, $_SESSION['usuario_id'] ?? null, 'CREAR_RACK', "Jefe de TI agregó el rack $codigoRack ($nombreRack)");
        }
        header("Location: " . BASE_URL . "/index.php?page=user&msg=rack_creado");
        exit();
    }

    /**
     * Exclusivo para Jefe de TI (rol 4): Configurar umbrales de alerta de un Rack.
     */
    public function actualizarRack() {
        $rolUsuario = (int)($_SESSION['rol'] ?? 1);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($rolUsuario === 4 || $rolUsuario === 2)) {
            $idRack = (int)($_POST['id_rack'] ?? 0);
            $idEmpresa = (int)($_SESSION['id_empresa'] ?? $_POST['id_empresa'] ?? 1);
            $nombreRack = htmlspecialchars(trim($_POST['nombre_rack'] ?? ''));
            $ubicacion = htmlspecialchars(trim($_POST['ubicacion'] ?? ''));
            $tempMax = (float)($_POST['temp_max'] ?? 27.0);
            $humMax = (float)($_POST['hum_max'] ?? 60.0);
            $kwMax = (float)($_POST['kw_max'] ?? 2.5);

            if ($idRack > 0) {
                $sensorModel = new Sensor();
                $sensorModel->actualizarUmbralesRack($idRack, $idEmpresa, $nombreRack, $ubicacion, $tempMax, $humMax, $kwMax);
                (new Usuario())->registrarBitacora($idEmpresa, $_SESSION['usuario_id'] ?? null, 'UMBRALES_RACK', "Jefe de TI actualizó umbrales de Rack #$idRack (Temp máx: {$tempMax}°C)");
            }
        }
        header("Location: " . BASE_URL . "/index.php?page=user&msg=umbrales_actualizados");
        exit();
    }

    public function resolverAlerta() {
        if (isset($_GET['id'])) {
            $idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : null;
            (new Sensor())->resolverAlerta((int)$_GET['id'], $idEmpresa);
        }
        header("Location: " . BASE_URL . "/index.php?page=user&msg=alerta_resuelta");
        exit();
    }

    public function simularLectura() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idRack = (int)($_POST['id_rack'] ?? 1);
            $temp = (float)($_POST['temperatura'] ?? 23.0);
            $hum = (float)($_POST['humedad'] ?? 45.0);
            $kw = (float)($_POST['consumo_kw'] ?? 1.2);
            $puerta = isset($_POST['puerta_abierta']) ? (int)$_POST['puerta_abierta'] : 0;

            (new Sensor())->registrarLectura($idRack, $temp, $hum, $kw, $puerta);
        }
        header("Location: " . BASE_URL . "/index.php?page=user&msg=telemetria_registrada");
        exit();
    }

    public function perfil() {
        $usuarioModel = new Usuario();
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 1);
        $usuario = $usuarioModel->obtenerPorId($idUsuario);
        if ($usuario && empty($usuario['mfa_secret'])) {
            $secreto = TOTP::generarSecreto();
            $usuarioModel->guardarSecretoMfa($idUsuario, $secreto, 1);
            $usuario['mfa_secret'] = $secreto;
        }
        $qrMfaUrl = $usuario ? TOTP::obtenerQrImageUrl($usuario['correo_electronico'], $usuario['mfa_secret'], MFA_ISSUER) : '';
        require_once 'views/user/perfil.php';
    }

    public function actualizarPerfil() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_id'])) {
            $idUsuario = (int)$_SESSION['usuario_id'];
            $nombre = htmlspecialchars(trim($_POST['nombre_usuario'] ?? ''));
            $apellidos = htmlspecialchars(trim($_POST['apellidos_usuario'] ?? ''));
            $password = trim($_POST['password'] ?? '');

            $hash = null;
            if (!empty($password) && strlen($password) >= 8) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
            }

            $usuarioModel = new Usuario();
            $usuarioModel->actualizarPerfil($idUsuario, $nombre, $apellidos, $hash);
            $_SESSION['nombre_usuario'] = $nombre;
            $_SESSION['apellidos'] = $apellidos;
            header("Location: " . BASE_URL . "/index.php?page=perfil&msg=perfil_actualizado");
            exit();
        }
        header("Location: " . BASE_URL . "/index.php?page=perfil");
        exit();
    }

    public function subirFoto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto']) && isset($_SESSION['usuario_id'])) {
            $file = $_FILES['foto'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $nuevoNombre = 'avatar_' . (int)$_SESSION['usuario_id'] . '_' . time() . '.' . $ext;
                    $destino = __DIR__ . '/../assets/uploads/' . $nuevoNombre;
                    if (move_uploaded_file($file['tmp_name'], $destino)) {
                        (new Usuario())->actualizarFoto((int)$_SESSION['usuario_id'], $nuevoNombre);
                        $_SESSION['foto_perfil'] = $nuevoNombre;
                    }
                }
            }
        }
        header("Location: " . BASE_URL . "/index.php?page=perfil&msg=foto_actualizada");
        exit();
    }
}

