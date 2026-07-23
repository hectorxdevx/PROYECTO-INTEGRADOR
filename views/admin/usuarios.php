<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios | SysMonitor</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app.css">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app-dark.css">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/iconly.css">
    
    <!-- Nuestro estilo de colores pastel (para el modo claro) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/dashboard/custom-theme.css?v=3">
    
    <style>
        /* Estilos del Widget de Accesibilidad */
        .a11y-widget { position: fixed; bottom: 30px; right: 30px; z-index: 1000; }
        .a11y-toggle { background-color: #0d6efd; color: white; border: none; border-radius: 50% !important; width: 60px !important; height: 60px !important; min-width: 60px !important; padding: 0 !important; font-size: 28px !important; display: flex !important; align-items: center !important; justify-content: center !important; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.2); transition: transform 0.3s ease, background-color 0.3s ease; line-height: 0; }
        .a11y-toggle i { display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important; margin: 0 !important; font-size: 28px !important; }
        .a11y-toggle:hover { transform: scale(1.1); background-color: #0b5ed7; }
        .a11y-panel { position: absolute; bottom: 60px; right: 0; background-color: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); width: 250px; padding: 15px; display: none; flex-direction: column; gap: 10px; border: 1px solid rgba(0,0,0,0.1); }
        body.theme-dark .a11y-panel { background-color: #1e1e2d; border-color: #2b2b40; }
        .a11y-panel.show { display: flex; animation: popIn 0.3s ease forwards; }
        .a11y-header { font-weight: bold; margin-bottom: 10px; color: var(--bs-heading-color); border-bottom: 1px solid var(--bs-border-color); padding-bottom: 5px; }
        .a11y-btn { background: transparent; border: 1px solid var(--bs-border-color); border-radius: 8px; padding: 8px 12px; text-align: left; cursor: pointer; display: flex; align-items: center; gap: 10px; color: var(--bs-body-color); transition: background 0.2s; }
        .a11y-btn:hover { background-color: var(--bs-secondary-bg); }
        .a11y-btn.active-reader { background-color: #198754; color: white; border-color: #198754; }
        @keyframes popIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        body.high-contrast-mode .page-content, body.high-contrast-mode .sidebar-wrapper, body.high-contrast-mode header { filter: contrast(150%) saturate(120%); }
        body.high-contrast-mode .card { border: 2px solid #fff !important; }
    </style>
</head>
<body>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/initTheme.js"></script>
    <div id="app">
        <div id="sidebar">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                            <a href="<?php echo BASE_URL; ?>/"><h4 class="text-primary mt-2">SysMonitor</h4></a>
                        </div>
                        <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--system-uicons" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21"><g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2" opacity=".3"></path><g transform="translate(-210 -1)"><path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path><circle cx="220.5" cy="11.5" r="4"></circle><path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path></g></g></svg>
                            <div class="form-check form-switch fs-6 mb-0">
                                <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                                <label class="form-check-label"></label>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z"></path></svg>
                        </div>
                        <div class="sidebar-toggler x">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Menú Principal (Administrador)</li>
                        
                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin" class='sidebar-link'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Panel General</span>
                            </a>
                        </li>

                        <li class="sidebar-item active">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin_users" class='sidebar-link'>
                                <i class="bi bi-people-fill"></i>
                                <span>Gestión de Usuarios</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-cpu-fill"></i>
                                <span>Sensores y Equipos</span>
                            </a>
                        </li>

                        <li class="sidebar-title">Cuenta</li>
                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php" class='sidebar-link text-danger'>
                                <i class="bi bi-box-arrow-left text-danger"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3>Gestión de Usuarios</h3>
                            <p class="text-subtitle text-muted">Administra los accesos de los estudiantes y personal a la plataforma.</p>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title">Usuarios Registrados</h4>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario"><i class="bi bi-person-plus-fill"></i> Nuevo Usuario</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Apellidos</th>
                                            <th>Correo Electrónico</th>
                                            <th>Rol</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (isset($usuarios) && count($usuarios) > 0): ?>
                                            <?php foreach ($usuarios as $u): ?>
                                            <tr>
                                                <td class="text-bold-500"><?php echo htmlspecialchars($u['id_usuario']); ?></td>
                                                <td><?php echo htmlspecialchars($u['nombre_usuario']); ?></td>
                                                <td><?php echo htmlspecialchars($u['apellidos']); ?></td>
                                                <td><?php echo htmlspecialchars($u['correo_electronico']); ?></td>
                                                <td><?php echo htmlspecialchars($u['nombre_rol'] ?? 'Sin Rol'); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario<?php echo $u['id_usuario']; ?>" aria-label="Editar usuario"><i class="bi bi-pencil-square" aria-hidden="true"></i></button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" data-url="<?php echo BASE_URL; ?>/index.php?page=admin_users_eliminar&id=<?php echo $u['id_usuario']; ?>" aria-label="Eliminar usuario"><i class="bi bi-trash" aria-hidden="true"></i></button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No hay usuarios registrados en la base de datos.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL CREAR USUARIO -->
                    <div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Nuevo Usuario</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?php echo BASE_URL; ?>/index.php?page=admin_users_crear" method="POST">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nombre</label>
                                            <input type="text" class="form-control" name="nombre" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Apellidos</label>
                                            <input type="text" class="form-control" name="apellidos" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Correo Electrónico</label>
                                            <input type="email" class="form-control" name="correo" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Contraseña</label>
                                            <input type="password" class="form-control" name="password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Rol</label>
                                            <select class="form-select" name="rol" required>
                                                <option value="1">Usuario (Estudiante/Personal)</option>
                                                <option value="2">Administrador</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- MODALES EDITAR USUARIO (UNO POR CADA USUARIO) -->
                    <?php if (isset($usuarios)): ?>
                        <?php foreach ($usuarios as $u): ?>
                        <div class="modal fade" id="modalEditarUsuario<?php echo $u['id_usuario']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar Usuario</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?php echo BASE_URL; ?>/index.php?page=admin_users_editar" method="POST">
                                        <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($u['id_usuario']); ?>">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($u['nombre_usuario']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Apellidos</label>
                                                <input type="text" class="form-control" name="apellidos" value="<?php echo htmlspecialchars($u['apellidos']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Correo Electrónico</label>
                                                <input type="email" class="form-control" name="correo" value="<?php echo htmlspecialchars($u['correo_electronico']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Rol</label>
                                                <select class="form-select" name="rol" required>
                                                    <option value="1" <?php echo ($u['id_rol'] == 1) ? 'selected' : ''; ?>>Usuario</option>
                                                    <option value="2" <?php echo ($u['id_rol'] == 2) ? 'selected' : ''; ?>>Administrador</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </section>
            </div>
            
            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>2026 &copy; SysMonitor IoT</p>
                    </div>
                    <div class="float-end">
                        <p>Proyecto Integrador UTM</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Widget de Accesibilidad -->
    <div class="a11y-widget" id="a11yWidget">
      <button class="a11y-toggle" id="a11yToggle" aria-label="Opciones de accesibilidad">
        <i class="bi bi-person-wheelchair"></i>
      </button>
      <div class="a11y-panel" id="a11yPanel">
        <div class="a11y-header">Accesibilidad</div>
        <button class="a11y-btn" id="a11yIncreaseText" aria-label="Aumentar tamaño del texto"><i class="bi bi-zoom-in"></i> Aumentar Texto</button>
        <button class="a11y-btn" id="a11yDecreaseText" aria-label="Reducir tamaño del texto"><i class="bi bi-zoom-out"></i> Reducir Texto</button>
        <button class="a11y-btn" id="a11yContrast" aria-label="Activar modo de alto contraste para daltonismo"><i class="bi bi-circle-half"></i> Alto Contraste</button>
        <button class="a11y-btn" id="a11yReadText" aria-label="Activar lector de voz de la página"><i class="bi bi-play-circle"></i> Iniciar Lector</button>
        <button class="a11y-btn" id="a11yStopText" aria-label="Detener lector de voz de la página"><i class="bi bi-stop-circle"></i> Detener Lector</button>
      </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/components/dark.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/compiled/js/app.js"></script>
    
    <script>
        // UX: SweetAlert2 para eliminar
        document.querySelectorAll('.btn-eliminar').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('data-url');
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede deshacer y borrará al usuario de la base de datos.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });

        // --- Accesibilidad A11y ---
        const a11yToggle = document.getElementById('a11yToggle');
        const a11yPanel = document.getElementById('a11yPanel');
        const btnIncText = document.getElementById('a11yIncreaseText');
        const btnDecText = document.getElementById('a11yDecreaseText');
        const btnContrast = document.getElementById('a11yContrast');
        const btnReadText = document.getElementById('a11yReadText');
        const btnStopText = document.getElementById('a11yStopText');
        
        let currentFontSize = 100;
        let synth = window.speechSynthesis;
        let utterance = null;

        a11yToggle.addEventListener('click', () => { a11yPanel.classList.toggle('show'); });

        btnIncText.addEventListener('click', () => {
            if(currentFontSize < 150) { currentFontSize += 10; document.documentElement.style.fontSize = currentFontSize + '%'; }
        });
        btnDecText.addEventListener('click', () => {
            if(currentFontSize > 80) { currentFontSize -= 10; document.documentElement.style.fontSize = currentFontSize + '%'; }
        });
        btnContrast.addEventListener('click', () => { document.body.classList.toggle('high-contrast-mode'); });

        btnReadText.addEventListener('click', () => {
            if(synth.speaking && !synth.paused) {
                synth.pause();
                btnReadText.innerHTML = '<i class="bi bi-play-circle"></i> Reanudar Lector';
                btnReadText.classList.remove('active-reader');
            } else if (synth.paused) {
                synth.resume();
                btnReadText.innerHTML = '<i class="bi bi-pause-circle"></i> Pausar Lector';
                btnReadText.classList.add('active-reader');
            } else {
                synth.cancel();
                let textoALeer = `Estás en la vista de gestión de usuarios del administrador. Aquí puedes crear, editar y eliminar los accesos de los estudiantes y el personal.`;
                utterance = new SpeechSynthesisUtterance(textoALeer);
                utterance.lang = 'es-ES';
                utterance.onend = () => {
                    btnReadText.classList.remove('active-reader');
                    btnReadText.innerHTML = '<i class="bi bi-play-circle"></i> Iniciar Lector';
                };
                synth.speak(utterance);
                btnReadText.classList.add('active-reader');
                btnReadText.innerHTML = '<i class="bi bi-pause-circle"></i> Pausar Lector';
            }
        });
        
        btnStopText.addEventListener('click', () => {
            synth.cancel();
            btnReadText.classList.remove('active-reader');
            btnReadText.innerHTML = '<i class="bi bi-play-circle"></i> Iniciar Lector';
        });
        
        document.querySelectorAll('i').forEach(icon => icon.setAttribute('aria-hidden', 'true'));
    </script>
</body>
</html>
