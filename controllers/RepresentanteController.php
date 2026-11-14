<?php
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Usuario.php';

class RepresentanteController {

    private function verificarAccesoRepresentante(): int {
        if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol'] ?? 0) !== 3) {
            header("Location: " . BASE_URL . "/index.php?page=login");
            exit();
        }
        return (int)$_SESSION['usuario_id'];
    }

    public function dashboard() {
        $idRepresentante = $this->verificarAccesoRepresentante();
        $empresaModel = new Empresa();
        $usuarioModel = new Usuario();

        $usuarioActual = $usuarioModel->obtenerPorId($idRepresentante);
        $empresa = $empresaModel->obtenerPorRepresentante($idRepresentante);
        $solicitudes = $empresa ? $empresaModel->obtenerSolicitudesPorEmpresa((int)$empresa['id_empresa']) : [];
        $personalInstitucional = $empresa ? $usuarioModel->obtenerPorEmpresa((int)$empresa['id_empresa']) : [];

        require_once 'views/representante/dashboard.php';
    }

    public function registrarEmpresa() {
        $idRepresentante = $this->verificarAccesoRepresentante();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombreEmpresa = htmlspecialchars(trim($_POST['nombre_empresa'] ?? ''));
            $rfcNit = strtoupper(htmlspecialchars(trim($_POST['rfc_nit'] ?? '')));
            $slugDominio = strtolower(trim($_POST['slug_dominio'] ?? ''));
            $sector = htmlspecialchars(trim($_POST['sector'] ?? 'DataCenter & Cloud'));
            $telefono = htmlspecialchars(trim($_POST['telefono'] ?? ''));

            if ($nombreEmpresa && $rfcNit && $slugDominio) {
                $empresaModel = new Empresa();
                $idEmpresa = $empresaModel->registrarEmpresa($idRepresentante, $nombreEmpresa, $rfcNit, $slugDominio, $sector, $telefono);
                if ($idEmpresa) {
                    $_SESSION['id_empresa'] = $idEmpresa;
                    (new Usuario())->registrarBitacora($idEmpresa, $idRepresentante, 'ALTA_EMPRESA', "Representante registró la empresa: $nombreEmpresa");
                    header("Location: " . BASE_URL . "/index.php?page=representante&msg=empresa_creada");
                    exit();
                }
            }
            header("Location: " . BASE_URL . "/index.php?page=representante&error=datos_invalidos");
            exit();
        }
    }

    public function solicitarCuenta() {
        $idRepresentante = $this->verificarAccesoRepresentante();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $empresaModel = new Empresa();
            $empresa = $empresaModel->obtenerPorRepresentante($idRepresentante);

            if ($empresa) {
                $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
                $apellidos = htmlspecialchars(trim($_POST['apellidos'] ?? ''));
                $rolSolicitado = ($_POST['rol_solicitado'] ?? 'operador') === 'jefe_ti' ? 'jefe_ti' : 'operador';

                if ($nombre && $apellidos) {
                    $empresaModel->crearSolicitudCuenta((int)$empresa['id_empresa'], $nombre, $apellidos, $rolSolicitado);
                    (new Usuario())->registrarBitacora((int)$empresa['id_empresa'], $idRepresentante, 'SOLICITUD_CORREO', "Solicitó correo institucional ($rolSolicitado) para $nombre $apellidos");
                    header("Location: " . BASE_URL . "/index.php?page=representante&msg=solicitud_enviada");
                    exit();
                }
            }
            header("Location: " . BASE_URL . "/index.php?page=representante&error=solicitud_fallida");
            exit();
        }
    }
}
?>
