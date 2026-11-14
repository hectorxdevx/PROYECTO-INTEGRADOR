<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consola de Monitoreo IoT | SysMonitor</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.42.0/dist/apexcharts.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.42.0/dist/apexcharts.min.js"></script>
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
                        <li class="sidebar-title">
                            <?php echo $esJefeTi ? 'Consola Jefe de TI' : 'Consola Operador IoT'; ?>
                        </li>

                        <li class="sidebar-item active">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=user" class='sidebar-link'>
                                <i class="bi bi-grid-1x2-fill"></i>
                                <span>Racks y Telemetría</span>
                            </a>
                        </li>

                        <?php if ($esJefeTi): ?>
                        <li class="sidebar-item">
                            <a href="#seccion-gestion-ti" class='sidebar-link'>
                                <i class="bi bi-sliders"></i>
                                <span>Umbrales y Equipo TI</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=perfil" class='sidebar-link'>
                                <i class="bi bi-person-badge-fill"></i>
                                <span>Mi Perfil & MFA</span>
                            </a>
                        </li>

                        <?php if ((int)($_SESSION['rol'] ?? 0) === 2): ?>
                        <li class="sidebar-item">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=admin" class='sidebar-link'>
                                <i class="bi bi-shield-lock-fill"></i>
                                <span>Volver a SuperAdmin</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <li class="sidebar-title">Sesión</li>
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
                <a href="#" class="burger-btn d-block d-xl-none"><i class="bi bi-justify fs-3"></i></a>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <span class="ac-badge ac-badge-blue d-none d-sm-inline-flex">
                        <i class="bi bi-building"></i>
                        <?php echo htmlspecialchars($empresaActual['nombre_empresa'] ?? 'InfraOps Cloud & DataCenter'); ?>
                    </span>
                    <button type="button" class="ac-theme-btn">
                        <i class="bi bi-moon-stars-fill"></i> <span>Modo Oscuro</span>
                    </button>
                </div>
            </header>

            <!-- Encabezado Minimalist Blue -->
            <div class="ac-bento-card ac-beam-top p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="ac-squircle-logo">
                            <i class="bi bi-hdd-network"></i>
                        </div>
                        <div>
                            <h3 class="fw-extrabold mb-1">
                                Hola, <?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Alex'); ?>
                            </h3>
                            <p class="small mb-0" style="color: var(--ac-text-muted);">
                                <?php echo count($racks); ?> racks monitoreados en tiempo real
                            </p>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-primary rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalSimularLectura">
                            <i class="bi bi-broadcast me-1"></i> Simular Lectura
                        </button>
                        <?php if ($esJefeTi): ?>
                            <button class="ac-btn-primary ac-btn-pill py-2 px-4" data-bs-toggle="modal" data-bs-target="#modalNuevoRack">
                                <i class="bi bi-plus-circle"></i> Nuevo Rack
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="page-content">
                <!-- Resumen Bento Grid (Estado Global, Temp Promedio, Consumo Total, Alertas) -->
                <?php
                    $sumaTemp = 0; $sumaKw = 0; $puertasAbiertas = 0;
                    foreach ($racks as $r) {
                        $sumaTemp += (float)($r['lectura']['temperatura'] ?? 22);
                        $sumaKw += (float)($r['lectura']['consumo_kw'] ?? 1.0);
                        if (!empty($r['lectura']['puerta_abierta'])) $puertasAbiertas++;
                    }
                    $tempProm = count($racks) > 0 ? round($sumaTemp / count($racks), 1) : 22.4;
                ?>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="ac-bento-card p-3 p-md-4 h-100">
                            <span class="ac-label">ESTADO GLOBAL</span>
                            <h3 class="fw-extrabold mb-1 <?php echo $puertasAbiertas > 0 ? 'text-danger' : 'text-primary'; ?>">
                                <?php echo $puertasAbiertas > 0 ? 'Alerta Física' : 'Óptimo'; ?>
                            </h3>
                            <span class="small" style="color: var(--ac-text-muted);">99.99% Uptime de sensores</span>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="ac-bento-card p-3 p-md-4 h-100">
                            <span class="ac-label">TEMP. PROMEDIO SALA</span>
                            <h3 class="fw-extrabold mb-1"><?php echo $tempProm; ?> °C</h3>
                            <span class="small text-success fw-semibold"><i class="bi bi-check-circle"></i> Rango ASHRAE</span>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="ac-bento-card p-3 p-md-4 h-100">
                            <span class="ac-label">CARGA ELÉCTRICA TOTAL</span>
                            <h3 class="fw-extrabold mb-1"><?php echo round($sumaKw, 2); ?> kW</h3>
                            <span class="small" style="color: var(--ac-text-muted);">Medición PDU en vivo</span>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="ac-bento-card p-3 p-md-4 h-100">
                            <span class="ac-label">PUERTAS DE RACKS</span>
                            <h3 class="fw-extrabold mb-1 <?php echo $puertasAbiertas > 0 ? 'text-danger' : 'text-success'; ?>">
                                <?php echo $puertasAbiertas > 0 ? $puertasAbiertas . ' Abierta(s)' : '100% Cerradas'; ?>
                            </h3>
                            <span class="small" style="color: var(--ac-text-muted);">Sensores magnéticos IoT</span>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Rendimiento Térmico 24h + Panel de Alertas Activas -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-lg-8">
                        <div class="ac-bento-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0">Curva Térmica en Tiempo Real (°C)</h5>
                                    <p class="small mb-0" style="color: var(--ac-text-muted);">Monitoreo continuo de los nodos IoT de la institución</p>
                                </div>
                                <span class="ac-badge ac-badge-blue">Últimas 24h</span>
                            </div>
                            <div id="chart-temperature"></div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="ac-bento-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Eventos y Alertas</h5>
                                <span class="ac-badge ac-badge-danger"><?php echo count($alertas); ?> recientes</span>
                            </div>
                            <div class="d-flex flex-column gap-2" style="max-height: 270px; overflow-y: auto;">
                                <?php if (!empty($alertas)): ?>
                                    <?php foreach ($alertas as $al): ?>
                                        <div class="p-3 rounded-4 d-flex justify-content-between align-items-start gap-2" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);">
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="ac-badge <?php echo $al['nivel'] === 'critical' ? 'ac-badge-danger' : 'ac-badge-warning'; ?>">
                                                        <?php echo strtoupper(htmlspecialchars($al['codigo_rack'])); ?>
                                                    </span>
                                                    <strong class="small"><?php echo htmlspecialchars($al['tipo_alerta']); ?></strong>
                                                </div>
                                                <p class="small mb-1" style="color: var(--ac-text-muted); font-size: 0.78rem;">
                                                    <?php echo htmlspecialchars($al['mensaje']); ?>
                                                </p>
                                            </div>
                                            <?php if ((int)$al['resuelta'] === 0): ?>
                                                <a href="<?php echo BASE_URL; ?>/index.php?page=user_resolver_alerta&id=<?php echo (int)$al['id_alerta']; ?>" class="btn btn-sm btn-outline-success rounded-pill" title="Marcar como atendida">
                                                    <i class="bi bi-check2"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="small text-muted text-center my-auto">Todos los racks operan dentro de los parámetros normales.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid Bento de Racks Físicos y sus 4 Sensores IoT -->
                <h5 class="fw-bold mb-3"><i class="bi bi-hdd-rack text-primary me-2"></i>Estado Físico por Rack</h5>
                <div class="row g-4 mb-4">
                    <?php foreach ($racks as $rack): ?>
                        <?php
                            $lec = $rack['lectura'];
                            $resDia = $rack['resumen_dia'];
                            $tempActual = (float)$lec['temperatura'];
                            $humActual = (float)$lec['humedad'];
                            $kwActual = (float)$lec['consumo_kw'];
                            $puertaAbierta = (int)$lec['puerta_abierta'] === 1;
                            $alertaTemp = $tempActual > (float)$rack['temp_max'];
                        ?>
                        <div class="col-12 col-xl-6">
                            <div class="ac-bento-card ac-beam-top p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="ac-badge ac-badge-blue"><?php echo htmlspecialchars($rack['codigo_rack']); ?></span>
                                            <span class="small" style="color: var(--ac-text-muted);"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($rack['ubicacion']); ?></span>
                                        </div>
                                        <h5 class="fw-bold mt-1 mb-0"><?php echo htmlspecialchars($rack['nombre_rack']); ?></h5>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($puertaAbierta || $rack['estado'] === 'critical'): ?>
                                            <span class="ac-badge ac-badge-danger"><i class="bi bi-exclamation-octagon"></i> Crítico</span>
                                        <?php elseif ($alertaTemp || $rack['estado'] === 'warning'): ?>
                                            <span class="ac-badge ac-badge-warning"><i class="bi bi-exclamation-triangle"></i> Advertencia</span>
                                        <?php else: ?>
                                            <span class="ac-badge ac-badge-success"><span class="pulsing-dot"></span> Óptimo</span>
                                        <?php endif; ?>

                                        <?php if ($esJefeTi): ?>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#modalUmbralesRack<?php echo (int)$rack['id_rack']; ?>" title="Configurar Umbrales (Jefe de TI)">
                                                <i class="bi bi-sliders"></i> Umbrales
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- 4 Tarjetas de Sensores del Rack -->
                                <div class="row g-2">
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 rounded-4 text-center h-100 cursor-pointer" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);"
                                             onclick="openSensorModal('Temperatura', '<?php echo $tempActual; ?> °C', '<?php echo htmlspecialchars($rack['nombre_rack']); ?>', '<?php echo $resDia['temp_min'] ?? 21.0; ?> °C', '<?php echo $resDia['temp_avg'] ?? 22.4; ?> °C', '<?php echo $resDia['temp_max_dia'] ?? 24.5; ?> °C')">
                                            <i class="bi bi-thermometer-half text-primary fs-4"></i>
                                            <div class="ac-label mt-1 mb-0" style="font-size: 0.65rem;">TEMPERATURA</div>
                                            <div class="fw-extrabold fs-6 <?php echo $alertaTemp ? 'text-danger' : ''; ?>"><?php echo $tempActual; ?> °C</div>
                                            <div class="small text-muted" style="font-size: 0.68rem;">Máx: <?php echo $rack['temp_max']; ?>°C</div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 rounded-4 text-center h-100 cursor-pointer" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);"
                                             onclick="openSensorModal('Humedad Relativa', '<?php echo $humActual; ?> %', '<?php echo htmlspecialchars($rack['nombre_rack']); ?>', '<?php echo $resDia['hum_min'] ?? 40; ?> %', '<?php echo $resDia['hum_avg'] ?? 45; ?> %', '<?php echo $resDia['hum_max_dia'] ?? 50; ?> %')">
                                            <i class="bi bi-droplet-half text-info fs-4"></i>
                                            <div class="ac-label mt-1 mb-0" style="font-size: 0.65rem;">HUMEDAD</div>
                                            <div class="fw-extrabold fs-6"><?php echo $humActual; ?> %</div>
                                            <div class="small text-muted" style="font-size: 0.68rem;">Máx: <?php echo $rack['hum_max']; ?>%</div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 rounded-4 text-center h-100 cursor-pointer" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);"
                                             onclick="openSensorModal('Consumo Eléctrico', '<?php echo $kwActual; ?> kW', '<?php echo htmlspecialchars($rack['nombre_rack']); ?>', '<?php echo $resDia['kw_min'] ?? 0.8; ?> kW', '<?php echo $resDia['kw_avg'] ?? 1.1; ?> kW', '<?php echo $resDia['kw_max_dia'] ?? 1.4; ?> kW')">
                                            <i class="bi bi-lightning-charge text-warning fs-4"></i>
                                            <div class="ac-label mt-1 mb-0" style="font-size: 0.65rem;">CONSUMO</div>
                                            <div class="fw-extrabold fs-6"><?php echo $kwActual; ?> kW</div>
                                            <div class="small text-muted" style="font-size: 0.68rem;">Máx: <?php echo $rack['kw_max']; ?> kW</div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 rounded-4 text-center h-100 cursor-pointer" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);"
                                             onclick="openSensorModal('Puerta Física', '<?php echo $puertaAbierta ? 'Abierta' : 'Cerrada'; ?>', '<?php echo htmlspecialchars($rack['nombre_rack']); ?>', 'Cerrada', '99.4% Cerrada', '<?php echo $puertaAbierta ? 'Alerta Activa' : 'Sin incidentes'; ?>')">
                                            <i class="bi <?php echo $puertaAbierta ? 'bi-door-open-fill text-danger' : 'bi-door-closed-fill text-success'; ?> fs-4"></i>
                                            <div class="ac-label mt-1 mb-0" style="font-size: 0.65rem;">PUERTA RACK</div>
                                            <div class="fw-extrabold fs-6 <?php echo $puertaAbierta ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo $puertaAbierta ? 'Abierta' : 'Cerrada'; ?>
                                            </div>
                                            <div class="small text-muted" style="font-size: 0.68rem;">Sensor Magnético</div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($esJefeTi): ?>
                                    <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center small" style="border-color: var(--ac-border) !important; color: var(--ac-text-muted);">
                                        <span>API Key IoT (Postman / ESP32):</span>
                                        <code class="text-primary"><?php echo htmlspecialchars($rack['api_key_iot']); ?></code>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($esJefeTi): ?>
                        <!-- Modal Configurar Umbrales del Rack (Exclusivo Jefe de TI) -->
                        <div class="modal fade" id="modalUmbralesRack<?php echo (int)$rack['id_rack']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content ac-bento-card border-0">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title fw-bold">Configurar Umbrales • <?php echo htmlspecialchars($rack['codigo_rack']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?php echo BASE_URL; ?>/index.php?page=user_actualizar_rack" method="POST">
                                        <input type="hidden" name="id_rack" value="<?php echo (int)$rack['id_rack']; ?>">
                                        <input type="hidden" name="id_empresa" value="<?php echo (int)$rack['id_empresa']; ?>">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="ac-label">Nombre del Rack</label>
                                                <input type="text" name="nombre_rack" class="form-control rounded-3" value="<?php echo htmlspecialchars($rack['nombre_rack']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="ac-label">Ubicación Física</label>
                                                <input type="text" name="ubicacion" class="form-control rounded-3" value="<?php echo htmlspecialchars($rack['ubicacion']); ?>" required>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <label class="ac-label">Temp. Máx (°C)</label>
                                                    <input type="number" step="0.1" name="temp_max" class="form-control rounded-3" value="<?php echo htmlspecialchars($rack['temp_max']); ?>" required>
                                                </div>
                                                <div class="col-4">
                                                    <label class="ac-label">Humedad Máx (%)</label>
                                                    <input type="number" step="0.1" name="hum_max" class="form-control rounded-3" value="<?php echo htmlspecialchars($rack['hum_max']); ?>" required>
                                                </div>
                                                <div class="col-4">
                                                    <label class="ac-label">Consumo Máx (kW)</label>
                                                    <input type="number" step="0.05" name="kw_max" class="form-control rounded-3" value="<?php echo htmlspecialchars($rack['kw_max']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="ac-btn-primary py-2 px-4">Guardar Umbrales</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- SECCIÓN EXCLUSIVA PARA EL JEFE DE TI: Equipo de Operadores y Bitácora Institucional -->
                <?php if ($esJefeTi): ?>
                <div id="seccion-gestion-ti" class="row g-4 mt-2">
                    <div class="col-12 col-lg-6">
                        <div class="ac-bento-card p-4 h-100">
                            <h5 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Personal de TI de tu Institución</h5>
                            <p class="small mb-3" style="color: var(--ac-text-muted);">Operadores y responsables con correo institucional activo.</p>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr class="small text-uppercase" style="color: var(--ac-text-muted);">
                                            <th>Nombre</th>
                                            <th>Correo Institucional</th>
                                            <th>Rol</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($equipoEmpresa as $miembro): ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($miembro['nombre_usuario'] . ' ' . $miembro['apellidos']); ?></td>
                                                <td><code class="text-primary"><?php echo htmlspecialchars($miembro['correo_electronico']); ?></code></td>
                                                <td>
                                                    <span class="ac-badge <?php echo (int)$miembro['id_rol'] === 4 ? 'ac-badge-blue' : 'ac-badge-success'; ?>">
                                                        <?php echo htmlspecialchars($miembro['nombre_rol'] ?? 'operador'); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="ac-bento-card p-4 h-100">
                            <h5 class="fw-bold mb-1"><i class="bi bi-journal-check text-primary me-2"></i>Bitácora de Auditoría de la Empresa</h5>
                            <p class="small mb-3" style="color: var(--ac-text-muted);">Registro de accesos MFA, cambios de umbrales y eventos de racks.</p>
                            <div class="d-flex flex-column gap-2" style="max-height: 240px; overflow-y: auto;">
                                <?php if (!empty($bitacoraEmpresa)): ?>
                                    <?php foreach ($bitacoraEmpresa as $b): ?>
                                        <div class="p-2 px-3 rounded-3 d-flex justify-content-between align-items-center" style="background: var(--ac-bg-secondary); border: 1px solid var(--ac-border);">
                                            <div>
                                                <span class="ac-badge ac-badge-blue me-2"><?php echo htmlspecialchars($b['tipo_evento']); ?></span>
                                                <span class="small"><?php echo htmlspecialchars($b['descripcion']); ?></span>
                                            </div>
                                            <span class="small text-muted"><?php echo date('d M H:i', strtotime($b['fecha_evento'])); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="small text-muted">No hay eventos recientes en la bitácora.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Resumen Estadístico del Sensor -->
    <div class="modal fade" id="sensorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ac-bento-card border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-primary" id="sensorModalLabel">Resumen Estadístico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <h6 id="modalRackName" class="text-muted mb-2"></h6>
                    <h1 id="modalCurrentValue" class="display-4 fw-extrabold text-primary mb-4"></h1>
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="p-3 rounded-4" style="background: var(--ac-bg-secondary);">
                                <div class="small text-muted">Mínimo</div>
                                <div id="modalMin" class="fw-bold text-info"></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-4" style="background: var(--ac-bg-secondary);">
                                <div class="small text-muted">Promedio</div>
                                <div id="modalAvg" class="fw-bold text-primary"></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-4" style="background: var(--ac-bg-secondary);">
                                <div class="small text-muted">Máximo</div>
                                <div id="modalMax" class="fw-bold text-danger"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Inyectar Telemetría Demo -->
    <div class="modal fade" id="modalSimularLectura" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ac-bento-card border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-broadcast text-primary me-2"></i>Enviar Lectura IoT en Vivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo BASE_URL; ?>/index.php?page=user_simular_lectura" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="ac-label">Seleccionar Rack</label>
                            <select name="id_rack" class="form-select rounded-3">
                                <?php foreach ($racks as $r): ?>
                                    <option value="<?php echo (int)$r['id_rack']; ?>"><?php echo htmlspecialchars($r['codigo_rack'] . ' - ' . $r['nombre_rack']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="ac-label">Temp (°C)</label>
                                <input type="number" step="0.1" name="temperatura" class="form-control rounded-3" value="28.6" required>
                            </div>
                            <div class="col-4">
                                <label class="ac-label">Humedad (%)</label>
                                <input type="number" step="0.1" name="humedad" class="form-control rounded-3" value="46.0" required>
                            </div>
                            <div class="col-4">
                                <label class="ac-label">Consumo (kW)</label>
                                <input type="number" step="0.05" name="consumo_kw" class="form-control rounded-3" value="1.35" required>
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="puerta_abierta" value="1" id="chkPuerta">
                            <label class="form-check-label small fw-semibold" for="chkPuerta">Simular apertura física de puerta de Rack (Dispara alerta crítica)</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="ac-btn-primary py-2 px-4">Registrar Telemetría</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($esJefeTi): ?>
    <!-- Modal Crear Nuevo Rack (Exclusivo Jefe de TI) -->
    <div class="modal fade" id="modalNuevoRack" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ac-bento-card border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Dar de Alta Nuevo Rack IoT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo BASE_URL; ?>/index.php?page=user_crear_rack" method="POST">
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="ac-label">Código</label>
                                <input type="text" name="codigo_rack" class="form-control rounded-3" placeholder="RACK-05" required>
                            </div>
                            <div class="col-8">
                                <label class="ac-label">Nombre Descriptivo</label>
                                <input type="text" name="nombre_rack" class="form-control rounded-3" placeholder="Rack 05 - Nodos IA" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="ac-label">Ubicación en el Site</label>
                            <input type="text" name="ubicacion" class="form-control rounded-3" value="Site Principal - Pasillo B" required>
                        </div>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="ac-label">Temp Máx (°C)</label>
                                <input type="number" step="0.1" name="temp_max" class="form-control rounded-3" value="27.0" required>
                            </div>
                            <div class="col-4">
                                <label class="ac-label">Hum Máx (%)</label>
                                <input type="number" step="0.1" name="hum_max" class="form-control rounded-3" value="60.0" required>
                            </div>
                            <div class="col-4">
                                <label class="ac-label">kW Máx</label>
                                <input type="number" step="0.1" name="kw_max" class="form-control rounded-3" value="2.5" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="ac-btn-primary py-2 px-4">Crear Rack + Generar API Key</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="<?php echo BASE_URL; ?>/assets/dashboard/static/js/components/dark.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/dashboard/compiled/js/app.js"></script>
    <script>
        <?php
            $labelsChart = [];
            $valuesChart = [];
            foreach ($historial24h as $h) {
                $labelsChart[] = date('H:i', strtotime($h['fecha_lectura']));
                $valuesChart[] = (float)$h['temperatura'];
            }
            if (empty($valuesChart)) {
                $labelsChart = ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', 'Ahora'];
                $valuesChart = [21.8, 22.1, 22.5, 23.4, 24.1, 22.9, 22.4];
            }
        ?>
        var options = {
            series: [{ name: 'Temperatura (°C)', data: <?php echo json_encode($valuesChart); ?> }],
            chart: { height: 260, type: 'area', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
            colors: ['#1d4ed8'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02 } },
            xaxis: { categories: <?php echo json_encode($labelsChart); ?> }
        };
        new ApexCharts(document.querySelector("#chart-temperature"), options).render();

        function openSensorModal(sensorType, currentValue, rackName, minVal, avgVal, maxVal) {
            document.getElementById('sensorModalLabel').innerText = sensorType;
            document.getElementById('modalRackName').innerText = rackName;
            document.getElementById('modalCurrentValue').innerText = currentValue;
            document.getElementById('modalMin').innerText = minVal;
            document.getElementById('modalAvg').innerText = avgVal;
            document.getElementById('modalMax').innerText = maxVal;
            new bootstrap.Modal(document.getElementById('sensorModal')).show();
        }
    </script>
</body>
</html>
