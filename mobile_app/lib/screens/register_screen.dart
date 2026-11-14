import 'package:flutter/material.dart';

/// PANTALLA 2: Réplica fiel de "SysMonitor - Crea tu cuenta (Minimalist Blue)" (Google Stitch Imagen 2)
/// Adaptada al registro de Representantes de Empresa (OAuth Google/Microsoft + Alta Corporativa).
class RegisterScreen extends StatefulWidget {
  final VoidCallback onToggleTheme;

  const RegisterScreen({super.key, required this.onToggleTheme});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();
  bool _obscurePass = true;
  bool _acceptedTerms = true;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bgColor = isDark ? const Color(0xFF090D16) : const Color(0xFFF8FAFC);
    final cardColor = isDark ? const Color(0xFF111827) : Colors.white;
    final inputBg = isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9);
    final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final mutedColor =
        isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final primaryBlue = const Color(0xFF1D58E8);

    return Scaffold(
      backgroundColor: bgColor,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 22.0, vertical: 16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Barra superior (Píldora SysMonitor + Botón Candado/Tema)
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  GestureDetector(
                    onTap: () => Navigator.pop(context),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 8),
                      decoration: BoxDecoration(
                        color: cardColor,
                        borderRadius: BorderRadius.circular(999),
                        border: Border.all(
                            color: primaryBlue.withValues(alpha: 0.15)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.monitor_heart_outlined,
                              color: primaryBlue, size: 18),
                          const SizedBox(width: 6),
                          Text(
                            'SysMonitor',
                            style: TextStyle(
                              color: textColor,
                              fontWeight: FontWeight.w800,
                              fontSize: 13.5,
                            ),
                          ),
                        ],
                      ),
                    ),
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
                          size: 20,
                        ),
                      ),
                      Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: primaryBlue.withValues(alpha: 0.10),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(Icons.lock_outline_rounded,
                            color: primaryBlue, size: 20),
                      ),
                    ],
                  ),
                ],
              ),

              const SizedBox(height: 22),

              Text(
                'Crea tu cuenta',
                style: TextStyle(
                  color: textColor,
                  fontSize: 28,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                'Registra tu empresa y solicita cuentas para tu equipo',
                style: TextStyle(color: mutedColor, fontSize: 13),
              ),

              const SizedBox(height: 18),

              // Tarjeta Principal de Formulario (Idéntica a Imagen 2 de Stitch)
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: cardColor,
                  borderRadius: BorderRadius.circular(24),
                  border:
                      Border.all(color: primaryBlue.withValues(alpha: 0.10)),
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
                    _buildLabel('NOMBRE COMPLETO', mutedColor),
                    _buildInput(
                      controller: _nameCtrl,
                      hint: 'Nombre y apellidos',
                      icon: Icons.person_outline_rounded,
                      inputBg: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                    ),
                    const SizedBox(height: 16),

                    _buildLabel(
                        'CORREO CORPORATIVO', mutedColor),
                    _buildInput(
                      controller: _emailCtrl,
                      hint: 'correo@empresa.com',
                      icon: Icons.mail_outline_rounded,
                      inputBg: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                    ),
                    const SizedBox(height: 16),

                    _buildLabel('CONTRASEÑA', mutedColor),
                    _buildInput(
                      controller: _passCtrl,
                      hint: 'Mínimo 8 caracteres',
                      icon: Icons.lock_outline_rounded,
                      inputBg: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                      obscure: _obscurePass,
                      suffix: IconButton(
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
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Debe contener al menos 8 caracteres y un número',
                      style: TextStyle(color: mutedColor, fontSize: 11.5),
                    ),
                    const SizedBox(height: 14),

                    _buildLabel('CONFIRMAR CONTRASEÑA', mutedColor),
                    _buildInput(
                      controller: _confirmCtrl,
                      hint: 'Repite la contraseña',
                      icon: Icons.verified_user_outlined,
                      inputBg: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                      obscure: true,
                    ),
                    const SizedBox(height: 14),

                    Row(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Checkbox(
                          value: _acceptedTerms,
                          activeColor: primaryBlue,
                          onChanged: (v) =>
                              setState(() => _acceptedTerms = v ?? true),
                        ),
                        Expanded(
                          child: Text(
                            'Acepto los Términos y Condiciones y la Política de Privacidad de SysMonitor.',
                            style: TextStyle(color: mutedColor, fontSize: 12),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: ElevatedButton(
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text(
                                  'Cuenta de Representante lista. Vincula Google Authenticator en el login.'),
                            ),
                          );
                          Navigator.pushReplacementNamed(context, '/login');
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryBlue,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        child: const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              'Crear Cuenta',
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

              const SizedBox(height: 20),

              Center(
                child: GestureDetector(
                  onTap: () =>
                      Navigator.pushReplacementNamed(context, '/login'),
                  child: RichText(
                    text: TextSpan(
                      style: TextStyle(color: mutedColor, fontSize: 13.5),
                      children: [
                        const TextSpan(text: '¿Ya tienes cuenta? '),
                        TextSpan(
                          text: 'Inicia sesión',
                          style: TextStyle(
                            color: primaryBlue,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLabel(String text, Color color) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6.0),
      child: Text(
        text,
        style: TextStyle(
          color: color,
          fontSize: 11,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.6,
        ),
      ),
    );
  }

  Widget _buildInput({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    required Color inputBg,
    required Color textColor,
    required Color mutedColor,
    bool obscure = false,
    Widget? suffix,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: inputBg,
        borderRadius: BorderRadius.circular(14),
      ),
      child: TextField(
        controller: controller,
        obscureText: obscure,
        style: TextStyle(color: textColor, fontSize: 14.5),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: TextStyle(color: mutedColor.withValues(alpha: 0.7)),
          prefixIcon: Icon(icon, color: mutedColor, size: 20),
          suffixIcon: suffix,
          border: InputBorder.none,
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        ),
      ),
    );
  }
}
