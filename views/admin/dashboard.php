<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin • Consola Central | SysMonitor</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app.css">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app-dark.css">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/iconly.css">
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
                        <div class="logo">
                            <a href="<?php echo BASE_URL; ?>/" class="d-flex align-items-center gap-2 text-decoration-none">
                                <div class="ac-squircle-logo" style="width: 36px; height: 36px; border-radius: 10px; font-size: 1.1rem;">
                                    <i class="bi bi-activity"></i>
                                </div>
                                <h5 class="text-primary mb-0 fw-bold">SysMonitor</h5>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Super Administrador</li>

                        <li class="sidebar-item active">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin" class='sidebar-link'>
                                <i class="bi bi-grid-1x2-fill"></i>
                                <span>Consola Global</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin_empresas" class='sidebar-link'>
                                <i class="bi bi-buildings-fill"></i>
                                <span>Empresas y Correos</span>
                                <?php if (($stats['solicitudes_pendientes'] ?? 0) > 0): ?>
                                    <span class="badge bg-primary rounded-pill ms-auto"><?php echo (int)$stats['solicitudes_pendientes']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin_users" class='sidebar-link'>
                                <i class="bi bi-people-fill"></i>
                                <span>Usuarios y MFA</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=user" class='sidebar-link'>
                                <i class="bi bi-hdd-rack-fill"></i>
                                <span>Vista Racks IoT</span>
                            </a>
                        </li>

                        <li class="sidebar-title">Sesión Segura</li>
                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=logout" class='sidebar-link text-danger'>
                                <i class="bi bi-box-arrow-left text-danger"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="main">
            <header class="mb-3 d-flex justify-content-between align-items-center">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="ac-theme-btn">
                        <i class="bi bi-moon-stars-fill"></i> <span>Modo Oscuro</span>
                    </button>
                </div>
            </header>

            <div class="page-heading mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h3 class="fw-bold mb-0">Consola Global</h3>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=admin_empresas" class="ac-btn-primary py-2 px-4">
                        <i class="bi bi-envelope-plus"></i> Emitir Correos Institucionales
                    </a>
                </div>
            </div>

            <div class="page-content">
                <!-- Tarjetas Bento de Métricas Reales -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="ac-bento-card ac-beam-top p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="ac-label mb-0">EMPRESAS ACTIVAS</span>
                                <div class="ac-squircle-logo" style="width: 42px; height: 42px; border-radius: 12px; font-size: 1.2rem;">
                                    <i class="bi bi-buildings"></i>
                                </div>
                            </div>
                            <h2 class="fw-extrabold mb-1"><?php echo (int)($stats['total_empresas'] ?? 0); ?></h2>
                            <span class="small" style="color: var(--ac-text-muted);">
                                <?php echo (int)($stats['empresas_pendientes'] ?? 0); ?> pendientes
                            </span>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="ac-bento-card ac-beam-top p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="ac-label mb-0">USUARIOS</span>
                                <div class="ac-squircle-logo" style="width: 42px; height: 42px; border-radius: 12px; font-size: 1.2rem;">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                            </div>
                            <h2 class="fw-extrabold mb-1"><?php echo (int)($stats['total_usuarios'] ?? 0); ?></h2>
                            <span class="small text-primary fw-semibold">
                                Cuentas registradas
                            </span>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="ac-bento-card ac-beam-top p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="ac-label mb-0">RACKS MONITOREADOS</span>
                                <div class="ac-squircle-logo" style="width: 42px; height: 42px; border-radius: 12px; font-size: 1.2rem;">
                                    <i class="bi bi-hdd-rack"></i>
                                </div>
                            </div>
                            <h2 class="fw-extrabold mb-1"><?php echo (int)($stats['total_racks'] ?? 0); ?></h2>
                            <span class="ac-badge ac-badge-success">En línea</span>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="ac-bento-card ac-beam-top p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="ac-label mb-0">ALERTAS ACTIVAS</span>
                                <div class="ac-squircle-logo" style="width: 42px; height: 42px; border-radius: 12px; font-size: 1.2rem; background: rgba(239,68,68,0.12); color: #dc2626;">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                            </div>
                            <h2 class="fw-extrabold mb-1 text-danger"><?php echo (int)($stats['alertas_activas'] ?? 0); ?></h2>
                            <span class="small" style="color: var(--ac-text-muted);">En todos los sitios</span>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Columna Principal: Solicitudes de Correos Institucionales y Empresas -->
                    <div class="col-12 col-lg-8">
                        <div class="ac-bento-card p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0">Solicitudes de Correos Institucionales</h5>
                                </div>
                                <a href="<?php echo BASE_URL; ?>/index.php?page=admin_empresas" class="btn btn-sm btn-outline-primary rounded-pill">
                                    Ver todas
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr class="small text-uppercase" style="color: var(--ac-text-muted);">
                                            <th>Empresa</th>
                                            <th>Colaborador</th>
                                            <th>Rol</th>
                                            <th>Correo Generado / Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($solicitudesPendientes)): ?>
                                            <?php foreach (array_slice($solicitudesPendientes, 0, 5) as $sol): ?>
                                                <tr>
                                                    <td class="fw-semibold"><?php echo htmlspecialchars($sol['nombre_empresa']); ?></td>
                                                    <td><?php echo htmlspecialchars($sol['nombre'] . ' ' . $sol['apellidos']); ?></td>
                                                    <td>
                                                        <span class="ac-badge <?php echo $sol['rol_solicitado'] === 'jefe_ti' ? 'ac-badge-blue' : 'ac-badge-success'; ?>">
                                                            <?php echo $sol['rol_solicitado'] === 'jefe_ti' ? 'Jefe de TI' : 'Operador'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($sol['estado'] === 'pendiente'): ?>
                                                            <form action="<?php echo BASE_URL; ?>/index.php?page=admin_aprobar_solicitud" method="POST" class="d-inline">
                                                                <input type="hidden" name="id_solicitud" value="<?php echo (int)$sol['id_solicitud']; ?>">
                                                                <input type="hidden" name="redirect_to" value="admin">
                                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                                                                    <i class="bi bi-magic me-1"></i> Generar `@<?php echo htmlspecialchars($sol['slug_dominio']); ?>`
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <code class="text-primary fw-bold"><?php echo htmlspecialchars($sol['correo_asignado']); ?></code>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-3 small" style="color: var(--ac-text-muted);">
                                                    No hay solicitudes pendientes. Puedes gestionar empresas o crear correos en la sección Empresas y Correos.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Gráfica de Telemetría Global -->
                        <div class="ac-bento-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Rendimiento Térmico Global (24h)</h5>
                                <span class="ac-badge ac-badge-blue">Promedio Racks</span>
                            </div>
                            <div id="chart-profile-visit"></div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Buzón de Contacto y Bitácora Zero-Trust -->
                    <div class="col-12 col-lg-4">
                        <div class="ac-bento-card p-4 mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0 fw-bold">Buzón de Contacto Web</h6>
                                <?php if (isset($totalNoLeidos) && $totalNoLeidos > 0): ?>
                                    <span class="ac-badge ac-badge-danger"><?php echo (int)$totalNoLeidos; ?> nuevos</span>
                                <?php else: ?>
                                    <span class="ac-badge ac-badge-blue">Al día</span>
                                <?php endif; ?>
                            </div>
                            <p class="small mb-3" style="color: var(--ac-text-muted);">Mensajes de empresas interesadas desde la Landing Page.</p>
                            <button class="ac-btn-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#modalBandeja">
                                <i class="bi bi-envelope-open"></i> Abrir Bandeja
                            </button>
                        </div>

                        <div class="ac-bento-card p-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock text-primary me-2"></i>Bitácora de Seguridad Reciente</h6>
                            <div class="d-flex flex-column gap-3">
                                <?php if (!empty($bitacora)): ?>
                                    <?php foreach (array_slice($bitacora, 0, 5) as $ev): ?>
                                        <div class="p-2 px-3 rounded-3" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="ac-badge ac-badge-blue" style="font-size: 0.68rem;"><?php echo htmlspecialchars($ev['tipo_evento']); ?></span>
                                                <span class="small" style="color: var(--ac-text-muted); font-size: 0.72rem;"><?php echo date('d M H:i', strtotime($ev['fecha_evento'])); ?></span>
                                            </div>
                                            <div class="small mt-1 fw-medium"><?php echo htmlspecialchars($ev['descripcion']); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="small text-muted mb-0">Sin eventos registrados.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="mt-5">
                <div class="footer clearfix mb-0 text-muted small">
                    <div class="float-start"><p>2026 &copy; SysMonitor IoT • Seguridad Extrema</p></div>
                    <div class="float-end"><p>Proyecto Integrador UTM</p></div>
                </div>
            </footer>
        </div>
    </div>

    <!-- MODAL BANDEJA DE ENTRADA -->
    <div class="modal fade" id="modalBandeja" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ac-bento-card border-0">
          <div class="modal-header border-bottom-0">
            <h5 class="modal-title fw-bold"><i class="bi bi-inbox text-primary me-2"></i> Mensajes de Contacto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body p-4">
             <?php if (isset($mensajesNoLeidos) && count($mensajesNoLeidos) > 0): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($mensajesNoLeidos as $msg): ?>
                    <div class="p-3 rounded-4 border" style="background: var(--ac-bg-secondary); border-color: var(--ac-border) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong><?php echo htmlspecialchars($msg['asunto']); ?></strong>
                            <span class="small text-muted"><?php echo date('d M H:i', strtotime($msg['fecha_envio'])); ?></span>
                        </div>
                        <div class="small mb-2 text-primary"><?php echo htmlspecialchars($msg['nombre'] . ' (' . $msg['correo'] . ')'); ?></div>
                        <p class="small mb-2"><?php echo htmlspecialchars($msg['mensaje']); ?></p>
                        <div class="text-end">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin_mensaje_leido&id=<?php echo (int)$msg['id_contacto']; ?>" class="btn btn-sm btn-outline-success rounded-pill">
                                <i class="bi bi-check2-all"></i> Marcar como Leído
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
             <?php else: ?>
                 <div class="text-center py-4 text-muted">No tienes mensajes nuevos.</div>
             <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/components/dark.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/compiled/js/app.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/extensions/apexcharts/apexcharts.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/pages/dashboard.js"></script>
</body>
</html>
