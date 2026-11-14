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

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success rounded-4 py-2 px-3 small mb-4">
                <i class="bi bi-check-circle-fill me-1"></i> Operación guardada correctamente.
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

                            <button type="submit" class="ac-btn-primary w-100">
                                <span>Registrar Empresa</span> <i class="bi bi-arrow-right"></i>
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
        <?php endif; ?>
    </main>
</body>
</html>
