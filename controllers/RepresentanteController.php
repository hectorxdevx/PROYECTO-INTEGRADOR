<?php
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/MailerService.php';

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
        if (isset($_GET['dismiss_jefe_ti'])) {
            unset($_SESSION['recien_creado_jefe_ti'], $_SESSION['recien_creado_empresa_meta']);
        }

        $empresaModel = new Empresa();
        $usuarioModel = new Usuario();
        $mailerService = new MailerService();

        $usuarioActual = $usuarioModel->obtenerPorId($idRepresentante);
        $empresa = $empresaModel->obtenerPorRepresentante($idRepresentante);
        $solicitudes = $empresa ? $empresaModel->obtenerSolicitudesPorEmpresa((int)$empresa['id_empresa']) : [];
        $personalInstitucional = $empresa ? $usuarioModel->obtenerPorEmpresa((int)$empresa['id_empresa']) : [];
        $usoPlan = $empresa ? $empresaModel->obtenerUsoPlan((int)$empresa['id_empresa']) : null;
        $correosEnviados = $empresa ? $mailerService->obtenerCorreos((int)$empresa['id_empresa'], 25) : [];

        require_once 'views/representante/dashboard.php';
    }

    public function registrarEmpresa() {
        $idRepresentante = $this->verificarAccesoRepresentante();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombreEmpresa = htmlspecialchars(trim($_POST['nombre_empresa'] ?? ''));
            $rfcNit = strtoupper(htmlspecialchars(trim($_POST['rfc_nit'] ?? '')));
            $slugDominio = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '', trim($_POST['slug_dominio'] ?? '')));
            $sector = htmlspecialchars(trim($_POST['sector'] ?? 'DataCenter & Cloud'));
            $telefono = htmlspecialchars(trim($_POST['telefono'] ?? ''));
            $plan = trim($_POST['plan_suscripcion'] ?? 'corporate');

            $planesValidos = defined('B2B_PLANS') ? array_keys(B2B_PLANS) : ['starter', 'corporate', 'enterprise'];
            if (!in_array($plan, $planesValidos, true)) {
                $plan = 'corporate';
            }

            if ($nombreEmpresa && $rfcNit && $slugDominio) {
                $empresaModel = new Empresa();
                $usuarioModel = new Usuario();
                $rep = $usuarioModel->obtenerPorId($idRepresentante);
                $correoRep = $rep['correo_electronico'] ?? ($_SESSION['correo_electronico'] ?? null);

                $res = $empresaModel->registrarEmpresa(
                    $idRepresentante,
                    $nombreEmpresa,
                    $rfcNit,
                    $slugDominio,
                    $sector !== '' ? $sector : 'DataCenter & Cloud',
                    $telefono,
                    $plan,
                    $correoRep
                );

                if (is_array($res) && !empty($res['id_empresa'])) {
                    $idEmpresa = (int)$res['id_empresa'];
                    $_SESSION['id_empresa'] = $idEmpresa;
                    if (!empty($res['jefe_ti_credenciales'])) {
                        $_SESSION['recien_creado_jefe_ti'] = $res['jefe_ti_credenciales'];
                        $_SESSION['recien_creado_empresa_meta'] = [
                            'nombre_empresa' => $res['nombre_empresa'] ?? $nombreEmpresa,
                            'dominio_institucional' => $res['dominio_institucional'] ?? ($slugDominio . '.' . INSTITUTIONAL_BASE_DOMAIN),
                            'plan' => $res['plan'] ?? $plan,
                            'correo_enviado_a' => $res['correo_enviado_a'] ?? $correoRep,
                        ];
                    }
                    $usuarioModel->registrarBitacora($idEmpresa, $idRepresentante, 'ALTA_EMPRESA', "Representante registró la empresa: $nombreEmpresa (Plan: $plan)");
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
                if (!$empresaModel->puedeSolicitarCuenta((int)$empresa['id_empresa'])) {
                    header("Location: " . BASE_URL . "/index.php?page=representante&error=limite_plan");
                    exit();
                }

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

    public function cambiarPlan() {
        $idRepresentante = $this->verificarAccesoRepresentante();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevoPlan = trim($_POST['plan_suscripcion'] ?? '');
            $planesValidos = defined('B2B_PLANS') ? array_keys(B2B_PLANS) : ['starter', 'corporate', 'enterprise'];

            $empresaModel = new Empresa();
            $empresa = $empresaModel->obtenerPorRepresentante($idRepresentante);

            if ($empresa && in_array($nuevoPlan, $planesValidos, true)) {
                if ($empresaModel->cambiarPlanEmpresa((int)$empresa['id_empresa'], $nuevoPlan)) {
                    (new Usuario())->registrarBitacora(
                        (int)$empresa['id_empresa'],
                        $idRepresentante,
                        'CAMBIO_PLAN_B2B',
                        "Representante actualizó el plan B2B a $nuevoPlan"
                    );
                    header("Location: " . BASE_URL . "/index.php?page=representante&msg=plan_actualizado");
                    exit();
                }
            }
            header("Location: " . BASE_URL . "/index.php?page=representante&error=plan_invalido");
            exit();
        }
        header("Location: " . BASE_URL . "/index.php?page=representante");
        exit();
    }
}
?>
