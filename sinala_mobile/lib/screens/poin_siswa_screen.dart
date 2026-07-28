import 'package:flutter/material.dart';
import '../services/api_service.dart';

class PoinSiswaScreen extends StatefulWidget {
  const PoinSiswaScreen({super.key});

  @override
  State<PoinSiswaScreen> createState() => _PoinSiswaScreenState();
}

class _PoinSiswaScreenState extends State<PoinSiswaScreen> {
  final _api = ApiService();
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _data = [];
  List<String> _kelasList = [];
  String? _selectedKelas;
  String _searchQuery = '';
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    final res = await _api.getPoinSiswa(
      kelas: _selectedKelas,
      search: _searchQuery.isEmpty ? null : _searchQuery,
    );
    if (!mounted) return;
    if (res['status'] == 'success') {
      setState(() {
        _kelasList = List<String>.from(res['kelas_list'] ?? []);
        _data = List<Map<String, dynamic>>.from(res['data'] ?? []);
        _isLoading = false;
      });
    } else {
      setState(() {
        _error = res['message'] ?? 'Gagal memuat data.';
        _isLoading = false;
      });
    }
  }

  Color _spColor(String? sp) {
    switch (sp) {
      case 'SP 1': return Colors.orange;
      case 'SP 2': return Colors.deepOrange;
      case 'SP 3': return Colors.red;
      case 'DO': return Colors.red.shade900;
      default: return Colors.green;
    }
  }

  IconData _spIcon(String? sp) {
    switch (sp) {
      case 'SP 1':
      case 'SP 2':
      case 'SP 3': return Icons.warning_amber_rounded;
      case 'DO': return Icons.block;
      default: return Icons.check_circle_outline;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F7FB),
      appBar: AppBar(
        title: const Text('Poin & SP Siswa',
            style: TextStyle(fontWeight: FontWeight.w800, color: Colors.white, fontSize: 18)),
        backgroundColor: const Color(0xFF0F4C81),
        elevation: 0,
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFFB71C1C), Color(0xFFE53935)],
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
      body: Column(
        children: [
          // Search & Filter bar
          Container(
            padding: const EdgeInsets.fromLTRB(12, 12, 12, 8),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFFB71C1C), Color(0xFFE53935)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Column(
              children: [
                // Search box
                TextField(
                  controller: _searchCtrl,
                  style: const TextStyle(color: Colors.white, fontSize: 14),
                  decoration: InputDecoration(
                    hintText: 'Cari nama siswa...',
                    hintStyle: const TextStyle(color: Colors.white60, fontSize: 13),
                    filled: true,
                    fillColor: Colors.white.withValues(alpha: 0.15),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                    prefixIcon: const Icon(Icons.search, color: Colors.white70, size: 20),
                    suffixIcon: _searchQuery.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.close, color: Colors.white70, size: 18),
                            onPressed: () {
                              _searchCtrl.clear();
                              setState(() { _searchQuery = ''; });
                              _load();
                            },
                          )
                        : null,
                  ),
                  onChanged: (val) {
                    setState(() { _searchQuery = val; });
                    if (val.isEmpty || val.length >= 2) _load();
                  },
                ),
                const SizedBox(height: 8),
                // Kelas filter chips
                if (_kelasList.isNotEmpty)
                  SizedBox(
                    height: 30,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      children: [
                        _filterChip('Semua', null),
                        ..._kelasList.map((k) => _filterChip(k, k)),
                      ],
                    ),
                  ),
              ],
            ),
          ),

          // Summary bar
          if (!_isLoading && _data.isNotEmpty)
            _buildSummaryBar(),

          // List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFFB71C1C)))
                : _error != null
                    ? _buildError()
                    : _data.isEmpty
                        ? _buildEmpty()
                        : _buildList(),
          ),
        ],
      ),
    );
  }

  Widget _filterChip(String label, String? value) {
    final isSelected = _selectedKelas == value;
    return GestureDetector(
      onTap: () {
        setState(() { _selectedKelas = value; });
        _load();
      },
      child: Container(
        margin: const EdgeInsets.only(right: 6),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
        decoration: BoxDecoration(
          color: isSelected ? Colors.white : Colors.white.withValues(alpha: 0.2),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: isSelected ? const Color(0xFFB71C1C) : Colors.white,
          ),
        ),
      ),
    );
  }

  Widget _buildSummaryBar() {
    final spCount = _data.where((s) => s['sp_level'] != null).length;
    final doCount = _data.where((s) => s['sp_level'] == 'DO').length;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      color: Colors.white,
      child: Row(
        children: [
          _summaryChip('Total', '${_data.length}', const Color(0xFF0F4C81)),
          const SizedBox(width: 8),
          _summaryChip('Ada SP', '$spCount', Colors.orange),
          const SizedBox(width: 8),
          _summaryChip('DO Risk', '$doCount', Colors.red),
        ],
      ),
    );
  }

  Widget _summaryChip(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(label, style: TextStyle(fontSize: 11, color: color.withValues(alpha: 0.8))),
          const SizedBox(width: 4),
          Text(value, style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: color)),
        ],
      ),
    );
  }

  Widget _buildList() {
    return ListView.builder(
      padding: const EdgeInsets.all(14),
      itemCount: _data.length,
      itemBuilder: (context, index) {
        final siswa = _data[index];
        final sp = siswa['sp_level'] as String?;
        final spColor = _spColor(sp);
        final totalPelanggaran = siswa['total_pelanggaran'] as int? ?? 0;
        final totalPrestasi = siswa['total_prestasi'] as int? ?? 0;

        // Progress bar value: max 200 poin = DO
        final progress = (totalPelanggaran / 200).clamp(0.0, 1.0);

        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 8,
                offset: const Offset(0, 2),
              )
            ],
          ),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    // Avatar
                    Container(
                      width: 40,
                      height: 40,
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        color: spColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(_spIcon(sp), color: spColor, size: 22),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            siswa['nama'] ?? '-',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                              color: Color(0xFF1A2B4A),
                            ),
                          ),
                          Text(
                            '${siswa['kelas'] ?? '-'}  •  NIS: ${siswa['nis'] ?? '-'}',
                            style: const TextStyle(fontSize: 11, color: Color(0xFF8899AA)),
                          ),
                        ],
                      ),
                    ),
                    // SP Badge
                    if (sp != null)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: spColor.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: spColor.withValues(alpha: 0.3)),
                        ),
                        child: Text(
                          sp,
                          style: TextStyle(
                            color: spColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 11,
                          ),
                        ),
                      )
                    else
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.green.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          'Baik',
                          style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 11),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 12),
                // Progress bar pelanggaran
                Row(
                  children: [
                    const Text('Poin Pelanggaran:', style: TextStyle(fontSize: 11, color: Color(0xFF8899AA))),
                    const Spacer(),
                    Text(
                      '$totalPelanggaran / 200',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: spColor),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value: progress,
                    backgroundColor: Colors.grey.shade200,
                    valueColor: AlwaysStoppedAnimation<Color>(spColor),
                    minHeight: 6,
                  ),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Icon(Icons.emoji_events, size: 13, color: Colors.amber.shade700),
                    const SizedBox(width: 4),
                    Text(
                      'Poin Prestasi: $totalPrestasi  •  Jumlah Kasus: ${siswa['jumlah_kasus'] ?? 0}',
                      style: const TextStyle(fontSize: 11, color: Color(0xFF6B7A8D)),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
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
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFB71C1C), foregroundColor: Colors.white),
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
          Icon(Icons.people_outline, size: 64, color: Colors.grey.shade300),
          const SizedBox(height: 12),
          Text('Tidak ada data siswa.', style: TextStyle(color: Colors.grey.shade500, fontSize: 14)),
        ],
      ),
    );
  }
}
