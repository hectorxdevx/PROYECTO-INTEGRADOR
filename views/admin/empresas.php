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
                <h3 class="fw-bold mb-1">Empresas y Correos Institucionales</h3>
                <p class="small mb-0" style="color: var(--ac-text-muted);">
                    Aprueba las empresas registradas y asigna correos institucionales a su personal.
                </p>
            </div>

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
                                    <th>Representante (OAuth)</th>
                                    <th>Racks / Personal</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($empresas as $emp): ?>
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
                                        <span class="ac-badge ac-badge-blue"><?php echo (int)$emp['total_racks']; ?> Racks</span>
                                        <span class="ac-badge ac-badge-success"><?php echo (int)$emp['total_personal']; ?> Cuentas</span>
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
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/components/dark.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/compiled/js/app.js"></script>
</body>
</html>
