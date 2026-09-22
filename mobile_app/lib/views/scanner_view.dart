import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:nfc_manager/nfc_manager.dart';
import 'package:permission_handler/permission_handler.dart';

class ScannerView extends StatefulWidget {
  final Function(String code, String scanType) onCodeScanned;

  const ScannerView({super.key, required this.onCodeScanned});

  @override
  State<ScannerView> createState() => _ScannerViewState();
}

class _ScannerViewState extends State<ScannerView> {
  final MobileScannerController _scannerController = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
    torchEnabled: false,
    autoStart: false,
  );

  bool _nfcAvailable = false;
  bool _scanned = false;
  bool _checkingPermission = true;
  bool _hasCameraPermission = false;

  @override
  void initState() {
    super.initState();
    _initNfc();
    _requestCameraPermission();
  }

  Future<void> _requestCameraPermission() async {
    setState(() => _checkingPermission = true);

    PermissionStatus status = await Permission.camera.status;
    if (status.isDenied) {
      status = await Permission.camera.request();
    }

    if (!mounted) return;

    if (status.isGranted) {
      setState(() {
        _hasCameraPermission = true;
        _checkingPermission = false;
      });
      _scannerController.start();
    } else {
      setState(() {
        _hasCameraPermission = false;
        _checkingPermission = false;
      });
    }
  }

  void _initNfc() async {
    bool isAvailable = await NfcManager.instance.isAvailable();
    if (!mounted) return;
    setState(() => _nfcAvailable = isAvailable);

    if (isAvailable) {
      NfcManager.instance.startSession(onDiscovered: (NfcTag tag) async {
        if (_scanned) return;

        final ndef = Ndef.from(tag);
        String nfcCode = tag.data.toString();

        if (ndef != null && ndef.cachedMessage != null && ndef.cachedMessage!.records.isNotEmpty) {
          final record = ndef.cachedMessage!.records.first;
          nfcCode = String.fromCharCodes(record.payload);
        }

        _scanned = true;
        NfcManager.instance.stopSession();
        widget.onCodeScanned(nfcCode, 'NFC');
        if (!mounted) return;
        Navigator.pop(context);
      });
    }
  }

  @override
  void dispose() {
    _scannerController.dispose();
    if (_nfcAvailable) {
      NfcManager.instance.stopSession();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        title: const Text('Escáner de Activo / Repuesto', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF1E293B),
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          if (_hasCameraPermission) ...[
            ValueListenableBuilder<MobileScannerState>(
              valueListenable: _scannerController,
              builder: (context, state, child) {
                final isTorchOn = state.torchState == TorchState.on;
                return IconButton(
                  icon: Icon(
                    isTorchOn ? Icons.flash_on : Icons.flash_off,
                    color: isTorchOn ? Colors.amber : Colors.grey,
                  ),
                  onPressed: () => _scannerController.toggleTorch(),
                );
              },
            ),
            IconButton(
              icon: const Icon(Icons.cameraswitch, color: Colors.white),
              onPressed: () => _scannerController.switchCamera(),
            ),
          ],
        ],
      ),
      body: Stack(
        children: [
          // 1. Escáner de Cámara o Estado de Permiso
          if (_checkingPermission)
            const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(color: Colors.cyan),
                  SizedBox(height: 16),
                  Text('Verificando permiso de cámara...', style: TextStyle(color: Colors.white70, fontSize: 13)),
                ],
              ),
            )
          else if (!_hasCameraPermission)
            Container(
              color: const Color(0xFF0F172A),
              child: Center(
                child: Padding(
                  padding: const EdgeInsets.all(24.0),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.camera_alt_outlined, color: Colors.amber, size: 64),
                      const SizedBox(height: 16),
                      const Text(
                        'Permiso de Cámara Requerido',
                        style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'Para escanear códigos QR y de barras de activos y repuestos, la aplicación necesita acceso a la cámara.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.white70, fontSize: 13),
                      ),
                      const SizedBox(height: 24),
                      ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.cyan,
                          foregroundColor: Colors.black,
                          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                        ),
                        onPressed: _requestCameraPermission,
                        icon: const Icon(Icons.lock_open),
                        label: const Text('Solicitar Permiso de Cámara', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                      const SizedBox(height: 12),
                      OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.white70,
                          side: const BorderSide(color: Colors.white30),
                        ),
                        onPressed: () => openAppSettings(),
                        icon: const Icon(Icons.settings),
                        label: const Text('Abrir Ajustes de la App'),
                      ),
                    ],
                  ),
                ),
              ),
            )
          else
            MobileScanner(
              controller: _scannerController,
              errorBuilder: (context, error, child) {
                return Container(
                  color: const Color(0xFF0F172A),
                  child: Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24.0),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.no_photography, color: Colors.amber, size: 60),
                          const SizedBox(height: 16),
                          const Text(
                            'No se pudo activar la cámara',
                            style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Verifique los permisos en Ajustes de Android.\n(${error.errorCode})',
                            textAlign: TextAlign.center,
                            style: const TextStyle(color: Colors.white70, fontSize: 13),
                          ),
                          const SizedBox(height: 20),
                          ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.cyan,
                              foregroundColor: Colors.black,
                            ),
                            onPressed: () => _scannerController.start(),
                            icon: const Icon(Icons.refresh),
                            label: const Text('Reintentar cámara'),
                          ),
                          const SizedBox(height: 10),
                          TextButton(
                            onPressed: () => openAppSettings(),
                            child: const Text('Abrir Ajustes de Android', style: TextStyle(color: Colors.cyanAccent)),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              },
              onDetect: (capture) {
                if (_scanned) return;
                final List<Barcode> barcodes = capture.barcodes;
                for (final barcode in barcodes) {
                  if (barcode.rawValue != null) {
                    _scanned = true;
                    widget.onCodeScanned(barcode.rawValue!, 'BARCODE_QR');
                    if (!mounted) return;
                    Navigator.pop(context);
                    break;
                  }
                }
              },
            ),

          // Overlay guía visual del visor de escaneo
          if (_hasCameraPermission)
            Center(
              child: Container(
                width: 250,
                height: 250,
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.cyan, width: 2),
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
            ),

          // 2. Overlay informativo NFC / Cámara
          Positioned(
            bottom: 30,
            left: 20,
            right: 20,
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFF1E293B).withValues(alpha: 0.9),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.cyanAccent.withValues(alpha: 0.4)),
              ),
              child: Row(
                children: [
                  Icon(
                    _nfcAvailable ? Icons.nfc : Icons.qr_code_scanner,
                    color: Colors.cyanAccent,
                    size: 32,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          _nfcAvailable ? 'Lector NFC y Cámara Activos' : 'Escáner de Cámara Activo',
                          style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 13),
                        ),
                        Text(
                          _nfcAvailable
                              ? 'Acerque el teléfono a la etiqueta NFC o enfoque el código QR / Barras dentro del recuadro.'
                              : 'Enfoque el código de barras o QR dentro del recuadro.',
                          style: const TextStyle(color: Colors.white70, fontSize: 11),
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
