import 'package:flutter/material.dart';
import 'package:speech_to_text/speech_to_text.dart' as stt;

class VoiceTextField extends StatefulWidget {
  final TextEditingController controller;
  final String labelText;
  final String hintText;
  final int maxLines;

  const VoiceTextField({
    super.key,
    required this.controller,
    required this.labelText,
    this.hintText = 'Escriba o toque el micrófono para dictar por voz...',
    this.maxLines = 3,
  });

  @override
  State<VoiceTextField> createState() => _VoiceTextFieldState();
}

class _VoiceTextFieldState extends State<VoiceTextField> {
  late stt.SpeechToText _speech;
  bool _isListening = false;
  String _lastWords = '';

  @override
  void initState() {
    super.initState();
    _speech = stt.SpeechToText();
  }

  void _toggleListening() async {
    if (!_isListening) {
      bool available = await _speech.initialize(
        onError: (val) {
          if (!mounted) return;
          setState(() => _isListening = false);
        },
        onStatus: (val) {
          if (val == 'done' || val == 'notListening') {
            if (!mounted) return;
            setState(() => _isListening = false);
          }
        },
      );

      if (available) {
        if (!mounted) return;
        setState(() => _isListening = true);
        _speech.listen(
          listenOptions: stt.SpeechListenOptions(localeId: 'es_PE'),
          onResult: (val) {
            if (!mounted) return;
            setState(() {
              _lastWords = val.recognizedWords;
              widget.controller.text = _lastWords;
            });
          },
        );
      }
    } else {
      setState(() => _isListening = false);
      _speech.stop();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(widget.labelText, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.white70)),
        const SizedBox(height: 4),
        TextFormField(
          controller: widget.controller,
          maxLines: widget.maxLines,
          style: const TextStyle(color: Colors.white, fontSize: 13),
          decoration: InputDecoration(
            hintText: widget.hintText,
            hintStyle: const TextStyle(color: Colors.white38, fontSize: 12),
            filled: true,
            fillColor: const Color(0xFF0F172A),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
            suffixIcon: IconButton(
              icon: Icon(
                _isListening ? Icons.mic : Icons.mic_none,
                color: _isListening ? Colors.redAccent : Colors.cyanAccent,
              ),
              onPressed: _toggleListening,
              tooltip: 'Dictar por voz',
            ),
          ),
        ),
      ],
    );
  }
}
