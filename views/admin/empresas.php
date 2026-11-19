<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas y Aprovisionamiento de Correos | SysMonitor</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app.css">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app-dark.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/dashboard/custom-theme.css?v=4">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/aceternity-ui.css">
    <script src="<?php echo BASE_URL; ?>/assets/js/aceternity-ui.js"></script>
</head>
<body class="ac-body">
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/initTheme.js"></script>
    <div class="ac-dot-bg"></div>
    <div id="app">
        <div id="sidebar">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?php echo BASE_URL; ?>/" class="d-flex align-items-center gap-2 text-decoration-none">
                            <div class="ac-squircle-logo" style="width: 36px; height: 36px; border-radius: 10px; font-size: 1.1rem;">
                                <i class="bi bi-activity"></i>
                            </div>
                            <h5 class="text-primary mb-0 fw-bold">SysMonitor</h5>
                        </a>
                    </div>
                </div>
                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Super Administrador</li>
                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin" class='sidebar-link'>
                                <i class="bi bi-grid-1x2-fill"></i><span>Consola Global</span>
                            </a>
                        </li>
                        <li class="sidebar-item active">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin_empresas" class='sidebar-link'>
                                <i class="bi bi-buildings-fill"></i><span>Empresas y Correos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin_users" class='sidebar-link'>
                                <i class="bi bi-people-fill"></i><span>Usuarios y MFA</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=user" class='sidebar-link'>
                                <i class="bi bi-hdd-rack-fill"></i><span>Vista Racks IoT</span>
                            </a>
                        </li>
                        <li class="sidebar-title">Sesión Segura</li>
                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=logout" class='sidebar-link text-danger'>
                                <i class="bi bi-box-arrow-left text-danger"></i><span>Cerrar Sesión</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="main">
            <header class="mb-3 d-flex justify-content-between align-items-center">
                <a href="#" class="burger-btn d-block d-xl-none"><i class="bi bi-justify fs-3"></i></a>
                <button type="button" class="ac-theme-btn ms-auto">
                    <i class="bi bi-moon-stars-fill"></i> <span>Modo Oscuro</span>
                </button>
            </header>

            <div class="page-heading mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1">Empresas y Correos Institucionales</h3>
                        <p class="small mb-0" style="color: var(--ac-text-muted);">
                            Gestiona empresas B2B, planes de capacidad, correos institucionales y auditoría SMTP.
                        </p>
                    </div>
                    <button type="button" class="ac-btn-primary py-2 px-4" data-bs-toggle="modal" data-bs-target="#modalCrearEmpresaDirecta">
                        <i class="bi bi-building-add"></i> + Dar de Alta Nueva Empresa
                    </button>
                </div>
            </div>

            <?php
                $planesB2B = defined('B2B_PLANS') ? B2B_PLANS : [
                    'starter' => ['nombre' => 'Starter', 'precio' => 49.00, 'max_racks' => 2, 'max_cuentas' => 3],
                    'corporate' => ['nombre' => 'Corporate', 'precio' => 149.00, 'max_racks' => 10, 'max_cuentas' => 15],
                    'enterprise' => ['nombre' => 'Enterprise', 'precio' => 399.00, 'max_racks' => 999, 'max_cuentas' => 999],
                ];
            ?>

            <?php if (isset($_GET['msg'])): ?>
                <?php
                    $adminMsg = 'Operación completada correctamente.';
                    if ($_GET['msg'] === 'empresa_creada') $adminMsg = 'Empresa dada de alta, activada y Jefe de TI aprovisionado vía SMTP.';
                    elseif ($_GET['msg'] === 'plan_actualizado') $adminMsg = 'Plan B2B de la empresa actualizado correctamente.';
                    elseif ($_GET['msg'] === 'empresa_actualizada') $adminMsg = 'Estado de la empresa actualizado.';
                    elseif ($_GET['msg'] === 'correo_generado') $adminMsg = 'Correo institucional emitido y notificado.';
                ?>
                <div class="alert alert-success rounded-4 py-2 px-3 small mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span><?php echo htmlspecialchars($adminMsg); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger rounded-4 py-2 px-3 small mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                    <span>No se pudo completar la operación (<?php echo htmlspecialchars($_GET['error']); ?>). Verifica que los datos y subdominio no estén duplicados.</span>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['admin_ultima_empresa_creada'])): ?>
                <?php $ultimaEmp = $_SESSION['admin_ultima_empresa_creada']; unset($_SESSION['admin_ultima_empresa_creada']); ?>
                <div class="ac-bento-card p-3 px-4 mb-4 border-primary" style="border-width: 1.5px !important;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="ac-badge ac-badge-success me-2">Alta Directa Exitosa</span>
                            <strong><?php echo htmlspecialchars($ultimaEmp['nombre_empresa'] ?? ''); ?></strong>
                            <span class="small text-muted ms-2">Jefe de TI:</span>
                            <code class="text-primary fw-bold"><?php echo htmlspecialchars($ultimaEmp['jefe_ti']['correo_institucional'] ?? ''); ?></code>
                            <span class="small text-muted ms-2">Clave:</span>
                            <code><?php echo htmlspecialchars($ultimaEmp['jefe_ti']['password_temporal'] ?? ''); ?></code>
                        </div>
                        <span class="small text-muted">SMTP -> <?php echo htmlspecialchars($ultimaEmp['correo_enviado_a'] ?? ''); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="page-content">
                <!-- 1. Tabla de Empresas Registradas -->
                <div class="ac-bento-card ac-beam-top p-4 mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-buildings text-primary me-2"></i>Empresas / Instituciones Registradas</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="small text-uppercase" style="color: var(--ac-text-muted);">
                                    <th>ID</th>
                                    <th>Empresa</th>
                                    <th>RFC / ID</th>
                                    <th>Dominio Institucional</th>
                                    <th>Representante</th>
                                    <th>Plan B2B</th>
                                    <th>Uso de Cuotas</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($empresas as $emp): ?>
                                <?php
                                    $empPlan = $emp['plan_suscripcion'] ?? 'corporate';
                                    $maxRacks = (int)($emp['max_racks'] ?? ($planesB2B[$empPlan]['max_racks'] ?? 10));
                                    $maxCuentas = (int)($emp['max_cuentas'] ?? ($planesB2B[$empPlan]['max_cuentas'] ?? 15));
                                    $totalRacks = (int)($emp['total_racks'] ?? 0);
                                    $totalPersonal = (int)($emp['total_personal'] ?? 0);
                                ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo (int)$emp['id_empresa']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($emp['nombre_empresa']); ?></td>
                                    <td><code><?php echo htmlspecialchars($emp['rfc_nit']); ?></code></td>
                                    <td><code class="text-primary fw-bold">@<?php echo htmlspecialchars($emp['slug_dominio']); ?></code></td>
                                    <td>
                                        <div class="small fw-semibold"><?php echo htmlspecialchars(($emp['rep_nombre'] ?? '') . ' ' . ($emp['rep_apellidos'] ?? '')); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($emp['rep_correo'] ?? ''); ?></div>
                                    </td>
                                    <td>
                                        <form action="<?php echo BASE_URL; ?>/index.php?page=admin_cambiar_plan" method="POST" class="d-flex align-items-center gap-1">
                                            <input type="hidden" name="id_empresa" value="<?php echo (int)$emp['id_empresa']; ?>">
                                            <select name="plan_suscripcion" class="form-select form-select-sm rounded-3" style="min-width: 132px;" onchange="this.form.submit()">
                                                <?php foreach ($planesB2B as $pKey => $pData): ?>
                                                    <option value="<?php echo htmlspecialchars($pKey); ?>" <?php echo $empPlan === $pKey ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($pData['nombre']); ?> ($<?php echo number_format((float)$pData['precio'], 0); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="ac-badge ac-badge-blue">
                                            <?php echo $totalRacks; ?>/<?php echo $maxRacks >= 999 ? '∞' : $maxRacks; ?> Racks
                                        </span>
                                        <span class="ac-badge ac-badge-success">
                                            <?php echo $totalPersonal; ?>/<?php echo $maxCuentas >= 999 ? '∞' : $maxCuentas; ?> Cuentas
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($emp['estado'] === 'aprobada'): ?>
                                            <span class="ac-badge ac-badge-success">Aprobada</span>
                                        <?php elseif ($emp['estado'] === 'pendiente'): ?>
                                            <span class="ac-badge ac-badge-warning">Pendiente</span>
                                        <?php else: ?>
                                            <span class="ac-badge ac-badge-danger">Suspendida</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($emp['estado'] !== 'aprobada'): ?>
                                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin_aprobar_empresa&id=<?php echo (int)$emp['id_empresa']; ?>&estado=aprobada" class="btn btn-sm btn-success rounded-pill px-3">
                                                <i class="bi bi-check-lg"></i> Aprobar
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin_aprobar_empresa&id=<?php echo (int)$emp['id_empresa']; ?>&estado=suspendida" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                Suspender
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Generador de Correos Institucionales para Solicitudes -->
                <div class="ac-bento-card ac-beam-top p-4">
                    <h5 class="fw-bold mb-1"><i class="bi bi-at text-primary me-1"></i>Solicitudes de Correos Institucionales (Jefes de TI y Operadores)</h5>
                    <p class="small mb-3" style="color: var(--ac-text-muted);">
                        Personaliza o genera automáticamente el correo institucional y la contraseña inicial para cada miembro solicitado por el Representante.
                    </p>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="small text-uppercase" style="color: var(--ac-text-muted);">
                                    <th>Empresa</th>
                                    <th>Persona</th>
                                    <th>Rol</th>
                                    <th>Correo Institucional a Proporcionar</th>
                                    <th>Contraseña Inicial</th>
                                    <th>Acción / Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($solicitudes)): ?>
                                    <?php foreach ($solicitudes as $sol): ?>
                                        <?php
                                            $sugerido = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $sol['nombre'])[0]))
                                                      . '.' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $sol['apellidos'])[0]))
                                                      . '@' . $sol['slug_dominio'];
                                        ?>
                                        <tr>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($sol['nombre_empresa']); ?></td>
                                            <td><?php echo htmlspecialchars($sol['nombre'] . ' ' . $sol['apellidos']); ?></td>
                                            <td>
                                                <span class="ac-badge <?php echo $sol['rol_solicitado'] === 'jefe_ti' ? 'ac-badge-blue' : 'ac-badge-success'; ?>">
                                                    <?php echo $sol['rol_solicitado'] === 'jefe_ti' ? 'Jefe de TI' : 'Operador'; ?>
                                                </span>
                                            </td>
                                            <?php if ($sol['estado'] === 'pendiente'): ?>
                                                <form action="<?php echo BASE_URL; ?>/index.php?page=admin_aprobar_solicitud" method="POST">
                                                    <input type="hidden" name="id_solicitud" value="<?php echo (int)$sol['id_solicitud']; ?>">
                                                    <td>
                                                        <input type="email" name="correo_asignado" class="form-control form-control-sm rounded-3" value="<?php echo htmlspecialchars($sugerido); ?>" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="password_temporal" class="form-control form-control-sm rounded-3" value="SysMonitor2026!" required>
                                                    </td>
                                                    <td>
                                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                                                            <i class="bi bi-check2-circle"></i> Emitir Correo
                                                        </button>
                                                    </td>
                                                </form>
                                            <?php else: ?>
                                                <td><code class="text-primary fw-bold"><?php echo htmlspecialchars($sol['correo_asignado']); ?></code></td>
                                                <td><code><?php echo htmlspecialchars($sol['password_temporal']); ?></code></td>
                                                <td><span class="ac-badge ac-badge-success">Correo Emitido</span></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 small text-muted">No hay solicitudes registradas aún.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Auditoría de Correos SMTP y Filtro Anti-Spam WAF -->
                <div class="ac-bento-card ac-beam-top p-4 mt-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="bi bi-shield-check text-primary me-2"></i>Auditoría de Correos SMTP y Filtro Anti-Spam WAF</h5>
                            <p class="small mb-0" style="color: var(--ac-text-muted);">
                                Registro global de correos de credenciales aprovisionadas, alertas de contacto web e intentos bloqueados por filtro/rate-limit.
                            </p>
                        </div>
                        <span class="ac-badge ac-badge-blue"><?php echo count($correosAuditoria ?? []); ?> eventos</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr class="small text-uppercase" style="color: var(--ac-text-muted);">
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Destinatario</th>
                                    <th>Asunto</th>
                                    <th>Estado SMTP / Filtro</th>
                                    <th>IP Origen</th>
                                    <th>HTML</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($correosAuditoria)): ?>
                                    <?php foreach ($correosAuditoria as $mail): ?>
                                        <?php
                                            $estado = $mail['estado_smtp'] ?? 'cola_local_auditable';
                                            $badge = 'ac-badge-blue';
                                            $estadoTxt = 'Cola Local Auditable';
                                            if ($estado === 'enviado_smtp') {
                                                $badge = 'ac-badge-success';
                                                $estadoTxt = 'Enviado SMTP';
                                            } elseif ($estado === 'bloqueado_filtro') {
                                                $badge = 'ac-badge-danger';
                                                $estadoTxt = 'Bloqueado WAF';
                                            }
                                        ?>
                                        <tr>
                                            <td class="small text-muted"><?php echo htmlspecialchars($mail['fecha_envio'] ?? ''); ?></td>
                                            <td>
                                                <span class="ac-badge ac-badge-blue"><?php echo htmlspecialchars($mail['tipo_correo'] ?? 'sistema'); ?></span>
                                                <?php if (!empty($mail['nombre_empresa'])): ?>
                                                    <div class="small text-muted mt-1"><?php echo htmlspecialchars($mail['nombre_empresa']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code class="text-primary fw-bold"><?php echo htmlspecialchars($mail['destinatario'] ?? ''); ?></code>
                                                <?php if (!empty($mail['remitente_reply'])): ?>
                                                    <div class="small text-muted">Reply-To: <?php echo htmlspecialchars($mail['remitente_reply']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small fw-semibold"><?php echo htmlspecialchars($mail['asunto'] ?? ''); ?></td>
                                            <td>
                                                <span class="ac-badge <?php echo $badge; ?>"><?php echo htmlspecialchars($estadoTxt); ?></span>
                                                <?php if (!empty($mail['detalle_filtro'])): ?>
                                                    <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($mail['detalle_filtro']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><code class="small"><?php echo htmlspecialchars($mail['ip_origen'] ?? '127.0.0.1'); ?></code></td>
                                            <td>
                                                <?php if (!empty($mail['cuerpo_html'])): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-admin-preview-correo"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalAdminPreviewCorreo"
                                                        data-asunto="<?php echo htmlspecialchars($mail['asunto'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-destinatario="<?php echo htmlspecialchars($mail['destinatario'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-estado="<?php echo htmlspecialchars($estadoTxt, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-fecha="<?php echo htmlspecialchars($mail['fecha_envio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-html="<?php echo htmlspecialchars($mail['cuerpo_html'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    >
                                                        <i class="bi bi-eye"></i> Ver
                                                    </button>
                                                <?php else: ?>
                                                    <span class="small text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 small text-muted">Sin eventos SMTP o WAF registrados todavía.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Alta Directa de Empresa + Representante + Plan B2B -->
    <div class="modal fade" id="modalCrearEmpresaDirecta" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content ac-bento-card border-0 p-2">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-building-add text-primary me-2"></i>Dar de Alta Nueva Empresa B2B</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="<?php echo BASE_URL; ?>/index.php?page=admin_crear_empresa" method="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary small text-uppercase mb-1">1. Datos de la Empresa</h6>
                            </div>
                            <div class="col-md-7">
                                <label class="ac-label">Razón Social / Empresa</label>
                                <input type="text" name="nombre_empresa" class="form-control rounded-3" placeholder="Ej. NovaHost DataCenters" required>
                            </div>
                            <div class="col-md-5">
                                <label class="ac-label">RFC / ID Fiscal</label>
                                <input type="text" name="rfc_nit" class="form-control rounded-3" placeholder="NHD260501XYZ" required>
                            </div>
                            <div class="col-md-6">
                                <label class="ac-label">Subdominio Institucional</label>
                                <div class="input-group">
                                    <input type="text" name="slug_dominio" class="form-control rounded-start-3" placeholder="novahost" pattern="[a-zA-Z0-9\-]+" required>
                                    <span class="input-group-text">.<?php echo INSTITUTIONAL_BASE_DOMAIN; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="ac-label">Plan B2B</label>
                                <select name="plan_suscripcion" class="form-select rounded-3">
                                    <?php foreach ($planesB2B as $pKey => $pData): ?>
                                        <option value="<?php echo htmlspecialchars($pKey); ?>" <?php echo $pKey === 'corporate' ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($pData['nombre']); ?> ($<?php echo number_format((float)$pData['precio'], 0); ?>/mes — <?php echo (int)$pData['max_racks'] >= 999 ? 'Ilimitado' : (int)$pData['max_racks'] . ' Racks'; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="ac-label">Sector</label>
                                <input type="text" name="sector" class="form-control rounded-3" value="DataCenter & Cloud" required>
                            </div>
                            <div class="col-md-6">
                                <label class="ac-label">Teléfono de Contacto</label>
                                <input type="text" name="telefono" class="form-control rounded-3" placeholder="+52 999 000 0000" required>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="fw-bold text-primary small text-uppercase mb-1">2. Representante Legal (Cuenta Titular)</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="ac-label">Nombre(s)</label>
                                <input type="text" name="rep_nombre" class="form-control rounded-3" placeholder="Nombre del representante" required>
                            </div>
                            <div class="col-md-6">
                                <label class="ac-label">Apellidos</label>
                                <input type="text" name="rep_apellidos" class="form-control rounded-3" placeholder="Apellidos" required>
                            </div>
                            <div class="col-md-6">
                                <label class="ac-label">Correo del Representante</label>
                                <input type="email" name="rep_correo" class="form-control rounded-3" placeholder="rep@empresa.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="ac-label">Contraseña Inicial (opcional)</label>
                                <input type="text" name="rep_password" class="form-control rounded-3" placeholder="Autogenerada si se deja vacío">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="ac-btn-primary py-2 px-4">
                            <i class="bi bi-check2-circle"></i> Registrar y Activar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Vista Previa HTML Correo SMTP (Admin) -->
    <div class="modal fade" id="modalAdminPreviewCorreo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content ac-bento-card border-0 p-2">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="adminMailModalAsunto">Detalle del Correo SMTP</h5>
                        <div class="small text-muted" id="adminMailModalMeta"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-0">
                    <iframe id="adminMailIframe" sandbox="" class="w-100 border rounded-4" style="height: 430px; background: #ffffff;" title="Vista previa correo SMTP"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/components/dark.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/compiled/js/app.js"></script>
    <script>
        document.querySelectorAll('.btn-admin-preview-correo').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('adminMailModalAsunto').textContent = btn.getAttribute('data-asunto') || 'Correo SMTP';
                document.getElementById('adminMailModalMeta').textContent =
                    'Para: ' + (btn.getAttribute('data-destinatario') || '') +
                    ' • Estado: ' + (btn.getAttribute('data-estado') || '') +
                    ' • ' + (btn.getAttribute('data-fecha') || '');
                document.getElementById('adminMailIframe').srcdoc = btn.getAttribute('data-html') || '';
            });
        });
    </script>
</body>
</html>
