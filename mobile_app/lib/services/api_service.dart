import 'dart:convert';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:http/http.dart' as http;

/// Servicio de conexión con la API REST (/api/v1/index.php) de SysMonitor IoT.
/// Incluye respaldo local inmediato para demostración fluida en Web (Chrome), emulador o dispositivo físico.
class ApiService {
  // En Chrome/Web apunta directo a localhost de XAMPP; en emulador Android usa 10.0.2.2
  static String baseUrl = kIsWeb
      ? 'http://localhost/PROYECTO-INTEGRADOR/api/v1/index.php'
      : 'http://10.0.2.2/PROYECTO-INTEGRADOR/api/v1/index.php';

  static const Map<String, Map<String, dynamic>> b2bPlans = {
    'starter': {
      'nombre': 'Starter',
      'precio': 49.0,
      'max_racks': 2,
      'max_cuentas': 3,
      'descripcion': 'Pequeñas empresas / 1 site',
    },
    'corporate': {
      'nombre': 'Corporate',
      'precio': 149.0,
      'max_racks': 10,
      'max_cuentas': 15,
      'descripcion': 'Empresas medianas / multi-rack',
    },
    'enterprise': {
      'nombre': 'Enterprise',
      'precio': 399.0,
      'max_racks': 999,
      'max_cuentas': 999,
      'descripcion': 'DataCenters / cuentas ilimitadas',
    },
  };

  static String? jwtToken;
  static String? tempMfaToken;
  static Map<String, dynamic> currentUser = {
    'id_usuario': 2,
    'id_empresa': 1,
    'nombre': 'Alex Vargas',
    'correo': 'alex.vargas@infraops.sysmonitor.com',
    'rol': 'jefe_ti',
    'id_rol': 4,
    'empresa': 'InfraOps Cloud & DataCenter',
    'empresa_info': {
      'id_empresa': 1,
      'nombre_empresa': 'InfraOps Cloud & DataCenter',
      'slug_dominio': 'infraops.sysmonitor.com',
      'plan_suscripcion': 'corporate',
    },
  };

  static Map<String, dynamic> _demoEmpresa = {
    'id_empresa': 1,
    'nombre_empresa': 'InfraOps Cloud & DataCenter',
    'rfc_nit': 'IOC2601159A1',
    'slug_dominio': 'infraops.sysmonitor.com',
    'sector': 'DataCenter & Cloud',
    'telefono_contacto': '+52 999 123 4567',
    'plan_suscripcion': 'corporate',
    'max_racks': 10,
    'max_cuentas': 15,
    'costo_mensual': 149.0,
    'estado': 'aprobada',
  };

  static final List<Map<String, dynamic>> _demoRacks = [
    {
      'id_rack': 1,
      'codigo_rack': 'RACK-01',
      'nombre_rack': 'Rack 01 - Servidores Principales',
      'ubicacion': 'Site 1 - Pasillo Frío A',
      'estado': 'online',
      'temp_max': 27.0,
      'hum_max': 55.0,
      'kw_max': 2.5,
      'lectura': {
        'temperatura': 22.4,
        'humedad': 45.0,
        'consumo_kw': 1.20,
        'puerta_abierta': 0,
      }
    },
    {
      'id_rack': 2,
      'codigo_rack': 'RACK-02',
      'nombre_rack': 'Rack 02 - Almacenamiento SAN',
      'ubicacion': 'Site 1 - Pasillo Frío A',
      'estado': 'online',
      'temp_max': 26.5,
      'hum_max': 55.0,
      'kw_max': 2.2,
      'lectura': {
        'temperatura': 23.8,
        'humedad': 42.0,
        'consumo_kw': 0.95,
        'puerta_abierta': 0,
      }
    },
    {
      'id_rack': 3,
      'codigo_rack': 'RACK-03',
      'nombre_rack': 'Rack 03 - Core Switches & Firewall',
      'ubicacion': 'Site 1 - Telecomunicaciones',
      'estado': 'warning',
      'temp_max': 27.0,
      'hum_max': 60.0,
      'kw_max': 1.8,
      'lectura': {
        'temperatura': 28.4,
        'humedad': 48.0,
        'consumo_kw': 0.65,
        'puerta_abierta': 0,
      }
    },
    {
      'id_rack': 4,
      'codigo_rack': 'RACK-04',
      'nombre_rack': 'Rack 04 - Respaldo Crítico',
      'ubicacion': 'Site 2 - Bóveda Secundaria',
      'estado': 'critical',
      'temp_max': 26.0,
      'hum_max': 50.0,
      'kw_max': 2.0,
      'lectura': {
        'temperatura': 21.5,
        'humedad': 40.0,
        'consumo_kw': 1.10,
        'puerta_abierta': 1,
      }
    },
  ];

  static final List<Map<String, dynamic>> _demoAlertas = [
    {
      'id_alerta': 1,
      'id_rack': 4,
      'codigo_rack': 'RACK-04',
      'nombre_rack': 'Rack 04 - Respaldo Crítico',
      'tipo_alerta': 'Apertura Física de Puerta',
      'mensaje': 'Puerta de Rack 04 abierta fuera de ventana de mantenimiento',
      'nivel': 'critical',
      'resuelta': 0,
      'fecha_alerta': 'Hace 5m',
    },
    {
      'id_alerta': 2,
      'id_rack': 3,
      'codigo_rack': 'RACK-03',
      'nombre_rack': 'Rack 03 - Core Switches & Firewall',
      'tipo_alerta': 'Temperatura Elevada',
      'mensaje': 'Rack 03 superó el umbral de 27.0 °C (Actual: 28.4 °C)',
      'nivel': 'warning',
      'resuelta': 0,
      'fecha_alerta': 'Hace 18m',
    },
  ];

  static final List<Map<String, dynamic>> _demoSolicitudes = [
    {
      'id_solicitud': 1,
      'nombre': 'Alex',
      'apellidos': 'Vargas',
      'rol_solicitado': 'jefe_ti',
      'correo_asignado': 'alex.vargas@infraops.sysmonitor.com',
      'estado': 'aprobada',
      'fecha_solicitud': '2026-11-14 10:00:00',
    },
    {
      'id_solicitud': 2,
      'nombre': 'Elena',
      'apellidos': 'Torres',
      'rol_solicitado': 'operador',
      'correo_asignado': 'elena.torres@infraops.sysmonitor.com',
      'estado': 'aprobada',
      'fecha_solicitud': '2026-11-14 10:05:00',
    },
  ];

  static final List<Map<String, dynamic>> _demoCorreos = [
    {
      'id_correo': 1,
      'destinatario': 'carlos.mendoza@gmail.com',
      'asunto': 'Credenciales Jefe de TI - InfraOps Cloud & DataCenter',
      'tipo_correo': 'credenciales_ti',
      'estado_smtp': 'cola_local_auditable',
      'cuerpo_html':
          '<p>Cuenta institucional aprovisionada: <b>alex.vargas@infraops.sysmonitor.com</b> | Rol: Jefe de TI | Plan: Corporate</p>',
      'fecha_envio': '2026-11-14 10:01:00',
    },
  ];

  static Map<String, String> _authHeaders() => {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ${jwtToken ?? ""}',
      };

  static String _sanitizeSlug(String raw) {
    final cleaned = raw.toLowerCase().replaceAll(RegExp(r'[^a-z0-9\-]'), '');
    return cleaned.isEmpty ? 'empresa${DateTime.now().millisecond}' : cleaned;
  }

  static Map<String, dynamic> _buildDemoUsoPlan() {
    final planKey =
        (_demoEmpresa['plan_suscripcion'] ?? 'corporate').toString();
    final plan = b2bPlans[planKey] ?? b2bPlans['corporate']!;
    return {
      'plan_suscripcion': planKey,
      'nombre_plan': plan['nombre'],
      'costo_mensual': plan['precio'],
      'racks_usados': _demoRacks.length,
      'max_racks': plan['max_racks'],
      'cuentas_usadas': _demoSolicitudes.length,
      'max_cuentas': plan['max_cuentas'],
      'estado': _demoEmpresa['estado'] ?? 'aprobada',
    };
  }

  static void logout() {
    jwtToken = null;
    tempMfaToken = null;
  }

  /// Paso 1: Autenticación institucional (Correo proporcionado + contraseña)
  static Future<Map<String, dynamic>> loginInstitucional(
      String correo, String contrasena) async {
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl?endpoint=auth/login'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({'correo': correo, 'contrasena': contrasena}),
          )
          .timeout(const Duration(seconds: 4));

      final data = jsonDecode(res.body) as Map<String, dynamic>;
      if (data['ok'] == true) {
        tempMfaToken = data['temp_token']?.toString();
        if (data['usuario'] is Map) {
          currentUser = Map<String, dynamic>.from(data['usuario'] as Map);
          currentUser['empresa_info'] ??= {
            'id_empresa': currentUser['id_empresa'],
            'nombre_empresa': currentUser['empresa'],
            'slug_dominio': currentUser['slug_dominio'],
          };
        }
      }
      return data;
    } catch (_) {
      // Modo offline/demo resiliente si el emulador no alcanza XAMPP
      tempMfaToken = 'demo_temp_mfa_token';
      currentUser = {
        'id_usuario': correo.contains('elena') ? 3 : 2,
        'id_empresa': _demoEmpresa['id_empresa'] ?? 1,
        'nombre': correo.contains('elena') ? 'Elena Torres' : 'Alex Vargas',
        'correo': correo,
        'rol': correo.contains('elena') ? 'operador' : 'jefe_ti',
        'id_rol': correo.contains('elena') ? 1 : 4,
        'empresa':
            _demoEmpresa['nombre_empresa'] ?? 'InfraOps Cloud & DataCenter',
        'empresa_info': Map<String, dynamic>.from(_demoEmpresa),
      };
      return {
        'ok': true,
        'status': 'MFA_REQUIRED',
        'temp_token': tempMfaToken,
        'codigo_totp_actual_para_pruebas_postman': '482910',
        'usuario': currentUser,
      };
    }
  }

  /// Paso 2: Verificación de los 6 dígitos de Google Authenticator (TOTP)
  static Future<Map<String, dynamic>> verificarMfa(
      String correo, String codigo6Digitos) async {
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl?endpoint=auth/mfa-verify'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({
              'temp_token': tempMfaToken ?? '',
              'correo': correo,
              'codigo_mfa': codigo6Digitos,
            }),
          )
          .timeout(const Duration(seconds: 4));

      final data = jsonDecode(res.body) as Map<String, dynamic>;
      if (data['ok'] == true) {
        jwtToken = data['token']?.toString();
        if (data['usuario'] is Map) {
          final prevEmpresaInfo = currentUser['empresa_info'];
          currentUser = Map<String, dynamic>.from(data['usuario'] as Map);
          currentUser['empresa_info'] ??= prevEmpresaInfo ??
              {
                'id_empresa': currentUser['id_empresa'],
                'nombre_empresa': currentUser['empresa'],
                'slug_dominio': currentUser['slug_dominio'],
              };
        }
      }
      return data;
    } catch (_) {
      if (RegExp(r'^\d{6}$').hasMatch(codigo6Digitos.trim())) {
        jwtToken = 'demo_jwt_token_sysmonitor';
        return {'ok': true, 'token': jwtToken, 'usuario': currentUser};
      }
      return {'ok': false, 'error': 'Ingresa un código válido de 6 dígitos.'};
    }
  }

  /// Registro unificado de Representante + Alta de Empresa + Plan B2B + Jefe de TI inicial
  static Future<Map<String, dynamic>> registerCompanyAndRep({
    required String nombre,
    required String apellidos,
    required String correo,
    required String contrasena,
    required String nombreEmpresa,
    required String rfcNit,
    required String slugDominio,
    required String telefono,
    String sector = 'DataCenter & Cloud',
    String plan = 'corporate',
  }) async {
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl?endpoint=auth/register-company'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({
              'nombre': nombre,
              'apellidos': apellidos,
              'correo': correo,
              'contrasena': contrasena,
              'nombre_empresa': nombreEmpresa,
              'rfc_nit': rfcNit,
              'slug_dominio': slugDominio,
              'sector': sector,
              'telefono': telefono,
              'plan_suscripcion': plan,
            }),
          )
          .timeout(const Duration(seconds: 6));

      final data = jsonDecode(res.body) as Map<String, dynamic>;
      if (data['ok'] == true) {
        tempMfaToken = data['temp_token']?.toString();
        if (data['usuario'] is Map) {
          currentUser = Map<String, dynamic>.from(data['usuario'] as Map);
        }
        if (data['empresa'] is Map) {
          currentUser['empresa_info'] =
              Map<String, dynamic>.from(data['empresa'] as Map);
        }
      }
      return data;
    } catch (_) {
      final slug = _sanitizeSlug(slugDominio);
      final dominio = '$slug.sysmonitor.com';
      final planCfg = b2bPlans[plan] ?? b2bPlans['corporate']!;
      final newIdEmp = (_demoEmpresa['id_empresa'] as int? ?? 1) + 1;
      final jefeCorreo = 'jefe.ti@$dominio';
      final tempPass = 'SysJef4821!';

      _demoEmpresa = {
        'id_empresa': newIdEmp,
        'nombre_empresa': nombreEmpresa,
        'rfc_nit': rfcNit.toUpperCase(),
        'slug_dominio': dominio,
        'sector': sector,
        'telefono_contacto': telefono,
        'plan_suscripcion': plan,
        'max_racks': planCfg['max_racks'],
        'max_cuentas': planCfg['max_cuentas'],
        'costo_mensual': planCfg['precio'],
        'estado': 'aprobada',
      };

      _demoRacks
        ..clear()
        ..add({
          'id_rack': 1,
          'codigo_rack': 'RACK-01',
          'nombre_rack': 'Rack 01 - Nodo Principal',
          'ubicacion': 'Site Principal',
          'estado': 'online',
          'temp_max': 27.0,
          'hum_max': 60.0,
          'kw_max': 2.5,
          'lectura': {
            'temperatura': 22.0,
            'humedad': 45.0,
            'consumo_kw': 1.15,
            'puerta_abierta': 0,
          },
        });
      _demoAlertas.clear();
      _demoSolicitudes
        ..clear()
        ..add({
          'id_solicitud': 1,
          'nombre': 'Jefe',
          'apellidos': 'TI',
          'rol_solicitado': 'jefe_ti',
          'correo_asignado': jefeCorreo,
          'password_temporal': tempPass,
          'estado': 'aprobada',
          'fecha_solicitud': DateTime.now().toIso8601String(),
        });
      _demoCorreos.insert(0, {
        'id_correo': _demoCorreos.length + 1,
        'destinatario': correo,
        'asunto': 'Credenciales Jefe de TI - $nombreEmpresa',
        'tipo_correo': 'credenciales_ti',
        'estado_smtp': 'cola_local_auditable',
        'cuerpo_html':
            '<p>Empresa <b>$nombreEmpresa</b> activada (Plan ${planCfg['nombre']}).<br>Jefe de TI: <b>$jefeCorreo</b><br>Password temporal: <b>$tempPass</b></p>',
        'fecha_envio': DateTime.now().toIso8601String(),
      });

      tempMfaToken = 'demo_temp_mfa_reg';
      currentUser = {
        'id_usuario': 99,
        'id_empresa': newIdEmp,
        'nombre': '$nombre $apellidos',
        'correo': correo,
        'rol': 'representante',
        'id_rol': 3,
        'empresa': nombreEmpresa,
        'slug_dominio': dominio,
        'empresa_info': Map<String, dynamic>.from(_demoEmpresa),
      };

      final jefeCreds = {
        'id_usuario': 100,
        'correo_institucional': jefeCorreo,
        'password_temporal': tempPass,
        'rol': 'jefe_ti',
        'mfa_secret': 'JBSWY3DPEHPK3PXP',
      };

      return {
        'ok': true,
        'status': 'MFA_REQUIRED',
        'temp_token': tempMfaToken,
        'codigo_totp_actual_para_pruebas_postman': '482910',
        'mfa_secret': 'JBSWY3DPEHPK3PXP',
        'empresa': {
          'id_empresa': newIdEmp,
          'nombre_empresa': nombreEmpresa,
          'dominio_institucional': dominio,
          'plan': plan,
          'correo_enviado_a': correo,
        },
        'jefe_ti_credenciales': jefeCreds,
        'usuario': currentUser,
      };
    }
  }

  /// Alta de nueva empresa desde una sesión autenticada (Tab Empresa)
  static Future<Map<String, dynamic>> registrarNuevaEmpresa({
    required String nombreEmpresa,
    required String rfcNit,
    required String slugDominio,
    required String sector,
    required String telefono,
    String plan = 'corporate',
  }) async {
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl?endpoint=companies'),
            headers: _authHeaders(),
            body: jsonEncode({
              'nombre_empresa': nombreEmpresa,
              'rfc_nit': rfcNit,
              'slug_dominio': slugDominio,
              'sector': sector,
              'telefono': telefono,
              'plan_suscripcion': plan,
            }),
          )
          .timeout(const Duration(seconds: 6));

      final data = jsonDecode(res.body) as Map<String, dynamic>;
      if (data['ok'] == true) {
        if (data['token'] != null) {
          jwtToken = data['token'].toString();
        }
        currentUser['id_empresa'] = data['id_empresa'];
        currentUser['empresa'] = data['nombre_empresa'] ?? nombreEmpresa;
        currentUser['empresa_info'] = {
          'id_empresa': data['id_empresa'],
          'nombre_empresa': data['nombre_empresa'] ?? nombreEmpresa,
          'slug_dominio': data['dominio_institucional'],
          'plan_suscripcion': data['plan'] ?? plan,
        };
      }
      return data;
    } catch (_) {
      final slug = _sanitizeSlug(slugDominio);
      final dominio = '$slug.sysmonitor.com';
      final planCfg = b2bPlans[plan] ?? b2bPlans['corporate']!;
      final newIdEmp = (_demoEmpresa['id_empresa'] as int? ?? 1) + 1;
      final jefeCorreo = 'jefe.ti@$dominio';
      final tempPass = 'SysJef7392!';

      _demoEmpresa = {
        'id_empresa': newIdEmp,
        'nombre_empresa': nombreEmpresa,
        'rfc_nit': rfcNit.toUpperCase(),
        'slug_dominio': dominio,
        'sector': sector,
        'telefono_contacto': telefono,
        'plan_suscripcion': plan,
        'max_racks': planCfg['max_racks'],
        'max_cuentas': planCfg['max_cuentas'],
        'costo_mensual': planCfg['precio'],
        'estado': 'aprobada',
      };
      currentUser['id_empresa'] = newIdEmp;
      currentUser['empresa'] = nombreEmpresa;
      currentUser['empresa_info'] = Map<String, dynamic>.from(_demoEmpresa);

      _demoCorreos.insert(0, {
        'id_correo': _demoCorreos.length + 1,
        'destinatario': currentUser['correo'] ?? 'representante@empresa.com',
        'asunto': 'Credenciales Jefe de TI - $nombreEmpresa',
        'tipo_correo': 'credenciales_ti',
        'estado_smtp': 'cola_local_auditable',
        'cuerpo_html':
            '<p>Alta B2B completada: <b>$jefeCorreo</b> / Password: <b>$tempPass</b></p>',
        'fecha_envio': DateTime.now().toIso8601String(),
      });

      return {
        'ok': true,
        'message': 'Empresa registrada y activada en modo local.',
        'id_empresa': newIdEmp,
        'nombre_empresa': nombreEmpresa,
        'dominio_institucional': dominio,
        'plan': plan,
        'correo_enviado_a': currentUser['correo'] ?? '',
        'jefe_ti_credenciales': {
          'correo_institucional': jefeCorreo,
          'password_temporal': tempPass,
          'rol': 'jefe_ti',
          'mfa_secret': 'JBSWY3DPEHPK3PXP',
        },
        'uso_plan': _buildDemoUsoPlan(),
      };
    }
  }

  /// Obtiene empresa actual, uso del plan B2B, solicitudes y correos SMTP enviados
  static Future<Map<String, dynamic>> obtenerResumenEmpresa() async {
    try {
      final res = await http
          .get(
            Uri.parse('$baseUrl?endpoint=companies&mine=1'),
            headers: _authHeaders(),
          )
          .timeout(const Duration(seconds: 4));

      final data = jsonDecode(res.body) as Map<String, dynamic>;
      if (data['ok'] == true) {
        if (data['empresa'] is Map) {
          currentUser['empresa_info'] =
              Map<String, dynamic>.from(data['empresa'] as Map);
          currentUser['empresa'] =
              (data['empresa'] as Map)['nombre_empresa'] ?? currentUser['empresa'];
        }
        return data;
      }
    } catch (_) {}

    return {
      'ok': true,
      'empresa': Map<String, dynamic>.from(_demoEmpresa),
      'uso_plan': _buildDemoUsoPlan(),
      'solicitudes': List<Map<String, dynamic>>.from(_demoSolicitudes),
      'correos_enviados': List<Map<String, dynamic>>.from(_demoCorreos),
      'planes_disponibles': b2bPlans,
    };
  }

  /// Actualiza el plan B2B (starter, corporate, enterprise)
  static Future<Map<String, dynamic>> cambiarPlanB2B(String nuevoPlan) async {
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl?endpoint=companies/upgrade'),
            headers: _authHeaders(),
            body: jsonEncode({'plan_suscripcion': nuevoPlan}),
          )
          .timeout(const Duration(seconds: 4));

      return jsonDecode(res.body) as Map<String, dynamic>;
    } catch (_) {
      final planCfg = b2bPlans[nuevoPlan];
      if (planCfg == null) {
        return {'ok': false, 'error': 'Plan B2B no válido.'};
      }
      _demoEmpresa['plan_suscripcion'] = nuevoPlan;
      _demoEmpresa['max_racks'] = planCfg['max_racks'];
      _demoEmpresa['max_cuentas'] = planCfg['max_cuentas'];
      _demoEmpresa['costo_mensual'] = planCfg['precio'];
      return {
        'ok': true,
        'message': 'Plan B2B actualizado a ${planCfg['nombre']}.',
        'plan_suscripcion': nuevoPlan,
        'uso_plan': _buildDemoUsoPlan(),
      };
    }
  }

  /// Solicita un nuevo correo institucional (@empresa.sysmonitor.com)
  static Future<Map<String, dynamic>> solicitarCorreoInstitucional({
    required String nombre,
    required String apellidos,
    required String rolSolicitado,
  }) async {
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl?endpoint=companies/provision-email'),
            headers: _authHeaders(),
            body: jsonEncode({
              'nombre': nombre,
              'apellidos': apellidos,
              'rol_solicitado': rolSolicitado,
            }),
          )
          .timeout(const Duration(seconds: 4));

      return jsonDecode(res.body) as Map<String, dynamic>;
    } catch (_) {
      final uso = _buildDemoUsoPlan();
      final usadas = (uso['cuentas_usadas'] as int?) ?? 0;
      final maxCuentas = (uso['max_cuentas'] as int?) ?? 15;
      if (usadas >= maxCuentas) {
        return {
          'ok': false,
          'error':
              'Límite de cuentas ($maxCuentas) alcanzado para tu plan actual. Mejora tu plan B2B.',
        };
      }
      final slugDom =
          (_demoEmpresa['slug_dominio'] ?? 'empresa.sysmonitor.com').toString();
      final userPart =
          '${nombre.trim().split(' ').first.toLowerCase()}.${apellidos.trim().split(' ').first.toLowerCase()}'
              .replaceAll(RegExp(r'[^a-z0-9\.]'), '');
      _demoSolicitudes.insert(0, {
        'id_solicitud': _demoSolicitudes.length + 1,
        'nombre': nombre,
        'apellidos': apellidos,
        'rol_solicitado': rolSolicitado,
        'correo_asignado': '$userPart@$slugDom',
        'estado': 'pendiente',
        'fecha_solicitud': DateTime.now().toIso8601String(),
      });
      return {
        'ok': true,
        'message': 'Solicitud de correo institucional registrada correctamente.',
        'solicitudes': List<Map<String, dynamic>>.from(_demoSolicitudes),
        'correos_enviados': List<Map<String, dynamic>>.from(_demoCorreos),
        'uso_plan': _buildDemoUsoPlan(),
      };
    }
  }

  /// Obtiene los Racks y sus 4 sensores IoT desde la API REST
  static Future<List<Map<String, dynamic>>> obtenerRacks() async {
    try {
      final res = await http
          .get(
            Uri.parse('$baseUrl?endpoint=racks'),
            headers: _authHeaders(),
          )
          .timeout(const Duration(seconds: 4));

      final data = jsonDecode(res.body) as Map<String, dynamic>;
      if (data['ok'] == true && data['racks'] is List) {
        return List<Map<String, dynamic>>.from(data['racks']);
      }
    } catch (_) {}

    return _demoRacks
        .map((r) => {
              ...r,
              'lectura': Map<String, dynamic>.from(
                  (r['lectura'] as Map<String, dynamic>?) ?? {}),
            })
        .toList();
  }

  /// Crea un nuevo Rack IoT o actualiza los umbrales de alerta de un Rack existente
  static Future<Map<String, dynamic>> crearOActualizarRack({
    int? idRack,
    String? codigoRack,
    required String nombreRack,
    required String ubicacion,
    double tempMax = 27.0,
    double humMax = 60.0,
    double kwMax = 2.5,
  }) async {
    try {
      final payload = <String, dynamic>{
        'nombre_rack': nombreRack,
        'ubicacion': ubicacion,
        'temp_max': tempMax,
        'hum_max': humMax,
        'kw_max': kwMax,
      };
      if (idRack != null) payload['id_rack'] = idRack;
      if (codigoRack != null && codigoRack.trim().isNotEmpty) {
        payload['codigo_rack'] = codigoRack.trim().toUpperCase();
      }

      final res = await http
          .post(
            Uri.parse('$baseUrl?endpoint=racks'),
            headers: _authHeaders(),
            body: jsonEncode(payload),
          )
          .timeout(const Duration(seconds: 4));

      return jsonDecode(res.body) as Map<String, dynamic>;
    } catch (_) {
      if (idRack != null) {
        final idx = _demoRacks.indexWhere((r) => r['id_rack'] == idRack);
        if (idx >= 0) {
          _demoRacks[idx]['nombre_rack'] = nombreRack;
          _demoRacks[idx]['ubicacion'] = ubicacion;
          _demoRacks[idx]['temp_max'] = tempMax;
          _demoRacks[idx]['hum_max'] = humMax;
          _demoRacks[idx]['kw_max'] = kwMax;
          return {
            'ok': true,
            'message': 'Umbrales del rack actualizados correctamente.',
          };
        }
      }

      final uso = _buildDemoUsoPlan();
      final maxRacks = (uso['max_racks'] as int?) ?? 10;
      if (_demoRacks.length >= maxRacks) {
        return {
          'ok': false,
          'error':
              'Límite de racks ($maxRacks) alcanzado para tu plan actual. Mejora tu plan B2B.',
        };
      }

      final newId = _demoRacks.isEmpty
          ? 1
          : _demoRacks
                  .map((r) => (r['id_rack'] as int?) ?? 0)
                  .reduce((a, b) => a > b ? a : b) +
              1;
      final code = (codigoRack != null && codigoRack.trim().isNotEmpty)
          ? codigoRack.trim().toUpperCase()
          : 'RACK-0$newId';
      _demoRacks.add({
        'id_rack': newId,
        'codigo_rack': code,
        'nombre_rack': nombreRack,
        'ubicacion': ubicacion,
        'estado': 'online',
        'temp_max': tempMax,
        'hum_max': humMax,
        'kw_max': kwMax,
        'lectura': {
          'temperatura': 22.0,
          'humedad': 44.0,
          'consumo_kw': 1.05,
          'puerta_abierta': 0,
        },
      });
      return {
        'ok': true,
        'id_rack': newId,
        'message': 'Nuevo Rack IoT registrado.',
      };
    }
  }

  /// Simula / ingesta una lectura de telemetría ESP32 para un Rack
  static Future<Map<String, dynamic>> simularTelemetria({
    required int idRack,
    required double temperatura,
    required double humedad,
    required double consumoKw,
    required int puertaAbierta,
  }) async {
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl?endpoint=telemetry'),
            headers: _authHeaders(),
            body: jsonEncode({
              'id_rack': idRack,
              'temperatura': temperatura,
              'humedad': humedad,
              'consumo_kw': consumoKw,
              'puerta_abierta': puertaAbierta,
            }),
          )
          .timeout(const Duration(seconds: 4));

      return jsonDecode(res.body) as Map<String, dynamic>;
    } catch (_) {
      final idx = _demoRacks.indexWhere((r) => r['id_rack'] == idRack);
      if (idx < 0) {
        return {'ok': false, 'error': 'El Rack indicado no existe.'};
      }
      final rack = _demoRacks[idx];
      rack['lectura'] = {
        'temperatura': temperatura,
        'humedad': humedad,
        'consumo_kw': consumoKw,
        'puerta_abierta': puertaAbierta,
      };

      String estado = 'online';
      final disparadas = <String>[];
      final tMax = (rack['temp_max'] as num?)?.toDouble() ?? 27.0;
      final hMax = (rack['hum_max'] as num?)?.toDouble() ?? 60.0;
      final kMax = (rack['kw_max'] as num?)?.toDouble() ?? 2.5;

      void addAlert(String tipo, String msg, String nivel) {
        disparadas.add(msg);
        _demoAlertas.insert(0, {
          'id_alerta': _demoAlertas.length + 10,
          'id_rack': idRack,
          'codigo_rack': rack['codigo_rack'],
          'nombre_rack': rack['nombre_rack'],
          'tipo_alerta': tipo,
          'mensaje': msg,
          'nivel': nivel,
          'resuelta': 0,
          'fecha_alerta': 'Justo ahora',
        });
      }

      if (puertaAbierta == 1) {
        estado = 'critical';
        addAlert(
          'Apertura Física de Rack',
          'Puerta física ABIERTA en ${rack['nombre_rack']}',
          'critical',
        );
      }
      if (temperatura > tMax) {
        final crit = temperatura > tMax + 3;
        if (estado != 'critical') estado = crit ? 'critical' : 'warning';
        addAlert(
          'Sobrecalentamiento Térmico',
          'Temperatura elevada en ${rack['nombre_rack']}: $temperatura °C (Máx: $tMax °C)',
          crit ? 'critical' : 'warning',
        );
      }
      if (humedad > hMax) {
        if (estado == 'online') estado = 'warning';
        addAlert(
          'Humedad Crítica',
          'Humedad elevada en ${rack['nombre_rack']}: $humedad% (Máx: $hMax%)',
          'warning',
        );
      }
      if (consumoKw > kMax) {
        if (estado == 'online') estado = 'warning';
        addAlert(
          'Exceso de Consumo Eléctrico',
          'Sobrecarga en ${rack['nombre_rack']}: $consumoKw kW (Máx: $kMax kW)',
          'warning',
        );
      }
      rack['estado'] = estado;

      return {
        'ok': true,
        'message': 'Lectura de telemetría IoT almacenada y evaluada.',
        'resultado': {
          'id_rack': idRack,
          'estado_rack': estado,
          'alertas_disparadas': disparadas,
        },
      };
    }
  }

  /// Obtiene las alertas IoT activas e históricas
  static Future<List<Map<String, dynamic>>> obtenerAlertas() async {
    try {
      final res = await http
          .get(
            Uri.parse('$baseUrl?endpoint=alerts'),
            headers: _authHeaders(),
          )
          .timeout(const Duration(seconds: 4));

      final data = jsonDecode(res.body) as Map<String, dynamic>;
      if (data['ok'] == true && data['alertas'] is List) {
        return List<Map<String, dynamic>>.from(data['alertas']);
      }
    } catch (_) {}

    return List<Map<String, dynamic>>.from(_demoAlertas);
  }

  /// Marca una alerta como resuelta
  static Future<Map<String, dynamic>> resolverAlerta(int idAlerta) async {
    try {
      final res = await http
          .post(
            Uri.parse('$baseUrl?endpoint=alerts/resolve'),
            headers: _authHeaders(),
            body: jsonEncode({'id_alerta': idAlerta}),
          )
          .timeout(const Duration(seconds: 4));

      return jsonDecode(res.body) as Map<String, dynamic>;
    } catch (_) {
      final idx = _demoAlertas.indexWhere((a) => a['id_alerta'] == idAlerta);
      if (idx >= 0) {
        _demoAlertas[idx]['resuelta'] = 1;
      }
      return {'ok': true, 'message': 'Alerta marcada como resuelta.'};
    }
  }
}
