import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';
import 'package:sqflite_common_ffi_web/sqflite_ffi_web.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:convert';
import 'services/database_helper.dart';
import 'services/sync_service.dart';
import 'widgets/voice_text_field.dart';
import 'widgets/signature_pad.dart';
import 'views/scanner_view.dart';
import 'views/login_view.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  if (kIsWeb) {
    databaseFactory = databaseFactoryFfiWeb;
  } else if (defaultTargetPlatform == TargetPlatform.windows ||
             defaultTargetPlatform == TargetPlatform.linux ||
             defaultTargetPlatform == TargetPlatform.macOS) {
    sqfliteFfiInit();
    databaseFactory = databaseFactoryFfi;
  }

  // Inicializar SQLite local
  await DatabaseHelper.instance.database;
  runApp(const CmmsApp());
}

class CmmsApp extends StatelessWidget {
  const CmmsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'CMMS Leonplast Mobile',
      debugShowCheckedModeBanner: false,
      theme: ThemeData.dark().copyWith(
        scaffoldBackgroundColor: const Color(0xFF0B0F19),
        primaryColor: Colors.cyan,
        colorScheme: const ColorScheme.dark(
          primary: Colors.cyan,
          secondary: Colors.amber,
          surface: Color(0xFF1E293B),
        ),
      ),
      home: const AuthWrapper(),
    );
  }
}

class AuthWrapper extends StatefulWidget {
  const AuthWrapper({super.key});

  @override
  State<AuthWrapper> createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  bool _checkingAuth = true;
  String? _authToken;

  @override
  void initState() {
    super.initState();
    _checkSession();
  }

  Future<void> _checkSession() async {
    final token = await SyncService.instance.getAuthToken();
    if (!mounted) return;
    setState(() {
      _authToken = token;
      _checkingAuth = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_checkingAuth) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator(color: Colors.cyan)),
      );
    }

    if (_authToken == null || _authToken!.isEmpty) {
      return LoginView(
        onLoginSuccess: _checkSession,
      );
    }

    return WorkOrdersListView(
      onLogout: () async {
        await SyncService.instance.logout();
        _checkSession();
      },
    );
  }
}

class WorkOrdersListView extends StatefulWidget {
  final VoidCallback onLogout;

  const WorkOrdersListView({super.key, required this.onLogout});

  @override
  State<WorkOrdersListView> createState() => _WorkOrdersListViewState();
}

class _WorkOrdersListViewState extends State<WorkOrdersListView> {
  List<Map<String, dynamic>> _workOrders = [];
  bool _isLoading = true;
  String _userName = 'Cargando usuario...';
  String? _token;

  @override
  void initState() {
    super.initState();
    _initAppSession();
  }

  Future<void> _initAppSession() async {
    _token = await SyncService.instance.getAuthToken();
    _userName = await SyncService.instance.getUserName();
    if (_token != null) {
      SyncService.instance.initConnectivityListener(_token!);
      await _loadWorkOrders();
    }
  }

  Future<void> _loadWorkOrders() async {
    if (_token == null) return;
    setState(() => _isLoading = true);
    final ots = await SyncService.instance.pullDeltaSync(_token!);
    if (!mounted) return;
    setState(() {
      _workOrders = ots;
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('OTs Asignadas (Offline)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            Text(_userName, style: const TextStyle(fontSize: 11, color: Colors.cyanAccent)),
          ],
        ),
        backgroundColor: const Color(0xFF1E293B),
        actions: [
          IconButton(
            icon: const Icon(Icons.qr_code_scanner, color: Colors.cyanAccent),
            tooltip: 'Escanear QR/NFC',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => ScannerView(
                    onCodeScanned: (code, scanType) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('Código ($scanType) escaneado: $code')),
                      );
                    },
                  ),
                ),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.sync, color: Colors.white),
            tooltip: 'Sincronizar ahora',
            onPressed: _loadWorkOrders,
          ),
          IconButton(
            icon: const Icon(Icons.logout, color: Colors.redAccent),
            tooltip: 'Cerrar Sesión',
            onPressed: widget.onLogout,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Colors.cyan))
          : _workOrders.isEmpty
              ? _buildEmptyState()
              : ListView.builder(
                  padding: const EdgeInsets.all(12),
                  itemCount: _workOrders.length,
                  itemBuilder: (context, index) {
                    final ot = _workOrders[index];
                    return _buildWorkOrderCard(ot);
                  },
                ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.build_circle_outlined, size: 64, color: Colors.white24),
          const SizedBox(height: 12),
          const Text('No hay Órdenes de Trabajo asignadas a este técnico', style: TextStyle(color: Colors.white54, fontSize: 13)),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: _loadWorkOrders,
            icon: const Icon(Icons.refresh),
            label: const Text('Sincronizar ahora'),
          ),
        ],
      ),
    );
  }

  Widget _buildWorkOrderCard(Map<String, dynamic> ot) {
    final bool requierePts = (ot['requiere_permiso_especial'] == 1 || ot['requiere_permiso_especial'] == true);
    final String estado = ot['estado'] ?? 'Pendiente';

    Color estadoColor = Colors.amber;
    String estadoTexto = 'Pendiente / Asignada';
    if (estado == 'En_Progreso' || estado == 'En_Proceso') {
      estadoColor = Colors.cyanAccent;
      estadoTexto = 'En Proceso ⚙️';
    } else if (estado == 'Completada' || estado == 'Resuelta') {
      estadoColor = Colors.greenAccent;
      estadoTexto = 'Completada ✓';
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      color: const Color(0xFF1E293B),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: ListTile(
        contentPadding: const EdgeInsets.all(16),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(color: Colors.cyan.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(6)),
              child: Text(ot['codigo_ot'] ?? '', style: const TextStyle(color: Colors.cyanAccent, fontWeight: FontWeight.bold, fontSize: 11)),
            ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(color: estadoColor.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(6)),
              child: Text(estadoTexto, style: TextStyle(color: estadoColor, fontWeight: FontWeight.bold, fontSize: 10)),
            ),
            const SizedBox(width: 8),
            if (requierePts)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(color: Colors.red.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(6)),
                child: const Text('🛡️ PTS', style: TextStyle(color: Colors.redAccent, fontWeight: FontWeight.bold, fontSize: 10)),
              ),
          ],
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 6),
            Text(ot['titulo'] ?? '', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
            const SizedBox(height: 4),
            Text('Activo: ${ot['activo_nombre'] ?? 'General'}', style: const TextStyle(color: Colors.white60, fontSize: 12)),
          ],
        ),
        trailing: Icon(Icons.chevron_right, color: Colors.cyanAccent.withValues(alpha: 0.7)),
        onTap: () => _openWorkOrderDetail(ot),
      ),
    );
  }

  void _openWorkOrderDetail(Map<String, dynamic> ot) {
    final diagController = TextEditingController(text: ot['diagnostico'] ?? '');
    final solucionController = TextEditingController(text: ot['solucion'] ?? 'Mantenimiento ejecutado según procedimiento técnico.');
    String? firmaBase64 = ot['firma_tecnico'];
    String? fotoAntesBase64;
    String? fotoDespuesBase64;
    String estadoActual = ot['estado'] ?? 'Pendiente';

    final ImagePicker picker = ImagePicker();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: const Color(0xFF0F172A),
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) {
          Future<void> pickPhoto(bool esAntes, ImageSource source) async {
            try {
              final XFile? photo = await picker.pickImage(
                source: source,
                maxWidth: 1280,
                maxHeight: 1280,
                imageQuality: 85,
              );
              if (photo != null) {
                final bytes = await photo.readAsBytes();
                final base64Image = 'data:image/jpeg;base64,${base64Encode(bytes)}';
                setModalState(() {
                  if (esAntes) {
                    fotoAntesBase64 = base64Image;
                  } else {
                    fotoDespuesBase64 = base64Image;
                  }
                });
              }
            } catch (e) {
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('Error al capturar foto: $e')),
                );
              }
            }
          }

          return SingleChildScrollView(
            child: Padding(
              padding: EdgeInsets.only(
                top: 20,
                left: 20,
                right: 20,
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Cabecera OT
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          'OT ${ot['codigo_ot']}: ${ot['titulo']}',
                          style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 16),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: (estadoActual == 'En_Progreso' || estadoActual == 'En_Proceso')
                              ? Colors.cyan.withValues(alpha: 0.2)
                              : (estadoActual == 'Completada')
                                  ? Colors.green.withValues(alpha: 0.2)
                                  : Colors.amber.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          (estadoActual == 'En_Progreso' || estadoActual == 'En_Proceso')
                              ? 'En Proceso'
                              : (estadoActual == 'Completada')
                                  ? 'Completada'
                                  : 'Asignada',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 11,
                            color: (estadoActual == 'En_Progreso' || estadoActual == 'En_Proceso')
                                ? Colors.cyanAccent
                                : (estadoActual == 'Completada')
                                    ? Colors.greenAccent
                                    : Colors.amber,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // BOTÓN 1: Iniciar OT (Cambiar estado a En_Progreso)
                  if (estadoActual != 'En_Progreso' && estadoActual != 'En_Proceso' && estadoActual != 'Completada')
                    Container(
                      margin: const EdgeInsets.only(bottom: 16),
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.cyan,
                          foregroundColor: Colors.black,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () async {
                          await DatabaseHelper.instance.updateLocalOtStatus(
                            ot['id'],
                            'En_Progreso',
                          );

                          await DatabaseHelper.instance.addToSyncQueue(
                            '/ordenes-trabajo/${ot['id']}/cambiar-estado',
                            'POST',
                            {
                              'estado': 'En_Progreso',
                              'observaciones': 'Trabajo de mantenimiento iniciado por el técnico desde la app móvil',
                            },
                          );

                          setModalState(() {
                            estadoActual = 'En_Progreso';
                          });

                          _loadWorkOrders();

                          if (!context.mounted) return;
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('▶️ OT Iniciada. Estado actualizado a En Proceso.')),
                          );
                        },
                        icon: const Icon(Icons.play_arrow_rounded, size: 22),
                        label: const Text('▶️ INICIAR OT (Marcar En Proceso)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                      ),
                    ),

                  // Dictado por Voz Diagnóstico
                  VoiceTextField(
                    controller: diagController,
                    labelText: 'Diagnóstico Técnico / Observaciones (Dictado por Voz)',
                  ),
                  const SizedBox(height: 16),

                  // SECCIÓN: Evidencia Fotográfica (ANTES y DESPUÉS)
                  const Text('📸 Evidencia Fotográfica', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.cyanAccent, fontSize: 13)),
                  const SizedBox(height: 8),

                  Row(
                    children: [
                      // Foto ANTES
                      Expanded(
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E293B),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.white10),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('1. Foto ANTES', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white70, fontSize: 11)),
                              const SizedBox(height: 8),
                              if (fotoAntesBase64 != null)
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(8),
                                  child: Image.memory(
                                    base64Decode(fotoAntesBase64!.split(',').last),
                                    height: 80,
                                    width: double.infinity,
                                    fit: BoxFit.cover,
                                  ),
                                )
                              else
                                const Text('Sin foto registrada', style: TextStyle(color: Colors.white38, fontSize: 10)),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  IconButton(
                                    icon: const Icon(Icons.camera_alt, color: Colors.cyanAccent, size: 20),
                                    tooltip: 'Cámara',
                                    onPressed: () => pickPhoto(true, ImageSource.camera),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.photo_library, color: Colors.amberAccent, size: 20),
                                    tooltip: 'Galería',
                                    onPressed: () => pickPhoto(true, ImageSource.gallery),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),

                      // Foto DESPUÉS
                      Expanded(
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E293B),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.white10),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('2. Foto DESPUÉS', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white70, fontSize: 11)),
                              const SizedBox(height: 8),
                              if (fotoDespuesBase64 != null)
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(8),
                                  child: Image.memory(
                                    base64Decode(fotoDespuesBase64!.split(',').last),
                                    height: 80,
                                    width: double.infinity,
                                    fit: BoxFit.cover,
                                  ),
                                )
                              else
                                const Text('Sin foto registrada', style: TextStyle(color: Colors.white38, fontSize: 10)),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  IconButton(
                                    icon: const Icon(Icons.camera_alt, color: Colors.cyanAccent, size: 20),
                                    tooltip: 'Cámara',
                                    onPressed: () => pickPhoto(false, ImageSource.camera),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.photo_library, color: Colors.amberAccent, size: 20),
                                    tooltip: 'Galería',
                                    onPressed: () => pickPhoto(false, ImageSource.gallery),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Sección Firma Digital
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Firma del Técnico (*Obligatoria)', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white70, fontSize: 12)),
                      TextButton.icon(
                        onPressed: () {
                          showDialog(
                            context: context,
                            builder: (_) => Dialog(
                              backgroundColor: Colors.transparent,
                              child: SignaturePadWidget(
                                title: 'Firma del Técnico de Campo',
                                onSigned: (base64) {
                                  setModalState(() {
                                    firmaBase64 = base64;
                                  });
                                },
                              ),
                            ),
                          );
                        },
                        icon: const Icon(Icons.gesture, size: 16, color: Colors.cyanAccent),
                        label: Text(firmaBase64 != null ? 'Firma Registrada ✓' : 'Capturar Firma', style: const TextStyle(color: Colors.cyanAccent, fontSize: 12)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Botón Completar OT
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF10B981),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: () async {
                        if (firmaBase64 == null) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Error: La firma digital del técnico es obligatoria para completar.')),
                          );
                          return;
                        }

                        // 1. Guardar localmente estado Completada en SQLite
                        await DatabaseHelper.instance.updateLocalOtStatus(
                          ot['id'],
                          'Completada',
                          firmaBase64: firmaBase64,
                        );

                        // 2. Encolar Fotos si fueron capturadas
                        if (fotoAntesBase64 != null) {
                          await DatabaseHelper.instance.addToSyncQueue(
                            '/ordenes-trabajo/${ot['id']}/fotos',
                            'POST',
                            {
                              'tipo_foto': 'antes',
                              'foto_base64': fotoAntesBase64,
                            },
                          );
                        }

                        if (fotoDespuesBase64 != null) {
                          await DatabaseHelper.instance.addToSyncQueue(
                            '/ordenes-trabajo/${ot['id']}/fotos',
                            'POST',
                            {
                              'tipo_foto': 'despues',
                              'foto_base64': fotoDespuesBase64,
                            },
                          );
                        }

                        // 3. Encolar Solicitud de Cierre Completado
                        await DatabaseHelper.instance.addToSyncQueue(
                          '/ordenes-trabajo/${ot['id']}/completar',
                          'POST',
                          {
                            'diagnostico': diagController.text.isNotEmpty ? diagController.text : 'Mantenimiento ejecutado en campo',
                            'solucion': solucionController.text,
                            'duracion_real_horas': 1.5,
                            'firma_tecnico': firmaBase64,
                          },
                        );

                        if (!context.mounted) return;
                        Navigator.pop(context);
                        _loadWorkOrders();
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('OT Completada con firma y fotos. Encolada para sincronización offline.')),
                        );
                      },
                      child: const Text('✓ Completar OT (Guardar Local/Sync)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
