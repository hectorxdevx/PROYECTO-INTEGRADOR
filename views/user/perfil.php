<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil Institucional y MFA | SysMonitor</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app.css">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app-dark.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/dashboard/custom-theme.css?v=5">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/aceternity-ui.css?v=2">
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
                        <li class="sidebar-title">Menú Institucional</li>
                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=user" class='sidebar-link'>
                                <i class="bi bi-grid-1x2-fill"></i><span>Racks y Telemetría</span>
                            </a>
                        </li>
                        <li class="sidebar-item active">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=perfil" class='sidebar-link'>
                                <i class="bi bi-person-badge-fill"></i><span>Mi Perfil & MFA</span>
                            </a>
                        </li>
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
            <header class="mb-4 d-flex justify-content-between align-items-center">
                <a href="#" class="burger-btn d-block d-xl-none"><i class="bi bi-justify fs-3"></i></a>
                <div class="ms-auto d-flex align-items-center pe-2">
                    <button type="button" class="ac-theme-btn">
                        <i class="bi bi-moon-stars-fill"></i> <span>Modo Oscuro</span>
                    </button>
                </div>
            </header>

            <div class="page-heading mb-4">
                <h3 class="fw-bold mb-0">Mi Perfil</h3>
            </div>

            <div class="page-content">
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success rounded-4 small py-2 px-3 mb-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Tus datos han sido actualizados en la base de datos.
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <div class="col-12 col-lg-4">
                        <div class="ac-bento-card ac-beam-top p-4 text-center mb-4">
                            <form action="<?php echo BASE_URL; ?>/index.php?page=upload_photo" method="POST" enctype="multipart/form-data" class="d-flex flex-column align-items-center">
                                <div class="mx-auto mb-3 position-relative d-flex align-items-center justify-content-center" style="width: 96px; height: 96px;">
                                    <?php if (!empty($usuario['foto_perfil']) && $usuario['foto_perfil'] !== 'default.png'): ?>
                                        <img src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Avatar" style="width: 96px; height: 96px; object-fit: cover; border-radius: 50%; border: 3px solid var(--ac-border-hover); box-shadow: 0 4px 14px rgba(29, 78, 216, 0.2);">
                                    <?php else: ?>
                                        <div class="ac-squircle-logo rounded-circle d-flex align-items-center justify-content-center" style="width: 88px; height: 88px; font-size: 2.2rem; border: 2px solid var(--ac-border-hover); box-shadow: 0 4px 14px rgba(29, 78, 216, 0.15);">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <h5 class="fw-bold mb-1 w-100 text-break"><?php echo htmlspecialchars(($usuario['nombre_usuario'] ?? '') . ' ' . ($usuario['apellidos'] ?? '')); ?></h5>
                                <div class="small text-primary mb-2 w-100 text-break" style="font-family: 'Fira Code', monospace; word-break: break-all;">
                                    <?php echo htmlspecialchars($usuario['correo_electronico'] ?? ''); ?>
                                </div>
                                <div class="mb-3">
                                    <span class="ac-badge ac-badge-blue">
                                        <?php echo strtoupper(htmlspecialchars($usuario['nombre_rol'] ?? 'Institucional')); ?>
                                    </span>
                                </div>
                                <div class="w-100 pt-1 pb-1">
                                    <label class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm" for="profile-upload" style="cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                                        <i class="bi bi-camera me-1"></i> Cambiar Foto
                                    </label>
                                    <input type="file" name="foto" id="profile-upload" accept="image/png, image/jpeg" class="d-none" onchange="this.form.submit()">
                                </div>
                            </form>
                        </div>

                        <!-- Tarjeta de Google Authenticator vinculada -->
                        <div class="ac-bento-card p-4 text-center">
                            <span class="ac-badge ac-badge-success mb-2"><i class="bi bi-shield-check"></i> MFA TOTP ACTIVO</span>
                            <h6 class="fw-bold mb-2">Llave Google Authenticator</h6>
                            <?php if (!empty($qrMfaUrl)): ?>
                                <div class="p-2 bg-white border rounded-4 d-inline-block mb-2">
                                    <img src="<?php echo htmlspecialchars($qrMfaUrl); ?>" alt="QR MFA" width="150" height="150">
                                </div>
                                <div class="small text-muted">Secreto Base32: <code class="text-primary fw-bold"><?php echo htmlspecialchars($usuario['mfa_secret'] ?? ''); ?></code></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12 col-lg-8">
                        <div class="ac-bento-card ac-beam-top p-4 p-md-5">
                            <h5 class="fw-bold mb-4">Actualizar Información y Contraseña</h5>
                            <form action="<?php echo BASE_URL; ?>/index.php?page=perfil_actualizar" method="POST">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="ac-label">Nombre(s)</label>
                                        <div class="ac-input-wrapper">
                                            <i class="bi bi-person"></i>
                                            <input type="text" name="nombre_usuario" class="ac-input" value="<?php echo htmlspecialchars($usuario['nombre_usuario'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ac-label">Apellidos</label>
                                        <div class="ac-input-wrapper">
                                            <i class="bi bi-person-vcard"></i>
                                            <input type="text" name="apellidos_usuario" class="ac-input" value="<?php echo htmlspecialchars($usuario['apellidos'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="ac-label">Correo Institucional Asignado (Solo lectura)</label>
                                    <div class="ac-input-wrapper" style="opacity: 0.8;">
                                        <i class="bi bi-envelope-lock"></i>
                                        <input type="email" class="ac-input" value="<?php echo htmlspecialchars($usuario['correo_electronico'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <h6 class="fw-bold mb-3 pt-2 border-top" style="border-color: var(--ac-border) !important;">Cambiar Contraseña</h6>
                                <div class="mb-4">
                                    <label class="ac-label">Nueva Contraseña (dejar en blanco para conservar la actual)</label>
                                    <div class="ac-input-wrapper">
                                        <i class="bi bi-lock"></i>
                                        <input type="password" name="password" class="ac-input" placeholder="Mínimo 8 caracteres">
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="ac-btn-primary">
                                        <i class="bi bi-check2-circle"></i> Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/components/dark.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/compiled/js/app.js"></script>
</body>
</html>
