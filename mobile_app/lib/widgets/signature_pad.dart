import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:signature/signature.dart';

class SignaturePadWidget extends StatefulWidget {
  final String title;
  final Function(String base64Png) onSigned;

  const SignaturePadWidget({
    Key? key,
    required this.title,
    required this.onSigned,
  }) : super(key: key);

  @override
  _SignaturePadWidgetState createState() => _SignaturePadWidgetState();
}

class _SignaturePadWidgetState extends State<SignaturePadWidget> {
  final SignatureController _controller = SignatureController(
    penStrokeWidth: 3,
    penColor: const Color(0xFF0F172A),
    exportBackgroundColor: Colors.white,
  );

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.white10),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                widget.title,
                style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.cyanAccent, fontSize: 13),
              ),
              IconButton(
                icon: const Icon(Icons.close, color: Colors.white54),
                onPressed: () => Navigator.pop(context),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: Signature(
              controller: _controller,
              height: 180,
              backgroundColor: Colors.white,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              OutlinedButton.icon(
                onPressed: () => _controller.clear(),
                icon: const Icon(Icons.delete_outline, size: 16, color: Colors.white70),
                label: const Text('Limpiar', style: TextStyle(color: Colors.white70, fontSize: 12)),
              ),
              ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.cyan,
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                onPressed: () async {
                  if (_controller.isNotEmpty) {
                    final bytes = await _controller.toPngBytes();
                    if (bytes != null) {
                      final base64String = 'data:image/png;base64,${base64Encode(bytes)}';
                      widget.onSigned(base64String);
                      Navigator.pop(context);
                    }
                  } else {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Por favor dibuje la firma en el lienzo.')),
                    );
                  }
                },
                icon: const Icon(Icons.check, size: 16),
                label: const Text('Guardar Firma', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
