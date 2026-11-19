import 'package:flutter/material.dart';
import '../services/api_service.dart';

/// PANTALLA 2: Registro unificado B2B "Crea tu cuenta y da de alta tu Empresa" (Minimalist Blue)
class RegisterScreen extends StatefulWidget {
  final VoidCallback onToggleTheme;

  const RegisterScreen({super.key, required this.onToggleTheme});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _nombreCtrl = TextEditingController();
  final _apellidosCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();

  final _empresaCtrl = TextEditingController();
  final _rfcCtrl = TextEditingController();
  final _slugCtrl = TextEditingController();
  final _telefonoCtrl = TextEditingController();

  bool _obscurePass = true;
  bool _obscureConfirm = true;
  bool _acceptedTerms = true;
  bool _submitting = false;
  bool _slugEditedManually = false;
  String _selectedPlan = 'corporate';
  String _selectedSector = 'DataCenter & Cloud';

  static const List<String> _sectores = [
    'DataCenter & Cloud',
    'Finanzas y Banca',
    'Salud y Hospitales',
    'Manufactura IoT',
    'Educación y Gobierno',
  ];

  @override
  void dispose() {
    _nombreCtrl.dispose();
    _apellidosCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _confirmCtrl.dispose();
    _empresaCtrl.dispose();
    _rfcCtrl.dispose();
    _slugCtrl.dispose();
    _telefonoCtrl.dispose();
    super.dispose();
  }

  String get _cleanSlug {
    final raw = _slugCtrl.text.trim().toLowerCase();
    final cleaned = raw.replaceAll(RegExp(r'[^a-z0-9\-]'), '');
    return cleaned.isEmpty ? 'tuempresa' : cleaned;
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: Colors.redAccent,
      ),
    );
  }

  Future<void> _submitRegistro() async {
    final nombre = _nombreCtrl.text.trim();
    final apellidos = _apellidosCtrl.text.trim();
    final correo = _emailCtrl.text.trim().toLowerCase();
    final pass = _passCtrl.text;
    final confirm = _confirmCtrl.text;
    final nombreEmpresa = _empresaCtrl.text.trim();
    final rfc = _rfcCtrl.text.trim().toUpperCase();
    final slug = _slugCtrl.text
        .trim()
        .toLowerCase()
        .replaceAll(RegExp(r'[^a-z0-9\-]'), '');
    final telefono = _telefonoCtrl.text.trim();

    if (nombre.isEmpty ||
        apellidos.isEmpty ||
        correo.isEmpty ||
        pass.isEmpty ||
        confirm.isEmpty ||
        nombreEmpresa.isEmpty ||
        rfc.isEmpty ||
        slug.isEmpty ||
        telefono.isEmpty) {
      _showError('Completa todos los campos del representante y la empresa.');
      return;
    }

    if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(correo)) {
      _showError('Ingresa un correo electrónico válido.');
      return;
    }

    if (pass.length < 8) {
      _showError('La contraseña debe tener al menos 8 caracteres.');
      return;
    }

    if (pass != confirm) {
      _showError('Las contraseñas no coinciden.');
      return;
    }

    if (!_acceptedTerms) {
      _showError('Debes aceptar los Términos y la Política de Privacidad.');
      return;
    }

    setState(() => _submitting = true);
    final res = await ApiService.registerCompanyAndRep(
      nombre: nombre,
      apellidos: apellidos,
      correo: correo,
      contrasena: pass,
      nombreEmpresa: nombreEmpresa,
      rfcNit: rfc,
      slugDominio: slug,
      telefono: telefono,
      sector: _selectedSector,
      plan: _selectedPlan,
    );
    if (!mounted) return;
    setState(() => _submitting = false);

    if (res['ok'] == true) {
      _mostrarDialogoActivacionYMfa(res, correo);
    } else {
      _showError(
        res['error']?.toString() ?? 'No fue posible registrar la empresa.',
      );
    }
  }

  void _mostrarDialogoActivacionYMfa(
      Map<String, dynamic> res, String correoRep) {
    final empresa = (res['empresa'] as Map<String, dynamic>?) ?? {};
    final jefeCreds =
        (res['jefe_ti_credenciales'] as Map<String, dynamic>?) ?? {};
    final totpHint =
        res['codigo_totp_actual_para_pruebas_postman']?.toString() ?? '482910';
    final otpCtrl = TextEditingController(text: totpHint);
    final primaryBlue = const Color(0xFF1D58E8);
    bool verifying = false;
    String? dialogError;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) {
          final isDark = Theme.of(ctx).brightness == Brightness.dark;
          final softBg =
              isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9);
          final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
          final mutedColor =
              isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);

          return AlertDialog(
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
            title: Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: const Color(0xFF059669).withValues(alpha: 0.14),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.verified_rounded,
                      color: Color(0xFF059669), size: 22),
                ),
                const SizedBox(width: 10),
                const Expanded(
                  child: Text(
                    'Empresa Activada + MFA',
                    style:
                        TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800),
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
                    '${empresa['nombre_empresa'] ?? 'Tu empresa'} está activa con RACK-01 listo y Plan ${(empresa['plan'] ?? _selectedPlan).toString().toUpperCase()}.',
                    style: TextStyle(fontSize: 12.5, color: textColor),
                  ),
                  const SizedBox(height: 12),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: softBg,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                          color: primaryBlue.withValues(alpha: 0.18)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(Icons.mark_email_read_outlined,
                                size: 16, color: primaryBlue),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                'Jefe de TI generado (enviado vía SMTP)',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w800,
                                  color: primaryBlue,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Correo: ${jefeCreds['correo_institucional'] ?? 'jefe.ti@$_cleanSlug.sysmonitor.com'}',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: textColor,
                          ),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          'Contraseña temporal: ${jefeCreds['password_temporal'] ?? 'Generada en SMTP'}',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: textColor,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Se envió copia segura a ${empresa['correo_enviado_a'] ?? correoRep}',
                          style: TextStyle(fontSize: 11, color: mutedColor),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                  Text(
                    'Verifica tu código de 6 dígitos (Google Authenticator TOTP) para entrar al Dashboard:',
                    style: TextStyle(fontSize: 12.5, color: mutedColor),
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: otpCtrl,
                    keyboardType: TextInputType.number,
                    maxLength: 6,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 8,
                    ),
                    decoration: InputDecoration(
                      counterText: '',
                      filled: true,
                      fillColor: softBg,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Código TOTP actual (demo): $totpHint',
                    style: TextStyle(
                      fontSize: 11.5,
                      color: primaryBlue,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  if (dialogError != null) ...[
                    const SizedBox(height: 8),
                    Text(
                      dialogError!,
                      style: const TextStyle(
                        color: Colors.redAccent,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: verifying
                    ? null
                    : () {
                        Navigator.pop(ctx);
                        Navigator.pushReplacementNamed(context, '/login');
                      },
                child: const Text('Ir a Login'),
              ),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryBlue,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
                onPressed: verifying
                    ? null
                    : () async {
                        final code = otpCtrl.text.trim();
                        if (code.length != 6) {
                          setDialogState(() =>
                              dialogError = 'Ingresa los 6 dígitos TOTP.');
                          return;
                        }
                        setDialogState(() {
                          verifying = true;
                          dialogError = null;
                        });
                        final verify =
                            await ApiService.verificarMfa(correoRep, code);
                        if (!ctx.mounted) return;
                        if (verify['ok'] == true) {
                          Navigator.pop(ctx);
                          if (mounted) {
                            Navigator.pushReplacementNamed(
                                context, '/dashboard');
                          }
                        } else {
                          setDialogState(() {
                            verifying = false;
                            dialogError = verify['error']?.toString() ??
                                'Código MFA inválido.';
                          });
                        }
                      },
                child: verifying
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                            strokeWidth: 2, color: Colors.white),
                      )
                    : const Text('Verificar y Entrar'),
              ),
            ],
          );
        },
      ),
    );
  }

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
          padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Barra superior
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

              const SizedBox(height: 18),

              Text(
                'Crea tu cuenta y da de alta tu Empresa',
                style: TextStyle(
                  color: textColor,
                  fontSize: 25,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                'Aprovisiona tu sitio IoT, recibe credenciales por SMTP y activa MFA al instante.',
                style: TextStyle(color: mutedColor, fontSize: 13),
              ),

              const SizedBox(height: 18),

              // Tarjeta Principal Unificada B2B
              Container(
                padding: const EdgeInsets.all(18),
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
                    _buildSectionTitle(
                      step: '1',
                      title: 'Representante Legal',
                      primaryBlue: primaryBlue,
                      textColor: textColor,
                    ),
                    const SizedBox(height: 12),

                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildLabel('NOMBRE(S)', mutedColor),
                              _buildInput(
                                controller: _nombreCtrl,
                                hint: 'Carlos',
                                icon: Icons.person_outline_rounded,
                                inputBg: inputBg,
                                textColor: textColor,
                                mutedColor: mutedColor,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildLabel('APELLIDOS', mutedColor),
                              _buildInput(
                                controller: _apellidosCtrl,
                                hint: 'Mendoza',
                                icon: Icons.badge_outlined,
                                inputBg: inputBg,
                                textColor: textColor,
                                mutedColor: mutedColor,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    _buildLabel('CORREO ELECTRÓNICO', mutedColor),
                    _buildInput(
                      controller: _emailCtrl,
                      hint: 'representante@empresa.com',
                      icon: Icons.mail_outline_rounded,
                      inputBg: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                      keyboardType: TextInputType.emailAddress,
                    ),
                    const SizedBox(height: 12),

                    _buildLabel('CONTRASEÑA (MÍN. 8 CARACTERES)', mutedColor),
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
                    const SizedBox(height: 12),

                    _buildLabel('CONFIRMAR CONTRASEÑA', mutedColor),
                    _buildInput(
                      controller: _confirmCtrl,
                      hint: 'Repite la contraseña',
                      icon: Icons.verified_user_outlined,
                      inputBg: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                      obscure: _obscureConfirm,
                      suffix: IconButton(
                        icon: Icon(
                          _obscureConfirm
                              ? Icons.visibility_outlined
                              : Icons.visibility_off_outlined,
                          color: mutedColor,
                          size: 20,
                        ),
                        onPressed: () =>
                            setState(() => _obscureConfirm = !_obscureConfirm),
                      ),
                    ),

                    const SizedBox(height: 20),
                    Divider(color: mutedColor.withValues(alpha: 0.15)),
                    const SizedBox(height: 14),

                    // SECCIÓN 2: ALTA DE EMPRESA
                    _buildSectionTitle(
                      step: '2',
                      title: 'Alta de Empresa',
                      primaryBlue: primaryBlue,
                      textColor: textColor,
                    ),
                    const SizedBox(height: 12),

                    _buildLabel('NOMBRE / RAZÓN SOCIAL', mutedColor),
                    _buildInput(
                      controller: _empresaCtrl,
                      hint: 'Ej. NovaCloud DataCenters S.A.',
                      icon: Icons.business_rounded,
                      inputBg: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                      onChanged: (val) {
                        if (!_slugEditedManually) {
                          final autoSlug = val
                              .toLowerCase()
                              .replaceAll(RegExp(r'[^a-z0-9]'), '');
                          _slugCtrl.text = autoSlug.length > 18
                              ? autoSlug.substring(0, 18)
                              : autoSlug;
                          setState(() {});
                        }
                      },
                    ),
                    const SizedBox(height: 12),

                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildLabel('RFC / ID FISCAL', mutedColor),
                              _buildInput(
                                controller: _rfcCtrl,
                                hint: 'NCD260101AA1',
                                icon: Icons.receipt_long_outlined,
                                inputBg: inputBg,
                                textColor: textColor,
                                mutedColor: mutedColor,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildLabel('TELÉFONO', mutedColor),
                              _buildInput(
                                controller: _telefonoCtrl,
                                hint: '+52 999 123 4567',
                                icon: Icons.phone_outlined,
                                inputBg: inputBg,
                                textColor: textColor,
                                mutedColor: mutedColor,
                                keyboardType: TextInputType.phone,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    _buildLabel('SUBDOMINIO INSTITUCIONAL', mutedColor),
                    _buildInput(
                      controller: _slugCtrl,
                      hint: 'novacloud',
                      icon: Icons.alternate_email_rounded,
                      inputBg: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                      onChanged: (_) {
                        _slugEditedManually = true;
                        setState(() {});
                      },
                    ),
                    const SizedBox(height: 6),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 7),
                      decoration: BoxDecoration(
                        color: primaryBlue.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.domain_verification_rounded,
                              size: 15, color: primaryBlue),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              'Dominio de correos: @$_cleanSlug.sysmonitor.com',
                              style: TextStyle(
                                color: primaryBlue,
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),

                    _buildLabel('SECTOR INDUSTRIAL', mutedColor),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14),
                      decoration: BoxDecoration(
                        color: inputBg,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          value: _selectedSector,
                          isExpanded: true,
                          dropdownColor: cardColor,
                          style: TextStyle(color: textColor, fontSize: 14),
                          items: _sectores
                              .map((s) => DropdownMenuItem(
                                    value: s,
                                    child: Text(s),
                                  ))
                              .toList(),
                          onChanged: (v) {
                            if (v != null) setState(() => _selectedSector = v);
                          },
                        ),
                      ),
                    ),

                    const SizedBox(height: 20),
                    Divider(color: mutedColor.withValues(alpha: 0.15)),
                    const SizedBox(height: 14),

                    // SECCIÓN 3: SELECTOR DE PLAN B2B
                    _buildSectionTitle(
                      step: '3',
                      title: 'Selector de Plan B2B',
                      primaryBlue: primaryBlue,
                      textColor: textColor,
                    ),
                    const SizedBox(height: 10),

                    _buildPlanCard(
                      id: 'starter',
                      title: 'Starter',
                      price: '\$49/mes',
                      limits: '2 Racks • 3 Cuentas',
                      subtitle: 'Pequeñas empresas / 1 site',
                      primaryBlue: primaryBlue,
                      cardColor: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                    ),
                    const SizedBox(height: 8),
                    _buildPlanCard(
                      id: 'corporate',
                      title: 'Corporate',
                      price: '\$149/mes',
                      limits: '10 Racks • 15 Cuentas',
                      subtitle: 'Empresas medianas / multi-rack',
                      recommended: true,
                      primaryBlue: primaryBlue,
                      cardColor: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                    ),
                    const SizedBox(height: 8),
                    _buildPlanCard(
                      id: 'enterprise',
                      title: 'Enterprise',
                      price: '\$399/mes',
                      limits: 'Ilimitados (999 Racks/Cuentas)',
                      subtitle: 'DataCenters y alta disponibilidad',
                      primaryBlue: primaryBlue,
                      cardColor: inputBg,
                      textColor: textColor,
                      mutedColor: mutedColor,
                    ),

                    const SizedBox(height: 12),

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
                    const SizedBox(height: 12),

                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: ElevatedButton(
                        onPressed: _submitting ? null : _submitRegistro,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryBlue,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        child: _submitting
                            ? const SizedBox(
                                width: 22,
                                height: 22,
                                child: CircularProgressIndicator(
                                    strokeWidth: 2.5, color: Colors.white),
                              )
                            : const Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    'Crear Cuenta y Activar Empresa',
                                    style: TextStyle(
                                      fontSize: 15,
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

              const SizedBox(height: 18),

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

  Widget _buildSectionTitle({
    required String step,
    required String title,
    required Color primaryBlue,
    required Color textColor,
  }) {
    return Row(
      children: [
        Container(
          width: 24,
          height: 24,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: primaryBlue,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Text(
            step,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 12,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
        const SizedBox(width: 8),
        Text(
          title,
          style: TextStyle(
            color: textColor,
            fontSize: 15.5,
            fontWeight: FontWeight.w800,
          ),
        ),
      ],
    );
  }

  Widget _buildPlanCard({
    required String id,
    required String title,
    required String price,
    required String limits,
    required String subtitle,
    bool recommended = false,
    required Color primaryBlue,
    required Color cardColor,
    required Color textColor,
    required Color mutedColor,
  }) {
    final selected = _selectedPlan == id;
    return GestureDetector(
      onTap: () => setState(() => _selectedPlan = id),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.all(13),
        decoration: BoxDecoration(
          color:
              selected ? primaryBlue.withValues(alpha: 0.09) : cardColor,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: selected
                ? primaryBlue
                : mutedColor.withValues(alpha: 0.2),
            width: selected ? 1.8 : 1,
          ),
        ),
        child: Row(
          children: [
            Icon(
              selected
                  ? Icons.radio_button_checked_rounded
                  : Icons.radio_button_off_rounded,
              color: selected ? primaryBlue : mutedColor,
              size: 20,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(
                        title,
                        style: TextStyle(
                          color: textColor,
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      if (recommended) ...[
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 7, vertical: 2),
                          decoration: BoxDecoration(
                            color: primaryBlue.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            'Popular',
                            style: TextStyle(
                              color: primaryBlue,
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '$limits • $subtitle',
                    style: TextStyle(color: mutedColor, fontSize: 11.5),
                  ),
                ],
              ),
            ),
            Text(
              price,
              style: TextStyle(
                color: selected ? primaryBlue : textColor,
                fontSize: 13.5,
                fontWeight: FontWeight.w900,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLabel(String text, Color color) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 5.0),
      child: Text(
        text,
        style: TextStyle(
          color: color,
          fontSize: 10.5,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.5,
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
    TextInputType? keyboardType,
    ValueChanged<String>? onChanged,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: inputBg,
        borderRadius: BorderRadius.circular(14),
      ),
      child: TextField(
        controller: controller,
        obscureText: obscure,
        keyboardType: keyboardType,
        onChanged: onChanged,
        style: TextStyle(color: textColor, fontSize: 14),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle:
              TextStyle(color: mutedColor.withValues(alpha: 0.7), fontSize: 13),
          prefixIcon: Icon(icon, color: mutedColor, size: 19),
          suffixIcon: suffix,
          border: InputBorder.none,
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 12, vertical: 13),
        ),
      ),
    );
  }
}
