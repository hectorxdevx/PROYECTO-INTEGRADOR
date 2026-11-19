<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Representante Corporativo | SysMonitor</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/aceternity-ui.css">
    <script src="<?php echo BASE_URL; ?>/assets/js/aceternity-ui.js"></script>
    <style>
        .plan-radio-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .plan-radio-label {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 14px;
            border-radius: 16px;
            border: 1.5px solid var(--ac-border);
            background: var(--ac-surface-solid);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .plan-radio-label:hover {
            border-color: var(--ac-border-hover);
            transform: translateY(-2px);
        }
        .plan-radio-input:checked + .plan-radio-label {
            border-color: var(--ac-primary);
            background: var(--ac-primary-light);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.14);
        }
    </style>
</head>
<body class="ac-body pb-5">
    <div class="ac-dot-bg"></div>

    <!-- Barra Superior Aceternity FloatingNav -->
    <nav class="navbar navbar-expand-lg sticky-top ac-floating-nav py-3 mb-4">
        <div class="container">
            <a class="ac-brand-pill" href="<?php echo BASE_URL; ?>/index.php?page=representante">
                <i class="bi bi-building-fill-check text-primary fs-5"></i>
                <span>SysMonitor • Representante</span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <a href="<?php echo BASE_URL; ?>/index.php?page=logout" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </a>
                <button type="button" class="ac-theme-btn">
                    <i class="bi bi-moon-stars-fill"></i> <span>Modo Oscuro</span>
                </button>
            </div>
        </div>
    </nav>

    <main class="container">
        <!-- Encabezado de Bienvenida -->
        <div class="ac-bento-card ac-beam-top p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-1">Hola, <?php echo htmlspecialchars(($usuarioActual['nombre_usuario'] ?? 'Representante') . ' ' . ($usuarioActual['apellidos'] ?? '')); ?></h2>
                    <p class="small mb-0" style="color: var(--ac-text-muted);">
                        Registra tu empresa y solicita los correos institucionales para tu Jefe de TI y operadores.
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php if ($empresa): ?>
                        <?php if ($empresa['estado'] === 'aprobada'): ?>
                            <span class="ac-badge ac-badge-success fs-6 px-3 py-2">
                                <i class="bi bi-patch-check-fill"></i> Empresa Aprobada
                            </span>
                        <?php else: ?>
                            <span class="ac-badge ac-badge-warning fs-6 px-3 py-2">
                                <i class="bi bi-hourglass-split"></i> En Revisión
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="ac-badge ac-badge-warning fs-6 px-3 py-2">
                            <i class="bi bi-exclamation-circle"></i> Sin Empresa Registrada
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php
            $planesB2B = defined('B2B_PLANS') ? B2B_PLANS : [
                'starter' => ['nombre' => 'Starter', 'precio' => 49.00, 'max_racks' => 2, 'max_cuentas' => 3, 'descripcion' => 'Pequeñas empresas / 1 site'],
                'corporate' => ['nombre' => 'Corporate', 'precio' => 149.00, 'max_racks' => 10, 'max_cuentas' => 15, 'descripcion' => 'Empresas medianas / multi-rack'],
                'enterprise' => ['nombre' => 'Enterprise', 'precio' => 399.00, 'max_racks' => 999, 'max_cuentas' => 999, 'descripcion' => 'DataCenters / cuentas ilimitadas'],
            ];
        ?>

        <?php if (isset($_GET['msg'])): ?>
            <?php
                $msgText = 'Operación guardada correctamente.';
                if ($_GET['msg'] === 'empresa_creada') $msgText = 'Empresa activada y primer Jefe de TI aprovisionado automáticamente.';
                elseif ($_GET['msg'] === 'solicitud_enviada') $msgText = 'Solicitud de cuenta institucional enviada.';
                elseif ($_GET['msg'] === 'plan_actualizado') $msgText = 'Tu plan de suscripción B2B ha sido actualizado correctamente.';
            ?>
            <div class="alert alert-success rounded-4 py-2 px-3 small mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span><?php echo htmlspecialchars($msgText); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <?php
                $errText = 'No se pudo completar la operación.';
                if ($_GET['error'] === 'limite_plan') {
                    $errText = 'Has alcanzado el límite de cuentas institucionales de tu plan B2B actual. Mejora tu plan para agregar más personal.';
                } elseif ($_GET['error'] === 'plan_invalido') {
                    $errText = 'El plan B2B seleccionado no es válido.';
                } elseif ($_GET['error'] === 'datos_invalidos') {
                    $errText = 'Verifica los datos de la empresa o que el subdominio no esté en uso.';
                } elseif ($_GET['error'] === 'solicitud_fallida') {
                    $errText = 'No se pudo enviar la solicitud de correo institucional.';
                }
            ?>
            <div class="alert alert-danger rounded-4 py-2 px-3 small mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <span><?php echo htmlspecialchars($errText); ?></span>
                </div>
                <?php if ($_GET['error'] === 'limite_plan' && $empresa): ?>
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalCambiarPlan">
                        Mejorar Plan B2B
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['recien_creado_jefe_ti'])): ?>
            <?php
                $jefeTi = $_SESSION['recien_creado_jefe_ti'];
                $metaEmpresa = $_SESSION['recien_creado_empresa_meta'] ?? [];
                $correoNotif = $metaEmpresa['correo_enviado_a'] ?? ($usuarioActual['correo_electronico'] ?? '');
                $estadoSmtpJefe = $jefeTi['smtp']['estado_smtp'] ?? 'cola_local_auditable';
                $smtpReal = ($estadoSmtpJefe === 'enviado_smtp');
            ?>
            <div class="ac-bento-card ac-beam-top p-4 mb-4 border-primary" style="border-width: 1.5px !important;">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="ac-squircle-logo" style="width: 48px; height: 48px; font-size: 1.35rem;">
                            <i class="bi bi-envelope-check-fill"></i>
                        </div>
                        <div>
                            <span class="ac-badge <?php echo $smtpReal ? 'ac-badge-success' : 'ac-badge-blue'; ?> mb-1">
                                <i class="bi bi-send-check"></i>
                                <?php echo $smtpReal ? 'Enviado vía SMTP' : 'Notificación SMTP (Cola Auditable)'; ?>
                            </span>
                            <h5 class="fw-bold mb-0">Credenciales del Primer Jefe de TI Aprovisionadas</h5>
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=representante&dismiss_jefe_ti=1" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        Ocultar aviso
                    </a>
                </div>
                <p class="small mb-3" style="color: var(--ac-text-secondary);">
                    Se ha creado automáticamente el acceso institucional de tu <strong>Jefe de TI</strong> y se notificó por correo electrónico a
                    <strong><?php echo htmlspecialchars($correoNotif); ?></strong>:
                </p>
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="p-3 rounded-4 h-100" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);">
                            <div class="ac-label mb-1">Correo Institucional</div>
                            <code class="fw-bold text-primary d-block text-break"><?php echo htmlspecialchars($jefeTi['correo_institucional'] ?? ''); ?></code>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-4 h-100" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);">
                            <div class="ac-label mb-1">Contraseña Temporal</div>
                            <code class="fw-bold d-block"><?php echo htmlspecialchars($jefeTi['password_temporal'] ?? ''); ?></code>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-4 h-100" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);">
                            <div class="ac-label mb-1">Secreto MFA (Base32)</div>
                            <code class="fw-bold text-primary d-block"><?php echo htmlspecialchars($jefeTi['mfa_secret'] ?? ''); ?></code>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$empresa): ?>
            <!-- FORMULARIO DE ALTA DE EMPRESA -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="ac-bento-card ac-beam-top p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="ac-squircle-logo">
                                <i class="bi bi-building-add"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1">Registrar mi Empresa</h4>
                                <p class="small mb-0" style="color: var(--ac-text-muted);">
                                    Define el nombre y dominio institucional para las cuentas de tu equipo.
                                </p>
                            </div>
                        </div>

                        <form action="<?php echo BASE_URL; ?>/index.php?page=rep_registrar_empresa" method="POST">
                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <label class="ac-label">Razón Social / Institución</label>
                                    <div class="ac-input-wrapper">
                                        <i class="bi bi-building"></i>
                                        <input type="text" name="nombre_empresa" class="ac-input" placeholder="Nombre de la institución" required>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="ac-label">RFC / Identificador Fiscal</label>
                                    <div class="ac-input-wrapper">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <input type="text" name="rfc_nit" class="ac-input" placeholder="Identificador fiscal" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="ac-label">Dominio Institucional</label>
                                    <div class="ac-input-wrapper">
                                        <i class="bi bi-globe2"></i>
                                        <input type="text" name="slug_dominio" class="ac-input" placeholder="empresa" pattern="[a-zA-Z0-9\-]+" required>
                                        <span class="small fw-bold text-primary">.sysmonitor.com</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="ac-label">Sector</label>
                                    <div class="ac-input-wrapper">
                                        <i class="bi bi-hdd-network"></i>
                                        <input type="text" name="sector" class="ac-input" value="Tecnología y Servidores" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="ac-label">Teléfono de Contacto</label>
                                <div class="ac-input-wrapper">
                                    <i class="bi bi-telephone"></i>
                                    <input type="text" name="telefono" class="ac-input" placeholder="+52 (999) 000-0000" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="ac-label mb-2">Plan de Suscripción B2B</label>
                                <div class="row g-2">
                                    <?php foreach ($planesB2B as $planKey => $planInfo): ?>
                                        <?php
                                            $racksLabel = ((int)$planInfo['max_racks'] >= 999) ? 'Racks ilimitados' : (int)$planInfo['max_racks'] . ' Racks IoT';
                                            $cuentasLabel = ((int)$planInfo['max_cuentas'] >= 999) ? 'Cuentas ilimitadas' : (int)$planInfo['max_cuentas'] . ' Cuentas';
                                        ?>
                                        <div class="col-md-4 position-relative">
                                            <input type="radio" name="plan_suscripcion" id="dash_plan_<?php echo htmlspecialchars($planKey); ?>" value="<?php echo htmlspecialchars($planKey); ?>" class="plan-radio-input" <?php echo $planKey === 'corporate' ? 'checked' : ''; ?>>
                                            <label for="dash_plan_<?php echo htmlspecialchars($planKey); ?>" class="plan-radio-label text-center">
                                                <span class="fw-bold"><?php echo htmlspecialchars($planInfo['nombre']); ?></span>
                                                <span class="fw-extrabold text-primary fs-5 my-1">$<?php echo number_format((float)$planInfo['precio'], 0); ?><small class="text-muted fs-6">/mes</small></span>
                                                <span class="small text-muted"><?php echo htmlspecialchars($racksLabel); ?> • <?php echo htmlspecialchars($cuentasLabel); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <button type="submit" class="ac-btn-primary w-100">
                                <span>Registrar y Activar Empresa</span> <i class="bi bi-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- PANEL DE EMPRESA REGISTRADA Y SOLICITUD DE CORREOS INSTITUCIONALES -->
            <div class="row g-4">
                <!-- Datos de la Empresa y Solicitud de Nuevas Cuentas -->
                <div class="col-lg-5">
                    <div class="ac-bento-card p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="ac-label">INSTITUCIÓN</span>
                                <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($empresa['nombre_empresa']); ?></h4>
                            </div>
                            <div class="ac-squircle-logo" style="width: 48px; height: 48px; font-size: 1.3rem;">
                                <i class="bi bi-building-check"></i>
                            </div>
                        </div>
                        <div class="p-3 rounded-4 mb-3" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);">
                            <div class="small mb-1" style="color: var(--ac-text-muted);">Dominio Institucional:</div>
                            <div class="fw-bold text-primary" style="font-family: 'Fira Code', monospace;">
                                @<?php echo htmlspecialchars($empresa['slug_dominio']); ?>
                            </div>
                        </div>
                        <div class="row g-2 small" style="color: var(--ac-text-secondary);">
                            <div class="col-6"><strong>RFC/ID:</strong> <?php echo htmlspecialchars($empresa['rfc_nit']); ?></div>
                            <div class="col-6"><strong>Sector:</strong> <?php echo htmlspecialchars($empresa['sector']); ?></div>
                        </div>
                    </div>

                    <!-- Tarjeta de Plan B2B y Cuotas -->
                    <?php
                        $planSlug = $usoPlan['plan_suscripcion'] ?? ($empresa['plan_suscripcion'] ?? 'corporate');
                        $planNombre = $usoPlan['nombre_plan'] ?? ucfirst($planSlug);
                        $costoMensual = (float)($usoPlan['costo_mensual'] ?? ($empresa['costo_mensual'] ?? 149.00));
                        $racksUsados = (int)($usoPlan['racks_usados'] ?? 0);
                        $maxRacks = (int)($usoPlan['max_racks'] ?? ($empresa['max_racks'] ?? 10));
                        $cuentasUsadas = (int)($usoPlan['cuentas_usadas'] ?? 0);
                        $maxCuentas = (int)($usoPlan['max_cuentas'] ?? ($empresa['max_cuentas'] ?? 15));
                        $pctRacks = $maxRacks >= 999 ? min(100, $racksUsados * 5) : ($maxRacks > 0 ? min(100, round(($racksUsados / $maxRacks) * 100)) : 0);
                        $pctCuentas = $maxCuentas >= 999 ? min(100, $cuentasUsadas * 5) : ($maxCuentas > 0 ? min(100, round(($cuentasUsadas / $maxCuentas) * 100)) : 0);
                    ?>
                    <div class="ac-bento-card ac-beam-top p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="ac-label mb-1">PLAN B2B ACTIVO</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="ac-badge ac-badge-blue fs-6 px-3 py-1">
                                        <i class="bi bi-award-fill"></i> <?php echo htmlspecialchars($planNombre); ?>
                                    </span>
                                    <span class="fw-bold text-primary">$<?php echo number_format($costoMensual, 2); ?>/mes</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCambiarPlan">
                                <i class="bi bi-arrow-up-circle me-1"></i> Cambiar Plan
                            </button>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold">Racks IoT</span>
                                <span class="fw-bold" style="font-family: 'Fira Code', monospace;">
                                    <?php echo $racksUsados; ?> / <?php echo $maxRacks >= 999 ? 'Ilimitados' : $maxRacks; ?>
                                </span>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px; background: var(--ac-input-bg);">
                                <div class="progress-bar <?php echo $pctRacks >= 90 ? 'bg-danger' : 'bg-primary'; ?>" style="width: <?php echo $pctRacks; ?>%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold">Cuentas Institucionales</span>
                                <span class="fw-bold" style="font-family: 'Fira Code', monospace;">
                                    <?php echo $cuentasUsadas; ?> / <?php echo $maxCuentas >= 999 ? 'Ilimitadas' : $maxCuentas; ?>
                                </span>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px; background: var(--ac-input-bg);">
                                <div class="progress-bar <?php echo $pctCuentas >= 90 ? 'bg-warning' : 'bg-success'; ?>" style="width: <?php echo $pctCuentas; ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario para solicitar Correo de Jefe de TI u Operador -->
                    <div class="ac-bento-card ac-beam-top p-4">
                        <h5 class="fw-bold mb-1"><i class="bi bi-person-plus-fill text-primary me-2"></i>Solicitar Correo Institucional</h5>
                        <p class="small mb-3" style="color: var(--ac-text-muted);">
                            Solicita cuentas corporativas para tu Jefe de TI u operadores.
                        </p>

                        <form action="<?php echo BASE_URL; ?>/index.php?page=rep_solicitar_cuenta" method="POST">
                            <div class="mb-3">
                                <label class="ac-label">Nombre(s)</label>
                                <div class="ac-input-wrapper">
                                    <i class="bi bi-person"></i>
                                    <input type="text" name="nombre" class="ac-input" placeholder="Nombre del colaborador" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="ac-label">Apellidos</label>
                                <div class="ac-input-wrapper">
                                    <i class="bi bi-person-vcard"></i>
                                    <input type="text" name="apellidos" class="ac-input" placeholder="Apellidos del colaborador" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="ac-label">Rol</label>
                                <div class="ac-input-wrapper">
                                    <i class="bi bi-shield-shaded"></i>
                                    <select name="rol_solicitado" class="ac-input form-select border-0 bg-transparent">
                                        <option value="jefe_ti">Jefe de TI (Gestiona Racks y Umbrales)</option>
                                        <option value="operador">Operador (Monitoreo en Vivo)</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="ac-btn-primary w-100">
                                <i class="bi bi-send-check"></i> Solicitar Correo
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Correos Institucionales Emitidos y Solicitudes -->
                <div class="col-lg-7">
                    <div class="ac-bento-card ac-beam-top p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0">Correos de tu Equipo</h5>
                                <p class="small mb-0" style="color: var(--ac-text-muted);">
                                    Comparte estas credenciales con tu personal para que inicien sesión.
                                </p>
                            </div>
                            <span class="ac-badge ac-badge-blue"><?php echo count($solicitudes); ?> solicitudes</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr class="small text-uppercase" style="color: var(--ac-text-muted);">
                                        <th>Colaborador</th>
                                        <th>Rol</th>
                                        <th>Correo Institucional</th>
                                        <th>Clave Inicial</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($solicitudes)): ?>
                                        <?php foreach ($solicitudes as $sol): ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($sol['nombre'] . ' ' . $sol['apellidos']); ?></td>
                                                <td>
                                                    <?php if ($sol['rol_solicitado'] === 'jefe_ti'): ?>
                                                        <span class="ac-badge ac-badge-blue">Jefe de TI</span>
                                                    <?php else: ?>
                                                        <span class="ac-badge ac-badge-success">Operador</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($sol['correo_asignado'])): ?>
                                                        <code class="fw-bold text-primary"><?php echo htmlspecialchars($sol['correo_asignado']); ?></code>
                                                    <?php else: ?>
                                                        <span class="small text-muted">Por asignar...</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($sol['password_temporal'])): ?>
                                                        <code><?php echo htmlspecialchars($sol['password_temporal']); ?></code>
                                                    <?php else: ?>
                                                        <span class="small text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($sol['estado'] === 'aprobada'): ?>
                                                        <span class="ac-badge ac-badge-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="ac-badge ac-badge-warning">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 small" style="color: var(--ac-text-muted);">
                                                Aún no has enviado solicitudes adicionales. Usa el formulario izquierdo para pedir correos para tu Jefe de TI y Operadores.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($personalInstitucional)): ?>
                            <hr class="my-4" style="border-color: var(--ac-border);">
                            <h6 class="fw-bold mb-3"><i class="bi bi-people-fill text-primary me-2"></i>Cuentas Activas en tu Institución</h6>
                            <div class="row g-2">
                                <?php foreach ($personalInstitucional as $p): ?>
                                    <?php if ((int)$p['id_rol'] === 3) continue; ?>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-4 d-flex align-items-center justify-content-between" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);">
                                            <div>
                                                <div class="fw-bold small"><?php echo htmlspecialchars($p['nombre_usuario'] . ' ' . $p['apellidos']); ?></div>
                                                <div class="small text-primary" style="font-family: 'Fira Code', monospace; font-size: 0.76rem;">
                                                    <?php echo htmlspecialchars($p['correo_electronico']); ?>
                                                </div>
                                            </div>
                                            <span class="ac-badge <?php echo ((int)$p['id_rol'] === 4) ? 'ac-badge-blue' : 'ac-badge-success'; ?>">
                                                <?php echo ((int)$p['id_rol'] === 4) ? 'Jefe TI' : 'Operador'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Bandeja de Correos SMTP Emitidos -->
            <div class="ac-bento-card ac-beam-top p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold mb-0"><i class="bi bi-envelope-paper-fill text-primary me-2"></i>Bandeja de Correos SMTP Emitidos</h5>
                        <p class="small mb-0" style="color: var(--ac-text-muted);">
                            Historial auditable de notificaciones de credenciales institucionales enviadas por SysMonitor SMTP.
                        </p>
                    </div>
                    <span class="ac-badge ac-badge-blue"><?php echo count($correosEnviados ?? []); ?> correos</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-uppercase" style="color: var(--ac-text-muted);">
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Destinatario</th>
                                <th>Asunto</th>
                                <th>Estado SMTP</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($correosEnviados)): ?>
                                <?php foreach ($correosEnviados as $mail): ?>
                                    <?php
                                        $estadoSmtp = $mail['estado_smtp'] ?? 'cola_local_auditable';
                                        $badgeSmtp = 'ac-badge-blue';
                                        $labelSmtp = 'Cola Local Auditable';
                                        if ($estadoSmtp === 'enviado_smtp') {
                                            $badgeSmtp = 'ac-badge-success';
                                            $labelSmtp = 'Enviado SMTP';
                                        } elseif ($estadoSmtp === 'bloqueado_filtro') {
                                            $badgeSmtp = 'ac-badge-danger';
                                            $labelSmtp = 'Bloqueado Filtro';
                                        }
                                    ?>
                                    <tr>
                                        <td class="small text-muted"><?php echo htmlspecialchars($mail['fecha_envio'] ?? ''); ?></td>
                                        <td><span class="ac-badge ac-badge-blue"><?php echo htmlspecialchars($mail['tipo_correo'] ?? 'sistema'); ?></span></td>
                                        <td><code class="text-primary"><?php echo htmlspecialchars($mail['destinatario'] ?? ''); ?></code></td>
                                        <td class="fw-semibold small"><?php echo htmlspecialchars($mail['asunto'] ?? ''); ?></td>
                                        <td>
                                            <span class="ac-badge <?php echo $badgeSmtp; ?>"><?php echo htmlspecialchars($labelSmtp); ?></span>
                                            <?php if (!empty($mail['detalle_filtro'])): ?>
                                                <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($mail['detalle_filtro']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-preview-correo"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalPreviewCorreo"
                                                data-asunto="<?php echo htmlspecialchars($mail['asunto'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-destinatario="<?php echo htmlspecialchars($mail['destinatario'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-estado="<?php echo htmlspecialchars($labelSmtp, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-fecha="<?php echo htmlspecialchars($mail['fecha_envio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-html="<?php echo htmlspecialchars($mail['cuerpo_html'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                                <i class="bi bi-eye"></i> Ver HTML
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 small" style="color: var(--ac-text-muted);">
                                        Aún no hay correos SMTP registrados para esta empresa.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Cambiar Plan B2B -->
            <div class="modal fade" id="modalCambiarPlan" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content ac-bento-card border-0 p-2">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold"><i class="bi bi-layers-fill text-primary me-2"></i>Actualizar Plan de Suscripción B2B</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <form action="<?php echo BASE_URL; ?>/index.php?page=rep_cambiar_plan" method="POST">
                            <div class="modal-body">
                                <p class="small mb-3" style="color: var(--ac-text-muted);">
                                    Selecciona el plan que mejor se adapte a la capacidad de Racks IoT y cuentas de tu centro de datos.
                                </p>
                                <div class="row g-3">
                                    <?php foreach ($planesB2B as $planKey => $planInfo): ?>
                                        <?php
                                            $racksLabel = ((int)$planInfo['max_racks'] >= 999) ? 'Racks Ilimitados' : (int)$planInfo['max_racks'] . ' Racks IoT';
                                            $cuentasLabel = ((int)$planInfo['max_cuentas'] >= 999) ? 'Cuentas Ilimitadas' : (int)$planInfo['max_cuentas'] . ' Cuentas';
                                        ?>
                                        <div class="col-md-4 position-relative">
                                            <input type="radio" name="plan_suscripcion" id="modal_plan_<?php echo htmlspecialchars($planKey); ?>" value="<?php echo htmlspecialchars($planKey); ?>" class="plan-radio-input" <?php echo $planKey === $planSlug ? 'checked' : ''; ?>>
                                            <label for="modal_plan_<?php echo htmlspecialchars($planKey); ?>" class="plan-radio-label text-center">
                                                <span class="fw-bold fs-6"><?php echo htmlspecialchars($planInfo['nombre']); ?></span>
                                                <span class="fw-extrabold text-primary fs-3 my-2">$<?php echo number_format((float)$planInfo['precio'], 0); ?><small class="fs-6 text-muted fw-normal">/mes</small></span>
                                                <span class="small fw-semibold"><?php echo htmlspecialchars($racksLabel); ?></span>
                                                <span class="small fw-semibold mb-1"><?php echo htmlspecialchars($cuentasLabel); ?></span>
                                                <span class="small text-muted"><?php echo htmlspecialchars($planInfo['descripcion']); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="ac-btn-primary py-2 px-4">
                                    <i class="bi bi-check2-circle"></i> Confirmar Plan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Vista Previa HTML Correo SMTP -->
            <div class="modal fade" id="modalPreviewCorreo" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content ac-bento-card border-0 p-2">
                        <div class="modal-header border-0">
                            <div>
                                <h5 class="modal-title fw-bold" id="repMailModalAsunto">Detalle del Correo SMTP</h5>
                                <div class="small text-muted" id="repMailModalMeta"></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body pt-0">
                            <iframe id="repMailIframe" sandbox="" class="w-100 border rounded-4" style="height: 430px; background: #ffffff;" title="Vista previa correo SMTP"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.btn-preview-correo').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('repMailModalAsunto').textContent = btn.getAttribute('data-asunto') || 'Correo SMTP';
                document.getElementById('repMailModalMeta').textContent =
                    'Para: ' + (btn.getAttribute('data-destinatario') || '') +
                    ' • Estado: ' + (btn.getAttribute('data-estado') || '') +
                    ' • ' + (btn.getAttribute('data-fecha') || '');
                document.getElementById('repMailIframe').srcdoc = btn.getAttribute('data-html') || '';
            });
        });
    </script>
</body>
</html>
