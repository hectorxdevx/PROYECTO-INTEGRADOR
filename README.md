# SysMonitor IoT — Plataforma Corporativa B2B de Monitoreo Físico de Servidores

**SysMonitor IoT** es una plataforma integral de monitoreo físico para centros de datos y cuartos de servidores (Temperatura, Humedad, Consumo Eléctrico en kW y Apertura Física de Puertas de Racks) diseñada bajo un modelo de negocio **B2B Zero-Trust de Seguridad Extrema**.

---

## 1. Nuevo Modelo de Negocio (4 Roles y Seguridad Extrema)

El sistema separa el registro legal de las instituciones respecto a la operación técnica de monitoreo, funcionando de manera análoga al aprovisionamiento de correos institucionales en las universidades:

| Rol (`id_rol`) | Tipo de Cuenta | Método de Acceso | Permisos y Alcance |
| :--- | :--- | :--- | :--- |
| **Representante (`3`)** | Cuenta Personal | **OAuth 2.0 (Google / Microsoft)** + **Google Authenticator (TOTP)** | Da de alta a su empresa/institución y solicita cuentas institucionales para su Jefe de TI y Operadores. **No visualiza sensores**. |
| **SuperAdmin (`2`)** | Cuenta Maestra | Correo Institucional + Contraseña + **Google Authenticator (TOTP)** | Aprueba empresas registradas, emite los correos institucionales (`@empresa.sysmonitor.com`), audita MFA y gestiona toda la plataforma. |
| **Jefe de TI (`4`)** | Cuenta Institucional | Correo Proporcionado (`@empresa.sysmonitor.com`) + Contraseña + **Google Authenticator** | Monitorea todos los racks y sensores de su institución, **da de alta nuevos Racks**, **configura umbrales de alerta** (°C, %, kW), supervisa a su equipo de operadores y consulta la bitácora de auditoría. |
| **Operador (`1`)** | Cuenta Institucional | Correo Proporcionado (`@empresa.sysmonitor.com`) + Contraseña + **Google Authenticator** | Monitorea en tiempo real la telemetría de los racks asignados a su empresa y atiende alertas críticas. |

---

## 2. Sistema de Diseño: Aceternity UI + Minimalist Blue

Todas las vistas dentro de `views/` implementan el sistema de diseño [aceternity-ui.css](file:///c:/xampp/htdocs/PROYECTO-INTEGRADOR/assets/css/aceternity-ui.css) y [aceternity-ui.js](file:///c:/xampp/htdocs/PROYECTO-INTEGRADOR/assets/js/aceternity-ui.js):
- **Modo Claro por defecto (Blanco y Azul):** Fondos en Blanco Puro (`#FFFFFF`) y Gris Azulado (`#F8FAFC`) con acentos en Azul Corporativo (`#1D4ED8` / `#2563EB`), matriz de puntos (`ac-dot-bg`) y tarjetas **Bento Grid** (`ac-bento-card`) con efecto *Spotlight* interactivo al mover el cursor.
- **Modo Oscuro sincronizado:** Conmutador instantáneo en todas las pantallas (`views/home/`, `views/auth/`, `views/representante/`, `views/admin/`, `views/user/`) persistido en `localStorage`.

---

## 3. Credenciales Demo Preconfiguradas

Al abrir el proyecto en XAMPP (`http://localhost/PROYECTO-INTEGRADOR`), el sistema auto-migra y siembra datos iniciales en MariaDB (`puerto 3307`) para pruebas inmediatas:

- **Jefe de TI Institucional:** `alex.vargas@infraops.sysmonitor.com` / Contraseña: `InfraOps2026!`
- **Operador Institucional:** `elena.torres@infraops.sysmonitor.com` / Contraseña: `InfraOps2026!`
- **Super Administrador:** `admin@sysmonitor.com` / Contraseña: `Admin123!`
- **Representante (OAuth Google/Microsoft):** Botones **Google** o **Microsoft** en la pantalla de Login (`carlos.mendoza@gmail.com`).
- **Google Authenticator (TOTP):** En el primer acceso muestra el código QR compatible con la app **Google Authenticator** y un botón de autocompletado del código TOTP actual para agilizar pruebas locales.

---

## 4. ¿Qué es un Endpoint y cómo usar la API REST en Postman?

Un **endpoint** es una URL específica del servidor que recibe peticiones (`GET`, `POST`) y responde con datos estructurados en formato **JSON**, permitiendo que **Postman**, placas **IoT (ESP32)** y la **App en Flutter** se comuniquen con la base de datos MariaDB.

### Importar Colección en Postman
1. Abre **Postman** y haz clic en **Import**.
2. Selecciona el archivo [postman/SysMonitor_Postman_Collection.json](file:///c:/xampp/htdocs/PROYECTO-INTEGRADOR/postman/SysMonitor_Postman_Collection.json).
3. Ejecuta los endpoints en orden:
   - `GET /api/v1/index.php?endpoint=status` — Verifica el estado del servidor y lista de endpoints.
   - `POST /api/v1/index.php?endpoint=auth/login` — Valida correo institucional y devuelve el código MFA activo.
   - `POST /api/v1/index.php?endpoint=auth/mfa-verify` — Valida los 6 dígitos de Google Authenticator y entrega tu **Token Bearer (`jwt_token`)**.
   - `GET /api/v1/index.php?endpoint=racks` — Devuelve los racks y sensores de la empresa del usuario autenticado.
   - `POST /api/v1/index.php?endpoint=telemetry` — Simula el envío de lecturas desde un sensor IoT usando la cabecera `X-API-Key: iot_key_rack01_sysmonitor_2026` y dispara alertas automáticas si supera los límites.

---

## 5. Aplicación Móvil en Flutter (`/mobile_app`)

Ubicada en la carpeta [mobile_app/](file:///c:/xampp/htdocs/PROYECTO-INTEGRADOR/mobile_app), desarrollada en código **Dart** e integrada con la API REST:
1. [welcome_screen.dart](file:///c:/xampp/htdocs/PROYECTO-INTEGRADOR/mobile_app/lib/screens/welcome_screen.dart) — Pantalla de Bienvenida HeroUI Minimalist Blue.
2. [register_screen.dart](file:///c:/xampp/htdocs/PROYECTO-INTEGRADOR/mobile_app/lib/screens/register_screen.dart) — Pantalla de Alta de Cuenta de Representante.
3. [login_screen.dart](file:///c:/xampp/htdocs/PROYECTO-INTEGRADOR/mobile_app/lib/screens/login_screen.dart) — Inicio de Sesión Corporativo + OAuth Google/Microsoft + Modal de 6 dígitos de Google Authenticator.
4. [dashboard_screen.dart](file:///c:/xampp/htdocs/PROYECTO-INTEGRADOR/mobile_app/lib/screens/dashboard_screen.dart) — Consola Móvil de Monitoreo de Racks IoT (Temperatura, Humedad, Consumo kW, Puerta Física, Curva Térmica y Acciones de Jefe de TI / Operador).

### Ejecución de la App Flutter
```bash
cd mobile_app
flutter pub get
flutter run
```

---

## 6. Guía Súper Fácil: Cómo hacer que funcione en CUALQUIER laptop (Paso a paso)

Si te pasaron la carpeta del proyecto por USB, ZIP o GitHub y casi no sabes de programación, **no te preocupes**. Sigue estos pasos en orden como si fuera una receta de cocina.

---

### Paso 1: Descargar e instalar XAMPP (el "motor" de la página)
Tu computadora normal no entiende archivos `.php` ni bases de datos por sí sola. **XAMPP** es un programa gratuito que trae todo lo necesario en un solo clic.
1. Entra a [https://www.apachefriends.org](https://www.apachefriends.org) y descarga **XAMPP** para tu sistema (Windows o Mac).
2. Instálalo dando clic en **Next / Siguiente** a todo (déjalo en la ruta por defecto: `C:\xampp`).
3. *(Opcional)* Solo si también quieres abrir la **App Móvil**, instala **Flutter** y **Google Chrome**. Si solo usarás la **página web**, **NO** necesitas Flutter.

---

### Paso 2: Poner la carpeta en el lugar EXACTO
Este es el error #1 más común. Fíjate muy bien en el nombre y el lugar:
1. Copia tu carpeta del proyecto y pégala dentro de **`C:\xampp\htdocs\`** (en Mac es `/Applications/XAMPP/xamppfiles/htdocs/`).
2. La ruta final de los archivos debe verse **exactamente así**:
   - ✅ **Correcto:** `C:\xampp\htdocs\PROYECTO-INTEGRADOR\index.php`
   - ❌ **Incorrecto (carpeta doble):** `C:\xampp\htdocs\PROYECTO-INTEGRADOR\PROYECTO-INTEGRADOR\index.php`
   - ❌ **Incorrecto (otro nombre por descargar ZIP de GitHub):** `C:\xampp\htdocs\PROYECTO-INTEGRADOR-main\index.php`
   > **Nota:** Si descargaste un ZIP y la carpeta se llama `PROYECTO-INTEGRADOR-main`, cámbiale el nombre a **`PROYECTO-INTEGRADOR`** (en mayúsculas y sin `-main`).

---

### Paso 3: Configurar la conexión a la Base de Datos (¡Muy Importante!)
La laptop donde se creó este proyecto usaba el puerto **3307** y la contraseña **`mariadb`**, pero **una laptop nueva con XAMPP recién instalado casi siempre usa el puerto `3306` y NO tiene contraseña**.

1. Abre la carpeta `C:\xampp\htdocs\PROYECTO-INTEGRADOR\config\`.
2. Haz clic derecho en el archivo **`config.php`** -> **Abrir con** -> **Bloc de notas** (o VS Code).
3. Busca las líneas 10 a 14 que dicen esto:
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_USER', 'root');
   define('DB_PASS', 'mariadb');
   define('DB_NAME', 'server_monitor');
   define('DB_PORT', 3307);
   ```
4. **Si acabas de instalar XAMPP en tu laptop (lo normal)**, cámbialo para que quede así (contraseña vacía `''` y puerto `3306`):
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'server_monitor');
   define('DB_PORT', 3306);
   ```
5. Guarda el archivo (`Ctrl + S`).

---

### Paso 4: Encender los motores en XAMPP
1. Busca y abre el programa **XAMPP Control Panel** (el icono naranja con una X).
2. Presiona el botón **Start** al lado de **Apache** (espera a que el nombre se ponga **verde**).
3. Presiona el botón **Start** al lado de **MySQL** (espera a que se ponga **verde**).
   - *Tip:* En el panel de XAMPP verás qué puerto está usando MySQL en la columna `Port(s)` (casi siempre dirá `3306`). ¡Asegúrate de que ese mismo número sea el que pusiste en `DB_PORT` en el Paso 3!

---

### Paso 5: ¡Abrir la página web! (La base de datos se crea sola)
1. Abre **Google Chrome** (o Edge / Brave).
2. En la barra de direcciones arriba, escribe exactamente esto y presiona Enter:
   👉 **`http://localhost/PROYECTO-INTEGRADOR`**
3. **¡Magia!** La primera vez tardará 1 o 2 segundos más porque el sistema crea automáticamente la base de datos `server_monitor`, todas sus tablas y las cuentas de prueba. **No tienes que importar ningún archivo `.sql` manualmente.**

---

### Paso 6 (Opcional): Hacer que los correos lleguen a un Gmail real
Por defecto, cuando creas una empresa o mandas una duda desde la página, el sistema guarda todos los correos en una **Bandeja Interna** dentro del panel de Representante y SuperAdmin para que nunca falle aunque no tengas internet o configuración SMTP.

Si quieres que además lleguen a un Gmail de verdad:
1. En la carpeta `C:\xampp\htdocs\PROYECTO-INTEGRADOR\`, haz una copia del archivo **`.env.example`** y llámala exactamente **`.env`** (sin `.example`).
2. Ábrelo con el Bloc de notas y pon tu correo y una **Contraseña de Aplicación de Google** (no tu contraseña normal de Gmail; se genera en *Cuenta de Google -> Seguridad -> Verificación en 2 pasos -> Contraseñas de aplicaciones*).

---

### Paso 7 (Opcional): Abrir la App Móvil (Flutter)
1. Asegúrate de tener **Apache y MySQL encendidos en verde** en XAMPP (la app móvil pide los datos a la web).
2. Abre una terminal (CMD o PowerShell) y escribe estos 3 comandos uno por uno:
   ```powershell
   cd C:\xampp\htdocs\PROYECTO-INTEGRADOR\mobile_app
   flutter pub get
   flutter run -d chrome
   ```
3. Se abrirá una ventana de Chrome con la app móvil lista para usar. Para cerrarla, en la terminal presiona la letra **`q`**.

---

### 🆘 Solución a TODOS los fallos comunes ("¿Qué hago si me sale error?")

| Problema que ves en pantalla | ¿Por qué pasa? | Cómo arreglarlo en 1 minuto |
| :--- | :--- | :--- |
| **Sale: `Error, no se pudo establecer la conexion`** | 1) No encendiste **MySQL** en XAMPP, o <br>2) `config/config.php` tiene puerto `3307` y clave `'mariadb'`, pero tu XAMPP usa `3306` y clave vacía `''`. | Enciende MySQL en XAMPP y revisa el **Paso 3** para poner `DB_PORT` igual al número verde de MySQL en XAMPP y `DB_PASS` en `''`. |
| **La página se ve rota, sin colores, o sale `404 Not Found`** | La carpeta no se llama exactamente `PROYECTO-INTEGRADOR` o quedó una carpeta dentro de otra carpeta. | Verifica en el explorador de archivos que la ruta sea `C:\xampp\htdocs\PROYECTO-INTEGRADOR\index.php`. Si la carpeta tiene otro nombre (ej. `mi-proyecto`), cámbiaselo o edita `BASE_URL` en `config/config.php`. |
| **En XAMPP, `Apache` sale en ROJO y dice `Port 80 in use`** | Otro programa (como Skype, VMware o IIS de Windows) está usando el puerto 80. | En XAMPP clic en **Config** (de Apache) -> `httpd.conf` -> cambia `Listen 80` a `Listen 8080` y `ServerName localhost:8080`. Luego en `config/config.php` cambia `BASE_URL` a `http://localhost:8080/PROYECTO-INTEGRADOR` y entra por ahí. |
| **En XAMPP, `MySQL` se apaga solo o sale ROJO (`Port 3306 in use` o `shutdown unexpectedly`)** | Ya había otro MySQL instalado en esa laptop o XAMPP no se cerró bien la última vez. | 1) Abre el Administrador de tareas o `Servicios` de Windows y detén cualquier otro `MySQL`. <br>2) Si sigue igual, cambia el puerto de MySQL en XAMPP (**Config** -> `my.ini` -> cambia `3306` por `3307` en `[client]` y `[mysqld]`) y pon `3307` también en `config/config.php`. |
| **Pide código de 6 dígitos (Google Authenticator) y dice "Código inválido"** | El reloj de la laptop está desfasado unos minutos/segundos, o no escaneaste el QR en tu celular. | 1) Haz clic derecho en el reloj de Windows -> **Ajustar fecha y hora** -> **Sincronizar ahora**. <br>2) En las cuentas demo, usa el **Secreto Base32** que aparece en pantalla (o el botón de autocompletado si estás en pruebas). |
| **Al subir foto de perfil sale error o no cambia** | La carpeta `assets/uploads` no existe o no tiene permisos de escritura. | Entra a `C:\xampp\htdocs\PROYECTO-INTEGRADOR\assets\` y crea manualmente una carpeta llamada `uploads` si no estuviera ahí. |
| **La App Móvil dice que `flutter` no se reconoce como comando** | Esa laptop no tiene instalado Flutter SDK o no está agregado al `PATH` de Windows. | Para probar solo la web **no hace falta Flutter**. Si quieres correr la app móvil en esa laptop, instala Flutter desde [flutter.dev](https://flutter.dev) y reinicia la terminal. |
| **En la App Móvil no cargan los datos o sale error de red** | Apache o MySQL en XAMPP están apagados, o `BASE_URL` usa un puerto distinto al `80`. | Enciende Apache y MySQL en XAMPP. Si cambiaste Apache al puerto `8080`, actualiza también la línea `baseUrl` en `mobile_app/lib/services/api_service.dart` poniendo `:8080`. |
| **Los correos no llegan a mi Gmail/Hotmail** | No creaste el archivo `.env` con credenciales SMTP reales. | No es un fallo: en modo local sin `.env`, los correos se guardan automáticamente en la **Bandeja de Correos SMTP Emitidos** dentro de los paneles de Representante y SuperAdmin para que los veas al instante. |
