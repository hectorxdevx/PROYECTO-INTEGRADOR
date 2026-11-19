import 'package:flutter/material.dart';
import '../services/api_service.dart';

/// PANTALLA 4: Dashboard IoT & B2B Completo (Minimalist Blue)
/// 4 pestañas funcionales: Inicio (Telemetría), Racks (Gestión + Simulador ESP32),
/// Empresa (Plan B2B, Alta, Cuentas @empresa, Correos SMTP) y Alertas en vivo.
class DashboardScreen extends StatefulWidget {
  final VoidCallback onToggleTheme;

  const DashboardScreen({super.key, required this.onToggleTheme});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  List<Map<String, dynamic>> _racks = [];
  List<Map<String, dynamic>> _alertas = [];
  Map<String, dynamic> _resumenEmpresa = {};
  int _selectedRackIndex = 0;
  int _bottomNavIndex = 0;
  String _selectedRange = '24h';
  bool _loading = true;
  bool _soloAlertasActivas = true;

  @override
  void initState() {
    super.initState();
    _cargarTodo();
  }

  int _toInt(dynamic v, [int fallback = 0]) {
    if (v is int) return v;
    if (v is num) return v.toInt();
    return int.tryParse(v?.toString() ?? '') ?? fallback;
  }

  double _toDouble(dynamic v, [double fallback = 0.0]) {
    if (v is double) return v;
    if (v is num) return v.toDouble();
    return double.tryParse(v?.toString() ?? '') ?? fallback;
  }

  String _stripHtml(String html) {
    return html
        .replaceAll(RegExp(r'<br\s*/?>', caseSensitive: false), '\n')
        .replaceAll(RegExp(r'</(p|div|tr|li|h[1-6])>', caseSensitive: false), '\n')
        .replaceAll(RegExp(r'<[^>]*>'), '')
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&amp;', '&')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll(RegExp(r'\n{3,}'), '\n\n')
        .trim();
  }

  Future<void> _cargarTodo() async {
    setState(() => _loading = true);
    final results = await Future.wait([
      ApiService.obtenerRacks(),
      ApiService.obtenerAlertas(),
      ApiService.obtenerResumenEmpresa(),
    ]);
    if (!mounted) return;
    final racks = results[0] as List<Map<String, dynamic>>;
    final alertas = results[1] as List<Map<String, dynamic>>;
    final resumen = results[2] as Map<String, dynamic>;
    setState(() {
      _racks = racks;
      _alertas = alertas;
      _resumenEmpresa = resumen;
      if (_selectedRackIndex >= _racks.length) {
        _selectedRackIndex = 0;
      }
      _loading = false;
    });
  }

  void _cerrarSesion() {
    ApiService.logout();
    Navigator.pushReplacementNamed(context, '/login');
  }

  void _snack(String msg, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: isError ? Colors.redAccent : null,
      ),
    );
  }

  String _initials() {
    final name = (ApiService.currentUser['nombre'] ?? 'AX').toString().trim();
    if (name.isEmpty) return 'SM';
    final parts = name.split(RegExp(r'\s+'));
    if (parts.length >= 2 && parts[0].isNotEmpty && parts[1].isNotEmpty) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return name.substring(0, name.length >= 2 ? 2 : 1).toUpperCase();
  }

  bool get _puedeGestionarRacks {
    final rol = _toInt(ApiService.currentUser['id_rol'], 4);
    return rol == 2 || rol == 3 || rol == 4;
  }

  String get _rolLabel {
    final rol = _toInt(ApiService.currentUser['id_rol'], 4);
    switch (rol) {
      case 2:
        return 'SuperAdmin';
      case 3:
        return 'Representante';
      case 4:
        return 'Jefe de TI';
      default:
        return 'Operador IoT';
    }
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

    final activeAlertsCount =
        _alertas.where((a) => _toInt(a['resuelta']) == 0).length;

    return Scaffold(
      backgroundColor: bgColor,
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _cargarTodo,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding:
                const EdgeInsets.symmetric(horizontal: 20.0, vertical: 14.0),
            child: _buildCurrentTab(
              isDark: isDark,
              bgColor: bgColor,
              cardColor: cardColor,
              softBg: softBg,
              textColor: textColor,
              mutedColor: mutedColor,
              primaryBlue: primaryBlue,
              activeAlertsCount: activeAlertsCount,
            ),
          ),
        ),
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _bottomNavIndex,
        onTap: (i) => setState(() => _bottomNavIndex = i),
        type: BottomNavigationBarType.fixed,
        backgroundColor: cardColor,
        selectedItemColor: primaryBlue,
        unselectedItemColor: mutedColor,
        selectedLabelStyle:
            const TextStyle(fontWeight: FontWeight.w700, fontSize: 11.5),
        unselectedLabelStyle: const TextStyle(fontSize: 11.5),
        items: [
          const BottomNavigationBarItem(
            icon: Icon(Icons.grid_view_rounded),
            label: 'Inicio',
          ),
          const BottomNavigationBarItem(
            icon: Icon(Icons.dns_outlined),
            label: 'Racks',
          ),
          const BottomNavigationBarItem(
            icon: Icon(Icons.business_rounded),
            label: 'Empresa',
          ),
          BottomNavigationBarItem(
            icon: Stack(
              clipBehavior: Clip.none,
              children: [
                const Icon(Icons.notifications_none_rounded),
                if (activeAlertsCount > 0)
                  Positioned(
                    right: -6,
                    top: -4,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 5, vertical: 1),
                      decoration: BoxDecoration(
                        color: Colors.redAccent,
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        '$activeAlertsCount',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 9.5,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
            label: 'Alertas',
          ),
        ],
      ),
    );
  }

  Widget _buildCurrentTab({
    required bool isDark,
    required Color bgColor,
    required Color cardColor,
    required Color softBg,
    required Color textColor,
    required Color mutedColor,
    required Color primaryBlue,
    required int activeAlertsCount,
  }) {
    switch (_bottomNavIndex) {
      case 1:
        return _buildRacksTab(
          cardColor: cardColor,
          softBg: softBg,
          textColor: textColor,
          mutedColor: mutedColor,
          primaryBlue: primaryBlue,
        );
      case 2:
        return _buildEmpresaTab(
          cardColor: cardColor,
          softBg: softBg,
          textColor: textColor,
          mutedColor: mutedColor,
          primaryBlue: primaryBlue,
        );
      case 3:
        return _buildAlertasTab(
          cardColor: cardColor,
          softBg: softBg,
          textColor: textColor,
          mutedColor: mutedColor,
          primaryBlue: primaryBlue,
          activeAlertsCount: activeAlertsCount,
        );
      default:
        return _buildInicioTab(
          isDark: isDark,
          cardColor: cardColor,
          softBg: softBg,
          textColor: textColor,
          mutedColor: mutedColor,
          primaryBlue: primaryBlue,
          activeAlertsCount: activeAlertsCount,
        );
    }
  }

  // =========================================================================
  // TAB 0: INICIO (TELEMETRÍA BENTO GRID + CURVA TÉRMICA + PLAN B2B PILL)
  // =========================================================================
  Widget _buildInicioTab({
    required bool isDark,
    required Color cardColor,
    required Color softBg,
    required Color textColor,
    required Color mutedColor,
    required Color primaryBlue,
    required int activeAlertsCount,
  }) {
    final safeIdx =
        _racks.isEmpty ? 0 : _selectedRackIndex.clamp(0, _racks.length - 1);
    final rackActual = _racks.isNotEmpty
        ? _racks[safeIdx]
        : {
            'id_rack': 1,
            'codigo_rack': 'RACK-01',
            'nombre_rack': 'Rack 01 - Servidores Principales',
            'ubicacion': 'Site 1 - Pasillo Frío A',
            'estado': 'online',
            'temp_max': 27.0,
            'hum_max': 60.0,
            'kw_max': 2.5,
            'lectura': {
              'temperatura': 22.4,
              'humedad': 45.0,
              'consumo_kw': 1.20,
              'puerta_abierta': 0,
            }
          };

    final lectura =
        (rackActual['lectura'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final temp = _toDouble(lectura['temperatura'], 22.4);
    final hum = _toDouble(lectura['humedad'], 45.0);
    final kw = _toDouble(lectura['consumo_kw'], 1.20);
    final tempMax = _toDouble(rackActual['temp_max'], 27.0);
    final puertaAbierta = _toInt(lectura['puerta_abierta']) == 1;

    final usoPlan =
        (_resumenEmpresa['uso_plan'] as Map<String, dynamic>?) ?? {};
    final empresaData =
        (_resumenEmpresa['empresa'] as Map<String, dynamic>?) ?? {};
    final empresaNombre = empresaData['nombre_empresa']?.toString() ??
        ApiService.currentUser['empresa']?.toString() ??
        'SysMonitor';
    final planNombre = usoPlan['nombre_plan']?.toString() ?? 'Corporate';
    final racksUsados = _toInt(usoPlan['racks_usados'], _racks.length);
    final maxRacks = _toInt(usoPlan['max_racks'], 10);

    final alertasActivas =
        _alertas.where((a) => _toInt(a['resuelta']) == 0).take(3).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // 1. HEADER SUPERIOR
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Expanded(
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 21,
                    backgroundColor: isDark
                        ? primaryBlue.withValues(alpha: 0.25)
                        : const Color(0xFFE0E7FF),
                    child: Text(
                      _initials(),
                      style: TextStyle(
                        color: primaryBlue,
                        fontWeight: FontWeight.w800,
                        fontSize: 13.5,
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Hola, ${ApiService.currentUser['nombre']?.toString().split(' ').first ?? 'Alex'}',
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            color: textColor,
                            fontSize: 17.5,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        Text(
                          '$_rolLabel • $empresaNombre',
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(color: mutedColor, fontSize: 11.5),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            Row(
              children: [
                if (_racks.isNotEmpty)
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
                          horizontal: 10, vertical: 7),
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
                          const SizedBox(width: 5),
                          Text(
                            rackActual['codigo_rack'].toString(),
                            style: TextStyle(
                              color: textColor,
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          Icon(Icons.keyboard_arrow_down_rounded,
                              size: 16, color: mutedColor),
                        ],
                      ),
                    ),
                  ),
                IconButton(
                  onPressed: widget.onToggleTheme,
                  icon: Icon(
                    isDark ? Icons.wb_sunny_rounded : Icons.dark_mode_rounded,
                    color: primaryBlue,
                    size: 20,
                  ),
                ),
              ],
            ),
          ],
        ),

        const SizedBox(height: 14),

        // 2. PÍLDORA DE SINCRONIZACIÓN IOT + PLAN B2B
        GestureDetector(
          onTap: () => setState(() => _bottomNavIndex = 2),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
            decoration: BoxDecoration(
              color: softBg,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Row(
                    children: [
                      Icon(Icons.check_circle_outline_rounded,
                          color: primaryBlue, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          '${_racks.length} Racks IoT activos • MFA verificado',
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            color: textColor,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                  decoration: BoxDecoration(
                    color: primaryBlue.withValues(alpha: 0.14),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    _loading
                        ? 'Sync...'
                        : 'Plan $planNombre ($racksUsados/${maxRacks >= 999 ? '∞' : maxRacks})',
                    style: TextStyle(
                      color: primaryBlue,
                      fontSize: 10.5,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),

        const SizedBox(height: 16),

        // 3. BENTO GRID 2x2 DE SENSORES FÍSICOS
        Row(
          children: [
            Expanded(
              child: _buildBentoMetricCard(
                title: 'TEMPERATURA RACK',
                value: '${temp.toStringAsFixed(1)} °C',
                subtitle: 'Límite máx: ${tempMax.toStringAsFixed(1)} °C',
                icon: Icons.thermostat_rounded,
                accentColor: temp > tempMax ? Colors.orange : primaryBlue,
                cardColor: cardColor,
                textColor: textColor,
                mutedColor: mutedColor,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildBentoMetricCard(
                title: 'HUMEDAD RELATIVA',
                value: '${hum.toStringAsFixed(0)} %',
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
                value: '${kw.toStringAsFixed(2)} kW',
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

        // 4. GRÁFICA DE TELEMETRÍA TÉRMICA
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
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Curva Térmica (${rackActual['codigo_rack']})',
                          style: TextStyle(
                            color: textColor,
                            fontSize: 15.5,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        Text(
                          'Telemetría del sensor DHT22 / DS18B20',
                          style: TextStyle(color: mutedColor, fontSize: 11.5),
                        ),
                      ],
                    ),
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
                          onTap: () => setState(() => _selectedRange = r),
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 9, vertical: 4),
                            decoration: BoxDecoration(
                              color: active ? cardColor : Colors.transparent,
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: Text(
                              r,
                              style: TextStyle(
                                color: active ? primaryBlue : mutedColor,
                                fontSize: 11,
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
              const SizedBox(height: 18),
              SizedBox(
                height: 105,
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
                      style: TextStyle(color: mutedColor, fontSize: 11)),
                  Text('12:00',
                      style: TextStyle(color: mutedColor, fontSize: 11)),
                  Text(
                    'Actual: ${temp.toStringAsFixed(1)} °C',
                    style: TextStyle(
                      color: primaryBlue,
                      fontSize: 11.5,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  Text('Ahora',
                      style: TextStyle(color: mutedColor, fontSize: 11)),
                ],
              ),
            ],
          ),
        ),

        const SizedBox(height: 18),

        // 5. ACCIONES ESENCIALES
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
                onTap: () async {
                  await _cargarTodo();
                  _snack('Sensores IoT sincronizados correctamente.');
                },
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildQuickAction(
                icon: Icons.tune_rounded,
                label: _puedeGestionarRacks ? 'Umbrales TI' : 'Ver Límites',
                softBg: softBg,
                primaryBlue: primaryBlue,
                textColor: textColor,
                onTap: () {
                  if (_racks.isEmpty) {
                    _snack('Registra un Rack primero en la pestaña Racks.',
                        isError: true);
                    return;
                  }
                  if (_puedeGestionarRacks) {
                    _abrirModalRack(rackEditar: rackActual);
                  } else {
                    _snack(
                        'Umbral configurado: ${tempMax.toStringAsFixed(1)} °C • ${_toDouble(rackActual['hum_max'], 60)}%');
                  }
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
                onTap: () => _mostrarDialogoBitacoraMfa(
                    primaryBlue, cardColor, textColor, mutedColor),
              ),
            ),
          ],
        ),

        const SizedBox(height: 20),

        // 6. EVENTOS Y ALERTAS RECIENTES
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
            GestureDetector(
              onTap: () => setState(() => _bottomNavIndex = 3),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: (activeAlertsCount > 0 ? Colors.redAccent : primaryBlue)
                      .withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  '$activeAlertsCount activas',
                  style: TextStyle(
                    color:
                        activeAlertsCount > 0 ? Colors.redAccent : primaryBlue,
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        if (alertasActivas.isEmpty)
          _buildEventTile(
            icon: Icons.verified_user_outlined,
            title: 'Todos los racks operan dentro de umbrales',
            subtitle: 'Sin alertas críticas o advertencias activas',
            cardColor: cardColor,
            softBg: softBg,
            accentColor: const Color(0xFF059669),
            textColor: textColor,
            mutedColor: mutedColor,
          )
        else
          ...alertasActivas.map((a) {
            final crit = a['nivel']?.toString() == 'critical';
            return Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: GestureDetector(
                onTap: () => setState(() => _bottomNavIndex = 3),
                child: _buildEventTile(
                  icon: crit
                      ? Icons.warning_amber_rounded
                      : Icons.thermostat_rounded,
                  title: a['tipo_alerta']?.toString() ?? 'Alerta IoT',
                  subtitle:
                      '${a['mensaje'] ?? ''} • ${a['fecha_alerta'] ?? ''}',
                  cardColor: cardColor,
                  softBg: softBg,
                  accentColor: crit ? Colors.redAccent : Colors.orange,
                  textColor: textColor,
                  mutedColor: mutedColor,
                ),
              ),
            );
          }),
      ],
    );
  }

  // =========================================================================
  // TAB 1: RACKS IOT (LISTADO, NUEVO RACK, UMBRALES, SIMULADOR ESP32)
  // =========================================================================
  Widget _buildRacksTab({
    required Color cardColor,
    required Color softBg,
    required Color textColor,
    required Color mutedColor,
    required Color primaryBlue,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Gestión de Racks IoT',
                    style: TextStyle(
                      color: textColor,
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  Text(
                    '${_racks.length} nodos monitoreados en tiempo real',
                    style: TextStyle(color: mutedColor, fontSize: 12.5),
                  ),
                ],
              ),
            ),
            if (_puedeGestionarRacks)
              ElevatedButton.icon(
                onPressed: () => _abrirModalRack(),
                icon: const Icon(Icons.add_rounded, size: 18),
                label: const Text('Nuevo Rack IoT'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryBlue,
                  foregroundColor: Colors.white,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                  textStyle: const TextStyle(
                      fontSize: 12.5, fontWeight: FontWeight.w700),
                ),
              ),
          ],
        ),
        const SizedBox(height: 16),

        if (_racks.isEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: cardColor,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Column(
              children: [
                Icon(Icons.dns_outlined, size: 42, color: mutedColor),
                const SizedBox(height: 10),
                Text(
                  'Sin racks registrados aún',
                  style: TextStyle(
                    color: textColor,
                    fontWeight: FontWeight.w800,
                    fontSize: 15,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Registra tu empresa o crea tu primer Rack IoT para comenzar a recibir telemetría.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: mutedColor, fontSize: 12.5),
                ),
              ],
            ),
          )
        else
          ...List.generate(_racks.length, (i) {
            final rack = _racks[i];
            final lec = (rack['lectura'] as Map<String, dynamic>?) ?? {};
            final estado = (rack['estado'] ?? 'online').toString();
            final puerta = _toInt(lec['puerta_abierta']) == 1;
            final Color badgeColor = estado == 'critical'
                ? Colors.redAccent
                : estado == 'warning'
                    ? Colors.orange
                    : const Color(0xFF059669);

            return Container(
              margin: const EdgeInsets.only(bottom: 14),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: cardColor,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: estado == 'online'
                      ? primaryBlue.withValues(alpha: 0.08)
                      : badgeColor.withValues(alpha: 0.35),
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.03),
                    blurRadius: 12,
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
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '${rack['codigo_rack']} • ${rack['nombre_rack']}',
                              style: TextStyle(
                                color: textColor,
                                fontSize: 14.5,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              rack['ubicacion']?.toString() ?? 'Site Principal',
                              style:
                                  TextStyle(color: mutedColor, fontSize: 11.5),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: badgeColor.withValues(alpha: 0.14),
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(
                          estado.toUpperCase(),
                          style: TextStyle(
                            color: badgeColor,
                            fontSize: 10.5,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // 4 sensores rápidos
                  Row(
                    children: [
                      _sensorMiniChip(
                        icon: Icons.thermostat_rounded,
                        text:
                            '${_toDouble(lec['temperatura'], 22.0).toStringAsFixed(1)} °C',
                        softBg: softBg,
                        textColor: textColor,
                        iconColor: primaryBlue,
                      ),
                      const SizedBox(width: 6),
                      _sensorMiniChip(
                        icon: Icons.water_drop_outlined,
                        text:
                            '${_toDouble(lec['humedad'], 45.0).toStringAsFixed(0)}%',
                        softBg: softBg,
                        textColor: textColor,
                        iconColor: const Color(0xFF0284C7),
                      ),
                      const SizedBox(width: 6),
                      _sensorMiniChip(
                        icon: Icons.bolt_rounded,
                        text:
                            '${_toDouble(lec['consumo_kw'], 1.1).toStringAsFixed(2)} kW',
                        softBg: softBg,
                        textColor: textColor,
                        iconColor: const Color(0xFFD97706),
                      ),
                      const SizedBox(width: 6),
                      _sensorMiniChip(
                        icon: puerta
                            ? Icons.sensor_door_outlined
                            : Icons.lock_outline_rounded,
                        text: puerta ? 'Abierta' : 'Cerrada',
                        softBg: softBg,
                        textColor: puerta ? Colors.redAccent : textColor,
                        iconColor: puerta
                            ? Colors.redAccent
                            : const Color(0xFF059669),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Text(
                    'Umbrales: ${_toDouble(rack['temp_max'], 27).toStringAsFixed(1)} °C máx • ${_toDouble(rack['hum_max'], 60).toStringAsFixed(0)}% hum • ${_toDouble(rack['kw_max'], 2.5).toStringAsFixed(1)} kW',
                    style: TextStyle(color: mutedColor, fontSize: 11),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      if (_puedeGestionarRacks)
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () => _abrirModalRack(rackEditar: rack),
                            icon: const Icon(Icons.tune_rounded, size: 16),
                            label: const Text('Editar Umbrales'),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: primaryBlue,
                              side: BorderSide(
                                  color: primaryBlue.withValues(alpha: 0.35)),
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12)),
                              padding: const EdgeInsets.symmetric(vertical: 10),
                              textStyle: const TextStyle(
                                  fontSize: 12, fontWeight: FontWeight.w700),
                            ),
                          ),
                        ),
                      if (_puedeGestionarRacks) const SizedBox(width: 8),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: () => _abrirModalSimularEsp32(rack),
                          icon:
                              const Icon(Icons.memory_rounded, size: 16),
                          label: const Text('Simular ESP32'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: softBg,
                            foregroundColor: primaryBlue,
                            elevation: 0,
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12)),
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            textStyle: const TextStyle(
                                fontSize: 12, fontWeight: FontWeight.w700),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            );
          }),
      ],
    );
  }

  Widget _sensorMiniChip({
    required IconData icon,
    required String text,
    required Color softBg,
    required Color textColor,
    required Color iconColor,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 4),
        decoration: BoxDecoration(
          color: softBg,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          children: [
            Icon(icon, size: 15, color: iconColor),
            const SizedBox(height: 3),
            Text(
              text,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: textColor,
                fontSize: 11,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // =========================================================================
  // TAB 2: EMPRESA B2B (PLAN, QUOTAS, ALTA, SOLICITAR CORREOS, LOG SMTP)
  // =========================================================================
  Widget _buildEmpresaTab({
    required Color cardColor,
    required Color softBg,
    required Color textColor,
    required Color mutedColor,
    required Color primaryBlue,
  }) {
    final empresa = _resumenEmpresa['empresa'] as Map<String, dynamic>?;
    final usoPlan =
        (_resumenEmpresa['uso_plan'] as Map<String, dynamic>?) ?? {};
    final solicitudes = (_resumenEmpresa['solicitudes'] as List?)
            ?.map((e) => Map<String, dynamic>.from(e as Map))
            .toList() ??
        [];
    final correos = (_resumenEmpresa['correos_enviados'] as List?)
            ?.map((e) => Map<String, dynamic>.from(e as Map))
            .toList() ??
        [];

    final planKey =
        (usoPlan['plan_suscripcion'] ?? empresa?['plan_suscripcion'] ?? 'corporate')
            .toString();
    final planNombre = usoPlan['nombre_plan']?.toString() ??
        (planKey[0].toUpperCase() + planKey.substring(1));
    final costoMensual = _toDouble(usoPlan['costo_mensual'], 149.0);
    final racksUsados = _toInt(usoPlan['racks_usados'], _racks.length);
    final maxRacks = _toInt(usoPlan['max_racks'], 10);
    final cuentasUsadas = _toInt(usoPlan['cuentas_usadas'], solicitudes.length);
    final maxCuentas = _toInt(usoPlan['max_cuentas'], 15);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header + Cerrar Sesión + Alta Empresa
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Empresa & Cuentas B2B',
                    style: TextStyle(
                      color: textColor,
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  Text(
                    'Suscripción, correos @sysmonitor.com y auditoría SMTP',
                    style: TextStyle(color: mutedColor, fontSize: 12),
                  ),
                ],
              ),
            ),
            IconButton(
              onPressed: _cerrarSesion,
              tooltip: 'Cerrar Sesión',
              icon: const Icon(Icons.logout_rounded, color: Colors.redAccent),
            ),
          ],
        ),
        const SizedBox(height: 14),

        // Tarjeta Empresa + Plan B2B
        if (empresa == null)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: cardColor,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Aún no tienes una empresa vinculada',
                  style: TextStyle(
                    color: textColor,
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Da de alta tu organización para activar tu dominio corporativo, RACK-01 y cuentas institucionales.',
                  style: TextStyle(color: mutedColor, fontSize: 12.5),
                ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _abrirModalAltaEmpresa,
                    icon: const Icon(Icons.add_business_rounded),
                    label: const Text('Dar de Alta Empresa'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: primaryBlue,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
              ],
            ),
          )
        else ...[
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: cardColor,
              borderRadius: BorderRadius.circular(22),
              border: Border.all(color: primaryBlue.withValues(alpha: 0.12)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            empresa['nombre_empresa']?.toString() ?? 'Empresa',
                            style: TextStyle(
                              color: textColor,
                              fontSize: 17,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            '@${empresa['slug_dominio'] ?? 'empresa.sysmonitor.com'}',
                            style: TextStyle(
                              color: primaryBlue,
                              fontSize: 12.5,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          Text(
                            'RFC: ${empresa['rfc_nit'] ?? '-'} • ${empresa['sector'] ?? 'DataCenter'}',
                            style: TextStyle(color: mutedColor, fontSize: 11),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: primaryBlue.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        children: [
                          Text(
                            planNombre.toUpperCase(),
                            style: TextStyle(
                              color: primaryBlue,
                              fontSize: 11,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                          Text(
                            '\$${costoMensual.toStringAsFixed(0)}/mes',
                            style: TextStyle(
                              color: textColor,
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                _buildUsageBar(
                  label: 'Racks IoT utilizados',
                  used: racksUsados,
                  max: maxRacks,
                  primaryBlue: primaryBlue,
                  softBg: softBg,
                  textColor: textColor,
                  mutedColor: mutedColor,
                ),
                const SizedBox(height: 10),
                _buildUsageBar(
                  label: 'Cuentas institucionales',
                  used: cuentasUsadas,
                  max: maxCuentas,
                  primaryBlue: const Color(0xFF059669),
                  softBg: softBg,
                  textColor: textColor,
                  mutedColor: mutedColor,
                ),
                const SizedBox(height: 14),

                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _abrirModalCambiarPlan(planKey),
                        icon: const Icon(Icons.upgrade_rounded, size: 17),
                        label: const Text('Cambiar Plan B2B'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: primaryBlue,
                          side: BorderSide(
                              color: primaryBlue.withValues(alpha: 0.4)),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                          padding: const EdgeInsets.symmetric(vertical: 11),
                          textStyle: const TextStyle(
                              fontSize: 12, fontWeight: FontWeight.w700),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: _abrirModalAltaEmpresa,
                        icon: const Icon(Icons.add_business_rounded, size: 17),
                        label: const Text('+ Alta Empresa'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryBlue,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                          padding: const EdgeInsets.symmetric(vertical: 11),
                          textStyle: const TextStyle(
                              fontSize: 12, fontWeight: FontWeight.w700),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Solicitar Correo Institucional
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () => _abrirModalSolicitarCorreo(
                  empresa['slug_dominio']?.toString() ??
                      'empresa.sysmonitor.com'),
              icon: const Icon(Icons.alternate_email_rounded, size: 18),
              label: Text(
                  'Solicitar Correo @${empresa['slug_dominio'] ?? 'empresa.sysmonitor.com'}'),
              style: ElevatedButton.styleFrom(
                backgroundColor: softBg,
                foregroundColor: primaryBlue,
                elevation: 0,
                padding: const EdgeInsets.symmetric(vertical: 13),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14)),
                textStyle: const TextStyle(
                    fontSize: 13, fontWeight: FontWeight.w800),
              ),
            ),
          ),

          const SizedBox(height: 18),

          // Solicitudes de Cuentas
          Text(
            'Cuentas e Historial de Solicitudes (${solicitudes.length})',
            style: TextStyle(
              color: textColor,
              fontSize: 15.5,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          if (solicitudes.isEmpty)
            Text(
              'No hay solicitudes de cuentas registradas todavía.',
              style: TextStyle(color: mutedColor, fontSize: 12.5),
            )
          else
            ...solicitudes.map((s) {
              final estado = (s['estado'] ?? 'pendiente').toString();
              final aprobada = estado == 'aprobada';
              return Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.all(13),
                decoration: BoxDecoration(
                  color: cardColor,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 38,
                      height: 38,
                      decoration: BoxDecoration(
                        color: softBg,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(
                        s['rol_solicitado'] == 'jefe_ti'
                            ? Icons.admin_panel_settings_outlined
                            : Icons.person_outline_rounded,
                        color: primaryBlue,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${s['nombre']} ${s['apellidos']} (${s['rol_solicitado'] == 'jefe_ti' ? 'Jefe TI' : 'Operador'})',
                            style: TextStyle(
                              color: textColor,
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          Text(
                            s['correo_asignado']?.toString() ??
                                'Pendiente de asignación',
                            style: TextStyle(color: mutedColor, fontSize: 11.5),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: (aprobada
                                ? const Color(0xFF059669)
                                : Colors.orange)
                            .withValues(alpha: 0.14),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        estado.toUpperCase(),
                        style: TextStyle(
                          color: aprobada
                              ? const Color(0xFF059669)
                              : Colors.orange,
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }),

          const SizedBox(height: 16),

          // Correos Enviados por SMTP
          Text(
            'Credenciales y Correos Enviados SMTP (${correos.length})',
            style: TextStyle(
              color: textColor,
              fontSize: 15.5,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          if (correos.isEmpty)
            Text(
              'Sin envíos SMTP registrados para esta empresa.',
              style: TextStyle(color: mutedColor, fontSize: 12.5),
            )
          else
            ...correos.map((c) {
              final estadoSmtp = (c['estado_smtp'] ?? 'cola_local_auditable').toString();
              final enviado = estadoSmtp == 'enviado_smtp';
              return GestureDetector(
                onTap: () => _mostrarPreviewCorreo(c, primaryBlue, textColor, mutedColor),
                child: Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(13),
                  decoration: BoxDecoration(
                    color: cardColor,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 38,
                        height: 38,
                        decoration: BoxDecoration(
                          color: softBg,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(Icons.mark_email_read_outlined,
                            color: primaryBlue, size: 19),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              c['asunto']?.toString() ?? 'Notificación SMTP',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                color: textColor,
                                fontSize: 13,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            Text(
                              'Para: ${c['destinatario'] ?? '-'} • ${enviado ? 'SMTP Real' : 'Cola Auditable'}',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style:
                                  TextStyle(color: mutedColor, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                      Icon(Icons.visibility_outlined,
                          size: 18, color: primaryBlue),
                    ],
                  ),
                ),
              );
            }),
        ],
      ],
    );
  }

  Widget _buildUsageBar({
    required String label,
    required int used,
    required int max,
    required Color primaryBlue,
    required Color softBg,
    required Color textColor,
    required Color mutedColor,
  }) {
    final unlimited = max >= 999;
    final ratio = unlimited ? 0.15 : (max > 0 ? (used / max).clamp(0.0, 1.0) : 0.0);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(label,
                style: TextStyle(
                    color: mutedColor,
                    fontSize: 12,
                    fontWeight: FontWeight.w600)),
            Text(
              unlimited ? '$used / Ilimitados' : '$used / $max',
              style: TextStyle(
                color: textColor,
                fontSize: 12,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
        const SizedBox(height: 5),
        ClipRRect(
          borderRadius: BorderRadius.circular(999),
          child: LinearProgressIndicator(
            value: ratio,
            minHeight: 7,
            backgroundColor: softBg,
            valueColor: AlwaysStoppedAnimation<Color>(primaryBlue),
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // TAB 3: ALERTAS EN VIVO + RESOLVER + CERRAR SESIÓN
  // =========================================================================
  Widget _buildAlertasTab({
    required Color cardColor,
    required Color softBg,
    required Color textColor,
    required Color mutedColor,
    required Color primaryBlue,
    required int activeAlertsCount,
  }) {
    final visibles = _soloAlertasActivas
        ? _alertas.where((a) => _toInt(a['resuelta']) == 0).toList()
        : _alertas;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Centro de Alertas IoT',
                    style: TextStyle(
                      color: textColor,
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  Text(
                    '$activeAlertsCount alertas activas sin resolver',
                    style: TextStyle(color: mutedColor, fontSize: 12.5),
                  ),
                ],
              ),
            ),
            TextButton.icon(
              onPressed: _cerrarSesion,
              icon: const Icon(Icons.logout_rounded,
                  size: 18, color: Colors.redAccent),
              label: const Text(
                'Cerrar Sesión',
                style: TextStyle(
                  color: Colors.redAccent,
                  fontWeight: FontWeight.w700,
                  fontSize: 12.5,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),

        Row(
          children: [
            ChoiceChip(
              label: Text('Activas ($activeAlertsCount)'),
              selected: _soloAlertasActivas,
              onSelected: (_) => setState(() => _soloAlertasActivas = true),
            ),
            const SizedBox(width: 8),
            ChoiceChip(
              label: Text('Todas (${_alertas.length})'),
              selected: !_soloAlertasActivas,
              onSelected: (_) => setState(() => _soloAlertasActivas = false),
            ),
            const Spacer(),
            IconButton(
              onPressed: _cargarTodo,
              icon: Icon(Icons.refresh_rounded, color: primaryBlue),
            ),
          ],
        ),
        const SizedBox(height: 12),

        if (visibles.isEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: cardColor,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Column(
              children: [
                const Icon(Icons.check_circle_outline_rounded,
                    size: 42, color: Color(0xFF059669)),
                const SizedBox(height: 10),
                Text(
                  'Sin alertas pendientes',
                  style: TextStyle(
                    color: textColor,
                    fontSize: 15.5,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Usa "Simular ESP32" en la pestaña Racks para probar disparo de alertas en vivo.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: mutedColor, fontSize: 12.5),
                ),
              ],
            ),
          )
        else
          ...visibles.map((a) {
            final resuelta = _toInt(a['resuelta']) == 1;
            final nivel = (a['nivel'] ?? 'warning').toString();
            final Color sevColor = nivel == 'critical'
                ? Colors.redAccent
                : nivel == 'info'
                    ? primaryBlue
                    : Colors.orange;
            final idAlerta = _toInt(a['id_alerta']);

            return Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(15),
              decoration: BoxDecoration(
                color: cardColor,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(
                  color: resuelta
                      ? mutedColor.withValues(alpha: 0.15)
                      : sevColor.withValues(alpha: 0.35),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 9, vertical: 3),
                        decoration: BoxDecoration(
                          color: (resuelta ? const Color(0xFF059669) : sevColor)
                              .withValues(alpha: 0.14),
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(
                          resuelta ? 'RESUELTA' : nivel.toUpperCase(),
                          style: TextStyle(
                            color: resuelta
                                ? const Color(0xFF059669)
                                : sevColor,
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                      Text(
                        a['codigo_rack']?.toString() ?? 'RACK',
                        style: TextStyle(
                          color: mutedColor,
                          fontSize: 11.5,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    a['tipo_alerta']?.toString() ?? 'Evento IoT',
                    style: TextStyle(
                      color: textColor,
                      fontSize: 14.5,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    a['mensaje']?.toString() ?? '',
                    style: TextStyle(color: mutedColor, fontSize: 12.5),
                  ),
                  const SizedBox(height: 10),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        a['fecha_alerta']?.toString() ?? '',
                        style: TextStyle(color: mutedColor, fontSize: 11),
                      ),
                      if (!resuelta)
                        ElevatedButton.icon(
                          onPressed: () async {
                            final res =
                                await ApiService.resolverAlerta(idAlerta);
                            if (!mounted) return;
                            if (res['ok'] == true) {
                              _snack('Alerta marcada como resuelta.');
                              await _cargarTodo();
                            } else {
                              _snack(
                                res['error']?.toString() ??
                                    'No se pudo resolver la alerta.',
                                isError: true,
                              );
                            }
                          },
                          icon: const Icon(Icons.check_rounded, size: 16),
                          label: const Text('Resolver'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF059669),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 7),
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(10)),
                            textStyle: const TextStyle(
                                fontSize: 12, fontWeight: FontWeight.w700),
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            );
          }),
      ],
    );
  }

  // =========================================================================
  // MODALES INTERACTIVOS (RACK/UMBRALES, ESP32, PLAN B2B, EMPRESA, EMAIL)
  // =========================================================================
  void _abrirModalRack({Map<String, dynamic>? rackEditar}) {
    final esEdicion = rackEditar != null;
    final codigoCtrl = TextEditingController(
        text: rackEditar?['codigo_rack']?.toString() ??
            'RACK-0${_racks.length + 1}');
    final nombreCtrl = TextEditingController(
        text: rackEditar?['nombre_rack']?.toString() ?? '');
    final ubicacionCtrl = TextEditingController(
        text: rackEditar?['ubicacion']?.toString() ?? 'Site Principal');
    final tempCtrl = TextEditingController(
        text: _toDouble(rackEditar?['temp_max'], 27.0).toString());
    final humCtrl = TextEditingController(
        text: _toDouble(rackEditar?['hum_max'], 60.0).toString());
    final kwCtrl = TextEditingController(
        text: _toDouble(rackEditar?['kw_max'], 2.5).toString());
    bool saving = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
          title: Text(
            esEdicion ? 'Editar Umbrales TI' : 'Nuevo Rack IoT',
            style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800),
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                if (!esEdicion)
                  TextField(
                    controller: codigoCtrl,
                    decoration: const InputDecoration(
                      labelText: 'Código Rack (ej. RACK-05)',
                    ),
                  ),
                TextField(
                  controller: nombreCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Nombre del Rack',
                  ),
                ),
                TextField(
                  controller: ubicacionCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Ubicación física',
                  ),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: tempCtrl,
                        keyboardType: TextInputType.number,
                        decoration:
                            const InputDecoration(labelText: 'Temp Máx °C'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: humCtrl,
                        keyboardType: TextInputType.number,
                        decoration:
                            const InputDecoration(labelText: 'Hum Máx %'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: kwCtrl,
                        keyboardType: TextInputType.number,
                        decoration:
                            const InputDecoration(labelText: 'kW Máx'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: saving ? null : () => Navigator.pop(ctx),
              child: const Text('Cancelar'),
            ),
            ElevatedButton(
              onPressed: saving
                  ? null
                  : () async {
                      final nombre = nombreCtrl.text.trim();
                      final ubicacion = ubicacionCtrl.text.trim();
                      if (nombre.isEmpty || ubicacion.isEmpty) {
                        _snack('Indica nombre y ubicación del rack.',
                            isError: true);
                        return;
                      }
                      setDialogState(() => saving = true);
                      final res = await ApiService.crearOActualizarRack(
                        idRack: esEdicion ? _toInt(rackEditar['id_rack']) : null,
                        codigoRack: esEdicion ? null : codigoCtrl.text.trim(),
                        nombreRack: nombre,
                        ubicacion: ubicacion,
                        tempMax: double.tryParse(tempCtrl.text) ?? 27.0,
                        humMax: double.tryParse(humCtrl.text) ?? 60.0,
                        kwMax: double.tryParse(kwCtrl.text) ?? 2.5,
                      );
                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);
                      if (res['ok'] == true) {
                        _snack(res['message']?.toString() ?? 'Rack guardado.');
                        await _cargarTodo();
                      } else {
                        _snack(
                          res['error']?.toString() ??
                              'No se pudo guardar el rack.',
                          isError: true,
                        );
                      }
                    },
              child: saving
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : Text(esEdicion ? 'Guardar Umbrales' : 'Crear Rack'),
            ),
          ],
        ),
      ),
    );
  }

  void _abrirModalSimularEsp32(Map<String, dynamic> rack) {
    final lec = (rack['lectura'] as Map<String, dynamic>?) ?? {};
    final tempCtrl = TextEditingController(
        text: _toDouble(lec['temperatura'], 22.5).toString());
    final humCtrl =
        TextEditingController(text: _toDouble(lec['humedad'], 45.0).toString());
    final kwCtrl = TextEditingController(
        text: _toDouble(lec['consumo_kw'], 1.2).toString());
    bool puertaAbierta = _toInt(lec['puerta_abierta']) == 1;
    bool sending = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
          title: Text(
            'Simular ESP32 • ${rack['codigo_rack']}',
            style: const TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800),
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Wrap(
                  spacing: 6,
                  children: [
                    ActionChip(
                      label: const Text('Normal 22°C'),
                      onPressed: () {
                        tempCtrl.text = '22.0';
                        humCtrl.text = '44.0';
                        kwCtrl.text = '1.10';
                        setDialogState(() => puertaAbierta = false);
                      },
                    ),
                    ActionChip(
                      label: const Text('Calor 31°C'),
                      onPressed: () {
                        tempCtrl.text = '31.2';
                        setDialogState(() {});
                      },
                    ),
                    ActionChip(
                      label: const Text('Puerta Abierta'),
                      onPressed: () =>
                          setDialogState(() => puertaAbierta = true),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: tempCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'Temperatura °C'),
                ),
                TextField(
                  controller: humCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'Humedad %'),
                ),
                TextField(
                  controller: kwCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'Consumo kW'),
                ),
                SwitchListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('Puerta Física Abierta'),
                  value: puertaAbierta,
                  onChanged: (v) => setDialogState(() => puertaAbierta = v),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: sending ? null : () => Navigator.pop(ctx),
              child: const Text('Cancelar'),
            ),
            ElevatedButton(
              onPressed: sending
                  ? null
                  : () async {
                      setDialogState(() => sending = true);
                      final res = await ApiService.simularTelemetria(
                        idRack: _toInt(rack['id_rack'], 1),
                        temperatura: double.tryParse(tempCtrl.text) ?? 22.5,
                        humedad: double.tryParse(humCtrl.text) ?? 45.0,
                        consumoKw: double.tryParse(kwCtrl.text) ?? 1.2,
                        puertaAbierta: puertaAbierta ? 1 : 0,
                      );
                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);
                      if (res['ok'] == true) {
                        _snack('Telemetría ESP32 enviada y evaluada.');
                        await _cargarTodo();
                      } else {
                        _snack(
                          res['error']?.toString() ??
                              'Error al enviar telemetría.',
                          isError: true,
                        );
                      }
                    },
              child: sending
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Enviar Lectura'),
            ),
          ],
        ),
      ),
    );
  }

  void _abrirModalCambiarPlan(String planActual) {
    String seleccionado = planActual;
    bool updating = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
          title: const Text(
            'Cambiar Plan B2B',
            style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: ApiService.b2bPlans.entries.map((entry) {
              final key = entry.key;
              final cfg = entry.value;
              final selected = seleccionado == key;
              return ListTile(
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                selected: selected,
                leading: Icon(
                  selected
                      ? Icons.radio_button_checked_rounded
                      : Icons.radio_button_off_rounded,
                ),
                title: Text(
                  '${cfg['nombre']} (\$${(cfg['precio'] as num).toStringAsFixed(0)}/mes)',
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
                subtitle: Text(
                  '${cfg['max_racks'] >= 999 ? 'Ilimitados' : cfg['max_racks']} Racks • ${cfg['max_cuentas'] >= 999 ? 'Ilimitadas' : cfg['max_cuentas']} Cuentas',
                ),
                onTap: () => setDialogState(() => seleccionado = key),
              );
            }).toList(),
          ),
          actions: [
            TextButton(
              onPressed: updating ? null : () => Navigator.pop(ctx),
              child: const Text('Cancelar'),
            ),
            ElevatedButton(
              onPressed: updating
                  ? null
                  : () async {
                      setDialogState(() => updating = true);
                      final res = await ApiService.cambiarPlanB2B(seleccionado);
                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);
                      if (res['ok'] == true) {
                        _snack(res['message']?.toString() ??
                            'Plan B2B actualizado.');
                        await _cargarTodo();
                      } else {
                        _snack(
                          res['error']?.toString() ??
                              'No se pudo actualizar el plan.',
                          isError: true,
                        );
                      }
                    },
              child: updating
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Aplicar Plan'),
            ),
          ],
        ),
      ),
    );
  }

  void _abrirModalAltaEmpresa() {
    final nombreCtrl = TextEditingController();
    final rfcCtrl = TextEditingController();
    final slugCtrl = TextEditingController();
    final sectorCtrl = TextEditingController(text: 'DataCenter & Cloud');
    final telCtrl = TextEditingController(text: '+52 999 123 4567');
    String planSel = 'corporate';
    bool creating = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
          title: const Text(
            'Dar de Alta Nueva Empresa',
            style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800),
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nombreCtrl,
                  decoration: const InputDecoration(labelText: 'Razón Social'),
                ),
                TextField(
                  controller: rfcCtrl,
                  decoration: const InputDecoration(labelText: 'RFC / ID Fiscal'),
                ),
                TextField(
                  controller: slugCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Subdominio (ej. cloudops)',
                  ),
                ),
                TextField(
                  controller: sectorCtrl,
                  decoration: const InputDecoration(labelText: 'Sector'),
                ),
                TextField(
                  controller: telCtrl,
                  decoration: const InputDecoration(labelText: 'Teléfono'),
                ),
                const SizedBox(height: 8),
                DropdownButtonFormField<String>(
                  initialValue: planSel,
                  decoration: const InputDecoration(labelText: 'Plan B2B'),
                  items: const [
                    DropdownMenuItem(
                        value: 'starter', child: Text('Starter (\$49/mes)')),
                    DropdownMenuItem(
                        value: 'corporate',
                        child: Text('Corporate (\$149/mes)')),
                    DropdownMenuItem(
                        value: 'enterprise',
                        child: Text('Enterprise (\$399/mes)')),
                  ],
                  onChanged: (v) {
                    if (v != null) setDialogState(() => planSel = v);
                  },
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: creating ? null : () => Navigator.pop(ctx),
              child: const Text('Cancelar'),
            ),
            ElevatedButton(
              onPressed: creating
                  ? null
                  : () async {
                      if (nombreCtrl.text.trim().isEmpty ||
                          rfcCtrl.text.trim().isEmpty ||
                          slugCtrl.text.trim().isEmpty) {
                        _snack('Completa razón social, RFC y subdominio.',
                            isError: true);
                        return;
                      }
                      setDialogState(() => creating = true);
                      final res = await ApiService.registrarNuevaEmpresa(
                        nombreEmpresa: nombreCtrl.text.trim(),
                        rfcNit: rfcCtrl.text.trim().toUpperCase(),
                        slugDominio: slugCtrl.text.trim().toLowerCase(),
                        sector: sectorCtrl.text.trim(),
                        telefono: telCtrl.text.trim(),
                        plan: planSel,
                      );
                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);
                      if (res['ok'] == true) {
                        final jefe = res['jefe_ti_credenciales']
                            as Map<String, dynamic>?;
                        _snack(
                          jefe != null
                              ? 'Empresa activa. Jefe TI: ${jefe['correo_institucional']}'
                              : 'Empresa registrada y activada.',
                        );
                        await _cargarTodo();
                      } else {
                        _snack(
                          res['error']?.toString() ??
                              'Error al registrar la empresa.',
                          isError: true,
                        );
                      }
                    },
              child: creating
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Registrar Empresa'),
            ),
          ],
        ),
      ),
    );
  }

  void _abrirModalSolicitarCorreo(String dominio) {
    final nombreCtrl = TextEditingController();
    final apellidosCtrl = TextEditingController();
    String rolSel = 'operador';
    bool sending = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
          title: Text(
            'Solicitar @$dominio',
            style: const TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nombreCtrl,
                decoration:
                    const InputDecoration(labelText: 'Nombre del colaborador'),
              ),
              TextField(
                controller: apellidosCtrl,
                decoration: const InputDecoration(labelText: 'Apellidos'),
              ),
              const SizedBox(height: 8),
              DropdownButtonFormField<String>(
                initialValue: rolSel,
                decoration:
                    const InputDecoration(labelText: 'Rol Institucional'),
                items: const [
                  DropdownMenuItem(
                      value: 'operador', child: Text('Operador IoT')),
                  DropdownMenuItem(
                      value: 'jefe_ti', child: Text('Jefe de TI')),
                ],
                onChanged: (v) {
                  if (v != null) setDialogState(() => rolSel = v);
                },
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: sending ? null : () => Navigator.pop(ctx),
              child: const Text('Cancelar'),
            ),
            ElevatedButton(
              onPressed: sending
                  ? null
                  : () async {
                      if (nombreCtrl.text.trim().isEmpty ||
                          apellidosCtrl.text.trim().isEmpty) {
                        _snack('Indica nombre y apellidos.', isError: true);
                        return;
                      }
                      setDialogState(() => sending = true);
                      final res = await ApiService.solicitarCorreoInstitucional(
                        nombre: nombreCtrl.text.trim(),
                        apellidos: apellidosCtrl.text.trim(),
                        rolSolicitado: rolSel,
                      );
                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);
                      if (res['ok'] == true) {
                        _snack(res['message']?.toString() ??
                            'Solicitud registrada.');
                        await _cargarTodo();
                      } else {
                        _snack(
                          res['error']?.toString() ??
                              'No se pudo enviar la solicitud.',
                          isError: true,
                        );
                      }
                    },
              child: sending
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Enviar Solicitud'),
            ),
          ],
        ),
      ),
    );
  }

  void _mostrarPreviewCorreo(
    Map<String, dynamic> correo,
    Color primaryBlue,
    Color textColor,
    Color mutedColor,
  ) {
    final cuerpo = _stripHtml(correo['cuerpo_html']?.toString() ?? '');
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        title: Row(
          children: [
            Icon(Icons.mail_outline_rounded, color: primaryBlue),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                correo['asunto']?.toString() ?? 'Detalle de Correo SMTP',
                style: const TextStyle(
                    fontSize: 15.5, fontWeight: FontWeight.w800),
              ),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Destinatario: ${correo['destinatario'] ?? '-'}',
                style: TextStyle(
                    color: textColor,
                    fontSize: 12.5,
                    fontWeight: FontWeight.w700),
              ),
              Text(
                'Estado SMTP: ${correo['estado_smtp'] ?? 'cola_local_auditable'}',
                style: TextStyle(color: primaryBlue, fontSize: 12),
              ),
              Text(
                'Fecha: ${correo['fecha_envio'] ?? '-'}',
                style: TextStyle(color: mutedColor, fontSize: 11.5),
              ),
              const Divider(height: 18),
              SelectableText(
                cuerpo.isEmpty ? '(Sin contenido HTML)' : cuerpo,
                style: TextStyle(color: textColor, fontSize: 12.5),
              ),
            ],
          ),
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cerrar'),
          ),
        ],
      ),
    );
  }

  void _mostrarDialogoBitacoraMfa(
    Color primaryBlue,
    Color cardColor,
    Color textColor,
    Color mutedColor,
  ) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        title: Row(
          children: [
            Icon(Icons.verified_user_rounded, color: primaryBlue),
            const SizedBox(width: 8),
            const Text('Sesión Zero-Trust MFA',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Usuario: ${ApiService.currentUser['nombre'] ?? '-'}',
                style: TextStyle(
                    color: textColor, fontWeight: FontWeight.w700)),
            const SizedBox(height: 4),
            Text('Correo: ${ApiService.currentUser['correo'] ?? '-'}',
                style: TextStyle(color: mutedColor, fontSize: 12.5)),
            const SizedBox(height: 4),
            Text('Rol: $_rolLabel',
                style: TextStyle(color: mutedColor, fontSize: 12.5)),
            const SizedBox(height: 8),
            Text(
              'Token JWT activo y autenticado mediante Google Authenticator (TOTP RFC 6238).',
              style: TextStyle(color: primaryBlue, fontSize: 12),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cerrar'),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // WIDGETS AUXILIARES MINIMALIST BLUE
  // =========================================================================
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
    required Color accentColor,
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
            child: Icon(icon, color: accentColor, size: 20),
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

    canvas.drawCircle(
      Offset(size.width * 0.66, size.height * 0.21),
      5.5,
      Paint()..color = color,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
