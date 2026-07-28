import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../services/api_service.dart';
import '../widgets/camera_web_widget.dart';

const _kPrimary = Color(0xFF0F4C81);
const _kPrimaryLight = Color(0xFF1565C0);
const _kBg = Color(0xFFF4F7FB);

class PresensiScreen extends StatefulWidget {
  const PresensiScreen({super.key});

  @override
  State<PresensiScreen> createState() => _PresensiScreenState();
}

class _PresensiScreenState extends State<PresensiScreen> {
  final _apiService = ApiService();
  bool _isLoading = true;
  bool _isSubmitting = false;
  Map<String, dynamic>? _todayStatus;

  // GPS Real
  double? _lat;
  double? _lng;
  bool _isLocating = false;
  String _locationStatus = 'Mengambil lokasi...';
  String _tipe = 'datang';

  // Kamera
  final CameraWebController _camController = CameraWebController();
  Uint8List? _imageBytes;
  String? _base64Image;

  @override
  void initState() {
    super.initState();
    _loadStatus();
    _determinePosition();
  }

  Future<void> _loadStatus() async {
    final data = await _apiService.getTodayPresensi();
    if (mounted) setState(() { _todayStatus = data; _isLoading = false; });
  }

  void _onPhotoTaken(Uint8List bytes, String base64) {
    setState(() { _imageBytes = bytes; _base64Image = base64; });
    _showSnack('Foto berhasil diambil!', isError: false);
  }

  void _retakePhoto() => setState(() { _imageBytes = null; _base64Image = null; });

  Future<void> _determinePosition() async {
    setState(() {
      _isLocating = true;
      _locationStatus = 'Mengambil koordinat GPS...';
    });

    try {
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        setState(() {
          _locationStatus = 'GPS tidak aktif di HP Anda.';
          _isLocating = false;
        });
        return;
      }

      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          setState(() {
            _locationStatus = 'Izin akses lokasi GPS ditolak.';
            _isLocating = false;
          });
          return;
        }
      }

      if (permission == LocationPermission.deniedForever) {
        setState(() {
          _locationStatus = 'Izin GPS ditolak permanen. Aktifkan di setelan HP.';
          _isLocating = false;
        });
        return;
      }

      Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: const Duration(seconds: 10),
      );

      setState(() {
        _lat = position.latitude;
        _lng = position.longitude;
        _locationStatus = 'GPS terkunci';
        _isLocating = false;
      });
    } catch (e) {
      setState(() {
        _locationStatus = 'Gagal mendeteksi lokasi GPS.';
        _isLocating = false;
      });
    }
  }

  Future<void> _submitAttendance() async {
    if (_base64Image == null) {
      _showSnack('Silakan ambil foto wajah terlebih dahulu!', isError: true);
      return;
    }
    if (_lat == null || _lng == null) {
      _showSnack('GPS belum terkunci. Mengambil ulang lokasi...', isError: true);
      _determinePosition();
      return;
    }
    setState(() => _isSubmitting = true);
    final res = await _apiService.submitPresensi(_lat!, _lng!, _tipe, _base64Image!);
    setState(() => _isSubmitting = false);
    if (mounted) {
      final ok = res['status'] == 200;
      _showSnack(res['body']['message'] ?? (res['body']['error'] ?? 'Presensi gagal'), isError: !ok);
      if (ok) _loadStatus();
    }
  }

  void _showSnack(String msg, {required bool isError}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Row(children: [
        Icon(isError ? Icons.error_outline : Icons.check_circle_outline, color: Colors.white, size: 18),
        const SizedBox(width: 10),
        Expanded(child: Text(msg, style: const TextStyle(fontSize: 13))),
      ]),
      backgroundColor: isError ? const Color(0xFFC62828) : const Color(0xFF2E7D32),
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      margin: const EdgeInsets.all(16),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        title: const Text('Presensi GPS & Kamera',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 17)),
        backgroundColor: _kPrimary,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
                colors: [_kPrimary, _kPrimaryLight],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight),
          ),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: _kPrimary))
          : SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 100),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // ── GPS Status Card ────────────────────────────────
                  _buildGpsCard(),
                  const SizedBox(height: 16),

                  // ── Riwayat Presensi (jika sudah absen) ───────────
                  if (_todayStatus != null && _todayStatus!['today'] != null) ...[
                    _buildHistoryCard(),
                    const SizedBox(height: 16),
                  ],

                  // ── Kamera ─────────────────────────────────────────
                  _buildCameraCard(),
                  const SizedBox(height: 16),

                  // ── Pilih Tipe ─────────────────────────────────────
                  _buildTipeCard(),
                ],
              ),
            ),

      // ── Sticky Submit ───────────────────────────────────────
      bottomNavigationBar: Container(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 16, offset: const Offset(0, -4))],
        ),
        child: SizedBox(
          height: 52,
          child: DecoratedBox(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              gradient: LinearGradient(
                colors: _base64Image != null
                    ? [const Color(0xFF1B5E20), const Color(0xFF388E3C)]
                    : [Colors.grey.shade400, Colors.grey.shade500],
              ),
              boxShadow: [
                if (_base64Image != null)
                  BoxShadow(color: Colors.green.withOpacity(0.4), blurRadius: 14, offset: const Offset(0, 5)),
              ],
            ),
            child: ElevatedButton.icon(
              onPressed: _isSubmitting ? null : _submitAttendance,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.transparent,
                shadowColor: Colors.transparent,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              icon: _isSubmitting
                  ? const SizedBox(width: 20, height: 20,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Icon(Icons.fingerprint, color: Colors.white, size: 22),
              label: Text(
                _isSubmitting ? 'Mengirim…' : (_tipe == 'datang' ? 'Kirim Absen Datang' : 'Kirim Absen Pulang'),
                style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildGpsCard() => Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
              colors: [Color(0xFF0D47A1), Color(0xFF1565C0)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight),
          borderRadius: BorderRadius.circular(18),
          boxShadow: [BoxShadow(color: _kPrimary.withOpacity(0.3), blurRadius: 16, offset: const Offset(0, 6))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            const Icon(Icons.satellite_alt, color: Colors.white70, size: 16),
            const SizedBox(width: 6),
            const Text('Lokasi GPS Anda Saat Ini',
                style: TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w600)),
            const Spacer(),
            if (_isLocating)
              const SizedBox(
                width: 12,
                height: 12,
                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 1.5),
              )
            else
              GestureDetector(
                onTap: _determinePosition,
                child: const Icon(Icons.refresh, color: Colors.white70, size: 16),
              ),
          ]),
          const SizedBox(height: 12),
          Row(children: [
            const Icon(Icons.location_on, color: Colors.redAccent, size: 36),
            const SizedBox(width: 12),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(
                  (_lat != null && _lng != null) ? '$_lat, $_lng' : 'GPS belum terkunci',
                  style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                  decoration: BoxDecoration(
                      color: (_lat != null && _lng != null)
                          ? Colors.greenAccent.withOpacity(0.2)
                          : Colors.orangeAccent.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                          color: (_lat != null && _lng != null)
                              ? Colors.greenAccent.shade400
                              : Colors.orangeAccent.shade400,
                          width: 1)),
                  child: Row(mainAxisSize: MainAxisSize.min, children: [
                    Icon(
                        (_lat != null && _lng != null)
                            ? Icons.check_circle
                            : Icons.hourglass_empty,
                        color: (_lat != null && _lng != null)
                            ? Colors.greenAccent.shade400
                            : Colors.orangeAccent.shade400,
                        size: 13),
                    const SizedBox(width: 4),
                    Text(
                      _locationStatus,
                      style: TextStyle(
                          color: (_lat != null && _lng != null)
                              ? Colors.greenAccent.shade400
                              : Colors.orangeAccent.shade400,
                          fontSize: 11,
                          fontWeight: FontWeight.bold),
                    ),
                  ]),
                ),
              ]),
            ),
          ]),
        ]),
      );

  Widget _buildHistoryCard() {
    final today = _todayStatus!['today'];
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.green.shade100),
        boxShadow: [BoxShadow(color: Colors.green.withOpacity(0.08), blurRadius: 10, offset: const Offset(0, 3))],
      ),
      child: Row(children: [
        Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(color: Colors.green.shade50, shape: BoxShape.circle),
          child: Icon(Icons.check_circle, color: Colors.green.shade600, size: 24),
        ),
        const SizedBox(width: 14),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('Presensi Hari Ini', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.black87)),
          const SizedBox(height: 4),
          Text('Datang: ${today['jam_datang'] ?? '-'}  |  Pulang: ${today['jam_pulang'] ?? '-'}',
              style: const TextStyle(fontSize: 12, color: Color(0xFF555555))),
        ])),
        if (today['jam_datang'] != null && today['jam_pulang'] == null)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(color: Colors.orange.shade50, borderRadius: BorderRadius.circular(8)),
            child: Text('Belum Pulang', style: TextStyle(fontSize: 11, color: Colors.orange.shade700, fontWeight: FontWeight.w600)),
          ),
      ]),
    );
  }

  Widget _buildCameraCard() => Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 12, offset: const Offset(0, 4))],
        ),
        child: Column(children: [
          // Header
          Container(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
            decoration: const BoxDecoration(
              borderRadius: BorderRadius.only(topLeft: Radius.circular(18), topRight: Radius.circular(18)),
              gradient: LinearGradient(colors: [Color(0xFF263238), Color(0xFF37474F)],
                  begin: Alignment.topLeft, end: Alignment.bottomRight),
            ),
            child: const Row(children: [
              Icon(Icons.photo_camera, color: Colors.white, size: 18),
              SizedBox(width: 8),
              Text('Ambil Foto Wajah', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
              Spacer(),
              Text('Kamera Depan', style: TextStyle(color: Colors.white54, fontSize: 11)),
            ]),
          ),

          // Preview area
          ClipRRect(
            borderRadius: const BorderRadius.only(bottomLeft: Radius.circular(0), bottomRight: Radius.circular(0)),
            child: SizedBox(
              width: double.infinity,
              height: 230,
              child: _imageBytes != null
                  ? Image.memory(_imageBytes!, fit: BoxFit.cover)
                  : CameraWebWidget(controller: _camController, onPhotoTaken: _onPhotoTaken),
            ),
          ),

          // Button row
          Padding(
            padding: const EdgeInsets.all(14),
            child: _imageBytes == null
                ? SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () => _camController.capture(),
                      icon: const Icon(Icons.camera, color: Colors.white, size: 18),
                      label: const Text('Jepret Foto Sekarang', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF263238),
                        padding: const EdgeInsets.symmetric(vertical: 13),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  )
                : Row(children: [
                    const Icon(Icons.check_circle, color: Color(0xFF2E7D32), size: 18),
                    const SizedBox(width: 8),
                    const Expanded(child: Text('Foto siap dikirim', style: TextStyle(color: Color(0xFF2E7D32), fontWeight: FontWeight.w600, fontSize: 13))),
                    TextButton.icon(
                      onPressed: _retakePhoto,
                      icon: const Icon(Icons.refresh, size: 16),
                      label: const Text('Ganti'),
                      style: TextButton.styleFrom(foregroundColor: _kPrimary),
                    ),
                  ]),
          ),
        ]),
      );

  Widget _buildTipeCard() => Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 3))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('Pilih Tipe Presensi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF1A2B4A))),
          const SizedBox(height: 14),
          Row(children: [
            Expanded(child: _tipeBtn('datang', Icons.login, 'Datang', Colors.green)),
            const SizedBox(width: 12),
            Expanded(child: _tipeBtn('pulang', Icons.logout, 'Pulang', Colors.orange)),
          ]),
        ]),
      );

  Widget _tipeBtn(String val, IconData icon, String label, Color color) {
    final isSelected = _tipe == val;
    return GestureDetector(
      onTap: () => setState(() => _tipe = val),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: isSelected ? color.withOpacity(0.12) : const Color(0xFFF5F8FF),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: isSelected ? color : const Color(0xFFDDE3EF), width: isSelected ? 2 : 1),
        ),
        child: Column(children: [
          Icon(icon, color: isSelected ? color : Colors.grey, size: 28),
          const SizedBox(height: 6),
          Text(label, style: TextStyle(
              fontWeight: FontWeight.bold, fontSize: 13,
              color: isSelected ? color : Colors.grey.shade600)),
        ]),
      ),
    );
  }
}
