// ignore: avoid_web_libraries_in_flutter
import 'dart:html' as html; // ignore: deprecated_member_use
import 'dart:ui_web' as ui;
import 'dart:typed_data';
import 'dart:convert';
import 'package:flutter/material.dart';

/// Controller that lets the parent trigger photo capture
class CameraWebController {
  _CameraWebWidgetState? _state;

  void _attach(_CameraWebWidgetState s) => _state = s;
  void _detach() => _state = null;

  /// Call this to capture the current camera frame.
  Future<void> capture() async => _state?.capturePhoto();
}

class CameraWebWidget extends StatefulWidget {
  final Function(Uint8List bytes, String base64) onPhotoTaken;
  final CameraWebController? controller;

  const CameraWebWidget({
    super.key,
    required this.onPhotoTaken,
    this.controller,
  });

  @override
  State<CameraWebWidget> createState() => _CameraWebWidgetState();
}

class _CameraWebWidgetState extends State<CameraWebWidget> {
  html.VideoElement? _videoElement;
  html.MediaStream? _stream;
  bool _isReady = false;
  bool _hasError = false;
  String _errorMsg = '';
  late final String _viewId;

  @override
  void initState() {
    super.initState();
    _viewId = 'sinala-cam-${DateTime.now().millisecondsSinceEpoch}';
    widget.controller?._attach(this);
    _initCamera();
  }

  @override
  void dispose() {
    widget.controller?._detach();
    _stream?.getTracks().forEach((t) => t.stop());
    super.dispose();
  }

  Future<void> _initCamera() async {
    try {
      final mediaDevices = html.window.navigator.mediaDevices;
      if (mediaDevices == null) throw Exception('MediaDevices tidak tersedia');

      _stream = await mediaDevices.getUserMedia({
        'video': {'facingMode': 'user'},
        'audio': false,
      });

      _videoElement = html.VideoElement()
        ..srcObject = _stream
        ..autoplay = true
        ..muted = true
        ..style.width = '100%'
        ..style.height = '100%'
        ..style.objectFit = 'cover';

      // ignore: undefined_prefixed_name
      ui.platformViewRegistry.registerViewFactory(
        _viewId,
        (int _) => _videoElement!,
      );

      if (mounted) setState(() => _isReady = true);
    } catch (e) {
      if (mounted) {
        setState(() {
          _hasError = true;
          _errorMsg = 'Kamera tidak bisa dibuka:\n$e\n\n'
              'Pastikan browser mengizinkan akses kamera.';
        });
      }
    }
  }

  Future<void> capturePhoto() async {
    final video = _videoElement;
    if (video == null || video.videoWidth == 0) return;

    final canvas = html.CanvasElement(
      width: video.videoWidth,
      height: video.videoHeight,
    );
    canvas.context2D.drawImage(video, 0, 0);

    final blob = await canvas.toBlob('image/jpeg', 0.85);
    if (blob == null) return;

    final reader = html.FileReader();
    reader.readAsDataUrl(blob);
    await reader.onLoad.first;

    final dataUrl = reader.result as String;
    final base64Str = dataUrl.split(',').last;
    final bytes = base64Decode(base64Str);
    widget.onPhotoTaken(bytes, base64Str);
  }

  @override
  Widget build(BuildContext context) {
    if (_hasError) {
      return Container(
        color: Colors.grey.shade100,
        padding: const EdgeInsets.all(12),
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.no_photography, size: 48, color: Colors.grey.shade400),
              const SizedBox(height: 8),
              Text(
                _errorMsg,
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.red.shade600, fontSize: 12),
              ),
            ],
          ),
        ),
      );
    }

    if (!_isReady) {
      return Container(
        color: Colors.black,
        child: const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              CircularProgressIndicator(color: Colors.white),
              SizedBox(height: 12),
              Text('Membuka kamera...', style: TextStyle(color: Colors.white70)),
            ],
          ),
        ),
      );
    }

    return HtmlElementView(viewType: _viewId);
  }
}
