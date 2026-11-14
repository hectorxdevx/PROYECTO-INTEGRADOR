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
