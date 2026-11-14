# AGENT.md — Guía Arquitectónica y Reglas del Proyecto SysMonitor IoT

Este documento define el contexto técnico, las reglas de negocio y las convenciones de arquitectura que cualquier agente de IA o desarrollador debe respetar al trabajar en **SysMonitor IoT**.

---

## 1. Reglas del Modelo de Negocio (Seguridad Extrema B2B)

1. **Separación Estricta de Cuentas:**
   - **Cuenta Personal (`id_rol = 3`, Representante):** Solo puede crearse e iniciar sesión mediante **OAuth 2.0 (Google o Microsoft)**. Su único propósito es registrar una empresa en la tabla `empresas` y solicitar cuentas institucionales en `solicitudes_cuentas`. Nunca accede al panel de sensores.
   - **Cuentas Institucionales (`id_rol = 4` Jefe de TI, `id_rol = 1` Operador, `id_rol = 2` SuperAdmin):** El SuperAdmin aprueba la empresa y emite correos con el subdominio institucional de la empresa (`nombre.apellido@<slug>.sysmonitor.com`). Inician sesión únicamente con ese correo institucional + contraseña.
2. **Autenticación Multifactor Universal (TOTP RFC 6238):**
   - **Todos los usuarios** (sin excepción de rol) deben contar con un secreto Base32 (`mfa_secret`) y validar el código de 6 dígitos de **Google Authenticator** ([models/TOTP.php](file:///c:/xampp/htdocs/PROYECTO-INTEGRADOR/models/TOTP.php)) antes de establecer `$_SESSION['mfa_verified'] = true` o recibir un JWT definitivo en la API REST.
3. **Jerarquía Técnica en el Monitoreo:**
   - **Jefe de TI (`id_rol = 4`):** Monitorea los racks de su empresa (`id_empresa`) y además tiene permisos de escritura para dar de alta Racks (`crearRack`), configurar umbrales máximos (`temp_max`, `hum_max`, `kw_max`), ver el personal de su institución y auditar la `bitacora_accesos`.
   - **Operador (`id_rol = 1`):** Solo visualiza en tiempo real los racks y sensores de su `id_empresa` y marca alertas como atendidas.

---

## 2. Arquitectura del Código

```text
PROYECTO-INTEGRADOR/
├── api/v1/
│   ├── index.php             # Enrutador RESTful JSON (JWT Bearer + X-API-Key IoT)
│   └── .htaccess             # Reescritura hacia index.php?endpoint=...
├── assets/
│   ├── css/aceternity-ui.css # Sistema de diseño Aceternity UI (Blanco/Azul + Dark Mode)
│   ├── js/aceternity-ui.js   # Gestor universal de tema claro/oscuro y efecto Spotlight
│   └── dashboard/custom-theme.css
├── config/
│   └── config.php            # Conexión MariaDB (puerto 3307), constantes OAuth2, JWT y MFA
├── controllers/
│   ├── AuthController.php          # Login institucional, OAuth Google/Microsoft y flujo MFA
│   ├── RepresentanteController.php # Alta de empresas y solicitud de correos corporativos
│   ├── AdminController.php         # Aprobación de empresas, emisión de correos y CRUD usuarios
│   ├── UserController.php          # Consola IoT diferenciada para Jefe de TI y Operador
│   └── HomeController.php          # Landing page y buzón de contacto
├── models/
│   ├── Database.php          # Conexión mysqli + auto-migración B2B y semilla inicial
│   ├── TOTP.php              # Algoritmo RFC 6238 para Google Authenticator
│   ├── JwtHelper.php         # Firma y validación de tokens JWT HS256
│   ├── Empresa.php           # Gestión de empresas y generador de correos institucionales
│   ├── Usuario.php           # Gestión de identidades multi-empresa y bitácora
│   ├── Sensor.php            # Racks, lecturas IoT, evaluación de umbrales y alertas
│   └── Contacto.php          # Mensajes de contacto
├── views/                    # Todas las vistas usan Aceternity UI (Blanco/Azul + Modo Oscuro)
│   ├── home/index.php
│   ├── auth/{login.php, mfa_setup.php, mfa_verify.php}
│   ├── representante/dashboard.php
│   ├── admin/{dashboard.php, empresas.php, usuarios.php}
│   └── user/{dashboard.php, perfil.php}
├── postman/
│   └── SysMonitor_Postman_Collection.json
├── mobile_app/               # Proyecto Flutter (Dart) con las 4 pantallas basadas en Stitch
└── database.sql              # Script SQL completo para MariaDB
```

---

## 3. Convenciones de UI/UX y Código

- **Tema Visual Obligatorio en `views/`:** Mantener la paleta **Blanco (`#FFFFFF` / `#F8FAFC`) y Azul (`#1D4ED8` / `#2563EB`)** como modo predeterminado (`data-bs-theme="light"`) y asegurar que todo componente nuevo utilice variables CSS (`var(--ac-bg)`, `var(--ac-surface)`, `var(--ac-text)`, `var(--ac-border)`) para que el cambio a **Modo Oscuro (`data-bs-theme="dark"`)** funcione sin textos ilegibles.
- **Seguridad en Consultas:** Usar siempre sentencias preparadas (`$this->db->prepare(...)`) en todos los modelos y filtrar los datos de Racks y Alertas por `id_empresa` para garantizar aislamiento entre instituciones (Multi-Tenancy).
