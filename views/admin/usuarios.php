<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios y MFA | SysMonitor</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin_empresas" class='sidebar-link'>
                                <i class="bi bi-buildings-fill"></i><span>Empresas y Correos</span>
                            </a>
                        </li>
                        <li class="sidebar-item active">
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
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h3 class="fw-bold mb-0">Gestión de Usuarios</h3>
                    </div>
                    <button class="ac-btn-primary py-2 px-4" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                        <i class="bi bi-person-plus-fill"></i> Crear Cuenta Institucional
                    </button>
                </div>
            </div>

            <div class="page-content">
                <div class="ac-bento-card ac-beam-top p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr class="small text-uppercase" style="color: var(--ac-text-muted);">
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Correo (Personal / Institucional)</th>
                                    <th>Empresa</th>
                                    <th>Rol</th>
                                    <th>Proveedor / MFA</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo (int)$u['id_usuario']; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($u['nombre_usuario'] . ' ' . $u['apellidos']); ?></td>
                                    <td><code class="text-primary fw-bold"><?php echo htmlspecialchars($u['correo_electronico']); ?></code></td>
                                    <td><?php echo htmlspecialchars($u['nombre_empresa'] ?? 'Global / Sin asignar'); ?></td>
                                    <td>
                                        <?php
                                            $rolBadge = 'ac-badge-blue';
                                            $rolTexto = $u['nombre_rol'] ?? 'operador';
                                            if ((int)$u['id_rol'] === 2) { $rolTexto = 'SuperAdmin'; }
                                            elseif ((int)$u['id_rol'] === 3) { $rolTexto = 'Representante'; $rolBadge = 'ac-badge-warning'; }
                                            elseif ((int)$u['id_rol'] === 4) { $rolTexto = 'Jefe de TI'; $rolBadge = 'ac-badge-blue'; }
                                            else { $rolTexto = 'Operador'; $rolBadge = 'ac-badge-success'; }
                                        ?>
                                        <span class="ac-badge <?php echo $rolBadge; ?>"><?php echo htmlspecialchars($rolTexto); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border me-1"><?php echo strtoupper(htmlspecialchars($u['oauth_provider'] ?? 'institucional')); ?></span>
                                        <?php if (!empty($u['mfa_enabled'])): ?>
                                            <span class="ac-badge ac-badge-success"><i class="bi bi-qr-code"></i> TOTP Activo</span>
                                        <?php else: ?>
                                            <span class="ac-badge ac-badge-blue"><i class="bi bi-shield-lock"></i> TOTP Listo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario<?php echo (int)$u['id_usuario']; ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-3 btn-eliminar" data-url="<?php echo BASE_URL; ?>/index.php?page=admin_users_eliminar&id=<?php echo (int)$u['id_usuario']; ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CREAR USUARIO INSTITUCIONAL -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ac-bento-card border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Emitir Nueva Cuenta Institucional</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo BASE_URL; ?>/index.php?page=admin_users_crear" method="POST">
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="ac-label">Nombre(s)</label>
                                <input type="text" class="form-control rounded-3" name="nombre" required>
                            </div>
                            <div class="col-6">
                                <label class="ac-label">Apellidos</label>
                                <input type="text" class="form-control rounded-3" name="apellidos" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="ac-label">Empresa / Institución</label>
                            <select class="form-select rounded-3" name="id_empresa">
                                <option value="">Global (Sin empresa específica)</option>
                                <?php foreach ($empresas as $emp): ?>
                                    <option value="<?php echo (int)$emp['id_empresa']; ?>">
                                        <?php echo htmlspecialchars($emp['nombre_empresa'] . ' (@' . $emp['slug_dominio'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="ac-label">Correo Institucional Asignado</label>
                            <input type="email" class="form-control rounded-3" name="correo" placeholder="usuario@empresa.sysmonitor.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="ac-label">Contraseña Inicial</label>
                            <input type="password" class="form-control rounded-3" name="password" value="SysMonitor2026!" required>
                        </div>
                        <div class="mb-2">
                            <label class="ac-label">Rol en el Modelo de Negocio</label>
                            <select class="form-select rounded-3" name="rol" required>
                                <option value="1">Operador Institucional (Monitoreo)</option>
                                <option value="4">Jefe de TI Institucional (Monitoreo + Gestión Racks/Umbrales)</option>
                                <option value="3">Representante de Empresa (Solo Alta de Empresa)</option>
                                <option value="2">Super Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="ac-btn-primary py-2 px-4">Crear Cuenta + Secreto MFA</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODALES EDITAR USUARIO -->
    <?php foreach ($usuarios as $u): ?>
    <div class="modal fade" id="modalEditarUsuario<?php echo (int)$u['id_usuario']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ac-bento-card border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Editar Usuario #<?php echo (int)$u['id_usuario']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo BASE_URL; ?>/index.php?page=admin_users_editar" method="POST">
                    <input type="hidden" name="id_usuario" value="<?php echo (int)$u['id_usuario']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="ac-label">Nombre</label>
                            <input type="text" class="form-control rounded-3" name="nombre" value="<?php echo htmlspecialchars($u['nombre_usuario']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="ac-label">Apellidos</label>
                            <input type="text" class="form-control rounded-3" name="apellidos" value="<?php echo htmlspecialchars($u['apellidos']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="ac-label">Correo Electrónico</label>
                            <input type="email" class="form-control rounded-3" name="correo" value="<?php echo htmlspecialchars($u['correo_electronico']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="ac-label">Empresa</label>
                            <select class="form-select rounded-3" name="id_empresa">
                                <option value="">Sin empresa</option>
                                <?php foreach ($empresas as $emp): ?>
                                    <option value="<?php echo (int)$emp['id_empresa']; ?>" <?php echo ((int)($u['id_empresa'] ?? 0) === (int)$emp['id_empresa']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['nombre_empresa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="ac-label">Rol</label>
                            <select class="form-select rounded-3" name="rol" required>
                                <option value="1" <?php echo ((int)$u['id_rol'] === 1) ? 'selected' : ''; ?>>Operador Institucional</option>
                                <option value="4" <?php echo ((int)$u['id_rol'] === 4) ? 'selected' : ''; ?>>Jefe de TI Institucional</option>
                                <option value="3" <?php echo ((int)$u['id_rol'] === 3) ? 'selected' : ''; ?>>Representante de Empresa</option>
                                <option value="2" <?php echo ((int)$u['id_rol'] === 2) ? 'selected' : ''; ?>>Super Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="ac-btn-primary py-2 px-4">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/components/dark.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/compiled/js/app.js"></script>
    <script>
        document.querySelectorAll('.btn-eliminar').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('data-url');
                Swal.fire({
                    title: '¿Eliminar cuenta?',
                    text: "Se revocará el acceso institucional y el secreto MFA.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((r) => { if (r.isConfirmed) window.location.href = url; });
            });
        });
    </script>
</body>
</html>
