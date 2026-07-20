<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Usuario | SysMonitor</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    
    <!-- ApexCharts y SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.42.0/dist/apexcharts.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.42.0/dist/apexcharts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app.css">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/app-dark.css">
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>/assets/dashboard/compiled/css/iconly.css">
    
    <!-- Nuestro estilo de colores pastel (para el modo claro) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/dashboard/custom-theme.css?v=3">
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
                        <li class="sidebar-title">Menú Usuario</li>
                        
                        <li class="sidebar-item active">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=user" class='sidebar-link'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Mis Sensores</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=perfil" class='sidebar-link'>
                                <i class="bi bi-person-circle"></i>
                                <span>Mi Perfil</span>
                            </a>
                        </li>

                        <li class="sidebar-item mt-5">
                            <a href="#" class='sidebar-link bg-light-warning'>
                                <i class="bi bi-question-circle-fill text-warning"></i>
                                <span class="text-warning fw-bold">Pedir Ayuda</span>
                            </a>
                        </li>

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
                <h3>Mis Equipos Monitoreados</h3>
            </div>
            
            <style>
                /* Corrección para centrar perfectamente los iconos de Bootstrap dentro de los recuadros de Mazer */
                .stats-icon {
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                }
                .stats-icon i.bi {
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    height: 100% !important;
                    line-height: 1 !important;
                }
                .stats-icon i.bi::before {
                    vertical-align: middle !important;
                    line-height: 1 !important;
                }
                
                /* Efecto Hover para las tarjetas y cursor */
                .sensor-card {
                    cursor: pointer;
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }
                .sensor-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
                    border-color: var(--bs-primary) !important;
                }
                
                /* Estilos del Widget de Accesibilidad */
                .a11y-widget {
                    position: fixed;
                    bottom: 30px;
                    right: 30px;
                    z-index: 1000;
                }
                .a11y-toggle {
                    background-color: #0d6efd;
                    color: white;
                    border: none;
                    border-radius: 50% !important;
                    width: 60px !important;
                    height: 60px !important;
                    min-width: 60px !important;
                    padding: 0 !important;
                    font-size: 28px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    cursor: pointer;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                    transition: transform 0.3s ease, background-color 0.3s ease;
                    line-height: 0;
                }
                .a11y-toggle i { display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1 !important; margin: 0 !important; font-size: 28px !important; }
                .a11y-toggle:hover {
                    transform: scale(1.1);
                    background-color: #0b5ed7;
                }
                .a11y-panel {
                    position: absolute;
                    bottom: 60px;
                    right: 0;
                    background-color: white;
                    border-radius: 12px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                    width: 250px;
                    padding: 15px;
                    display: none;
                    flex-direction: column;
                    gap: 10px;
                    border: 1px solid rgba(0,0,0,0.1);
                }
                body.theme-dark .a11y-panel {
                    background-color: #1e1e2d;
                    border-color: #2b2b40;
                }
                .a11y-panel.show {
                    display: flex;
                    animation: popIn 0.3s ease forwards;
                }
                .a11y-header {
                    font-weight: bold;
                    margin-bottom: 10px;
                    color: var(--bs-heading-color);
                    border-bottom: 1px solid var(--bs-border-color);
                    padding-bottom: 5px;
                }
                .a11y-btn {
                    background: transparent;
                    border: 1px solid var(--bs-border-color);
                    border-radius: 8px;
                    padding: 8px 12px;
                    text-align: left;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    color: var(--bs-body-color);
                    transition: background 0.2s;
                }
                .a11y-btn:hover {
                    background-color: var(--bs-secondary-bg);
                }
                .a11y-btn.active-reader {
                    background-color: #198754;
                    color: white;
                    border-color: #198754;
                }
                @keyframes popIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                body.high-contrast-mode .page-content,
                body.high-contrast-mode .sidebar-wrapper,
                body.high-contrast-mode header {
                    filter: contrast(150%) saturate(120%);
                }
                body.high-contrast-mode .card {
                    border: 2px solid #fff !important;
                }
            </style>

            <div class="page-content">
                <!-- Gráfica de 24 horas -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h4 class="mb-0">Historial 24h - Temperatura Promedio General</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-temperature"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="row">
                    <div class="col-12">
                        <div class="accordion" id="racksAccordion">
                        
                            <!-- RACK 01 -->
                            <div class="accordion-item card mb-3 border-0 shadow-sm">
                                <h2 class="accordion-header" id="headingRack1">
                                    <button class="accordion-button bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRack1" aria-expanded="true" aria-controls="collapseRack1">
                                        <h5 class="mb-0 text-subtitle text-muted">Rack 01 - Servidores Principales</h5>
                                    </button>
                                </h2>
                                <div id="collapseRack1" class="accordion-collapse collapse show" aria-labelledby="headingRack1" data-bs-parent="#racksAccordion">
                                    <div class="accordion-body">
                                        <div class="row pt-2">
                                            <!-- Temp -->
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Temperatura', '22.4 °C', 'Rack 01', '°C')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon blue mb-3"><i class="bi bi-thermometer-half"></i></div>
                                                        <h6 class="text-muted font-semibold">Temperatura</h6>
                                                        <h6 class="font-extrabold mb-0">22.4 °C</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Humedad -->
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Humedad', '45%', 'Rack 01', '%')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon purple mb-3"><i class="bi bi-droplet-half"></i></div>
                                                        <h6 class="text-muted font-semibold">Humedad</h6>
                                                        <h6 class="font-extrabold mb-0">45%</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Consumo -->
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Consumo', '1.2 kW', 'Rack 01', 'kW')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon text-white mb-3" style="background-color: #f59e0b;"><i class="bi bi-lightning-charge"></i></div>
                                                        <h6 class="text-muted font-semibold">Consumo</h6>
                                                        <h6 class="font-extrabold mb-0">1.2 kW</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Puerta -->
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Estado de Puerta', 'Cerrada', 'Rack 01', '')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon red mb-3"><i class="bi bi-door-open"></i></div>
                                                        <h6 class="text-muted font-semibold">Puerta</h6>
                                                        <h6 class="font-extrabold mb-0 text-success">Cerrada</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- RACK 02 -->
                            <div class="accordion-item card mb-3 border-0 shadow-sm">
                                <h2 class="accordion-header" id="headingRack2">
                                    <button class="accordion-button collapsed bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRack2" aria-expanded="false" aria-controls="collapseRack2">
                                        <h5 class="mb-0 text-subtitle text-muted">Rack 02 - Almacenamiento</h5>
                                    </button>
                                </h2>
                                <div id="collapseRack2" class="accordion-collapse collapse" aria-labelledby="headingRack2" data-bs-parent="#racksAccordion">
                                    <div class="accordion-body">
                                        <div class="row pt-2">
                                            <!-- Temp -->
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Temperatura', '24.1 °C', 'Rack 02', '°C')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon blue mb-3"><i class="bi bi-thermometer-half"></i></div>
                                                        <h6 class="text-muted font-semibold">Temperatura</h6>
                                                        <h6 class="font-extrabold mb-0">24.1 °C</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Humedad -->
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Humedad', '42%', 'Rack 02', '%')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon purple mb-3"><i class="bi bi-droplet-half"></i></div>
                                                        <h6 class="text-muted font-semibold">Humedad</h6>
                                                        <h6 class="font-extrabold mb-0">42%</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Consumo -->
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Consumo', '0.8 kW', 'Rack 02', 'kW')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon text-white mb-3" style="background-color: #f59e0b;"><i class="bi bi-lightning-charge"></i></div>
                                                        <h6 class="text-muted font-semibold">Consumo</h6>
                                                        <h6 class="font-extrabold mb-0">0.8 kW</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Puerta -->
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Estado de Puerta', 'Cerrada', 'Rack 02', '')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon red mb-3"><i class="bi bi-door-open"></i></div>
                                                        <h6 class="text-muted font-semibold">Puerta</h6>
                                                        <h6 class="font-extrabold mb-0 text-success">Cerrada</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- RACK 03 -->
                            <div class="accordion-item card mb-3 border-0 shadow-sm">
                                <h2 class="accordion-header" id="headingRack3">
                                    <button class="accordion-button collapsed bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRack3" aria-expanded="false" aria-controls="collapseRack3">
                                        <h5 class="mb-0 text-subtitle text-muted">Rack 03 - Redes y Switches</h5>
                                    </button>
                                </h2>
                                <div id="collapseRack3" class="accordion-collapse collapse" aria-labelledby="headingRack3" data-bs-parent="#racksAccordion">
                                    <div class="accordion-body">
                                        <div class="row pt-2">
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Temperatura', '28.5 °C', 'Rack 03', '°C')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon blue mb-3"><i class="bi bi-thermometer-half"></i></div>
                                                        <h6 class="text-muted font-semibold">Temperatura</h6>
                                                        <h6 class="font-extrabold mb-0 text-warning">28.5 °C</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Humedad', '48%', 'Rack 03', '%')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon purple mb-3"><i class="bi bi-droplet-half"></i></div>
                                                        <h6 class="text-muted font-semibold">Humedad</h6>
                                                        <h6 class="font-extrabold mb-0">48%</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Consumo', '0.5 kW', 'Rack 03', 'kW')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon text-white mb-3" style="background-color: #f59e0b;"><i class="bi bi-lightning-charge"></i></div>
                                                        <h6 class="text-muted font-semibold">Consumo</h6>
                                                        <h6 class="font-extrabold mb-0">0.5 kW</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Estado de Puerta', 'Cerrada', 'Rack 03', '')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon red mb-3"><i class="bi bi-door-open"></i></div>
                                                        <h6 class="text-muted font-semibold">Puerta</h6>
                                                        <h6 class="font-extrabold mb-0 text-success">Cerrada</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- RACK 04 -->
                            <div class="accordion-item card mb-3 border-0 shadow-sm">
                                <h2 class="accordion-header" id="headingRack4">
                                    <button class="accordion-button collapsed bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRack4" aria-expanded="false" aria-controls="collapseRack4">
                                        <h5 class="mb-0 text-subtitle text-muted">Rack 04 - Respaldo</h5>
                                    </button>
                                </h2>
                                <div id="collapseRack4" class="accordion-collapse collapse" aria-labelledby="headingRack4" data-bs-parent="#racksAccordion">
                                    <div class="accordion-body">
                                        <div class="row pt-2">
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Temperatura', '21.0 °C', 'Rack 04', '°C')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon blue mb-3"><i class="bi bi-thermometer-half"></i></div>
                                                        <h6 class="text-muted font-semibold">Temperatura</h6>
                                                        <h6 class="font-extrabold mb-0">21.0 °C</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Humedad', '40%', 'Rack 04', '%')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon purple mb-3"><i class="bi bi-droplet-half"></i></div>
                                                        <h6 class="text-muted font-semibold">Humedad</h6>
                                                        <h6 class="font-extrabold mb-0">40%</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Consumo', '0.9 kW', 'Rack 04', 'kW')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon text-white mb-3" style="background-color: #f59e0b;"><i class="bi bi-lightning-charge"></i></div>
                                                        <h6 class="text-muted font-semibold">Consumo</h6>
                                                        <h6 class="font-extrabold mb-0">0.9 kW</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-3 col-md-6">
                                                <div class="card sensor-card border border-light" onclick="openSensorModal('Estado de Puerta', 'Abierta', 'Rack 04', '')">
                                                    <div class="card-body px-4 py-4-5 d-flex flex-column align-items-center text-center">
                                                        <div class="stats-icon red mb-3"><i class="bi bi-door-open"></i></div>
                                                        <h6 class="text-muted font-semibold">Puerta</h6>
                                                        <h6 class="font-extrabold mb-0 text-danger">Abierta</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
    
    <!-- Modal de Resumen del Día -->
    <div class="modal fade" id="sensorModal" tabindex="-1" aria-labelledby="sensorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="sensorModalLabel">Resumen del Día</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <h6 id="modalRackName" class="text-muted mb-3"></h6>
                    <h1 id="modalCurrentValue" class="display-4 font-bold text-primary mb-4"></h1>
                    
                    <div class="row mt-4">
                        <div class="col-4">
                            <p class="text-muted mb-1 text-sm font-semibold">Mínimo</p>
                            <h5 id="modalMin" class="text-info font-extrabold"></h5>
                        </div>
                        <div class="col-4 border-start border-end">
                            <p class="text-muted mb-1 text-sm font-semibold">Promedio</p>
                            <h5 id="modalAvg" class="text-primary font-extrabold"></h5>
                        </div>
                        <div class="col-4">
                            <p class="text-muted mb-1 text-sm font-semibold">Máximo</p>
                            <h5 id="modalMax" class="text-danger font-extrabold"></h5>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
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
    
    <script>
        // --- 1. Gráfica ApexCharts ---
        var options = {
            series: [{
                name: 'Temperatura',
                data: [21.5, 21.8, 22.0, 22.4, 23.1, 24.5, 23.8, 22.9, 22.4]
            }],
            chart: {
                height: 250,
                type: 'area',
                toolbar: { show: false },
                fontFamily: 'Nunito, sans-serif'
            },
            colors: ['#435ebe'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: ['00:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00', '24:00'],
            },
            tooltip: {
                y: { formatter: function (val) { return val + " °C" } }
            }
        };
        var chart = new ApexCharts(document.querySelector("#chart-temperature"), options);
        chart.render();

        // --- 2. Lógica del Modal "Resumen del Día" ---
        function openSensorModal(sensorType, currentValue, rackName, unit) {
            document.getElementById('sensorModalLabel').innerText = `Resumen del Día - ${sensorType}`;
            document.getElementById('modalRackName').innerText = rackName;
            
            if(sensorType === 'Estado de Puerta') {
                document.getElementById('modalCurrentValue').innerText = currentValue;
                document.getElementById('modalMax').innerText = "-";
                document.getElementById('modalAvg').innerText = "Cerrada 99%";
                document.getElementById('modalMin').innerText = "-";
            } else {
                let val = parseFloat(currentValue);
                document.getElementById('modalCurrentValue').innerText = currentValue;
                // Generar valores realistas basados en el valor actual para el demo
                document.getElementById('modalMax').innerText = (val + (val * 0.15)).toFixed(1) + " " + unit;
                document.getElementById('modalAvg').innerText = (val).toFixed(1) + " " + unit;
                document.getElementById('modalMin').innerText = (val - (val * 0.12)).toFixed(1) + " " + unit;
            }
            
            var myModal = new bootstrap.Modal(document.getElementById('sensorModal'));
            myModal.show();
        }

        // --- 3. Accesibilidad A11y ---
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
                let textoALeer = `Estás en el panel de control. Tienes 4 Racks configurados. Haz clic en un rack para ver sus sensores.`;
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
