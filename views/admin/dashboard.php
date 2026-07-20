<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | SysMonitor</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app.css">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app-dark.css">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/iconly.css">
    
    <!-- Nuestro estilo de colores pastel (para el modo claro) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/dashboard/custom-theme.css?v=3">
    
    <style>
        .stats-card { transition: transform 0.2s ease, box-shadow 0.2s ease; cursor: pointer; }
        .stats-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); border-color: var(--bs-primary) !important; }
        /* Estilos del Widget de Accesibilidad */
        .a11y-widget { position: fixed; bottom: 30px; right: 30px; z-index: 1000; }
        .pulse-badge { animation: pulseDanger 2s infinite; }
        @keyframes pulseDanger {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
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
                            <!-- LOGO DEL SISTEMA -->
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
                        
                        <li class="sidebar-item active">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin" class='sidebar-link'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Panel General</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
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
                <h3>Resumen de Infraestructura</h3>
            </div>
            
            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-9">
                        <div class="row">
                            <div class="col-6 col-lg-4 col-md-6">
                                <div class="card stats-card border border-light">
                                    <div class="card-body px-4 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                                <div class="stats-icon blue mb-2">
                                                    <i class="iconly-boldProfile"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                                <h6 class="text-muted font-semibold">Usuarios Activos</h6>
                                                <h6 class="font-extrabold mb-0">12</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4 col-md-6">
                                <div class="card stats-card border border-light">
                                    <div class="card-body px-4 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                                <div class="stats-icon green mb-2">
                                                    <i class="iconly-boldActivity"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                                <h6 class="text-muted font-semibold">Sensores en Línea</h6>
                                                <h6 class="font-extrabold mb-0">8</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4 col-md-6">
                                <div class="card stats-card border border-light">
                                    <div class="card-body px-4 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                                <div class="stats-icon red mb-2">
                                                    <i class="iconly-boldDanger"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                                <h6 class="text-muted font-semibold">Alertas Críticas</h6>
                                                <h6 class="font-extrabold mb-0 text-danger">2</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- BANDEJA DE ENTRADA -->
                            <div class="card p3r-card mt-4 border-0 shadow-sm" style="background-color: var(--bs-body-bg);">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="mb-0 fw-bold" style="color: var(--bs-heading-color);">Buzón de Contacto</h6>
                                        <?php if(isset($totalNoLeidos) && $totalNoLeidos > 0): ?>
                                            <span class="badge bg-danger rounded-pill pulse-badge" style="font-size: 14px;"><?php echo $totalNoLeidos; ?> nuevos</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill">Sin mensajes</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted small">Mensajes recibidos desde el formulario web.</p>
                                    <button class="btn btn-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#modalBandeja">
                                        <i class="bi bi-envelope-open me-2"></i> Abrir Bandeja
                                    </button>
                                </div>
                            </div>
                            
                        </div>

                        <!-- Gráfica Falsa de Monitoreo -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Temperatura Histórica (Site 1)</h4>
                                    </div>
                                    <div class="card-body">
                                        <div id="chart-profile-visit"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar Lateral Derecha -->
                    <div class="col-12 col-lg-3">
                        <div class="card">
                            <div class="card-body py-4 px-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                        <?php if(isset($_SESSION['foto_perfil']) && $_SESSION['foto_perfil'] != ''): ?>
                                            <img src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" alt="Admin" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                        <?php else: ?>
                                            <div class="bg-primary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-3 name">
                                        <h5 class="font-bold">Administrador</h5>
                                        <h6 class="text-muted mb-0">admin@sysmonitor.com</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h4>Últimos Accesos a Racks</h4>
                            </div>
                            <div class="card-content pb-4">
                                <div class="recent-message d-flex px-4 py-3">
                                    <div class="name ms-4">
                                        <h5 class="mb-1 text-danger">Rack 02 Abierto</h5>
                                        <h6 class="text-muted mb-0">Hace 5 mins</h6>
                                    </div>
                                </div>
                                <div class="recent-message d-flex px-4 py-3">
                                    <div class="name ms-4">
                                        <h5 class="mb-1 text-success">Rack 01 Cerrado</h5>
                                        <h6 class="text-muted mb-0">Hace 1 hora</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
    
    <!-- MODAL BANDEJA DE ENTRADA -->
    <div class="modal fade" id="modalBandeja" tabindex="-1" aria-labelledby="modalBandejaLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header border-bottom-0">
            <h5 class="modal-title fw-bold" id="modalBandejaLabel"><i class="bi bi-inbox text-primary me-2"></i> Mensajes Sin Leer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body p-4">
             <?php if(isset($mensajesNoLeidos) && count($mensajesNoLeidos) > 0): ?>
                <div class="accordion" id="accordionMensajes">
                    <?php foreach($mensajesNoLeidos as $index => $msg): ?>
                    <div class="accordion-item mb-3 border-0 shadow-sm rounded overflow-hidden">
                        <h2 class="accordion-header" id="headingMsg<?php echo $msg['id_contacto']; ?>">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMsg<?php echo $msg['id_contacto']; ?>" aria-expanded="false" aria-controls="collapseMsg<?php echo $msg['id_contacto']; ?>" style="background-color: var(--bs-secondary-bg); color: var(--bs-heading-color);">
                                <i class="bi bi-envelope-fill text-danger me-3"></i> 
                                <span style="<?php echo $msg['leido'] != 0 ? 'opacity: 0.6;' : ''; ?>"><?php echo htmlspecialchars($msg['asunto']); ?></span>
                                <span class="ms-auto small me-3" style="color: var(--bs-heading-color); opacity: 0.6;"><?php echo date('d M H:i', strtotime($msg['fecha_envio'])); ?></span>
                            </button>
                        </h2>
                        <div id="collapseMsg<?php echo $msg['id_contacto']; ?>" class="accordion-collapse collapse" aria-labelledby="headingMsg<?php echo $msg['id_contacto']; ?>" data-bs-parent="#accordionMensajes">
                            <div class="accordion-body border-top">
                                <div class="mb-3 d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                        <?php echo strtoupper(substr($msg['nombre'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold" style="color: var(--bs-heading-color);"><?php echo htmlspecialchars($msg['nombre']); ?></h6>
                                        <a href="mailto:<?php echo htmlspecialchars($msg['correo']); ?>" class="small text-decoration-none text-primary"><?php echo htmlspecialchars($msg['correo']); ?></a>
                                    </div>
                                </div>
                                <div class="p-3 rounded" style="background-color: var(--bs-secondary-bg); color: var(--bs-body-color); white-space: pre-wrap;">
                                    <?php echo htmlspecialchars($msg['mensaje']); ?>
                                </div>
                                <div class="mt-3 text-end">
                                    <a href="<?php echo BASE_URL; ?>/index.php?page=admin_mensaje_leido&id=<?php echo $msg['id_contacto']; ?>" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-check2-all"></i> Marcar como Leído
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
             <?php else: ?>
                 <div class="text-center py-5">
                    <i class="bi bi-envelope-paper text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">No tienes mensajes nuevos</h5>
                    <p class="text-muted small">Tu bandeja de entrada está limpia.</p>
                 </div>
             <?php endif; ?>
          </div>
        </div>
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
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/extensions/apexcharts/apexcharts.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/pages/dashboard.js"></script>
    
    <script>
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
                let textoALeer = `Estás en el panel general de administrador. Aquí puedes ver el resumen de infraestructura, usuarios activos, sensores en línea y alertas críticas.`;
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
