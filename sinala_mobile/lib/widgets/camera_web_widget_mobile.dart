import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

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
  final ImagePicker _picker = ImagePicker();
  bool _isPicking = false;

  @override
  void initState() {
    super.initState();
    widget.controller?._attach(this);
  }

  @override
  void dispose() {
    widget.controller?._detach();
    super.dispose();
  }

  Future<void> capturePhoto() async {
    if (_isPicking) return;
    setState(() {
      _isPicking = true;
    });

    try {
      final XFile? photo = await _picker.pickImage(
        source: ImageSource.camera,
        preferredCameraDevice: CameraDevice.front,
        maxWidth: 800,
        maxHeight: 800,
        imageQuality: 85,
      );

      if (photo != null) {
        final Uint8List bytes = await photo.readAsBytes();
        final String base64Str = base64Encode(bytes);
        widget.onPhotoTaken(bytes, base64Str);
      }
    } catch (e) {
      debugPrint('Error picking image: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isPicking = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: capturePhoto,
      child: Container(
        color: const Color(0xFF1C2833),
        width: double.infinity,
        height: double.infinity,
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _isPicking
                  ? const CircularProgressIndicator(color: Colors.white)
                  : Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.1),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.add_a_photo_rounded,
                        size: 40,
                        color: Colors.white,
                      ),
                    ),
              const SizedBox(height: 12),
              Text(
                _isPicking ? 'Membuka Kamera...' : 'Ketuk untuk Mengaktifkan Kamera',
                style: const TextStyle(
                  color: Colors.white70,
                  fontSize: 13,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
