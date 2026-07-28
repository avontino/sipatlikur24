import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../services/api_service.dart';
import 'package:intl/intl.dart';


class RiwayatJurnalScreen extends StatefulWidget {
  const RiwayatJurnalScreen({super.key});

  @override
  State<RiwayatJurnalScreen> createState() => _RiwayatJurnalScreenState();
}

class _RiwayatJurnalScreenState extends State<RiwayatJurnalScreen> {
  final _api = ApiService();
  final _storage = const FlutterSecureStorage();
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _data = [];
  List<String> _kelasList = [];
  String? _selectedKelas;
  DateTime? _selectedDate;
  String? _userRole;

  @override
  void initState() {
    super.initState();
    _getUserRole();
    _load();
  }

  Future<void> _getUserRole() async {
    final role = await _storage.read(key: 'user_role');
    if (mounted) {
      setState(() {
        _userRole = role;
      });
    }
  }

  String? get _formattedDate =>

      _selectedDate == null ? null : '${_selectedDate!.year}-${_selectedDate!.month.toString().padLeft(2, '0')}-${_selectedDate!.day.toString().padLeft(2, '0')}';

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    final res = await _api.getRiwayatJurnal(
      tanggal: _formattedDate,
      kelas: _selectedKelas,
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
        _error = res['message'] ?? 'Gagal memuat riwayat jurnal.';
        _isLoading = false;
      });
    }
  }

  Future<void> _pickDate() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _selectedDate ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
      builder: (ctx, child) => Theme(
        data: ThemeData.light().copyWith(
          colorScheme: const ColorScheme.light(primary: Color(0xFF0F4C81)),
        ),
        child: child!,
      ),
    );
    if (date != null) {
      setState(() { _selectedDate = date; });
      _load();
    }
  }

  void _clearFilters() {
    setState(() {
      _selectedDate = null;
      _selectedKelas = null;
    });
    _load();
  }

  @override
  Widget build(BuildContext context) {
    final hasFilter = _selectedDate != null || _selectedKelas != null;

    return Scaffold(
      backgroundColor: const Color(0xFFF4F7FB),
      appBar: AppBar(
        title: const Text('Riwayat Jurnal',
            style: TextStyle(fontWeight: FontWeight.w800, color: Colors.white, fontSize: 18)),
        backgroundColor: const Color(0xFF1A237E),
        elevation: 0,
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF1A237E), Color(0xFF283593)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
        actions: [
          if (hasFilter)
            IconButton(
              icon: const Icon(Icons.filter_alt_off, color: Colors.white70),
              tooltip: 'Hapus Filter',
              onPressed: _clearFilters,
            ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            onPressed: _load,
          ),
        ],
      ),
      body: Column(
        children: [
          // Filter bar
          Container(
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFF1A237E), Color(0xFF283593)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Row(
              children: [
                // Date filter
                Expanded(
                  child: GestureDetector(
                    onTap: _pickDate,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.calendar_today, color: Colors.white70, size: 16),
                          const SizedBox(width: 8),
                          Text(
                            _selectedDate == null
                                ? 'Filter Tanggal'
                                : '${_selectedDate!.day}/${_selectedDate!.month}/${_selectedDate!.year}',
                            style: TextStyle(
                              color: _selectedDate == null ? Colors.white60 : Colors.white,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                // Kelas filter (admin only, shown when kelasList is non-empty)
                if (_kelasList.isNotEmpty) ...[
                  const SizedBox(width: 8),
                  Expanded(
                    child: DropdownButtonFormField<String>(
                      value: _selectedKelas,
                      dropdownColor: const Color(0xFF1A237E),
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: Colors.white.withValues(alpha: 0.15),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 9),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: BorderSide.none,
                        ),
                        prefixIcon: const Icon(Icons.class_, color: Colors.white70, size: 16),
                        hintText: 'Pilih Kelas',
                        hintStyle: const TextStyle(color: Colors.white60, fontSize: 12),
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
                  ),
                ],
              ],
            ),
          ),

          // Count bar
          if (!_isLoading && _data.isNotEmpty)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              color: Colors.white,
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1A237E).withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '${_data.length} entri jurnal',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF1A237E),
                      ),
                    ),
                  ),
                ],
              ),
            ),

          // List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF1A237E)))
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

  Widget _buildList() {
    return ListView.builder(
      padding: const EdgeInsets.all(14),
      itemCount: _data.length,
      itemBuilder: (context, index) {
        final j = _data[index];
        final isPenugasan = j['penugasan'] == 'Ada';
        final isJamKosong = (j['materi'] ?? '').toString().contains('Jam Kosong');
        final createdAt = j['created_at'] as String? ?? '';
        
        String formattedDateTime = '-';
        if (createdAt.isNotEmpty) {
          try {
            // Parse tanggal dari server (UTC)
            final parsedUtc = DateTime.parse(createdAt).toUtc();
            // Konversi ke Waktu Indonesia Barat (WIB / GMT+7)
            final schoolTime = parsedUtc.add(const Duration(hours: 7));
            // Buat objek DateTime lokal dengan komponen yang sama agar tidak digeser oleh device
            final localDisplay = DateTime(
              schoolTime.year,
              schoolTime.month,
              schoolTime.day,
              schoolTime.hour,
              schoolTime.minute,
              schoolTime.second,
            );
            formattedDateTime = DateFormat('dd MMM yyyy - HH:mm:ss').format(localDisplay);
          } catch (e) {
            formattedDateTime = createdAt;
          }
        }



        Color cardBgColor;
        Border? cardBorder;
        if (isJamKosong) {
          cardBgColor = const Color(0xFFFFF5F5); // Merah transparan
          cardBorder = Border.all(color: const Color(0xFFFEB2B2), width: 1);
        } else if (isPenugasan) {
          cardBgColor = const Color(0xFFFFFDF0); // Kuning transparan
          cardBorder = Border.all(color: const Color(0xFFFEEBC8), width: 1);
        } else {
          cardBgColor = const Color(0xFFF0FDF4); // Hijau transparan
          cardBorder = Border.all(color: const Color(0xFFBBF7D0), width: 1);
        }

        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          decoration: BoxDecoration(
            color: cardBgColor,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.03),
                blurRadius: 8,
                offset: const Offset(0, 2),
              )
            ],
            border: cardBorder,
          ),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header row
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1A237E).withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.menu_book, color: Color(0xFF1A237E), size: 20),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            j['mapel'] ?? '-',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                              color: Color(0xFF1A2B4A),
                            ),
                          ),
                          Text(
                            j['guru'] ?? '-',
                            style: const TextStyle(fontSize: 11, color: Color(0xFF8899AA)),
                          ),
                        ],
                      ),
                    ),
                    // Kelas badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1A237E).withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        j['kelas'] ?? '-',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF1A237E),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                const Divider(height: 1, thickness: 0.5),
                const SizedBox(height: 10),
                // Materi
                Text(
                  j['materi_text'] ?? j['materi'] ?? '-',
                  style: const TextStyle(fontSize: 12, color: Color(0xFF4A5568)),
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                ),
                if ((j['materi_url'] as String?)?.isNotEmpty == true) ...[
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.link, size: 12, color: Color(0xFF1A237E)),
                      const SizedBox(width: 4),
                      Text(
                        j['materi_url'] ?? '',
                        style: const TextStyle(fontSize: 11, color: Color(0xFF1A237E)),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ],
                const SizedBox(height: 10),
                
                // Absensi Siswa & Guru
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.people_outline, size: 13, color: Colors.blueGrey),
                    const SizedBox(width: 6),
                    Expanded(
                      child: RichText(
                        text: TextSpan(
                          style: const TextStyle(fontSize: 11, color: Colors.black87),
                          children: [
                            const TextSpan(text: "Siswa Tidak Masuk: ", style: TextStyle(fontWeight: FontWeight.bold, color: Colors.black54)),
                            TextSpan(text: j['siswa_tidak_masuk'] ?? 'Nihil'),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.person_off_outlined, size: 13, color: Colors.blueGrey),
                    const SizedBox(width: 6),
                    Expanded(
                      child: RichText(
                        text: TextSpan(
                          style: const TextStyle(fontSize: 11, color: Colors.black87),
                          children: [
                            const TextSpan(text: "Guru Tidak Masuk: ", style: TextStyle(fontWeight: FontWeight.bold, color: Colors.black54)),
                            TextSpan(text: j['guru_tidak_masuk'] ?? 'Nihil'),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 10),
                // Footer row
                Row(
                  children: [
                    const Icon(Icons.access_time, size: 12, color: Color(0xFF8899AA)),
                    const SizedBox(width: 4),
                    Text(
                      formattedDateTime,
                      style: const TextStyle(fontSize: 11, color: Color(0xFF8899AA)),
                    ),
                    const Spacer(),
                    if (isPenugasan)
                      _badge('Penugasan', Colors.blue),
                    if (isJamKosong)
                      _badge('Jam Kosong', Colors.orange),
                    if (_userRole == 'guru' || _userRole == 'admin' || _userRole == 'kurikulum') ...[
                      const SizedBox(width: 8),
                      TextButton.icon(
                        icon: const Icon(Icons.edit_note, size: 16),
                        label: const Text('Edit', style: TextStyle(fontSize: 11)),
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          foregroundColor: const Color(0xFF1A237E),
                        ),
                        onPressed: () => _showEditDialog(j),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _showEditDialog(Map<String, dynamic> journal) async {
    final id = journal['id'] as int;
    final materiCtrl = TextEditingController(text: journal['materi_text'] ?? journal['materi'] ?? '');
    final catatanCtrl = TextEditingController(text: journal['catatan'] ?? '');
    
    String selectedKetGuru = ['Hadir', 'Tidak Masuk'].contains(journal['ket_guru_mapel'])
        ? journal['ket_guru_mapel']
        : 'Hadir';
        
    String selectedPenugasan = ['Ada', 'Tidak Ada'].contains(journal['penugasan'])
        ? journal['penugasan']
        : 'Tidak Ada';

    bool isSubmitting = false;

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (modalCtx, setModalState) {

            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: Row(
                children: [
                  const Icon(Icons.edit_note, color: Color(0xFF1A237E)),
                  const SizedBox(width: 8),
                  Text('Edit Jurnal - Kelas ${journal['kelas']}', 
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                ],
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Keterangan Guru Mapel', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      value: selectedKetGuru,
                      decoration: InputDecoration(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'Hadir', child: Text('Hadir', style: TextStyle(fontSize: 13))),
                        DropdownMenuItem(value: 'Tidak Masuk', child: Text('Tidak Masuk', style: TextStyle(fontSize: 13))),
                      ],
                      onChanged: (val) {
                        if (val != null) {
                          setModalState(() {
                            selectedKetGuru = val;
                          });
                        }
                      },
                    ),
                    const SizedBox(height: 16),
                    const Text('Penugasan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      value: selectedPenugasan,
                      decoration: InputDecoration(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'Tidak Ada', child: Text('Tidak Ada', style: TextStyle(fontSize: 13))),
                        DropdownMenuItem(value: 'Ada', child: Text('Ada', style: TextStyle(fontSize: 13))),
                      ],
                      onChanged: (val) {
                        if (val != null) {
                          setModalState(() {
                            selectedPenugasan = val;
                          });
                        }
                      },
                    ),
                    const SizedBox(height: 16),
                    const Text('Materi Pelajaran', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey)),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: materiCtrl,
                      maxLines: 3,
                      style: const TextStyle(fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Tulis materi...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text('Catatan Tambahan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey)),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: catatanCtrl,
                      maxLines: 2,
                      style: const TextStyle(fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Tulis catatan...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: isSubmitting ? null : () => Navigator.pop(context),
                  child: const Text('Batal'),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF1A237E),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: isSubmitting
                      ? null
                      : () async {
                          final materiVal = materiCtrl.text.trim();
                          if (materiVal.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Materi tidak boleh kosong!'), backgroundColor: Colors.red),
                            );
                            return;
                          }

                          setModalState(() {
                            isSubmitting = true;
                          });

                          final res = await _api.updateJurnalKbm(
                            id,
                            materi: materiVal,
                            ketGuruMapel: selectedKetGuru,
                            penugasan: selectedPenugasan,
                            catatan: catatanCtrl.text.trim(),
                          );

                          if (res['status'] == 'success') {
                            if (!mounted) return;
                            Navigator.pop(ctx);
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Jurnal berhasil diperbarui!'), backgroundColor: Colors.green),
                            );
                            _load();
                          } else {
                            if (!mounted) return;
                            setModalState(() {
                              isSubmitting = false;
                            });
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text(res['message'] ?? 'Gagal memperbarui jurnal.'), backgroundColor: Colors.red),
                            );
                          }
                        },
                  child: isSubmitting
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Simpan'),
                ),
              ],
            );
          },
        );
      },
    );
  }


  Widget _badge(String label, Color color) {
    return Container(
      margin: const EdgeInsets.only(left: 6),
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(
        label,
        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: color),
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
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF1A237E), foregroundColor: Colors.white),
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
          Icon(Icons.library_books_outlined, size: 64, color: Colors.grey.shade300),
          const SizedBox(height: 12),
          const Text('Tidak ada jurnal ditemukan.', style: TextStyle(color: Color(0xFF8899AA), fontSize: 14)),
          if (_selectedDate != null || _selectedKelas != null) ...[
            const SizedBox(height: 8),
            TextButton.icon(
              icon: const Icon(Icons.filter_alt_off),
              label: const Text('Hapus Filter'),
              onPressed: _clearFilters,
            ),
          ],
        ],
      ),
    );
  }
}
