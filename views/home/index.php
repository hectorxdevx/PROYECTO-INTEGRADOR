<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SysMonitor | Gestión de Infraestructura</title>
    
    <!-- SEO y Accesibilidad -->
    <meta name="description" content="SysMonitor - Plataforma avanzada para el monitoreo físico de servidores IoT. Control de temperatura, humedad, consumo y seguridad física en tiempo real." />
    <meta name="keywords" content="monitoreo servidores, IoT, temperatura rack, seguridad servidores, telemetria, datacenter" />
    <meta property="og:title" content="SysMonitor | Gestión de Infraestructura" />
    <meta property="og:description" content="Control total de la infraestructura física de tus servidores con telemetría en tiempo real." />
    <meta property="og:type" content="website" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <link href="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/landing_page/css/color-style.css" rel="stylesheet" />
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
      html {
        scroll-behavior: smooth;
      }
      .p3r-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease !important;
      }
      .p3r-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(135, 206, 235, 0.15) !important;
      }
    </style>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
  </head>

  <body>
    <div class="bg-grid"></div>

    <nav class="navbar navbar-expand-lg fixed-top custom-navbar">
      <div class="container-fluid container-xl">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>/">
          
          <!-- ========================================== -->
          <!-- AQUI VA EL LOGO -->
          <!-- LOGO CREADO A MEDIDA (SVG Minimalista y Brillante) -->
          <div class="me-2 d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36">
              <!-- Caja del servidor (Limpio y claro) -->
              <rect x="1" y="2" width="22" height="20" rx="4" fill="rgba(135,206,235,0.15)" stroke="#87CEEB" stroke-width="2" />
              
              <!-- LEDs indicadores -->
              <circle cx="18.5" cy="6" r="1.5" fill="#87CEEB" />
              
              <!-- Línea de pulso (Telemetría), gruesa y brillante -->
              <path d="M 1 15 L 6 15 L 8.5 8 L 12 19 L 15 11 L 17 15 L 23 15" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" filter="drop-shadow(0px 0px 4px rgba(255,255,255,0.8))" />
            </svg>
          </div>
          <span class="logo-text">Sys<span class="text-white">Monitor</span></span>
          <!-- ========================================== -->

        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu" aria-label="Abrir menú de navegación" aria-expanded="false">
          <i class="bi bi-list text-white fs-2"></i>
        </button>

        <div class="collapse navbar-collapse" id="navmenu">
          <ul class="navbar-nav mx-auto gap-4">
            <li class="nav-item"><a class="nav-link" href="#caracteristicas">Características</a></li>
            <li class="nav-item"><a class="nav-link" href="#arquitectura">Arquitectura</a></li>
            <li class="nav-item"><a class="nav-link" href="#galeria">Interfaz</a></li>
            <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
          </ul>
          <div class="nav-actions d-flex align-items-center gap-3 mt-3 mt-lg-0">
            <a href="<?php echo BASE_URL; ?>/index.php?page=login" class="text-link">Iniciar sesión</a>
            <a href="<?php echo BASE_URL; ?>/index.php?page=login" class="btn btn-cyan text-uppercase">REGISTRATE</a>
          </div>
        </div>
      </div>
    </nav>

    <main class="hero-section">
      <div class="bg-glow"></div>

      <div class="container">
        <div class="row align-items-center justify-content-between min-vh-100 pt-5">
          <div class="col-lg-6 hero-content">
            <div class="badge-cyan mb-4"><span class="dot-pulse"></span> Software en desarrollo</div>
            <h1 class="display-3 fw-bold mb-4">Tu infraestructura,<br /><span class="text-cyan">bajo control total.</span></h1>
            <p class="hero-subtitle mb-5">Deja de preocuparte por fallos inesperados. Monitorea el estado físico de tus servidores desde una plataforma rápida y simple.</p>
            <div class="hero-cta d-flex align-items-center gap-4 mb-3">
            <a href="<?php echo BASE_URL; ?>/index.php?page=login" class="btn btn-cyan btn-lg d-inline-flex align-items-center gap-2">
                REGISTRATE <i class="bi bi-arrow-right"></i>
            </a>
            </div>
          </div> 
          
          <div class="col-lg-5 offset-lg-1">
            <div class="friendly-dashboard-card p-4" data-aos="fade-up">
              <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                  <h5 class="mb-0 fw-bold text-dark" style="font-family: 'Inter', sans-serif;">
                      <i class="bi bi-hdd-network text-primary me-2"></i>Rack Principal 04
                  </h5>
                  <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-bold">
                      <span class="pulsing-dot"></span> En línea
                  </span>
              </div>
              
              <div class="row g-3">
                  <div class="col-6">
                      <div class="metric-box">
                          <div class="metric-icon bg-blue-soft text-primary"><i class="bi bi-thermometer-half"></i></div>
                          <div class="metric-info">
                              <span class="metric-label">Temperatura</span>
                              <span class="metric-value" id="live-temp">22.4°C</span>
                          </div>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="metric-box">
                          <div class="metric-icon bg-info-soft text-info"><i class="bi bi-droplet"></i></div>
                          <div class="metric-info">
                              <span class="metric-label">Humedad</span>
                              <span class="metric-value" id="live-hum">45%</span>
                          </div>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="metric-box">
                          <div class="metric-icon bg-warning-soft text-warning"><i class="bi bi-lightning-charge"></i></div>
                          <div class="metric-info">
                              <span class="metric-label">Consumo</span>
                              <span class="metric-value" id="live-power">1.2 kW</span>
                          </div>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="metric-box alert-state">
                          <div class="metric-icon bg-danger-soft text-danger"><i class="bi bi-door-open"></i></div>
                          <div class="metric-info">
                              <span class="metric-label">Puerta Rack</span>
                              <span class="metric-value text-danger">Abierta</span>
                          </div>
                      </div>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <section id="caracteristicas" class="features-section py-5 mt-5">
      <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
          <h2 class="display-5 fw-bold mb-3">CARACTERÍSTICAS</h2>
        </div>
        <div class="row g-4 mt-4">
          <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
            <article class="p3r-card h-100 p-4 p-lg-5">
              <div class="d-flex justify-content-between align-items-start mb-4 card-header-icons">
                <div class="icon-wrapper"><i class="bi bi-thermometer-half"></i></div>
                <span class="card-badge">CORE</span>
              </div>
              <h3 class="h4 fw-bold text-white mb-3">Control Térmico</h3>
              <p class="mb-0">Protege tus equipos contra el sobrecalentamiento y evita daños críticos por humedad.</p>
            </article>
          </div>
          <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
            <article class="p3r-card h-100 p-4 p-lg-5">
              <div class="d-flex justify-content-between align-items-start mb-4 card-header-icons">
                <div class="icon-wrapper"><i class="bi bi-lightning-charge"></i></div>
                <span class="card-badge">ENERGY</span>
              </div>
              <h3 class="h4 fw-bold text-white mb-3">Eficiencia Energética</h3>
              <p class="mb-0">Optimiza el consumo energético de tus cuartos de servidores con indicadores precisos.</p>
            </article>
          </div>
          <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
            <article class="p3r-card h-100 p-4 p-lg-5">
              <div class="d-flex justify-content-between align-items-start mb-4 card-header-icons">
                <div class="icon-wrapper"><i class="bi bi-shield-check"></i></div>
                <span class="card-badge">SECURITY</span>
              </div>
              <h3 class="h4 fw-bold text-white mb-3">Seguridad Física</h3>
              <p class="mb-0">Mantén una bitácora automatizada de accesos y recibe alertas inmediatas ante cualquier intrusión.</p>
            </article>
          </div>
          <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
            <article class="p3r-card h-100 p-4 p-lg-5">
              <div class="d-flex justify-content-between align-items-start mb-4 card-header-icons">
                <div class="icon-wrapper"><i class="bi bi-diagram-3"></i></div>
                <span class="card-badge">SCALE</span>
              </div>
              <h3 class="h4 fw-bold text-white mb-3">Escalabilidad</h3>
              <p class="">Arquitectura diseñada para gestionar desde un solo rack hasta una gran cantidad.</p>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section id="arquitectura" class="arch-section py-5 mt-5">
      <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
          <h2 class="display-5 fw-bold mb-3 text-white">ARQUITECTURA</h2>
        </div>
        <div class="row mt-5">
          <div class="col-lg-4 col-md-5 mb-4 mb-md-0" data-aos="fade-right">
            <div class="arch-sidebar">
              <button class="arch-tab-btn active theme-blue" data-target="tab-telemetria" onclick="switchArchTab('tab-telemetria', 'blue')">
                <i class="bi bi-broadcast"></i> Telemetría
              </button>
              <button class="arch-tab-btn theme-purple" data-target="tab-ingenieria" onclick="switchArchTab('tab-ingenieria', 'purple')">
                <i class="bi bi-braces"></i> Ingeniería
              </button>
              <button class="arch-tab-btn theme-red" data-target="tab-seguridad" onclick="switchArchTab('tab-seguridad', 'red')">
                <i class="bi bi-shield-lock"></i> Seguridad
              </button>
              <button class="arch-tab-btn theme-green" data-target="tab-respaldo" onclick="switchArchTab('tab-respaldo', 'green')">
                <i class="bi bi-database"></i> Respaldo
              </button>
            </div>
          </div>
          <div class="col-lg-8 col-md-7" data-aos="fade-left">
            <div id="arch-content-container" class="arch-content-container theme-blue">
              <div id="tab-telemetria" class="arch-pane active-pane">
                <div class="pane-header mb-4">
                  <div class="pane-icon"><i class="bi bi-broadcast"></i></div>
                  <h3 class="pane-title">Telemetría</h3>
                </div>
                <p class="pane-text">Captura de datos precisa. Nuestros sensores envían información clave de forma automática para que siempre sepas qué está pasando en tu infraestructura.</p>
                <div class="pane-tags mt-4">
                  <span class="arch-tag">Sensores IoT</span>
                  <span class="arch-tag">Tiempo Real</span>
                  <span class="arch-tag">Métricas Exactas</span>
                </div>
              </div>
              <div id="tab-ingenieria" class="arch-pane">
                <div class="pane-header mb-4">
                  <div class="pane-icon"><i class="bi bi-braces"></i></div>
                  <h3 class="pane-title">Ingeniería</h3>
                </div>
                <p class="pane-text">Desarrollado con una estructura organizada que facilita el mantenimiento y asegura que el sistema siempre esté disponible.</p>
                <div class="pane-tags mt-4">
                  <span class="arch-tag">Arquitectura segura</span>
                  <span class="arch-tag">Escalabilidad</span>
                  <span class="arch-tag">Código Limpio</span>
                </div>
              </div>
              <div id="tab-seguridad" class="arch-pane">
                <div class="pane-header mb-4">
                  <div class="pane-icon"><i class="bi bi-shield-lock"></i></div>
                  <h3 class="pane-title">Seguridad</h3>
                </div>
                <p class="pane-text">Tus datos viajan aislados. La red de monitoreo nunca se cruza con tu tráfico de producción, garantizando un entorno libre de riesgos externos.</p>
                <div class="pane-tags mt-4">
                  <span class="arch-tag">VLAN Aislada</span>
                  <span class="arch-tag">Control de Acceso</span>
                  <span class="arch-tag">MariaDB Segura</span>
                </div>
              </div>
              <div id="tab-respaldo" class="arch-pane">
                <div class="pane-header mb-4">
                  <div class="pane-icon"><i class="bi bi-database"></i></div>
                  <h3 class="pane-title">Respaldo</h3>
                </div>
                <p class="pane-text">Se hace un resumen de las telemetrías del día, así como un respaldo cada 6 y 12 meses para guardar el historial de monitoreo.</p>
                <div class="pane-tags mt-4">
                  <span class="arch-tag">Resumen Diario</span>
                  <span class="arch-tag">Respaldo Semestral</span>
                  <span class="arch-tag">Historial Seguro</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="galeria" class="gallery-section py-5 mt-5">
      <div class="container">
          <div class="row justify-content-center">
              <div class="col-xl-10">
                  <div class="text-center mb-5" data-aos="fade-up">
                      <h2 class="display-5 fw-bold mb-3 text-white">INTERFAZ</h2>
                  </div>
                  <div class="row g-0 align-items-stretch arch-content-container theme-blue p-0 overflow-hidden">
                      <div class="col-md-3 border-end border-secondary p-4 bg-dark-surface">
                          <div class="gallery-sidebar d-flex flex-column gap-2">
                              <button class="gallery-tab-btn active" onclick="cambiarImagenGaleria(this, 'Panel General', '<?php echo BASE_URL; ?>/assets/landing_page/img/gallery/imgPanel.png')">
                                  <i class="bi bi-shield-lock"></i> Panel general
                              </button>
                              <button class="gallery-tab-btn" onclick="cambiarImagenGaleria(this, 'Perfil de Usuario', '<?php echo BASE_URL; ?>/assets/landing_page/img/gallery/imgPerfilUsuario.png')">
                                  <i class="bi bi-person-badge"></i> Perfil de Usuario
                              </button>
                              <button class="gallery-tab-btn" onclick="cambiarImagenGaleria(this, 'Métricas', '<?php echo BASE_URL; ?>/assets/landing_page/img/gallery/imgMetricas.png')">
                                  <i class="bi bi-graph-up"></i> Métricas
                              </button>
                              <button class="gallery-tab-btn" onclick="cambiarImagenGaleria(this, 'Administración de Usuarios', '<?php echo BASE_URL; ?>/assets/landing_page/img/gallery/imgAdminUsuarios.png')">
                                  <i class="bi bi-people"></i> Administración de Usuarios
                              </button>
                          </div>
                      </div>
                      <div class="col-md-9 bg-black d-flex p-3 rounded-end">
                          <img id="gallery-img" src="<?php echo BASE_URL; ?>/assets/landing_page/img/gallery/imgPanel.png" alt="Dashboard Preview" class="gallery-img-fit w-100 h-100" style="object-fit: cover; border-radius: 8px; border: none; box-shadow: none; max-height: 100%;">
                      </div>
                  </div> 
              </div>
          </div>
      </div>
    </section>

    <section id="contacto" class="contact-section py-5 mt-5 mb-5">
      <div class="text-center mb-5" data-aos="fade-up">
          <h2 class="display-5 fw-bold mb-3 text-white">CONTACTO</h2>
      </div>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-6 col-md-8">
            <div class="p3r-card p-4 p-lg-5" data-aos="fade-up">
              <div class="mb-4">
                <h2 class="h3 fw-bold text-white mb-2">¿Tienes dudas?</h2>
              </div>
              <form action="<?php echo BASE_URL; ?>/index.php?page=procesar_contacto" method="POST" class="custom-form" autocomplete="off">
                <div class="mb-4">
                  <label class="form-label lbl-color font-mono small">NOMBRE COMPLETO</label>
                  <input type="text" class="form-control p3r-input" name="nombre" required />
                </div>
                <div class="mb-4">
                  <label class="form-label lbl-color font-mono small">CORREO</label>
                  <input type="email" class="form-control p3r-input" name="correo" required />
                </div>
                <div class="mb-4">
                  <label class="form-label lbl-color font-mono small">ASUNTO</label>
                  <input type="text" class="form-control p3r-input" name="asunto" required />
                </div>
                <div class="mb-4">
                  <label class="form-label lbl-color font-mono small">MENSAJE</label>
                  <textarea class="form-control p3r-input" name="mensaje" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-cyan w-100 text-uppercase mt-2">
                  Enviar <i class="bi bi-terminal ms-2"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer class="site-footer border-top border-secondary py-4 mt-auto">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
            <p class="mb-0 copyright small font-mono">&copy; Todos los derechos reservados.</p>
          </div>
          <div class="col-md-6 text-center text-md-end">
            <div class="footer-icons d-flex justify-content-center justify-content-md-end gap-4">
              <a href="https://github.com/hectorxdevx/PROYECTO-INTEGRADOR.git" target="_blank" class="footer-link" aria-label="GitHub"><i class="bi bi-github"></i></a>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
      <?php if(isset($_GET['success']) && $_GET['success'] == 'contacto'): ?>
        Swal.fire({
            title: '¡Mensaje Enviado!',
            text: 'Nos pondremos en contacto contigo lo más pronto posible.',
            icon: 'success',
            confirmButtonColor: '#0dcaf0',
            background: '#1d355c',
            color: '#ffffff'
        });
        // Limpiar la URL sin recargar
        window.history.replaceState(null, null, window.location.pathname);
      <?php endif; ?>
    </script>
    <script>
    // ==========================================
    // SIMULADOR DE TELEMETRÍA EN VIVO (Widget)
    // ==========================================
    document.addEventListener('DOMContentLoaded', () => {
        const tempEl = document.getElementById('live-temp');
        const humEl = document.getElementById('live-hum');
        const powerEl = document.getElementById('live-power');

        if (!tempEl || !humEl || !powerEl) return;

        [tempEl, humEl, powerEl].forEach(el => {
            el.style.transition = "color 0.3s ease";
        });

        setInterval(() => {
            const newTemp = (Math.random() * (24.5 - 21.0) + 21.0).toFixed(1);
            const newHum = Math.floor(Math.random() * (48 - 42 + 1)) + 42;
            const newPower = (Math.random() * (1.35 - 1.10) + 1.10).toFixed(2);

            tempEl.innerText = newTemp + '°C';
            humEl.innerText = newHum + '%';
            powerEl.innerText = newPower + ' kW';

            [tempEl, humEl, powerEl].forEach(el => {
                el.style.color = '#10b981'; 
                setTimeout(() => {
                    el.style.color = '#0f172a';
                }, 400);
            });
        }, 3500); 
    });
    </script>
    <script>
      function switchArchTab(targetId, colorTheme) {
          const buttons = document.querySelectorAll('.arch-tab-btn');
          buttons.forEach(btn => btn.classList.remove('active'));

          const clickedBtn = document.querySelector(`[data-target="${targetId}"]`);
          clickedBtn.classList.add('active');

          const panes = document.querySelectorAll('.arch-pane');
          panes.forEach(pane => {
              pane.classList.remove('active-pane');
              pane.classList.remove('p3r-sweep'); 
          });

          const activePane = document.getElementById(targetId);
          activePane.classList.add('active-pane');
          void activePane.offsetWidth; 
          activePane.classList.add('p3r-sweep');

          const container = document.getElementById('arch-content-container');
          container.classList.remove('theme-blue', 'theme-purple', 'theme-red', 'theme-green');
          container.classList.add(`theme-${colorTheme}`);
      }
    </script>
    <script>
      function cambiarImagenGaleria(botonClickeado, nuevoTitulo, rutaImagen) {
        const botones = document.querySelectorAll(".gallery-tab-btn");
        botones.forEach((btn) => btn.classList.remove("active"));
        botonClickeado.classList.add("active");
        document.getElementById("gallery-img").src = rutaImagen;
      }
    </script>

    <!-- Botón Scroll to Top -->
    <button id="scrollToTopBtn" class="scroll-top-btn" onclick="scrollToTop()" title="Volver al inicio" aria-label="Botón para subir al inicio">
      <i class="bi bi-arrow-up"></i>
    </button>

    <style>
      .scroll-top-btn {
        position: fixed;
        bottom: 40px;
        right: 30px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #1d355c;
        color: white;
        border: none;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.2s ease;
        z-index: 1000;
      }
      .scroll-top-btn.show {
        opacity: 1;
        visibility: visible;
      }
      .scroll-top-btn:hover {
        transform: translateY(-5px);
        background-color: #26467a;
      }
    </style>

    <script>
      // Scroll to Top Logic
      const scrollToTopBtn = document.getElementById("scrollToTopBtn");
      
      window.addEventListener("scroll", () => {
        const scrollableArea = document.documentElement.scrollHeight - window.innerHeight;
        // Muestra el botón al bajar más de la mitad de la página
        if (window.scrollY > scrollableArea / 2) {
            scrollToTopBtn.classList.add("show");
        } else {
            scrollToTopBtn.classList.remove("show");
        }
      });

      function scrollToTop() {
        window.scrollTo({
          top: 0,
          behavior: "smooth"
        });
      }
    </script>
    
    <!-- AOS JS y Configuración -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
      AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 100
      });
    </script>

    <!-- Menú de Accesibilidad -->
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

    <style>
      body.high-contrast-mode nav,
      body.high-contrast-mode main,
      body.high-contrast-mode section,
      body.high-contrast-mode footer,
      body.high-contrast-mode .bg-grid {
        filter: contrast(150%) saturate(120%);
      }
      body.high-contrast-mode .p3r-card,
      body.high-contrast-mode .friendly-dashboard-card,
      body.high-contrast-mode .arch-content-container {
        border: 2px solid #fff !important;
      }
      .a11y-widget {
        position: fixed;
        bottom: 120px;
        right: 30px;
        z-index: 1000;
      }
      .a11y-toggle {
        width: 60px !important;
        height: 60px !important;
        min-width: 60px !important;
        padding: 0 !important;
        border-radius: 50% !important;
        background-color: #0d6efd;
        color: white;
        border: none;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        font-size: 28px !important;
        cursor: pointer;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        transition: transform 0.2s;
        line-height: 0;
      }
      .a11y-toggle i { display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important; margin: 0 !important; font-size: 28px !important; }
      .a11y-toggle:hover {
        transform: scale(1.1);
      }
      .a11y-panel {
        position: absolute;
        bottom: 60px;
        right: 0;
        background-color: #1e1e2d;
        border: 1px solid #323246;
        border-radius: 10px;
        padding: 10px;
        width: 200px;
        display: none;
        flex-direction: column;
        gap: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
      }
      .a11y-panel.show {
        display: flex;
      }
      .a11y-header {
        color: white;
        font-weight: bold;
        text-align: center;
        border-bottom: 1px solid #323246;
        padding-bottom: 8px;
        margin-bottom: 5px;
        font-size: 14px;
      }
      .a11y-btn {
        background: transparent;
        color: #adb5bd;
        border: 1px solid transparent;
        border-radius: 5px;
        padding: 8px;
        text-align: left;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
      }
      .a11y-btn:hover {
        background: rgba(255,255,255,0.1);
        color: white;
      }
      .a11y-btn.active-reader {
        background: #198754;
        color: white;
      }
    </style>

    <script>
      // Lógica de Accesibilidad
      const a11yToggle = document.getElementById('a11yToggle');
      const a11yPanel = document.getElementById('a11yPanel');
      const btnIncText = document.getElementById('a11yIncreaseText');
      const btnDecText = document.getElementById('a11yDecreaseText');
      const btnReadText = document.getElementById('a11yReadText');
      const btnStopText = document.getElementById('a11yStopText');
      const btnContrast = document.getElementById('a11yContrast');
      
      let currentFontSize = 100;
      let synth = window.speechSynthesis;
      let utterance = null;

      a11yToggle.addEventListener('click', () => {
        a11yPanel.classList.toggle('show');
      });

      btnIncText.addEventListener('click', () => {
        if(currentFontSize < 150) {
          currentFontSize += 10;
          document.documentElement.style.fontSize = currentFontSize + '%';
        }
      });

      btnDecText.addEventListener('click', () => {
        if(currentFontSize > 80) {
          currentFontSize -= 10;
          document.documentElement.style.fontSize = currentFontSize + '%';
        }
      });

      btnReadText.addEventListener('click', () => {
        if(synth.speaking && !synth.paused) {
          // Si está hablando, lo pausamos
          synth.pause();
          btnReadText.innerHTML = '<i class="bi bi-play-circle"></i> Reanudar Lector';
          btnReadText.classList.remove('active-reader');
        } else if (synth.paused) {
          // Si está pausado, lo reanudamos
          synth.resume();
          btnReadText.innerHTML = '<i class="bi bi-pause-circle"></i> Pausar Lector';
          btnReadText.classList.add('active-reader');
        } else {
          // Si no está hablando, empezamos de cero
          synth.cancel(); // Limpiar colas previas
          
          // Construir el texto personalizado (ignorando el rack y enfocando en info clave)
          let title = document.querySelector('.hero-content h1') ? document.querySelector('.hero-content h1').innerText : '';
          let subtitle = document.querySelector('.hero-content p') ? document.querySelector('.hero-content p').innerText : '';
          
          let featuresSection = document.getElementById('caracteristicas');
          let featuresText = featuresSection ? featuresSection.textContent : '';
          
          // Extraer manualmente Arquitectura para garantizar que lee todas las pestañas (incluso las ocultas)
          let archText = '';
          document.querySelectorAll('.arch-pane').forEach(pane => {
            let pTitle = pane.querySelector('.pane-title') ? pane.querySelector('.pane-title').textContent : '';
            let pText = pane.querySelector('.pane-text') ? pane.querySelector('.pane-text').textContent : '';
            archText += ` Pestaña ${pTitle}: ${pText}. `;
          });
          
          let contactSection = document.getElementById('contacto');
          let contactText = contactSection ? contactSection.innerText : '';
          
          // Crear un string limpio con el texto a leer
          let textoALeer = `Bienvenido a SysMonitor. ${title}. ${subtitle}. Sección Características: ${featuresText}. Sección Arquitectura: ${archText}. Sección Contacto: ${contactText}`;
          
          // Limpieza menor
          textoALeer = textoALeer.replace(/CORE|ENERGY|SECURITY|SCALE/g, ''); 
          
          utterance = new SpeechSynthesisUtterance(textoALeer);
          utterance.lang = 'es-ES';
          utterance.rate = 0.9; // Un poco más lento para mejor dicción
          
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
      
      btnContrast.addEventListener('click', () => {
        document.body.classList.toggle('high-contrast-mode');
      });

      // Ocultar automáticamente todos los íconos decorativos para los lectores de pantalla
      document.querySelectorAll('i').forEach(icon => {
        icon.setAttribute('aria-hidden', 'true');
      });
    </script>
  </body>
</html>
