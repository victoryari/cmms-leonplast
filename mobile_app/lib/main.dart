import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';
import 'package:sqflite_common_ffi_web/sqflite_ffi_web.dart';
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
    String? firmaBase64 = ot['firma_tecnico'];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: const Color(0xFF0F172A),
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) {
          return Padding(
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
                Text('OT ${ot['codigo_ot']}: ${ot['titulo']}', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 16)),
                const SizedBox(height: 12),
                
                // Campo de Dictado por Voz (Speech-to-Text)
                VoiceTextField(
                  controller: diagController,
                  labelText: 'Diagnóstico Técnico (Dictado por Voz)',
                ),
                const SizedBox(height: 12),

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

                      // Guardar localmente en SQLite y añadir a la cola Outbox
                      await DatabaseHelper.instance.updateLocalOtStatus(
                        ot['id'],
                        'Completada',
                        firmaBase64: firmaBase64,
                      );

                      await DatabaseHelper.instance.addToSyncQueue(
                        '/ordenes-trabajo/${ot['id']}/completar',
                        'POST',
                        {
                          'diagnostico': diagController.text,
                          'solucion': 'Mantenimiento ejecutado según procedimiento',
                          'duracion_real_horas': 1.5,
                          'firma_tecnico': firmaBase64,
                        },
                      );

                      if (!context.mounted) return;
                      Navigator.pop(context);
                      _loadWorkOrders();
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('OT Completada y encolada para sincronización offline.')),
                      );
                    },
                    child: const Text('✓ Completar OT (Guardar Local/Sync)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
