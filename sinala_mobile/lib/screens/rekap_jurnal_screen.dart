import 'package:flutter/material.dart';
import '../services/api_service.dart';

class RekapJurnalScreen extends StatefulWidget {
  const RekapJurnalScreen({super.key});

  @override
  State<RekapJurnalScreen> createState() => _RekapJurnalScreenState();
}

class _RekapJurnalScreenState extends State<RekapJurnalScreen> {
  final _api = ApiService();
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _rows = [];
  List<String> _kelasList = [];
  String? _selectedKelas;
  Map<String, dynamic> _kosong = {};
  Map<String, dynamic> _ok = {};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    final res = await _api.getRekapJurnal(kelas: _selectedKelas);
    if (!mounted) return;
    if (res['status'] == 'success') {
      setState(() {
        _kelasList = List<String>.from(res['kelas_list'] ?? []);
        _rows = List<Map<String, dynamic>>.from(res['rows'] ?? []);
        _kosong = Map<String, dynamic>.from(res['kosong'] ?? {});
        _ok = Map<String, dynamic>.from(res['ok'] ?? {});
        _isLoading = false;
      });
    } else {
      setState(() {
        _error = res['message'] ?? 'Gagal memuat rekap jurnal.';
        _isLoading = false;
      });
    }
  }

  int _totalKosong() {
    int total = 0;
    for (int i = 1; i <= 11; i++) {
      total += ((_kosong['n$i'] ?? 0) as num).toInt();
    }
    return total;
  }

  int _totalOk() {
    int total = 0;
    for (int i = 1; i <= 11; i++) {
      total += ((_ok['n$i'] ?? 0) as num).toInt();
    }
    return total;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F7FB),
      appBar: AppBar(
        title: const Text('Rekap Jurnal',
            style: TextStyle(fontWeight: FontWeight.w800, color: Colors.white, fontSize: 18)),
        elevation: 0,
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFFAD1457), Color(0xFFE91E63)],
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
          // Filter & Summary Header
          Container(
            padding: const EdgeInsets.fromLTRB(14, 10, 14, 14),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFFAD1457), Color(0xFFE91E63)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Column(
              children: [
                // Filter kelas (jika admin)
                if (_kelasList.isNotEmpty)
                  DropdownButtonFormField<String>(
                    value: _selectedKelas,
                    dropdownColor: const Color(0xFFAD1457),
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
                      setState(() { _selectedKelas = val?.isEmpty == true ? null : val; });
                      _load();
                    },
                  ),
                if (_kelasList.isNotEmpty) const SizedBox(height: 10),
                // Summary cards
                if (!_isLoading) ...[
                  Row(
                    children: [
                      Expanded(child: _summaryCard('Sudah Diisi', '${_totalOk()}', Colors.green.shade400, Icons.check_circle)),
                      const SizedBox(width: 10),
                      Expanded(child: _summaryCard('Belum Diisi', '${_totalKosong()}', Colors.orange.shade400, Icons.radio_button_unchecked)),
                      const SizedBox(width: 10),
                      Expanded(child: _summaryCard('Total Kelas', '${_rows.length}', Colors.white, Icons.class_)),
                    ],
                  ),
                ],
              ],
            ),
          ),

          // Statistik per Jam header
          if (!_isLoading && _rows.isNotEmpty)
            Container(
              color: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Statistik Kosong per Jam:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF1A2B4A))),
                  const SizedBox(height: 8),
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: List.generate(11, (i) {
                        final jam = i + 1;
                        final kosongCount = ((_kosong['n$jam'] ?? 0) as num).toInt();
                        final okCount = ((_ok['n$jam'] ?? 0) as num).toInt();
                        final total = kosongCount + okCount;
                        final pct = total > 0 ? (okCount / total) : 1.0;
                        return Container(
                          width: 46,
                          margin: const EdgeInsets.only(right: 6),
                          child: Column(
                            children: [
                              Text('J$jam', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF6B7A8D))),
                              const SizedBox(height: 4),
                              Stack(
                                alignment: Alignment.bottomCenter,
                                children: [
                                  Container(
                                    height: 40,
                                    width: 24,
                                    decoration: BoxDecoration(
                                      color: Colors.grey.shade200,
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                  ),
                                  Container(
                                    height: 40 * pct,
                                    width: 24,
                                    decoration: BoxDecoration(
                                      color: pct < 0.6 ? Colors.orange : pct < 0.9 ? Colors.amber : Colors.green,
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 2),
                              Text('$kosongCount', style: const TextStyle(fontSize: 9, color: Color(0xFF8899AA))),
                            ],
                          ),
                        );
                      }),
                    ),
                  ),
                ],
              ),
            ),

          const Divider(height: 1),

          // List rekap per kelas
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFFAD1457)))
                : _error != null
                    ? _buildError()
                    : _rows.isEmpty
                        ? _buildEmpty()
                        : _buildList(),
          ),
        ],
      ),
    );
  }

  Widget _summaryCard(String label, String value, Color color, IconData icon) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 18),
          const SizedBox(width: 6),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(value, style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 16)),
                Text(label, style: const TextStyle(color: Colors.white70, fontSize: 10)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildList() {
    return ListView.builder(
      padding: const EdgeInsets.all(14),
      itemCount: _rows.length,
      itemBuilder: (context, index) {
        final row = _rows[index];

        // Hitung persentase kelengkapan untuk baris ini
        int okCount = 0;
        int totalJam = 0;
        for (int i = 1; i <= 11; i++) {
          final val = row['j$i']?.toString() ?? '0';
          if (val != '0' && val.isNotEmpty) totalJam++;
          if (val == 'ok') okCount++;
        }
        final pct = totalJam > 0 ? okCount / totalJam : 0.0;

        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))
            ],
          ),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.class_, color: Color(0xFFAD1457), size: 18),
                    const SizedBox(width: 8),
                    Text(
                      'Kelas ${row['kelas'] ?? '-'}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1A2B4A)),
                    ),
                    const Spacer(),
                    Text(
                      '${(pct * 100).toStringAsFixed(0)}%',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: pct >= 0.9 ? Colors.green : pct >= 0.6 ? Colors.orange : Colors.red,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                // Progress bar
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value: pct,
                    backgroundColor: Colors.grey.shade200,
                    valueColor: AlwaysStoppedAnimation<Color>(
                      pct >= 0.9 ? Colors.green : pct >= 0.6 ? Colors.orange : Colors.red,
                    ),
                    minHeight: 5,
                  ),
                ),
                const SizedBox(height: 10),
                // Jam dots
                Wrap(
                  spacing: 5,
                  runSpacing: 5,
                  children: List.generate(11, (i) {
                    final val = row['j${i + 1}']?.toString() ?? '0';
                    final isOk = val == 'ok';
                    final isEmpty = val == '0' || val.isEmpty;
                    return Tooltip(
                      message: 'Jam ${i + 1}: ${isOk ? 'Terisi' : isEmpty ? 'Kosong' : val}',
                      child: Container(
                        width: 26,
                        height: 26,
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          color: isOk
                              ? Colors.green.withValues(alpha: 0.15)
                              : isEmpty
                                  ? Colors.grey.shade200
                                  : Colors.amber.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: isOk
                                ? Colors.green.withValues(alpha: 0.4)
                                : isEmpty
                                    ? Colors.grey.shade300
                                    : Colors.amber.withValues(alpha: 0.4),
                          ),
                        ),
                        child: Text(
                          '${i + 1}',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: isOk
                                ? Colors.green.shade700
                                : isEmpty
                                    ? Colors.grey.shade500
                                    : Colors.amber.shade800,
                          ),
                        ),
                      ),
                    );
                  }),
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
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFAD1457), foregroundColor: Colors.white),
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
          Icon(Icons.pie_chart_outline, size: 64, color: Colors.grey.shade300),
          const SizedBox(height: 12),
          Text('Tidak ada data rekap.', style: TextStyle(color: Colors.grey.shade500, fontSize: 14)),
        ],
      ),
    );
  }
}
