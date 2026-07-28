import 'dart:typed_data';
import 'package:flutter/material.dart';

class CameraWebController {
  Future<void> capture() async {
    throw UnimplementedError('CameraWebController is not implemented on this platform.');
  }
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
  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Text('Kamera tidak didukung di platform ini'),
    );
  }
}
