import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:nfc_manager/nfc_manager.dart';

class ScannerView extends StatefulWidget {
  final Function(String code, String scanType) onCodeScanned;

  const ScannerView({Key? key, required this.onCodeScanned}) : super(key: key);

  @override
  _ScannerViewState createState() => _ScannerViewState();
}

class _ScannerViewState extends State<ScannerView> {
  bool _nfcAvailable = false;
  bool _scanned = false;

  @override
  void initState() {
    super.initState();
    _initNfc();
  }

  void _initNfc() async {
    bool isAvailable = await NfcManager.instance.isAvailable();
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
        Navigator.pop(context);
      });
    }
  }

  @override
  void dispose() {
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
      ),
      body: Stack(
        children: [
          // 1. Escáner de Código de Barras / QR por Cámara
          MobileScanner(
            onDetect: (capture) {
              if (_scanned) return;
              final List<Barcode> barcodes = capture.barcodes;
              for (final barcode in barcodes) {
                if (barcode.rawValue != null) {
                  _scanned = true;
                  widget.onCodeScanned(barcode.rawValue!, 'BARCODE_QR');
                  Navigator.pop(context);
                  break;
                }
              }
            },
          ),

          // 2. Overlay informativo NFC
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
                          _nfcAvailable ? 'Lector NFC Activo' : 'Escáner de Cámara Activo',
                          style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 13),
                        ),
                        Text(
                          _nfcAvailable
                              ? 'Acerque el reverso del smartphone al tag NFC o enfoque el código QR / Barras.'
                              : 'Enfoque la etiqueta con código de barras o QR con la cámara.',
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
