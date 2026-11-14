import 'package:flutter/material.dart';

/// PANTALLA 1: Réplica fiel de "SysMonitor - Pantalla de Inicio HeroUI" (Google Stitch Imagen 1)
class WelcomeScreen extends StatelessWidget {
  final VoidCallback onToggleTheme;

  const WelcomeScreen({super.key, required this.onToggleTheme});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bgColor = isDark ? const Color(0xFF090D16) : const Color(0xFFFFFFFF);
    final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final subtitleColor =
        isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final primaryBlue = const Color(0xFF1D58E8);

    return Scaffold(
      backgroundColor: bgColor,
      body: Stack(
        children: [
          // Resplandor radial azul suave al centro (Estilo HeroUI / Aceternity)
          Align(
            alignment: const Alignment(0, -0.2),
            child: Container(
              width: 320,
              height: 320,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    primaryBlue.withValues(alpha: isDark ? 0.22 : 0.10),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),

          // Botón superior discreto para alternar Modo Claro / Oscuro
          SafeArea(
            child: Align(
              alignment: Alignment.topRight,
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: IconButton(
                  onPressed: onToggleTheme,
                  icon: Icon(
                    isDark ? Icons.wb_sunny_rounded : Icons.dark_mode_rounded,
                    color: primaryBlue,
                  ),
                  tooltip: 'Cambiar tema',
                ),
              ),
            ),
          ),

          // Contenido central idéntico a la Imagen 1 de Google Stitch
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 32.0),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Spacer(flex: 3),

                  // Squircle blanco con sombra azul suave e ícono de pulso
                  Container(
                    width: 118,
                    height: 118,
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF111827) : Colors.white,
                      borderRadius: BorderRadius.circular(32),
                      border: Border.all(
                        color: primaryBlue.withValues(alpha: 0.12),
                        width: 1.5,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: primaryBlue.withValues(alpha: 0.16),
                          blurRadius: 36,
                          offset: const Offset(0, 14),
                        ),
                      ],
                    ),
                    child: Center(
                      child: Container(
                        width: 58,
                        height: 58,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: primaryBlue.withValues(alpha: 0.35),
                            width: 1.5,
                          ),
                        ),
                        child: Icon(
                          Icons.monitor_heart_outlined,
                          color: primaryBlue,
                          size: 32,
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(height: 36),

                  // Título principal SysMonitor
                  Text(
                    'SysMonitor',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: textColor,
                      fontSize: 38,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.8,
                    ),
                  ),

                  const SizedBox(height: 10),

                  // Subtítulo
                  Text(
                    'Vigilancia segura y eficiente',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: subtitleColor,
                      fontSize: 16,
                      fontWeight: FontWeight.w400,
                    ),
                  ),

                  const SizedBox(height: 42),

                  // Botón píldora azul "Comencemos ->"
                  SizedBox(
                    width: double.infinity,
                    height: 56,
                    child: ElevatedButton(
                      onPressed: () =>
                          Navigator.pushNamed(context, '/login'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: primaryBlue,
                        foregroundColor: Colors.white,
                        elevation: 8,
                        shadowColor: primaryBlue.withValues(alpha: 0.45),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(999),
                        ),
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            'Comencemos',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          SizedBox(width: 10),
                          Icon(Icons.arrow_forward_rounded, size: 20),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 22),

                  // Sello de conexión cifrada
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.verified_user_outlined,
                        size: 16,
                        color: primaryBlue.withValues(alpha: 0.8),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        'Conexión cifrada de extremo a extremo',
                        style: TextStyle(
                          color: subtitleColor,
                          fontSize: 12.5,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),

                  const Spacer(flex: 4),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
