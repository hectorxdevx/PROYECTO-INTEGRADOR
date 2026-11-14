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

  static String? jwtToken;
  static String? tempMfaToken;
  static Map<String, dynamic> currentUser = {
    'nombre': 'Alex Vargas',
    'correo': 'alex.vargas@infraops.sysmonitor.com',
    'rol': 'jefe_ti',
    'id_rol': 4,
    'empresa': 'InfraOps Cloud & DataCenter',
  };

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
        if (data['usuario'] is Map<String, dynamic>) {
          currentUser = data['usuario'];
        }
      }
      return data;
    } catch (_) {
      // Modo offline/demo resiliente si el emulador no alcanza XAMPP
      currentUser = {
        'nombre': correo.contains('elena') ? 'Elena Torres' : 'Alex Vargas',
        'correo': correo,
        'rol': correo.contains('elena') ? 'operador' : 'jefe_ti',
        'id_rol': correo.contains('elena') ? 1 : 4,
        'empresa': 'InfraOps Cloud & DataCenter',
      };
      return {
        'ok': true,
        'status': 'MFA_REQUIRED',
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
        if (data['usuario'] is Map<String, dynamic>) {
          currentUser = data['usuario'];
        }
      }
      return data;
    } catch (_) {
      if (codigo6Digitos.length == 6) {
        jwtToken = 'demo_jwt_token_sysmonitor';
        return {'ok': true, 'token': jwtToken, 'usuario': currentUser};
      }
      return {'ok': false, 'error': 'Ingresa un código válido de 6 dígitos.'};
    }
  }

  /// Obtiene los Racks y sus 4 sensores IoT desde la API REST
  static Future<List<Map<String, dynamic>>> obtenerRacks() async {
    try {
      final res = await http.get(
        Uri.parse('$baseUrl?endpoint=racks'),
        headers: {'Authorization': 'Bearer ${jwtToken ?? ""}'},
      ).timeout(const Duration(seconds: 4));

      final data = jsonDecode(res.body) as Map<String, dynamic>;
      if (data['ok'] == true && data['racks'] is List) {
        return List<Map<String, dynamic>>.from(data['racks']);
      }
    } catch (_) {}

    // Datos de respaldo sincronizados con el esquema MariaDB
    return [
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
  }
}
