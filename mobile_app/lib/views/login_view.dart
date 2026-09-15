import 'package:flutter/material.dart';
import '../services/sync_service.dart';

class LoginView extends StatefulWidget {
  final VoidCallback onLoginSuccess;

  const LoginView({super.key, required this.onLoginSuccess});

  @override
  State<LoginView> createState() => _LoginViewState();
}

class _LoginViewState extends State<LoginView> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;

  Future<void> _handleLogin() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final success = await SyncService.instance.login(
      _emailController.text.trim(),
      _passwordController.text,
    );

    if (!mounted) return;

    if (success) {
      widget.onLoginSuccess();
    } else {
      setState(() {
        _isLoading = false;
        _errorMessage = 'Credenciales inválidas o sin acceso al servidor API.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: Colors.white,
      body: Stack(
        children: [
          // 1. Fondo Ola Superior Derecha (Gradiente Azul Periwinkle)
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            height: size.height * 0.38,
            child: ClipPath(
              clipper: TopWaveClipper(),
              child: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFFDBEAFE), Color(0xFF93C5FD), Color(0xFF60A5FA)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Stack(
                  children: [
                    // Círculo Decorativo Morado Superior
                    Positioned(
                      top: 40,
                      right: 30,
                      child: Container(
                        width: 140,
                        height: 140,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: const Color(0xFF7C3AED).withValues(alpha: 0.85),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF7C3AED).withValues(alpha: 0.3),
                              blurRadius: 15,
                              offset: const Offset(0, 5),
                            )
                          ],
                        ),
                        child: Stack(
                          alignment: Alignment.center,
                          children: [
                            const Icon(Icons.computer_rounded, size: 58, color: Colors.white),
                            Positioned(
                              top: 15,
                              left: 15,
                              child: Container(
                                padding: const EdgeInsets.all(4),
                                decoration: const BoxDecoration(color: Color(0xFFF97316), shape: BoxShape.circle),
                                child: const Icon(Icons.settings, size: 16, color: Colors.white),
                              ),
                            ),
                            Positioned(
                              top: 15,
                              right: 15,
                              child: Container(
                                padding: const EdgeInsets.all(4),
                                decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                                child: const Icon(Icons.access_time_filled, size: 16, color: Color(0xFF1E293B)),
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
          ),

          // 2. Fondo Ola Inferior Completa (Gradiente Morado 100% hasta la base del dispositivo)
          Positioned(
            top: size.height * 0.42,
            bottom: 0,
            left: 0,
            right: 0,
            child: ClipPath(
              clipper: BottomWaveClipper(),
              child: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF6D28D9), Color(0xFF8B5CF6), Color(0xFFA78BFA)],
                    begin: Alignment.bottomLeft,
                    end: Alignment.topRight,
                  ),
                ),
              ),
            ),
          ),

          // 3. Contenido Central (Formulario de Login)
          SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(height: size.height * 0.14),

                  // Título LOGIN
                  const Text(
                    'LOGIN',
                    style: TextStyle(
                      fontSize: 34,
                      fontWeight: FontWeight.w900,
                      color: Color(0xFF1D4ED8),
                      letterSpacing: 1.5,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'CMMS Leonplast • Mantenimiento',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
                  ),
                  const SizedBox(height: 36),

                  // Mensaje de Error si aplica
                  if (_errorMessage != null) ...[
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      decoration: BoxDecoration(
                        color: Colors.red.shade50,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.red.shade200),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.error_outline, color: Colors.red, size: 18),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(_errorMessage!, style: const TextStyle(color: Colors.red, fontSize: 11, fontWeight: FontWeight.w500)),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),
                  ],

                  // Campo Correo Electrónico
                  const Text(
                    'Correo Electrónico',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF334155)),
                  ),
                  const SizedBox(height: 4),
                  TextField(
                    controller: _emailController,
                    style: const TextStyle(color: Color(0xFF0F172A), fontSize: 14, fontWeight: FontWeight.w500),
                    decoration: const InputDecoration(
                      hintText: 'example@email.com',
                      hintStyle: TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
                      enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: Color(0xFF3B82F6), width: 1.5)),
                      focusedBorder: UnderlineInputBorder(borderSide: BorderSide(color: Color(0xFF1D4ED8), width: 2.5)),
                      contentPadding: EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                  const SizedBox(height: 28),

                  // Campo Contraseña
                  const Text(
                    'Contraseña',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF334155)),
                  ),
                  const SizedBox(height: 4),
                  TextField(
                    controller: _passwordController,
                    obscureText: true,
                    style: const TextStyle(color: Color(0xFF0F172A), fontSize: 14, fontWeight: FontWeight.w500),
                    decoration: const InputDecoration(
                      hintText: '••••••••',
                      hintStyle: TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
                      suffixIcon: Icon(Icons.help_outline, size: 18, color: Color(0xFF94A3B8)),
                      enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: Color(0xFFCBD5E1), width: 1.5)),
                      focusedBorder: UnderlineInputBorder(borderSide: BorderSide(color: Color(0xFF1D4ED8), width: 2.5)),
                      contentPadding: EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                  const SizedBox(height: 10),

                  // ¿Olvidaste tu contraseña?
                  Align(
                    alignment: Alignment.centerRight,
                    child: TextButton(
                      onPressed: () {},
                      style: TextButton.styleFrom(padding: EdgeInsets.zero, minimumSize: Size.zero, tapTargetSize: MaterialTapTargetSize.shrinkWrap),
                      child: const Text(
                        '¿Olvidaste tu contraseña?',
                        style: TextStyle(color: Color(0xFF2563EB), fontSize: 12, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),

                  SizedBox(height: size.height * 0.08),

                  // Botón Naranja CÁPSULA (LOGIN)
                  Center(
                    child: Column(
                      children: [
                        Container(
                          width: size.width * 0.65,
                          height: 52,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(30),
                            gradient: const LinearGradient(
                              colors: [Color(0xFFFB923C), Color(0xFFF97316), Color(0xFFEA580C)],
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFFF97316).withValues(alpha: 0.4),
                                blurRadius: 12,
                                offset: const Offset(0, 5),
                              )
                            ],
                          ),
                          child: ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.transparent,
                              shadowColor: Colors.transparent,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                            ),
                            onPressed: _isLoading ? null : _handleLogin,
                            child: _isLoading
                                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                                : const Text(
                                    'LOGIN',
                                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Colors.white, letterSpacing: 1.2),
                                  ),
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Subtítulo de registro/acceso
                        const Text(
                          'Ingreso Exclusivo para Personal Técnico',
                          style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600, shadows: [
                            Shadow(color: Colors.black45, blurRadius: 4),
                          ]),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Clipper para la Ola Superior Derecha
class TopWaveClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    var path = Path();
    path.lineTo(0, size.height * 0.25);
    var firstControlPoint = Offset(size.width * 0.40, size.height * 0.98);
    var firstEndPoint = Offset(size.width, size.height * 0.65);
    path.quadraticBezierTo(firstControlPoint.dx, firstControlPoint.dy, firstEndPoint.dx, firstEndPoint.dy);
    path.lineTo(size.width, 0);
    path.close();
    return path;
  }

  @override
  bool shouldReclip(CustomClipper<Path> oldClipper) => false;
}

/// Clipper para la Ola Inferior Izquierda (Cubre el 100% de la base)
class BottomWaveClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    var path = Path();
    path.moveTo(0, size.height * 0.22);
    var firstControlPoint = Offset(size.width * 0.45, 0);
    var firstEndPoint = Offset(size.width, size.height * 0.45);
    path.quadraticBezierTo(firstControlPoint.dx, firstControlPoint.dy, firstEndPoint.dx, firstEndPoint.dy);
    path.lineTo(size.width, size.height);
    path.lineTo(0, size.height);
    path.close();
    return path;
  }

  @override
  bool shouldReclip(CustomClipper<Path> oldClipper) => false;
}
