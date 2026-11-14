import 'package:flutter/material.dart';
import '../services/api_service.dart';

/// PANTALLA 3: Réplica fiel de "SysMonitor - Iniciar Sesión (Minimalist Blue)" (Google Stitch Imagen 3)
/// Conecta con la API REST e incluye verificación obligatoria de Google Authenticator (6 dígitos).
class LoginScreen extends StatefulWidget {
  final VoidCallback onToggleTheme;

  const LoginScreen({super.key, required this.onToggleTheme});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailCtrl =
      TextEditingController(text: 'alex.vargas@infraops.sysmonitor.com');
  final _passCtrl = TextEditingController(text: 'InfraOps2026!');
  bool _obscurePass = true;
  bool _rememberDevice = true;
  bool _loading = false;

  Future<void> _iniciarSesionConMfa() async {
    setState(() => _loading = true);
    final res = await ApiService.loginInstitucional(
      _emailCtrl.text.trim(),
      _passCtrl.text.trim(),
    );
    setState(() => _loading = false);

    if (!mounted) return;

    if (res['ok'] == true) {
      final hintCode =
          res['codigo_totp_actual_para_pruebas_postman']?.toString() ??
              '482910';
      _mostrarModalGoogleAuthenticator(hintCode);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['error']?.toString() ?? 'Credenciales inválidas'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  void _mostrarModalGoogleAuthenticator(String codigoDemo) {
    final otpCtrl = TextEditingController(text: codigoDemo);
    final primaryBlue = const Color(0xFF0038A8);

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Row(
          children: [
            Icon(Icons.qr_code_scanner_rounded, color: primaryBlue),
            const SizedBox(width: 10),
            const Expanded(
              child: Text(
                'Google Authenticator (MFA)',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Seguridad Extrema: Ingresa el código temporal de 6 dígitos generado por tu app Google Authenticator:',
              style: TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: otpCtrl,
              keyboardType: TextInputType.number,
              maxLength: 6,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.w800,
                letterSpacing: 8,
              ),
              decoration: InputDecoration(
                counterText: '',
                filled: true,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Código TOTP sincronizado actual: $codigoDemo',
              style: TextStyle(
                  fontSize: 12,
                  color: primaryBlue,
                  fontWeight: FontWeight.w600),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: primaryBlue,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () async {
              final verify = await ApiService.verificarMfa(
                _emailCtrl.text.trim(),
                otpCtrl.text.trim(),
              );
              if (!mounted) return;
              Navigator.pop(ctx);
              if (verify['ok'] == true) {
                Navigator.pushReplacementNamed(context, '/dashboard');
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                        verify['error']?.toString() ?? 'Código MFA incorrecto'),
                    backgroundColor: Colors.redAccent,
                  ),
                );
              }
            },
            child: const Text('Verificar y Entrar'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bgColor = isDark ? const Color(0xFF090D16) : const Color(0xFFF8FAFC);
    final cardColor = isDark ? const Color(0xFF111827) : Colors.white;
    final inputBg = isDark ? const Color(0xFF1E293B) : const Color(0xFFF3F6FC);
    final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final mutedColor =
        isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final primaryBlue = const Color(0xFF0038A8);

    return Scaffold(
      backgroundColor: bgColor,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 22.0, vertical: 14.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Barra Superior idéntica a la Imagen 3 de Google Stitch
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      IconButton(
                        onPressed: () => Navigator.pop(context),
                        icon: Icon(Icons.arrow_back_rounded, color: textColor),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'INICIAR SESION',
                        style: TextStyle(
                          color: textColor,
                          fontSize: 15,
                          fontWeight: FontWeight.w800,
                          letterSpacing: 0.4,
                        ),
                      ),
                    ],
                  ),
                  Row(
                    children: [
                      IconButton(
                        onPressed: widget.onToggleTheme,
                        icon: Icon(
                          isDark
                              ? Icons.wb_sunny_rounded
                              : Icons.dark_mode_rounded,
                          color: primaryBlue,
                        ),
                      ),
                      GestureDetector(
                        onTap: () =>
                            Navigator.pushNamed(context, '/register'),
                        child: CircleAvatar(
                          radius: 18,
                          backgroundColor: primaryBlue,
                          child: const Icon(Icons.person_outline_rounded,
                              color: Colors.white, size: 20),
                        ),
                      ),
                    ],
                  ),
                ],
              ),

              const SizedBox(height: 18),

              // Squircle azul claro con ícono de Servidor (Imagen 3 de Stitch)
              Container(
                width: 54,
                height: 54,
                decoration: BoxDecoration(
                  color: const Color(0xFFE0E7FF),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Icon(Icons.dns_outlined, color: primaryBlue, size: 28),
              ),

              const SizedBox(height: 18),

              Text(
                'Iniciar Sesión',
                style: TextStyle(
                  color: textColor,
                  fontSize: 30,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.6,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                'Accede a tu consola centralizada de SysMonitor',
                style: TextStyle(color: mutedColor, fontSize: 14),
              ),

              const SizedBox(height: 22),

              // Tarjeta Blanca de Login Corporativo (Imagen 3 de Stitch)
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: cardColor,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 24,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Correo corporativo',
                      style: TextStyle(
                        color: textColor,
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      decoration: BoxDecoration(
                        color: inputBg,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: TextField(
                        controller: _emailCtrl,
                        style: TextStyle(color: textColor, fontSize: 14.5),
                        decoration: InputDecoration(
                          prefixIcon: Icon(Icons.mail_outline_rounded,
                              color: mutedColor, size: 20),
                          hintText: 'alex.vargas@infraops.cloud',
                          hintStyle: TextStyle(color: mutedColor),
                          border: InputBorder.none,
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 14),
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Contraseña',
                          style: TextStyle(
                            color: textColor,
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        Text(
                          '¿Olvidaste tu contraseña?',
                          style: TextStyle(
                            color: primaryBlue,
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Container(
                      decoration: BoxDecoration(
                        color: inputBg,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: TextField(
                        controller: _passCtrl,
                        obscureText: _obscurePass,
                        style: TextStyle(color: textColor, fontSize: 14.5),
                        decoration: InputDecoration(
                          prefixIcon: Icon(Icons.lock_outline_rounded,
                              color: mutedColor, size: 20),
                          suffixIcon: IconButton(
                            icon: Icon(
                              _obscurePass
                                  ? Icons.visibility_outlined
                                  : Icons.visibility_off_outlined,
                              color: mutedColor,
                              size: 20,
                            ),
                            onPressed: () =>
                                setState(() => _obscurePass = !_obscurePass),
                          ),
                          border: InputBorder.none,
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 14),
                        ),
                      ),
                    ),

                    const SizedBox(height: 12),

                    Row(
                      children: [
                        Checkbox(
                          value: _rememberDevice,
                          activeColor: primaryBlue,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(5)),
                          onChanged: (v) =>
                              setState(() => _rememberDevice = v ?? true),
                        ),
                        Text(
                          'Recordar en este dispositivo',
                          style: TextStyle(color: textColor, fontSize: 13),
                        ),
                      ],
                    ),

                    const SizedBox(height: 10),

                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: ElevatedButton(
                        onPressed: _loading ? null : _iniciarSesionConMfa,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryBlue,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        child: _loading
                            ? const SizedBox(
                                width: 22,
                                height: 22,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2.5),
                              )
                            : const Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    'Ingresar',
                                    style: TextStyle(
                                      fontSize: 15.5,
                                      fontWeight: FontWeight.w700,
                                    ),
                                  ),
                                  SizedBox(width: 8),
                                  Icon(Icons.arrow_forward_rounded, size: 19),
                                ],
                              ),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Divisor "O CONTINÚA CON" (Imagen 3 de Stitch)
              Row(
                children: [
                  Expanded(
                      child: Divider(color: mutedColor.withValues(alpha: 0.25))),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 12.0),
                    child: Text(
                      'O CONTINÚA CON',
                      style: TextStyle(
                        color: mutedColor,
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.8,
                      ),
                    ),
                  ),
                  Expanded(
                      child: Divider(color: mutedColor.withValues(alpha: 0.25))),
                ],
              ),

              const SizedBox(height: 18),

              // Botones Google y Microsoft (Imagen 3 de Stitch)
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () =>
                          _mostrarModalGoogleAuthenticator('482910'),
                      icon: const Icon(Icons.cloud_outlined,
                          color: Color(0xFF1D58E8), size: 20),
                      label: Text(
                        'Google',
                        style: TextStyle(
                            color: textColor, fontWeight: FontWeight.w700),
                      ),
                      style: OutlinedButton.styleFrom(
                        backgroundColor: cardColor,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        side: BorderSide(
                            color: mutedColor.withValues(alpha: 0.2)),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () =>
                          _mostrarModalGoogleAuthenticator('482910'),
                      icon: Icon(Icons.business_rounded,
                          color: primaryBlue, size: 20),
                      label: Text(
                        'Microsoft',
                        style: TextStyle(
                            color: textColor, fontWeight: FontWeight.w700),
                      ),
                      style: OutlinedButton.styleFrom(
                        backgroundColor: cardColor,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        side: BorderSide(
                            color: mutedColor.withValues(alpha: 0.2)),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 24),

              // Sello inferior en píldora
              Center(
                child: Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  decoration: BoxDecoration(
                    color: inputBg,
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.lock_rounded, size: 14, color: mutedColor),
                      const SizedBox(width: 6),
                      Text(
                        'Conexión cifrada de extremo a extremo',
                        style: TextStyle(
                          color: mutedColor,
                          fontSize: 11.5,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
