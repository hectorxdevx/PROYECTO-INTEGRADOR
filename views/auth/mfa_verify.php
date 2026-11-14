<!doctype html>
<html lang="es" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verificación Multifactor (6 Dígitos) | SysMonitor</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/landing_page/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/css/aceternity-ui.css" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
    <script src="<?php echo BASE_URL; ?>/assets/js/aceternity-ui.js"></script>
  </head>
  <body class="ac-body d-flex flex-column justify-content-between py-4 px-3">
    <div class="ac-dot-bg"></div>

    <header class="container" style="max-width: 520px;">
      <div class="d-flex align-items-center justify-content-between">
        <a href="<?php echo BASE_URL; ?>/index.php?page=login" class="ac-brand-pill">
          <i class="bi bi-arrow-left text-primary"></i>
          <span>Cambiar cuenta</span>
        </a>
        <button type="button" class="ac-theme-btn">
          <i class="bi bi-moon-stars-fill"></i> <span>Modo Oscuro</span>
        </button>
      </div>
    </header>

    <main class="container my-auto py-4" style="max-width: 520px;">
      <div class="ac-bento-card ac-beam-top p-4 p-md-5 text-center">
        <div class="ac-squircle-logo mx-auto mb-3">
          <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h1 class="h3 fw-bold mb-1">Código de verificación</h1>
        <p class="small mb-4" style="color: var(--ac-text-muted);">
          Ingresa el código de 6 dígitos para <strong><?php echo htmlspecialchars($preUser['correo_electronico']); ?></strong>.
        </p>

        <?php if (isset($errorMfa)): ?>
          <div class="alert alert-danger rounded-4 small py-2 px-3 mb-4">
            <i class="bi bi-exclamation-octagon-fill me-1"></i> <?php echo htmlspecialchars($errorMfa); ?>
          </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/index.php?page=mfa_verify" method="POST">
          <div class="mb-3">
            <div class="ac-input-wrapper">
              <i class="bi bi-key-fill"></i>
              <input
                type="text"
                id="verifyOtpInput"
                name="codigo_mfa"
                class="ac-input ac-otp-input"
                placeholder="000000"
                maxlength="6"
                pattern="\d{6}"
                inputmode="numeric"
                required
                autofocus
              />
            </div>
          </div>

          <div class="p-2 px-3 rounded-3 small d-flex align-items-center justify-content-between mb-4" style="background: var(--ac-primary-light); color: var(--ac-primary);">
            <span><i class="bi bi-phone-vibrate me-1"></i> Código actual:</span>
            <button type="button" class="btn btn-sm btn-primary rounded-pill py-0 px-3 fw-bold" onclick="document.getElementById('verifyOtpInput').value='<?php echo htmlspecialchars($codigoActualDemo); ?>'">
              Usar <?php echo htmlspecialchars($codigoActualDemo); ?>
            </button>
          </div>

          <button type="submit" class="ac-btn-primary w-100">
            <span>Entrar</span> <i class="bi bi-arrow-right"></i>
          </button>
        </form>

        <div class="mt-4 pt-2 border-top" style="border-color: var(--ac-border) !important;">
          <a href="<?php echo BASE_URL; ?>/index.php?page=mfa_setup" class="small text-decoration-none fw-semibold" style="color: var(--ac-primary);">
            <i class="bi bi-qr-code me-1"></i> Ver código QR
          </a>
        </div>
      </div>
    </main>

    <div></div>
  </body>
</html>
