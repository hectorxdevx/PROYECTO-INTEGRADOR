import 'package:flutter/material.dart';
import '../services/api_service.dart';

/// PANTALLA 4: Basada en la estética Minimalist Blue de la Imagen 4 de Google Stitch,
/// pero mejorada y adaptada 100% al monitoreo físico de Racks IoT (Temperatura, Humedad,
/// Consumo kW, Puerta Física, Curva Térmica y Permisos de Jefe de TI / Operador).
class DashboardScreen extends StatefulWidget {
  final VoidCallback onToggleTheme;

  const DashboardScreen({super.key, required this.onToggleTheme});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  List<Map<String, dynamic>> _racks = [];
  int _selectedRackIndex = 0;
  int _bottomNavIndex = 0;
  String _selectedRange = '24h';
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _cargarDatosIoT();
  }

  Future<void> _cargarDatosIoT() async {
    setState(() => _loading = true);
    final racks = await ApiService.obtenerRacks();
    if (!mounted) return;
    setState(() {
      _racks = racks;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bgColor = isDark ? const Color(0xFF090D16) : const Color(0xFFF8FAFC);
    final cardColor = isDark ? const Color(0xFF111827) : Colors.white;
    final softBg = isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9);
    final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final mutedColor =
        isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final primaryBlue = const Color(0xFF1D4ED8);

    final rackActual = _racks.isNotEmpty
        ? _racks[_selectedRackIndex]
        : {
            'codigo_rack': 'RACK-01',
            'nombre_rack': 'Rack 01 - Servidores Principales',
            'ubicacion': 'Site 1 - Pasillo Frío A',
            'estado': 'online',
            'temp_max': 27.0,
            'lectura': {
              'temperatura': 22.4,
              'humedad': 45.0,
              'consumo_kw': 1.20,
              'puerta_abierta': 0,
            }
          };

    final lectura =
        (rackActual['lectura'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final temp = lectura['temperatura'] ?? 22.4;
    final hum = lectura['humedad'] ?? 45.0;
    final kw = lectura['consumo_kw'] ?? 1.20;
    final puertaAbierta = (lectura['puerta_abierta'] ?? 0) == 1;
    final esJefeTi = (ApiService.currentUser['id_rol'] ?? 4) == 4;

    return Scaffold(
      backgroundColor: bgColor,
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _cargarDatosIoT,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding:
                const EdgeInsets.symmetric(horizontal: 20.0, vertical: 14.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. HEADER SUPERIOR (Estilo Imagen 4 de Stitch: Avatar AX, Hola Alex, Selector de Rack, Campana/Tema)
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 22,
                          backgroundColor: const Color(0xFFE0E7FF),
                          child: Text(
                            'AX',
                            style: TextStyle(
                              color: primaryBlue,
                              fontWeight: FontWeight.w800,
                              fontSize: 14,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Hola, ${ApiService.currentUser['nombre']?.toString().split(' ').first ?? 'Alex'}',
                              style: TextStyle(
                                color: textColor,
                                fontSize: 18,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                            Text(
                              esJefeTi
                                  ? 'Jefe de TI • InfraOps'
                                  : 'Operador IoT • InfraOps',
                              style: TextStyle(color: mutedColor, fontSize: 12),
                            ),
                          ],
                        ),
                      ],
                    ),

                    Row(
                      children: [
                        // Selector rápido de Rack físico
                        PopupMenuButton<int>(
                          onSelected: (index) =>
                              setState(() => _selectedRackIndex = index),
                          itemBuilder: (ctx) => List.generate(
                            _racks.length,
                            (i) => PopupMenuItem(
                              value: i,
                              child: Text(_racks[i]['nombre_rack'].toString()),
                            ),
                          ),
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 7),
                            decoration: BoxDecoration(
                              color: softBg,
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  width: 8,
                                  height: 8,
                                  decoration: BoxDecoration(
                                    color: puertaAbierta
                                        ? Colors.redAccent
                                        : primaryBlue,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  rackActual['codigo_rack'].toString(),
                                  style: TextStyle(
                                    color: textColor,
                                    fontSize: 12,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                Icon(Icons.keyboard_arrow_down_rounded,
                                    size: 16, color: mutedColor),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(width: 6),
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
                      ],
                    ),
                  ],
                ),

                const SizedBox(height: 16),

                // 2. PÍLDORA DE SINCRONIZACIÓN IOT (Estilo Imagen 4 de Stitch)
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
                  decoration: BoxDecoration(
                    color: softBg,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.check_circle_outline_rounded,
                              color: primaryBlue, size: 18),
                          const SizedBox(width: 8),
                          Text(
                            '${_racks.isEmpty ? 4 : _racks.length} Racks IoT activos y MFA verificado',
                            style: TextStyle(
                              color: textColor,
                              fontSize: 12.5,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                      Text(
                        _loading ? 'Cargando...' : 'Sincronizado',
                        style: TextStyle(
                          color: primaryBlue,
                          fontSize: 11.5,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 16),

                // 3. BENTO GRID 2x2 DE SENSORES FÍSICOS DEL RACK SELECCIONADO
                Row(
                  children: [
                    Expanded(
                      child: _buildBentoMetricCard(
                        title: 'TEMPERATURA RACK',
                        value: '$temp °C',
                        subtitle:
                            'Límite máx: ${rackActual['temp_max'] ?? 27.0} °C',
                        icon: Icons.thermostat_rounded,
                        accentColor: (double.tryParse(temp.toString()) ?? 22) >
                                27.0
                            ? Colors.orange
                            : primaryBlue,
                        cardColor: cardColor,
                        textColor: textColor,
                        mutedColor: mutedColor,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _buildBentoMetricCard(
                        title: 'HUMEDAD RELATIVA',
                        value: '$hum %',
                        subtitle: 'Óptimo ASHRAE (40-55%)',
                        icon: Icons.water_drop_outlined,
                        accentColor: const Color(0xFF0284C7),
                        cardColor: cardColor,
                        textColor: textColor,
                        mutedColor: mutedColor,
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 12),

                Row(
                  children: [
                    Expanded(
                      child: _buildBentoMetricCard(
                        title: 'CONSUMO ELÉCTRICO',
                        value: '$kw kW',
                        subtitle: 'PDU 120.0 V • Estable',
                        icon: Icons.bolt_rounded,
                        accentColor: const Color(0xFFD97706),
                        cardColor: cardColor,
                        textColor: textColor,
                        mutedColor: mutedColor,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _buildBentoMetricCard(
                        title: 'PUERTA FÍSICA',
                        value: puertaAbierta ? 'Abierta' : 'Cerrada',
                        subtitle: puertaAbierta
                            ? '¡Alerta de intrusión!'
                            : '100% asegurada',
                        icon: puertaAbierta
                            ? Icons.sensor_door_outlined
                            : Icons.lock_outline_rounded,
                        accentColor: puertaAbierta
                            ? Colors.redAccent
                            : const Color(0xFF059669),
                        cardColor: cardColor,
                        textColor: textColor,
                        mutedColor: mutedColor,
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 16),

                // 4. GRÁFICA DE TELEMETRÍA TÉRMICA EN TIEMPO REAL (Estilo Imagen 4 de Stitch)
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: cardColor,
                    borderRadius: BorderRadius.circular(22),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.03),
                        blurRadius: 16,
                        offset: const Offset(0, 6),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Curva Térmica (${rackActual['codigo_rack']})',
                                style: TextStyle(
                                  color: textColor,
                                  fontSize: 16,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              Text(
                                'Telemetría del sensor DHT22 / DS18B20',
                                style:
                                    TextStyle(color: mutedColor, fontSize: 12),
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.all(3),
                            decoration: BoxDecoration(
                              color: softBg,
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: Row(
                              children: ['1h', '24h', '7d'].map((r) {
                                final active = _selectedRange == r;
                                return GestureDetector(
                                  onTap: () =>
                                      setState(() => _selectedRange = r),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: active
                                          ? cardColor
                                          : Colors.transparent,
                                      borderRadius: BorderRadius.circular(999),
                                    ),
                                    child: Text(
                                      r,
                                      style: TextStyle(
                                        color:
                                            active ? primaryBlue : mutedColor,
                                        fontSize: 11.5,
                                        fontWeight: FontWeight.w700,
                                      ),
                                    ),
                                  ),
                                );
                              }).toList(),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),
                      SizedBox(
                        height: 110,
                        width: double.infinity,
                        child: CustomPaint(
                          painter: _ThermalChartPainter(color: primaryBlue),
                        ),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('08:00',
                              style:
                                  TextStyle(color: mutedColor, fontSize: 11)),
                          Text('12:00',
                              style:
                                  TextStyle(color: mutedColor, fontSize: 11)),
                          Text(
                            'Pico: 24.5 °C',
                            style: TextStyle(
                              color: primaryBlue,
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          Text('Ahora',
                              style:
                                  TextStyle(color: mutedColor, fontSize: 11)),
                        ],
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 18),

                // 5. ACCIONES ESENCIALES (3 Botones estilo Imagen 4 de Stitch adaptados a IoT y Jefe de TI)
                Text(
                  'ACCIONES ESENCIALES',
                  style: TextStyle(
                    color: mutedColor,
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.7,
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: _buildQuickAction(
                        icon: Icons.sync_rounded,
                        label: 'Sondear IoT',
                        softBg: softBg,
                        primaryBlue: primaryBlue,
                        textColor: textColor,
                        onTap: _cargarDatosIoT,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: _buildQuickAction(
                        icon: Icons.tune_rounded,
                        label: esJefeTi ? 'Umbrales TI' : 'Ver Límites',
                        softBg: softBg,
                        primaryBlue: primaryBlue,
                        textColor: textColor,
                        onTap: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                esJefeTi
                                    ? 'Modo Jefe de TI: Umbral térmico activo en ${rackActual['temp_max'] ?? 27.0} °C'
                                    : 'Umbral configurado por tu Jefe de TI: 27.0 °C',
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: _buildQuickAction(
                        icon: Icons.shield_outlined,
                        label: 'Bitácora MFA',
                        softBg: softBg,
                        primaryBlue: primaryBlue,
                        textColor: textColor,
                        onTap: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text(
                                  'Sesión protegida con Google Authenticator (RFC 6238).'),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 20),

                // 6. EVENTOS Y ALERTAS RECIENTES DE LOS RACKS (Estilo Imagen 4 de Stitch)
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Alertas Físicas Recientes',
                      style: TextStyle(
                        color: textColor,
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: primaryBlue.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        '2 activos',
                        style: TextStyle(
                          color: primaryBlue,
                          fontSize: 11.5,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                _buildEventTile(
                  icon: Icons.sensor_door_outlined,
                  title: 'Apertura física en Rack 04 (Respaldo)',
                  subtitle: 'Sensor magnético activado • Hace 5m',
                  cardColor: cardColor,
                  softBg: softBg,
                  primaryBlue: Colors.redAccent,
                  textColor: textColor,
                  mutedColor: mutedColor,
                ),
                const SizedBox(height: 10),
                _buildEventTile(
                  icon: Icons.thermostat_rounded,
                  title: 'Temperatura elevada en Rack 03 (28.4 °C)',
                  subtitle: 'Superó umbral de 27.0 °C • Hace 18m',
                  cardColor: cardColor,
                  softBg: softBg,
                  primaryBlue: primaryBlue,
                  textColor: textColor,
                  mutedColor: mutedColor,
                ),
              ],
            ),
          ),
        ),
      ),

      // Barra de Navegación Inferior idéntica a la Imagen 4 de Google Stitch
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _bottomNavIndex,
        onTap: (i) {
          if (i == 3) {
            Navigator.pushReplacementNamed(context, '/login');
          } else {
            setState(() => _bottomNavIndex = i);
          }
        },
        type: BottomNavigationBarType.fixed,
        backgroundColor: cardColor,
        selectedItemColor: primaryBlue,
        unselectedItemColor: mutedColor,
        selectedLabelStyle:
            const TextStyle(fontWeight: FontWeight.w700, fontSize: 11.5),
        unselectedLabelStyle: const TextStyle(fontSize: 11.5),
        items: const [
          BottomNavigationBarItem(
              icon: Icon(Icons.grid_view_rounded), label: 'Inicio'),
          BottomNavigationBarItem(
              icon: Icon(Icons.dns_outlined), label: 'Racks'),
          BottomNavigationBarItem(
              icon: Icon(Icons.notifications_none_rounded), label: 'Alertas'),
          BottomNavigationBarItem(
              icon: Icon(Icons.person_outline_rounded), label: 'Salir'),
        ],
      ),
    );
  }

  Widget _buildBentoMetricCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color accentColor,
    required Color cardColor,
    required Color textColor,
    required Color mutedColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: cardColor,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(
                    color: mutedColor,
                    fontSize: 10.5,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.4,
                  ),
                ),
              ),
              Icon(icon, color: accentColor, size: 18),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            value,
            style: TextStyle(
              color: textColor,
              fontSize: 23,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: TextStyle(
              color: accentColor,
              fontSize: 11.5,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickAction({
    required IconData icon,
    required String label,
    required Color softBg,
    required Color primaryBlue,
    required Color textColor,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: softBg,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          children: [
            Icon(icon, color: primaryBlue, size: 22),
            const SizedBox(height: 6),
            Text(
              label,
              style: TextStyle(
                color: textColor,
                fontSize: 12,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEventTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color cardColor,
    required Color softBg,
    required Color primaryBlue,
    required Color textColor,
    required Color mutedColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: cardColor,
        borderRadius: BorderRadius.circular(18),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: softBg,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: primaryBlue, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    color: textColor,
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: TextStyle(color: mutedColor, fontSize: 11.5),
                ),
              ],
            ),
          ),
          Icon(Icons.chevron_right_rounded, color: mutedColor),
        ],
      ),
    );
  }
}

class _ThermalChartPainter extends CustomPainter {
  final Color color;

  _ThermalChartPainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final paintLine = Paint()
      ..color = color
      ..strokeWidth = 3.2
      ..style = PaintingStyle.stroke;

    final path = Path()
      ..moveTo(0, size.height * 0.72)
      ..cubicTo(
        size.width * 0.25,
        size.height * 0.68,
        size.width * 0.35,
        size.height * 0.25,
        size.width * 0.62,
        size.height * 0.22,
      )
      ..cubicTo(
        size.width * 0.78,
        size.height * 0.18,
        size.width * 0.88,
        size.height * 0.32,
        size.width,
        size.height * 0.18,
      );

    final fillPath = Path.from(path)
      ..lineTo(size.width, size.height)
      ..lineTo(0, size.height)
      ..close();

    final paintFill = Paint()
      ..shader = LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: [
          color.withValues(alpha: 0.28),
          color.withValues(alpha: 0.0),
        ],
      ).createShader(Rect.fromLTWH(0, 0, size.width, size.height));

    canvas.drawPath(fillPath, paintFill);
    canvas.drawPath(path, paintLine);

    // Punto destacado de pico térmico
    canvas.drawCircle(
      Offset(size.width * 0.66, size.height * 0.21),
      5.5,
      Paint()..color = color,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
