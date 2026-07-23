<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Autenticación | SysMonitor</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/login/login_style.css" />
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/favicon.svg">
  </head>
  <body>
    <main class="auth-container" id="auth-container">
      <div class="form-container sign-up-container">
        <!-- El formulario apuntará a la validación en el futuro -->
        <form action="<?php echo BASE_URL; ?>/index.php?page=registrar" method="POST">
          <h2>CREAR CUENTA</h2>
          <!-- Aquí mostraremos los mensajes de error o éxito -->
          <?php if (isset($_GET['error'])): ?>
              <div style="background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 10px; width: 100%; text-align: center; font-size: 14px;">
                  <?php echo htmlspecialchars($_GET['error']); ?>
              </div>
          <?php endif; ?>
          <?php if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso'): ?>
              <div style="background-color: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 10px; width: 100%; text-align: center; font-size: 14px;">
                  Registro exitoso. ¡Ahora puedes iniciar sesión!
              </div>
          <?php endif; ?>
          <span>o usa tu correo electrónico</span>
          <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="nombre_usuario" placeholder="Nombre(s)" required />
          </div>
          <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="apellidos" placeholder="Apellido(s)" required />
          </div>
          <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="correo" placeholder="Correo electrónico" required pattern="^[a-zA-Z0-9._%+\-]+@(gmail\.com|hotmail\.com)$" title="Solo se permiten correos de @gmail.com o @hotmail.com" />
          </div>
          <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="contrasena" placeholder="Contraseña" required minlength="8" title="La contraseña debe tener al menos 8 caracteres" />
          </div>
          <button type="submit" class="btn">REGISTRARSE</button>
        </form>
      </div>

      <div class="form-container sign-in-container">
        <!-- Simulación temporal de inicio de sesión hacia el dashboard del usuario -->
        <form action="<?php echo BASE_URL; ?>/index.php?page=autenticar" method="POST">
          <h2>INICIAR SESIÓN</h2>
          <?php if (isset($_GET['error'])): ?>
              <div style="background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 10px; width: 100%; text-align: center; font-size: 14px;">
                  <?php echo htmlspecialchars($_GET['error']); ?>
              </div>
          <?php endif; ?>
          <span>o usa tu cuenta de correo</span>
          <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="correo_electronico" placeholder="Correo electrónico" required />
          </div>
          <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="contrasena" placeholder="Contraseña" required />
          </div>
          <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
          <button type="submit" class="btn">ENTRAR</button>
        </form>
      </div>

      <div class="overlay-container">
        <div class="overlay">
          <div class="overlay-panel overlay-left">
            <h2 class="h2_white">¡Bienvenido de nuevo!</h2>
            <p>
              Gracias por formar parte de nuestro equipo.
            </p>
            <button class="btn btn-ghost" id="signIn">ENTRAR</button>
          </div>

          <div class="overlay-panel overlay-right">
            <h2 class="h2_white">¡Hola, Amigo!</h2>
            <p class="bold-white">¿Eres nuevo?</p>
            <p class="bold-white">
              Empieza tu viaje con nosotros hoy mismo.
            </p>
            <button class="btn btn-ghost" id="signUp">REGISTRARSE</button>
          </div>
        </div>
      </div>
    </main>

    <script>
      const signUpButton = document.getElementById("signUp");
      const signInButton = document.getElementById("signIn");
      const container = document.getElementById("auth-container");

      signUpButton.addEventListener("click", () => {
        container.classList.add("right-panel-active");
      });

      signInButton.addEventListener("click", () => {
        container.classList.remove("right-panel-active");
      });
    </script>
  </body>
</html>
