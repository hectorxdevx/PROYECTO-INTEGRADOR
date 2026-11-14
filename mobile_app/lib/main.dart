import 'package:flutter/material.dart';
import 'screens/welcome_screen.dart';
import 'screens/login_screen.dart';
import 'screens/register_screen.dart';
import 'screens/dashboard_screen.dart';

void main() {
  runApp(const SysMonitorApp());
}

class SysMonitorApp extends StatefulWidget {
  const SysMonitorApp({super.key});

  @override
  State<SysMonitorApp> createState() => _SysMonitorAppState();
}

class _SysMonitorAppState extends State<SysMonitorApp> {
  ThemeMode _themeMode = ThemeMode.light;

  void _toggleTheme() {
    setState(() {
      _themeMode =
          _themeMode == ThemeMode.light ? ThemeMode.dark : ThemeMode.light;
    });
  }

  @override
  Widget build(BuildContext context) {
    // Paleta Blanco y Azul (Minimalist Blue + Aceternity UI)
    final lightTheme = ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      scaffoldBackgroundColor: const Color(0xFFFFFFFF),
      primaryColor: const Color(0xFF1D4ED8),
      colorScheme: const ColorScheme.light(
        primary: Color(0xFF1D4ED8),
        secondary: Color(0xFF2563EB),
        surface: Color(0xFFFFFFFF),
        onSurface: Color(0xFF0F172A),
      ),
      fontFamily: 'Roboto',
    );

    final darkTheme = ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      scaffoldBackgroundColor: const Color(0xFF090D16),
      primaryColor: const Color(0xFF3B82F6),
      colorScheme: const ColorScheme.dark(
        primary: Color(0xFF3B82F6),
        secondary: Color(0xFF60A5FA),
        surface: Color(0xFF111827),
        onSurface: Color(0xFFF8FAFC),
      ),
      fontFamily: 'Roboto',
    );

    return MaterialApp(
      title: 'SysMonitor IoT',
      debugShowCheckedModeBanner: false,
      theme: lightTheme,
      darkTheme: darkTheme,
      themeMode: _themeMode,
      builder: (context, child) {
        final isDark = _themeMode == ThemeMode.dark;
        return Container(
          color: isDark ? const Color(0xFF050811) : const Color(0xFFE2E8F0),
          alignment: Alignment.center,
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 430),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(24),
              child: child ?? const SizedBox.shrink(),
            ),
          ),
        );
      },
      initialRoute: '/',
      routes: {
        '/': (context) => WelcomeScreen(onToggleTheme: _toggleTheme),
        '/login': (context) => LoginScreen(onToggleTheme: _toggleTheme),
        '/register': (context) => RegisterScreen(onToggleTheme: _toggleTheme),
        '/dashboard': (context) => DashboardScreen(onToggleTheme: _toggleTheme),
      },
    );
  }
}
