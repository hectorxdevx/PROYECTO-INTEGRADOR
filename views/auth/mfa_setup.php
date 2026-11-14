<!doctype html>
<html lang="es" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Configurar Google Authenticator (MFA) | SysMonitor</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/css/aceternity-ui.css" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    <script src="<?php echo BASE_URL; ?>/assets/js/aceternity-ui.js"></script>
  </head>
  <body class="ac-body d-flex flex-column justify-content-between py-4 px-3">
    <div class="ac-dot-bg"></div>

    <header class="container" style="max-width: 680px;">
      <div class="d-flex align-items-center justify-content-between">
        <a href="<?php echo BASE_URL; ?>/index.php?page=login" class="ac-brand-pill">
          <i class="bi bi-arrow-left text-primary"></i>
          <span>SysMonitor MFA</span>
        </a>
        <button type="button" class="ac-theme-btn">
          <i class="bi bi-moon-stars-fill"></i> <span>Modo Oscuro</span>
        </button>
      </div>
    </header>

    <main class="container my-auto py-4" style="max-width: 680px;">
      <div class="ac-bento-card ac-beam-top p-4 p-md-5">
        <div class="text-center mb-4">
          <div class="ac-squircle-logo mx-auto mb-3">
            <i class="bi bi-qr-code-scan"></i>
          </div>
          <h1 class="h3 fw-bold mb-1">Vincula tu autenticador</h1>
          <p class="small mb-0" style="color: var(--ac-text-muted);">
            <strong><?php echo htmlspecialchars($preUser['correo_electronico']); ?></strong>
          </p>
        </div>

        <?php if (isset($errorMfa)): ?>
          <div class="alert alert-danger rounded-4 small py-2 px-3 mb-4 text-center">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($errorMfa); ?>
          </div>
        <?php endif; ?>

        <div class="row g-4 align-items-center mb-4">
          <div class="col-md-5 text-center">
            <div class="p-3 rounded-4 d-inline-block bg-white border shadow-sm">
              <img src="<?php echo htmlspecialchars($qrUrl); ?>" alt="Código QR Google Authenticator" width="190" height="190" class="img-fluid rounded-3" />
            </div>
          </div>
          <div class="col-md-7">
            <p class="small mb-2" style="color: var(--ac-text-secondary);">
              Escanea el código QR con <strong>Google Authenticator</strong> o ingresa esta clave:
            </p>
            <div class="p-2 px-3 rounded-3 text-center mb-3" style="background: var(--ac-input-bg); border: 1px dashed var(--ac-primary); font-family: 'Fira Code', monospace; font-weight: 700; color: var(--ac-primary); letter-spacing: 0.12em;">
              <?php echo htmlspecialchars($preUser['mfa_secret']); ?>
            </div>
            <div class="p-2 px-3 rounded-3 small d-flex align-items-center justify-content-between" style="background: var(--ac-primary-light); color: var(--ac-primary);">
              <span><i class="bi bi-clock-history me-1"></i> Código actual:</span>
              <strong style="font-family: 'Fira Code', monospace; font-size: 1rem;"><?php echo htmlspecialchars($codigoActualDemo); ?></strong>
            </div>
          </div>
        </div>

        <form action="<?php echo BASE_URL; ?>/index.php?page=mfa_verify" method="POST">
          <input type="hidden" name="from_setup" value="1" />
          <div class="mb-4">
            <label class="ac-label text-center" for="codigoMfaInput">Código de 6 dígitos</label>
            <div class="ac-input-wrapper">
              <i class="bi bi-shield-check"></i>
              <input
                type="text"
                id="codigoMfaInput"
                name="codigo_mfa"
                class="ac-input ac-otp-input"
                placeholder="000000"
                maxlength="6"
                pattern="\d{6}"
                inputmode="numeric"
                autocomplete="one-time-code"
                required
                autofocus
              />
              <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="document.getElementById('codigoMfaInput').value='<?php echo htmlspecialchars($codigoActualDemo); ?>'">
                Usar <?php echo htmlspecialchars($codigoActualDemo); ?>
              </button>
            </div>
          </div>

          <button type="submit" class="ac-btn-primary w-100">
            <span>Continuar</span> <i class="bi bi-arrow-right"></i>
          </button>
        </form>
      </div>
    </main>

    <div></div>
  </body>
</html>
