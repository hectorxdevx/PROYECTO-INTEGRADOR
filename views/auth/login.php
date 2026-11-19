<!doctype html>
<html lang="es" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Autenticación de Seguridad Extrema | SysMonitor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/css/aceternity-ui.css" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    <script src="<?php echo BASE_URL; ?>/assets/js/aceternity-ui.js"></script>
    <style>
      .auth-shell {
        max-width: 640px;
        width: 100%;
        margin: 0 auto;
      }
      @media (min-width: 768px) {
        .auth-card-body {
          padding: 2.75rem 2.5rem !important;
        }
      }
      .plan-radio-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
      }
      .plan-radio-label {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 12px;
        border-radius: 16px;
        border: 1.5px solid var(--ac-border);
        background: var(--ac-surface-solid);
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
      }
      .plan-radio-label:hover {
        border-color: var(--ac-border-hover);
        transform: translateY(-2px);
      }
      .plan-radio-input:checked + .plan-radio-label {
        border-color: var(--ac-primary);
        background: var(--ac-primary-light);
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.14);
      }
      .reg-section-title {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--ac-primary);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
      }
      .tab-pill-group {
        display: inline-flex;
        background: var(--ac-input-bg);
        padding: 5px;
        border-radius: 16px;
        border: 1px solid var(--ac-border);
      }
      .tab-pill-btn {
        border: none;
        background: transparent;
        color: var(--ac-text-muted);
        font-weight: 600;
        font-size: 0.9rem;
        padding: 9px 20px;
        border-radius: 12px;
        transition: all 0.2s ease;
      }
      .tab-pill-btn.active {
        background: var(--ac-primary);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25);
      }
      .divider-line {
        display: flex;
        align-items: center;
        text-align: center;
        color: var(--ac-text-muted);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        margin: 22px 0;
      }
      .divider-line::before,
      .divider-line::after {
        content: "";
        flex: 1;
        border-bottom: 1px solid var(--ac-border);
      }
      .divider-line:not(:empty)::before { margin-right: 12px; }
      .divider-line:not(:empty)::after { margin-left: 12px; }
      .demo-credential-btn {
        font-size: 0.78rem;
        padding: 6px 12px;
        border-radius: 10px;
        border: 1px dashed var(--ac-border-hover);
        background: var(--ac-primary-light);
        color: var(--ac-primary);
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s ease;
      }
      .demo-credential-btn:hover {
        background: var(--ac-primary);
        color: #ffffff;
      }
    </style>
  </head>
  <body class="ac-body d-flex flex-column justify-content-between py-4 px-3">
    <div class="ac-dot-bg"></div>

    <!-- Barra Superior Minimalist Blue -->
    <header class="container auth-shell d-flex align-items-center justify-content-between mb-3">
      <div class="d-flex align-items-center gap-3">
        <a href="<?php echo BASE_URL; ?>/" class="btn btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: var(--ac-surface-solid); border: 1px solid var(--ac-border); color: var(--ac-text);" title="Volver al inicio">
          <i class="bi bi-arrow-left"></i>
        </a>
        <a href="<?php echo BASE_URL; ?>/" class="ac-brand-pill">
          <i class="bi bi-activity text-primary fs-5"></i>
          <span>SysMonitor</span>
        </a>
      </div>

      <button type="button" class="ac-theme-btn">
        <i class="bi bi-moon-stars-fill"></i> <span>Modo Oscuro</span>
      </button>
    </header>

    <!-- Contenedor Central Aceternity UI -->
    <main class="container auth-shell my-auto py-3">
      <div class="row justify-content-center">

        <!-- Tarjeta Principal Minimalist Blue (Acceso y Registro) -->
        <div class="col-12">
          <div class="ac-bento-card ac-beam-top p-4 p-md-5 auth-card-body shadow-lg">

            <?php
              $initialTab = in_array($_GET['tab'] ?? ($activeTab ?? 'login'), ['registro', 'representante'], true) ? 'representante' : 'login';
              $planesB2B = defined('B2B_PLANS') ? B2B_PLANS : [
                'starter' => ['nombre' => 'Starter', 'precio' => 49.00, 'max_racks' => 2, 'max_cuentas' => 3, 'descripcion' => 'Pequeñas empresas / 1 site'],
                'corporate' => ['nombre' => 'Corporate', 'precio' => 149.00, 'max_racks' => 10, 'max_cuentas' => 15, 'descripcion' => 'Empresas medianas / multi-rack'],
                'enterprise' => ['nombre' => 'Enterprise', 'precio' => 399.00, 'max_racks' => 999, 'max_cuentas' => 999, 'descripcion' => 'DataCenters / cuentas ilimitadas'],
              ];
              $selectedPlan = isset($_GET['plan']) && isset($planesB2B[$_GET['plan']]) ? $_GET['plan'] : 'corporate';
            ?>
            <!-- Selector de Modo: Iniciar Sesión vs Crea tu cuenta -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
              <div class="ac-squircle-logo">
                <i class="bi <?php echo $initialTab === 'representante' ? 'bi-building-add' : 'bi-hdd-stack'; ?>" id="authHeaderIcon"></i>
              </div>
              <div class="tab-pill-group" role="tablist">
                <button type="button" class="tab-pill-btn <?php echo $initialTab === 'login' ? 'active' : ''; ?>" id="btnTabLogin" onclick="switchAuthTab('login')">
                  Iniciar Sesión
                </button>
                <button type="button" class="tab-pill-btn <?php echo $initialTab === 'representante' ? 'active' : ''; ?>" id="btnTabRep" onclick="switchAuthTab('representante')">
                  Crea tu cuenta
                </button>
              </div>
            </div>

            <?php if (isset($_GET['error'])): ?>
              <div class="alert alert-danger d-flex align-items-center gap-2 rounded-4 py-2 px-3 small mb-4">
                <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                <div><?php echo htmlspecialchars($_GET['error']); ?></div>
              </div>
            <?php endif; ?>

            <!-- ========================================================== -->
            <!-- VISTA 1: INICIAR SESIÓN -->
            <!-- ========================================================== -->
            <div id="paneLogin" class="<?php echo $initialTab === 'representante' ? 'd-none' : ''; ?>">
              <h1 class="h2 fw-extrabold mb-1" style="font-weight: 800;">Iniciar Sesión</h1>
              <p class="small mb-4" style="color: var(--ac-text-muted);">
                Accede a tu consola centralizada de SysMonitor.
              </p>

              <form action="<?php echo BASE_URL; ?>/index.php?page=autenticar" method="POST" id="formInstitucional">
                <div class="mb-3">
                  <label class="ac-label" for="inputCorreoInst">Correo corporativo</label>
                  <div class="ac-input-wrapper">
                    <i class="bi bi-envelope"></i>
                    <input
                      type="email"
                      id="inputCorreoInst"
                      name="correo_electronico"
                      class="ac-input"
                      placeholder="usuario@empresa.sysmonitor.com"
                      required
                    />
                  </div>
                </div>

                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="ac-label mb-0" for="inputPassInst">Contraseña</label>
                    <a href="#demo-credentials" onclick="document.getElementById('demoBox').classList.toggle('d-none'); return false;" class="small fw-semibold text-decoration-none" style="color: var(--ac-primary);">
                      Cuentas de prueba
                    </a>
                  </div>
                  <div class="ac-input-wrapper">
                    <i class="bi bi-lock"></i>
                    <input
                      type="password"
                      id="inputPassInst"
                      name="contrasena"
                      class="ac-input"
                      placeholder="••••••••••••"
                      required
                    />
                    <button type="button" class="btn btn-sm border-0 p-0 text-muted" onclick="togglePassVisibility('inputPassInst', this)" aria-label="Mostrar contraseña">
                      <i class="bi bi-eye m-0"></i>
                    </button>
                  </div>
                </div>

                <div class="form-check mb-4">
                  <input class="form-check-input" type="checkbox" id="rememberDevice" checked>
                  <label class="form-check-label small" for="rememberDevice" style="color: var(--ac-text-secondary);">
                    Recordar en este dispositivo
                  </label>
                </div>

                <button type="submit" class="ac-btn-primary w-100">
                  <span>Ingresar</span> <i class="bi bi-arrow-right"></i>
                </button>
              </form>

              <!-- Accesos rápidos de prueba ocultos por defecto para no saturar -->
              <div id="demoBox" class="d-none mt-3 p-3 rounded-4 border" style="background: var(--ac-bg-secondary); border-color: var(--ac-border) !important;">
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" class="demo-credential-btn" onclick="fillDemoLogin('alex.vargas@infraops.sysmonitor.com', 'InfraOps2026!')">
                    Jefe de TI
                  </button>
                  <button type="button" class="demo-credential-btn" onclick="fillDemoLogin('elena.torres@infraops.sysmonitor.com', 'InfraOps2026!')">
                    Operador
                  </button>
                  <button type="button" class="demo-credential-btn" onclick="fillDemoLogin('admin@sysmonitor.com', 'Admin123!')">
                    SuperAdmin
                  </button>
                </div>
              </div>

              <div class="divider-line">O CONTINÚA CON</div>

              <div class="row g-2">
                <div class="col-6">
                  <button type="button" class="ac-oauth-btn" onclick="openOAuthModal('google')">
                    <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#2563EB" d="M21.35 11.1h-9.17v2.73h6.51c-.33 1.81-1.5 3.34-3.2 4.37v3.62h5.18c3.03-2.79 4.78-6.9 4.78-11.72 0-.8-.08-1.57-.2-2.3z"/><path fill="#1D4ED8" d="M12.18 22c4.32 0 7.95-1.43 10.6-3.88l-5.18-3.62c-1.43.96-3.26 1.53-5.42 1.53-4.17 0-7.7-2.81-8.96-6.6H-2.1v3.73C.54 18.4 5.93 22 12.18 22z"/></svg>
                    <span>Google</span>
                  </button>
                </div>
                <div class="col-6">
                  <button type="button" class="ac-oauth-btn" onclick="openOAuthModal('microsoft')">
                    <i class="bi bi-microsoft text-primary"></i>
                    <span>Microsoft</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- ========================================================== -->
            <!-- VISTA 2: REGISTRO UNIFICADO (REPRESENTANTE + EMPRESA + PLAN B2B) -->
            <!-- ========================================================== -->
            <div id="paneRepresentante" class="<?php echo $initialTab === 'representante' ? '' : 'd-none'; ?>">
              <h1 class="h2 fw-extrabold mb-1" style="font-weight: 800;">Crea tu cuenta</h1>
              <p class="small mb-3" style="color: var(--ac-text-muted);">
                Registra tu cuenta de representante y da de alta tu empresa con aprovisionamiento automático de Jefe de TI.
              </p>

              <!-- Acceso rápido OAuth opcional -->
              <div class="row g-2 mb-2">
                <div class="col-6">
                  <button type="button" class="ac-oauth-btn py-2" onclick="openOAuthModal('google')">
                    <svg width="16" height="16" viewBox="0 0 24 24"><path fill="#2563EB" d="M21.35 11.1h-9.17v2.73h6.51c-.33 1.81-1.5 3.34-3.2 4.37v3.62h5.18c3.03-2.79 4.78-6.9 4.78-11.72 0-.8-.08-1.57-.2-2.3z"/><path fill="#1D4ED8" d="M12.18 22c4.32 0 7.95-1.43 10.6-3.88l-5.18-3.62c-1.43.96-3.26 1.53-5.42 1.53-4.17 0-7.7-2.81-8.96-6.6H-2.1v3.73C.54 18.4 5.93 22 12.18 22z"/></svg>
                    <span>Google</span>
                  </button>
                </div>
                <div class="col-6">
                  <button type="button" class="ac-oauth-btn py-2" onclick="openOAuthModal('microsoft')">
                    <i class="bi bi-microsoft text-primary"></i>
                    <span>Microsoft</span>
                  </button>
                </div>
              </div>

              <div class="divider-line my-3">O REGISTRO CORPORATIVO UNIFICADO</div>

              <form action="<?php echo BASE_URL; ?>/index.php?page=registro_unificado" method="POST" id="formRegistroUnificado">
                <div id="regClientError" class="alert alert-danger d-none rounded-4 py-2 px-3 small mb-3"></div>

                <!-- 1. Datos del Representante -->
                <div class="reg-section-title">
                  <i class="bi bi-person-badge"></i> 1. Representante Legal
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-sm-6">
                    <label class="ac-label" for="regNombre">Nombre(s)</label>
                    <div class="ac-input-wrapper">
                      <i class="bi bi-person"></i>
                      <input type="text" id="regNombre" name="nombre" class="ac-input" placeholder="Tu nombre" required />
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="ac-label" for="regApellidos">Apellidos</label>
                    <div class="ac-input-wrapper">
                      <i class="bi bi-person-vcard"></i>
                      <input type="text" id="regApellidos" name="apellidos" class="ac-input" placeholder="Tus apellidos" required />
                    </div>
                  </div>
                </div>

                <div class="mb-2">
                  <label class="ac-label" for="regCorreo">Correo electrónico</label>
                  <div class="ac-input-wrapper">
                    <i class="bi bi-envelope"></i>
                    <input type="email" id="regCorreo" name="correo" class="ac-input" placeholder="representante@tuempresa.com" required />
                  </div>
                </div>

                <div class="row g-2 mb-3">
                  <div class="col-sm-6">
                    <label class="ac-label" for="regContrasena">Contraseña (mín. 8)</label>
                    <div class="ac-input-wrapper">
                      <i class="bi bi-lock"></i>
                      <input type="password" id="regContrasena" name="contrasena" class="ac-input" placeholder="Mínimo 8 caracteres" minlength="8" required />
                      <button type="button" class="btn btn-sm border-0 p-0 text-muted" onclick="togglePassVisibility('regContrasena', this)" aria-label="Mostrar contraseña">
                        <i class="bi bi-eye m-0"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="ac-label" for="regConfirmar">Confirmar Contraseña</label>
                    <div class="ac-input-wrapper">
                      <i class="bi bi-lock-fill"></i>
                      <input type="password" id="regConfirmar" name="confirmar_contrasena" class="ac-input" placeholder="Repite tu contraseña" minlength="8" required />
                      <button type="button" class="btn btn-sm border-0 p-0 text-muted" onclick="togglePassVisibility('regConfirmar', this)" aria-label="Mostrar confirmación">
                        <i class="bi bi-eye m-0"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- 2. Datos de la Empresa -->
                <div class="reg-section-title mt-3">
                  <i class="bi bi-building-add"></i> 2. Datos de tu Empresa
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-sm-7">
                    <label class="ac-label" for="regEmpresa">Razón Social / Empresa</label>
                    <div class="ac-input-wrapper">
                      <i class="bi bi-building"></i>
                      <input type="text" id="regEmpresa" name="nombre_empresa" class="ac-input" placeholder="Ej. CloudCore DataCenters" required />
                    </div>
                  </div>
                  <div class="col-sm-5">
                    <label class="ac-label" for="regRfc">RFC / ID Fiscal</label>
                    <div class="ac-input-wrapper">
                      <i class="bi bi-file-earmark-text"></i>
                      <input type="text" id="regRfc" name="rfc_nit" class="ac-input" placeholder="Ej. CCD260101ABC" required />
                    </div>
                  </div>
                </div>

                <div class="mb-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <label class="ac-label" for="regSlugDominio">Subdominio Institucional</label>
                    <span class="small fw-semibold text-primary" style="font-family: 'Fira Code', monospace; font-size: 0.72rem;">
                      jefe.ti@<span id="slugPreview">empresa</span>.<?php echo INSTITUTIONAL_BASE_DOMAIN; ?>
                    </span>
                  </div>
                  <div class="ac-input-wrapper">
                    <i class="bi bi-globe2"></i>
                    <input type="text" id="regSlugDominio" name="slug_dominio" class="ac-input" placeholder="miempresa" pattern="[a-zA-Z0-9\-]+" required />
                    <span class="small fw-bold text-primary flex-shrink-0">.<?php echo INSTITUTIONAL_BASE_DOMAIN; ?></span>
                  </div>
                </div>

                <div class="row g-2 mb-3">
                  <div class="col-sm-6">
                    <label class="ac-label" for="regTelefono">Teléfono</label>
                    <div class="ac-input-wrapper">
                      <i class="bi bi-telephone"></i>
                      <input type="text" id="regTelefono" name="telefono" class="ac-input" placeholder="+52 999 000 0000" required />
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="ac-label" for="regSector">Sector</label>
                    <div class="ac-input-wrapper">
                      <i class="bi bi-hdd-network"></i>
                      <input type="text" id="regSector" name="sector" class="ac-input" value="DataCenter & Cloud" required />
                    </div>
                  </div>
                </div>

                <!-- 3. Plan B2B -->
                <div class="reg-section-title mt-3">
                  <i class="bi bi-layers"></i> 3. Plan de Suscripción B2B
                </div>

                <div class="row g-2 mb-3">
                  <?php foreach ($planesB2B as $key => $planInfo): ?>
                    <?php
                      $isChecked = ($key === $selectedPlan);
                      $racksText = ((int)$planInfo['max_racks'] >= 999) ? 'Ilimitados' : (int)$planInfo['max_racks'] . ' Racks';
                      $cuentasText = ((int)$planInfo['max_cuentas'] >= 999) ? 'Ilimitadas' : (int)$planInfo['max_cuentas'] . ' Cuentas';
                    ?>
                    <div class="col-4 position-relative">
                      <input
                        type="radio"
                        name="plan_suscripcion"
                        id="plan_<?php echo htmlspecialchars($key); ?>"
                        value="<?php echo htmlspecialchars($key); ?>"
                        class="plan-radio-input"
                        <?php echo $isChecked ? 'checked' : ''; ?>
                      />
                      <label for="plan_<?php echo htmlspecialchars($key); ?>" class="plan-radio-label text-center">
                        <?php if ($key === 'corporate'): ?>
                          <span class="badge bg-primary rounded-pill mb-1 mx-auto" style="font-size: 0.62rem;">Recomendado</span>
                        <?php endif; ?>
                        <span class="fw-bold small" style="color: var(--ac-text);"><?php echo htmlspecialchars($planInfo['nombre']); ?></span>
                        <span class="fw-extrabold text-primary my-1" style="font-size: 1.05rem; font-weight: 800;">
                          $<?php echo number_format((float)$planInfo['precio'], 0); ?><small class="fw-normal text-muted" style="font-size: 0.7rem;">/mes</small>
                        </span>
                        <span class="text-muted" style="font-size: 0.68rem; line-height: 1.3;">
                          <?php echo htmlspecialchars($racksText); ?><br><?php echo htmlspecialchars($cuentasText); ?>
                        </span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>

                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="termsCheck" required checked>
                  <label class="form-check-label small" for="termsCheck" style="color: var(--ac-text-secondary);">
                    Acepto los <a href="#" class="text-primary fw-semibold">Términos y Condiciones</a> y el envío de credenciales SMTP.
                  </label>
                </div>

                <button type="submit" class="ac-btn-primary w-100">
                  <span>Crear Cuenta y Registrar Empresa</span> <i class="bi bi-arrow-right"></i>
                </button>
              </form>

              <div class="text-center mt-3 small" style="color: var(--ac-text-muted);">
                ¿Ya tienes cuenta? <a href="#" onclick="switchAuthTab('login'); return false;" class="fw-bold text-decoration-none" style="color: var(--ac-primary);">Inicia sesión</a>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>

    <!-- Modal OAuth 2.0 (Google / Microsoft) para Representantes -->
    <div class="modal fade" id="oauthModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ac-bento-card border-0 p-2">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="oauthModalTitle">
              <i class="bi bi-google text-primary"></i> Continuar con Google
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form action="<?php echo BASE_URL; ?>/index.php?page=oauth_callback" method="POST">
            <input type="hidden" name="provider" id="oauthProviderInput" value="google">
            <div class="modal-body">
              <p class="small" style="color: var(--ac-text-muted);">
                Confirma tus datos para acceder al panel de representante:
              </p>
              <div class="mb-3">
                <label class="ac-label">Nombre(s)</label>
                <div class="ac-input-wrapper">
                  <i class="bi bi-person"></i>
                  <input type="text" name="nombre" id="oauthNombre" class="ac-input" value="Carlos" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="ac-label">Apellidos</label>
                <div class="ac-input-wrapper">
                  <i class="bi bi-person-badge"></i>
                  <input type="text" name="apellidos" id="oauthApellidos" class="ac-input" value="Mendoza" required>
                </div>
              </div>
              <div class="mb-2">
                <label class="ac-label">Correo</label>
                <div class="ac-input-wrapper">
                  <i class="bi bi-envelope-check"></i>
                  <input type="email" name="correo" id="oauthCorreo" class="ac-input" value="carlos.mendoza@gmail.com" required>
                </div>
              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="ac-btn-primary py-2 px-4">
                Continuar <i class="bi bi-arrow-right"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <footer class="container auth-shell text-center small mt-3" style="color: var(--ac-text-muted);">
      &copy; 2026 SysMonitor IoT • Plataforma de Monitoreo Físico de Servidores
    </footer>

    <script src="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
      function switchAuthTab(tab) {
        const paneLogin = document.getElementById('paneLogin');
        const paneRep = document.getElementById('paneRepresentante');
        const btnLogin = document.getElementById('btnTabLogin');
        const btnRep = document.getElementById('btnTabRep');
        const icon = document.getElementById('authHeaderIcon');

        if (tab === 'representante') {
          paneLogin.classList.add('d-none');
          paneRep.classList.remove('d-none');
          btnLogin.classList.remove('active');
          btnRep.classList.add('active');
          icon.className = 'bi bi-building-add';
        } else {
          paneRep.classList.add('d-none');
          paneLogin.classList.remove('d-none');
          btnRep.classList.remove('active');
          btnLogin.classList.add('active');
          icon.className = 'bi bi-hdd-stack';
        }
      }

      function togglePassVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          icon.className = 'bi bi-eye-slash m-0';
        } else {
          input.type = 'password';
          icon.className = 'bi bi-eye m-0';
        }
      }

      function fillDemoLogin(email, pass) {
        document.getElementById('inputCorreoInst').value = email;
        document.getElementById('inputPassInst').value = pass;
      }

      function openOAuthModal(provider) {
        document.getElementById('oauthProviderInput').value = provider;
        const title = document.getElementById('oauthModalTitle');
        const emailInput = document.getElementById('oauthCorreo');
        if (provider === 'microsoft') {
          title.innerHTML = '<i class="bi bi-microsoft text-primary"></i> Continuar con Microsoft Entra ID';
          emailInput.value = 'carlos.mendoza@outlook.com';
        } else {
          title.innerHTML = '<i class="bi bi-google text-primary"></i> Continuar con Google OAuth 2.0';
          emailInput.value = 'carlos.mendoza@gmail.com';
        }
        new bootstrap.Modal(document.getElementById('oauthModal')).show();
      }

      // Abrir pestaña de registro y preseleccionar plan si vienen en la URL (?tab=registro / ?tab=representante)
      document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        if (tab === 'registro' || tab === 'representante') {
          switchAuthTab('representante');
        }
        const planParam = params.get('plan');
        if (planParam) {
          const planRadio = document.getElementById('plan_' + planParam);
          if (planRadio) {
            planRadio.checked = true;
          }
        }

        const slugInput = document.getElementById('regSlugDominio');
        const slugPreview = document.getElementById('slugPreview');
        if (slugInput && slugPreview) {
          slugInput.addEventListener('input', () => {
            const clean = slugInput.value.toLowerCase().replace(/[^a-z0-9\-]/g, '');
            slugPreview.textContent = clean || 'empresa';
          });
        }

        const regForm = document.getElementById('formRegistroUnificado');
        if (regForm) {
          regForm.addEventListener('submit', (e) => {
            const p1 = document.getElementById('regContrasena').value;
            const p2 = document.getElementById('regConfirmar').value;
            const errBox = document.getElementById('regClientError');
            if (p1.length < 8) {
              e.preventDefault();
              errBox.textContent = 'La contraseña debe tener al menos 8 caracteres.';
              errBox.classList.remove('d-none');
              return;
            }
            if (p1 !== p2) {
              e.preventDefault();
              errBox.textContent = 'Las contraseñas no coinciden.';
              errBox.classList.remove('d-none');
              return;
            }
            errBox.classList.add('d-none');
          });
        }
      });
    </script>
  </body>
</html>
