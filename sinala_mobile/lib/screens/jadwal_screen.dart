import 'package:flutter/material.dart';
import '../services/api_service.dart';

class JadwalScreen extends StatefulWidget {
  const JadwalScreen({super.key});

  @override
  State<JadwalScreen> createState() => _JadwalScreenState();
}

class _JadwalScreenState extends State<JadwalScreen> {
  final _api = ApiService();
  bool _isLoading = true;
  String? _error;
  Map<String, dynamic> _jadwalData = {};
  List<String> _kelasList = [];
  String? _selectedKelas;
  String _tahunAjaran = '';
  String _semester = '';

  // Urutan hari dalam seminggu
  final _hariOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  final _hariLabel = {
    'Monday': 'Senin',
    'Tuesday': 'Selasa',
    'Wednesday': 'Rabu',
    'Thursday': 'Kamis',
    'Friday': 'Jumat',
    'Saturday': 'Sabtu',
  };

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({String? kelas}) async {
    setState(() { _isLoading = true; _error = null; });
    final res = await _api.getJadwal(kelas: kelas);
    if (!mounted) return;
    if (res['status'] == 'success') {
      setState(() {
        _tahunAjaran = res['tahun_ajaran'] ?? '';
        _semester    = res['semester'] ?? '';
        _kelasList   = List<String>.from(res['kelas_list'] ?? []);
        _jadwalData  = Map<String, dynamic>.from(res['jadwal'] ?? {});
        _isLoading   = false;
      });
    } else {
      setState(() {
        _error     = res['message'] ?? 'Gagal memuat jadwal.';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F7FB),
      appBar: AppBar(
        title: const Text('Jadwal Pelajaran',
            style: TextStyle(fontWeight: FontWeight.w800, color: Colors.white, fontSize: 18)),
        backgroundColor: const Color(0xFF0F4C81),
        elevation: 0,
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF0F4C81), Color(0xFF1565C0)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            onPressed: () => _load(kelas: _selectedKelas),
          ),
        ],
      ),
      body: Column(
        children: [
          // Header info + filter kelas (jika ada)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFF0F4C81), Color(0xFF1565C0)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '$_tahunAjaran  •  Semester $_semester',
                  style: const TextStyle(color: Colors.white70, fontSize: 12),
                ),
                if (_kelasList.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  DropdownButtonFormField<String>(
                    value: _selectedKelas,
                    dropdownColor: const Color(0xFF1565C0),
                    style: const TextStyle(color: Colors.white, fontSize: 13),
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: Colors.white.withValues(alpha: 0.15),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: BorderSide.none,
                      ),
                      prefixIcon: const Icon(Icons.filter_list, color: Colors.white70, size: 18),
                      hintText: 'Filter Kelas',
                      hintStyle: const TextStyle(color: Colors.white60, fontSize: 13),
                    ),
                    items: [
                      const DropdownMenuItem(value: '', child: Text('Semua Kelas', style: TextStyle(color: Colors.white))),
                      ..._kelasList.map((k) => DropdownMenuItem(
                        value: k,
                        child: Text(k, style: const TextStyle(color: Colors.white)),
                      )),
                    ],
                    onChanged: (val) {
                      _selectedKelas = val?.isEmpty == true ? null : val;
                      _load(kelas: _selectedKelas);
                    },
                  ),
                ],
              ],
            ),
          ),
          // Content
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF0F4C81)))
                : _error != null
                    ? _buildError()
                    : _jadwalData.isEmpty
                        ? _buildEmpty()
                        : _buildJadwalList(),
          ),
        ],
      ),
    );
  }

  Widget _buildJadwalList() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _hariOrder.length,
      itemBuilder: (context, index) {
        final hari = _hariOrder[index];
        final label = _hariLabel[hari] ?? hari;
        final items = List<Map<String, dynamic>>.from(_jadwalData[hari] ?? []);
        if (items.isEmpty) return const SizedBox.shrink();

        return Container(
          margin: const EdgeInsets.only(bottom: 14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 10,
                offset: const Offset(0, 3),
              )
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: Column(
              children: [
                // Header hari
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [_hariColor(hari), _hariColor(hari).withValues(alpha: 0.75)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(_hariIcon(hari), color: Colors.white, size: 18),
                      const SizedBox(width: 8),
                      Text(
                        label,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                      const Spacer(),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.25),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          '${items.length} Jam',
                          style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                ),
                // Items
                ...items.asMap().entries.map((entry) {
                  final isLast = entry.key == items.length - 1;
                  final item = entry.value;
                  return Container(
                    decoration: BoxDecoration(
                      border: isLast ? null : Border(
                        bottom: BorderSide(color: Colors.grey.shade100),
                      ),
                    ),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 11),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Jam ke badge
                        Container(
                          width: 32,
                          height: 32,
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            color: _hariColor(hari).withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            '${item['jamke'] ?? '-'}',
                            style: TextStyle(
                              color: _hariColor(hari),
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                item['mapel'] ?? '-',
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                  color: Color(0xFF1A2B4A),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                item['guru'] ?? '-',
                                style: const TextStyle(fontSize: 11, color: Color(0xFF6B7A8D)),
                              ),
                            ],
                          ),
                        ),
                        // Kelas badge
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF0F4C81).withValues(alpha: 0.08),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            item['kelas'] ?? '-',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF0F4C81),
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                }),
              ],
            ),
          ),
        );
      },
    );
  }

  Color _hariColor(String hari) {
    const colors = {
      'Monday': Color(0xFF1565C0),
      'Tuesday': Color(0xFF2E7D32),
      'Wednesday': Color(0xFF6A1B9A),
      'Thursday': Color(0xFFE65100),
      'Friday': Color(0xFF00695C),
      'Saturday': Color(0xFF4527A0),
    };
    return colors[hari] ?? const Color(0xFF0F4C81);
  }

  IconData _hariIcon(String hari) {
    const icons = {
      'Monday': Icons.looks_one_outlined,
      'Tuesday': Icons.looks_two_outlined,
      'Wednesday': Icons.looks_3_outlined,
      'Thursday': Icons.looks_4_outlined,
      'Friday': Icons.looks_5_outlined,
      'Saturday': Icons.looks_6_outlined,
    };
    return icons[hari] ?? Icons.calendar_today;
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
              onPressed: () => _load(kelas: _selectedKelas),
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
          Icon(Icons.event_busy, size: 64, color: Colors.grey.shade300),
          const SizedBox(height: 12),
          Text('Tidak ada jadwal ditemukan.', style: TextStyle(color: Colors.grey.shade500, fontSize: 14)),
        ],
      ),
    );
  }
}
