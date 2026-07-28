import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'package:provider/provider.dart';
import '../main.dart';

class IjinSiswaScreen extends StatefulWidget {
  const IjinSiswaScreen({super.key});

  @override
  State<IjinSiswaScreen> createState() => _IjinSiswaScreenState();
}

class _IjinSiswaScreenState extends State<IjinSiswaScreen> {
  final _api = ApiService();
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _data = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    final res = await _api.getIjinSiswaDaftar();
    if (!mounted) return;
    if (res['status'] == 'success') {
      setState(() {
        _data = List<Map<String, dynamic>>.from(res['data'] ?? []);
        _isLoading = false;
      });
    } else {
      setState(() {
        _error = res['message'] ?? 'Gagal memuat data ijin siswa.';
        _isLoading = false;
      });
    }
  }

  Future<void> _verifikasi(Map<String, dynamic> ijin) async {
    final id = ijin['id'] as int;
    final nama = ijin['nama'] ?? '-';

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Konfirmasi Verifikasi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        content: Text(
          'Verifikasi ijin atas nama:\n\n$nama\n\n'
          'Jenis: ${ijin['ketijin'] ?? '-'}\n'
          'Tanggal: ${(ijin['tgl_izin'] ?? ijin['created_at'] ?? '-').toString().substring(0, 10)}',
          style: const TextStyle(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Verifikasi'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    // Show loading
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(children: [
            SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)),
            SizedBox(width: 12),
            Text('Memproses verifikasi...'),
          ]),
          duration: Duration(seconds: 30),
          backgroundColor: Color(0xFF0F4C81),
        ),
      );
    }

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final activeRole = authProvider.activeRole;
    final res = await _api.verifikasiIjinSiswa(id, activeRole: activeRole);

    if (!mounted) return;
    ScaffoldMessenger.of(context).hideCurrentSnackBar();

    if (res['status'] == 'success') {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(children: [
            Icon(Icons.check_circle, color: Colors.white),
            SizedBox(width: 8),
            Text('Verifikasi berhasil!'),
          ]),
          backgroundColor: Colors.green,
        ),
      );
      _load(); // refresh
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Verifikasi gagal.'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F7FB),
      appBar: AppBar(
        title: const Text('Ijin Siswa',
            style: TextStyle(fontWeight: FontWeight.w800, color: Colors.white, fontSize: 18)),
        elevation: 0,
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFFBF360C), Color(0xFFE64A19)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            onPressed: _load,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFFBF360C)))
          : _error != null
              ? _buildError()
              : _data.isEmpty
                  ? _buildEmpty()
                  : RefreshIndicator(
                      onRefresh: _load,
                      color: const Color(0xFFBF360C),
                      child: ListView.builder(
                        padding: const EdgeInsets.all(14),
                        itemCount: _data.length,
                        itemBuilder: (context, index) => _buildCard(_data[index]),
                      ),
                    ),
    );
  }

  Widget _buildCard(Map<String, dynamic> ijin) {
    final canVerify = ijin['can_verify'] == true;
    final nama = ijin['nama'] ?? '-';
    final kelas = ijin['kelas'] ?? '-';
    final jenis = ijin['ketijin'] ?? '-';
    final jumlah = ijin['jumlah'] ?? '-';
    final ket = ijin['ket'] ?? '-';

    // Hitung status dari field ok*
    final okBin = ijin['okbin'] == 'ok';
    final okAs = ijin['okas'] == 'ok';
    final okKur = ijin['okkur'] == 'ok';
    final okSis = ijin['oksis'] == 'ok';
    final totalOk = [okBin, okAs, okKur, okSis].where((b) => b).length;

    String statusLabel = 'Menunggu';
    Color statusColor = Colors.orange;
    IconData statusIcon = Icons.hourglass_empty;
    if (totalOk == 4) {
      statusLabel = 'Disetujui';
      statusColor = Colors.green;
      statusIcon = Icons.check_circle;
    } else if (totalOk > 0) {
      statusLabel = 'Proses ($totalOk/4)';
      statusColor = Colors.blue;
      statusIcon = Icons.pending;
    }

    final tanggal = (ijin['tgl_izin'] ?? ijin['created_at'] ?? '-').toString();
    final tanggalShow = tanggal.length >= 10 ? tanggal.substring(0, 10) : tanggal;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))
        ],
        border: canVerify
            ? Border.all(color: Colors.orange.withValues(alpha: 0.5), width: 1.5)
            : null,
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                CircleAvatar(
                  radius: 20,
                  backgroundColor: const Color(0xFFBF360C).withValues(alpha: 0.1),
                  child: Text(
                    nama.isNotEmpty ? nama[0].toUpperCase() : '?',
                    style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFFBF360C), fontSize: 16),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        nama,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1A2B4A)),
                      ),
                      Text(
                        'Kelas $kelas  •  $tanggalShow',
                        style: const TextStyle(fontSize: 11, color: Color(0xFF8899AA)),
                      ),
                    ],
                  ),
                ),
                // Status badge
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: statusColor.withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(statusIcon, size: 11, color: statusColor),
                      const SizedBox(width: 4),
                      Text(
                        statusLabel,
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: statusColor),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            const Divider(height: 1, thickness: 0.5),
            const SizedBox(height: 10),
            // Info rows
            _infoRow(Icons.assignment, 'Jenis Ijin', jenis),
            const SizedBox(height: 4),
            _infoRow(Icons.calendar_today, 'Jumlah Hari', '$jumlah hari'),
            const SizedBox(height: 4),
            _infoRow(Icons.notes, 'Keterangan', ket),
            const SizedBox(height: 10),
            // Approval checklist
            Row(
              children: [
                _approvalChip('Bimbingan', okBin),
                const SizedBox(width: 4),
                _approvalChip('Asuh', okAs),
                const SizedBox(width: 4),
                _approvalChip('Kurikulum', okKur),
                const SizedBox(width: 4),
                _approvalChip('Kesiswaan', okSis),
              ],
            ),
            // Verifikasi button
            if (canVerify) ...[
              const SizedBox(height: 10),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  icon: const Icon(Icons.verified_user, size: 16),
                  label: const Text('Verifikasi Sekarang'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    elevation: 0,
                  ),
                  onPressed: () => _verifikasi(ijin),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _infoRow(IconData icon, String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 13, color: const Color(0xFF8899AA)),
        const SizedBox(width: 6),
        Text('$label: ', style: const TextStyle(fontSize: 11, color: Color(0xFF8899AA))),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(fontSize: 11, color: Color(0xFF1A2B4A), fontWeight: FontWeight.w500),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }

  Widget _approvalChip(String label, bool isOk) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 4),
        decoration: BoxDecoration(
          color: isOk ? Colors.green.withValues(alpha: 0.1) : Colors.grey.shade100,
          borderRadius: BorderRadius.circular(6),
        ),
        child: Column(
          children: [
            Icon(
              isOk ? Icons.check : Icons.close,
              size: 12,
              color: isOk ? Colors.green : Colors.grey.shade400,
            ),
            Text(
              label,
              style: TextStyle(fontSize: 8, color: isOk ? Colors.green.shade700 : Colors.grey.shade500),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.wifi_off_rounded, size: 64, color: Colors.red.shade300),
            const SizedBox(height: 16),
            Text(_error!, textAlign: TextAlign.center, style: TextStyle(color: Colors.grey.shade600)),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              icon: const Icon(Icons.refresh),
              label: const Text('Coba Lagi'),
              onPressed: _load,
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFBF360C), foregroundColor: Colors.white),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.event_available, size: 64, color: Colors.grey.shade300),
          const SizedBox(height: 12),
          Text('Tidak ada pengajuan ijin.', style: TextStyle(color: Colors.grey.shade500, fontSize: 14)),
        ],
      ),
    );
  }
}
