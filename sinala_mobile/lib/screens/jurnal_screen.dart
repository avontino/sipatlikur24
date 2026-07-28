import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../main.dart';
import '../services/api_service.dart';

// ── Design tokens ──────────────────────────────────────────────────────
const _kPrimary = Color(0xFF0F4C81);
const _kPrimaryLight = Color(0xFF1565C0);
const _kBg = Color(0xFFF4F7FB);
const _kCardBg = Colors.white;
const _kInputBg = Color(0xFFF5F8FF);
const _kBorder = Color(0xFFDDE3EF);

InputDecoration _field(String label, {String? hint, IconData? icon, bool readOnly = false}) =>
    InputDecoration(
      labelText: label,
      hintText: hint,
      prefixIcon: icon != null ? Icon(icon, color: readOnly ? Colors.grey : _kPrimary, size: 20) : null,
      labelStyle: TextStyle(color: readOnly ? Colors.grey : _kPrimary, fontSize: 13, fontWeight: FontWeight.w600),
      contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
      filled: true,
      fillColor: readOnly ? const Color(0xFFF0F0F0) : _kInputBg,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _kBorder)),
      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _kBorder)),
      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _kPrimary, width: 1.8)),
      disabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _kBorder)),
    );
// ───────────────────────────────────────────────────────────────────────

class JurnalScreen extends StatefulWidget {
  const JurnalScreen({super.key});

  @override
  State<JurnalScreen> createState() => _JurnalScreenState();
}

class _JurnalScreenState extends State<JurnalScreen> {
  final _apiService = ApiService();
  final _materiController = TextEditingController();
  final _catatanController = TextEditingController();
  final _jamkeController = TextEditingController(text: '1');
  final _jumlahjamController = TextEditingController(text: '1');
  final _kelasController = TextEditingController();
  final _mapelController = TextEditingController();
  final _guruController = TextEditingController();

  List<dynamic> _schedules = [];
  Map<String, dynamic>? _selectedSchedule;
  bool _isLoadingSchedules = true;
  bool _isSubmitting = false;

  String _penugasan = 'Tidak';
  String _ketGuruMapel = 'Hadir';

  @override
  void initState() {
    super.initState();
    _loadSchedules();
  }

  Future<void> _loadSchedules() async {
    final response = await _apiService.getSchedules();
    setState(() {
      if (response['schedules'] != null) _schedules = response['schedules'];
      _isLoadingSchedules = false;
    });
  }

  void _onScheduleSelected(Map<String, dynamic>? sched) {
    if (sched == null) return;
    setState(() {
      _selectedSchedule = sched;
      _kelasController.text = sched['kelas'] ?? '';
      _mapelController.text = sched['mapel'] ?? '';
      _guruController.text = sched['guru'] ?? '';
      _jamkeController.text = (sched['jamke'] ?? '1').toString();
      _jumlahjamController.text = (sched['jumlahjam'] ?? '1').toString();
    });
  }

  Future<void> _handleSubmitJurnal() async {
    if (_kelasController.text.isEmpty || _mapelController.text.isEmpty ||
        _guruController.text.isEmpty || _materiController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        _snack('Silakan lengkapi semua kolom wajib (*)', isError: true),
      );
      return;
    }
    setState(() => _isSubmitting = true);
    final res = await _apiService.submitJurnal(
      kelas: _kelasController.text,
      mapel: _mapelController.text,
      guru: _guruController.text,
      materi: _materiController.text,
      catatan: _catatanController.text,
      jamke: _jamkeController.text,
      jumlahjam: int.tryParse(_jumlahjamController.text) ?? 1,
      penugasan: _penugasan,
      ketGuruMapel: _ketGuruMapel,
    );
    setState(() => _isSubmitting = false);
    if (mounted) {
      final ok = res['status'] == 200;
      ScaffoldMessenger.of(context).showSnackBar(
        _snack(res['body']['message'] ?? (ok ? 'Jurnal berhasil disimpan.' : 'Gagal menyimpan.'),
            isError: !ok),
      );
      if (ok) Navigator.pop(context);
    }
  }

  SnackBar _snack(String msg, {required bool isError}) => SnackBar(
        content: Row(children: [
          Icon(isError ? Icons.error_outline : Icons.check_circle_outline, color: Colors.white, size: 18),
          const SizedBox(width: 10),
          Expanded(child: Text(msg, style: const TextStyle(fontSize: 13))),
        ]),
        backgroundColor: isError ? const Color(0xFFC62828) : const Color(0xFF2E7D32),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        margin: const EdgeInsets.all(16),
      );

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final isGuru = authProvider.role == 'guru';
    final isSiswa = authProvider.role == 'siswa';

    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        title: const Text('Isi Jurnal Pelajaran',
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
      body: _isLoadingSchedules
          ? const Center(child: CircularProgressIndicator(color: _kPrimary))
          : SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 100),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // ── Jadwal Banner ─────────────────────────────────
                  if (_schedules.isNotEmpty) ...[
                    _sectionHeader(Icons.schedule, 'Jadwal Mengajar Hari Ini'),
                    const SizedBox(height: 10),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                      decoration: BoxDecoration(
                        color: _kCardBg,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: _kBorder),
                        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
                      ),
                      child: DropdownButtonFormField<String>(
                        value: _selectedSchedule == null ? null : _selectedSchedule!['id'].toString(),
                        decoration: const InputDecoration(
                          border: InputBorder.none,
                          hintText: 'Pilih kelas yang akan diajar…',
                          hintStyle: TextStyle(color: Color(0xFF9EA5B4), fontSize: 13),
                        ),
                        icon: const Icon(Icons.keyboard_arrow_down, color: _kPrimary),
                        items: _schedules.map((s) {
                          final sc = Map<String, dynamic>.from(s);
                          return DropdownMenuItem<String>(
                            value: sc['id'].toString(),
                            child: Text(
                              '${sc['kelas']} – ${sc['mapel']} (Jam ${sc['jamke']})',
                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
                            ),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) {
                            try {
                              final sel = _schedules.firstWhere((s) => s['id'].toString() == val);
                              _onScheduleSelected(Map<String, dynamic>.from(sel));
                            } catch (_) {}
                          }
                        },
                      ),
                    ),
                    const SizedBox(height: 24),
                  ] else ...[
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFF8E1),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFFFECB3)),
                      ),
                      child: const Row(children: [
                        Icon(Icons.info_outline, color: Color(0xFFF57F17)),
                        SizedBox(width: 10),
                        Expanded(child: Text(
                          'Tidak ada jadwal mengajar hari ini, atau Admin belum melakukan sinkronisasi.',
                          style: TextStyle(color: Color(0xFFE65100), fontSize: 13),
                        )),
                      ]),
                    ),
                    const SizedBox(height: 24),
                  ],

                  // ── Info Kelas ──────────────────────────────────────
                  _sectionHeader(Icons.class_, 'Informasi Kelas'),
                  const SizedBox(height: 10),
                  _card([
                    TextField(
                      controller: _kelasController,
                      readOnly: isSiswa,
                      style: const TextStyle(fontSize: 14),
                      decoration: _field('Kelas *', icon: Icons.meeting_room_outlined, readOnly: isSiswa),
                    ),
                    const SizedBox(height: 14),
                    TextField(
                      controller: _mapelController,
                      style: const TextStyle(fontSize: 14),
                      decoration: _field('Mata Pelajaran *', icon: Icons.menu_book_outlined),
                    ),
                    const SizedBox(height: 14),
                    TextField(
                      controller: _guruController,
                      readOnly: isGuru,
                      style: const TextStyle(fontSize: 14),
                      decoration: _field('Nama Guru *', icon: Icons.person_outline, readOnly: isGuru),
                    ),
                    const SizedBox(height: 14),
                    Row(children: [
                      Expanded(
                        child: TextField(
                          controller: _jamkeController,
                          style: const TextStyle(fontSize: 14),
                          decoration: _field('Jam Ke *', icon: Icons.access_time),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: TextField(
                          controller: _jumlahjamController,
                          keyboardType: TextInputType.number,
                          style: const TextStyle(fontSize: 14),
                          decoration: _field('Jumlah Jam *', icon: Icons.hourglass_empty),
                        ),
                      ),
                    ]),
                  ]),
                  const SizedBox(height: 20),

                  // ── Status ─────────────────────────────────────────
                  _sectionHeader(Icons.fact_check_outlined, 'Status KBM'),
                  const SizedBox(height: 10),
                  _card([
                    DropdownButtonFormField<String>(
                      value: _ketGuruMapel,
                      decoration: _field('Status Kehadiran Guru *', icon: Icons.how_to_reg_outlined),
                      items: ['Hadir', 'Tidak Masuk'].map((v) =>
                          DropdownMenuItem(value: v, child: Text(v, style: const TextStyle(fontSize: 14)))).toList(),
                      onChanged: (val) => setState(() => _ketGuruMapel = val!),
                    ),
                    const SizedBox(height: 14),
                    DropdownButtonFormField<String>(
                      value: _penugasan,
                      decoration: _field('Penugasan Mandiri *', icon: Icons.assignment_outlined),
                      items: ['Ada', 'Tidak'].map((v) =>
                          DropdownMenuItem(value: v, child: Text(v, style: const TextStyle(fontSize: 14)))).toList(),
                      onChanged: (val) => setState(() => _penugasan = val!),
                    ),
                  ]),
                  const SizedBox(height: 20),

                  // ── Materi ──────────────────────────────────────────
                  _sectionHeader(Icons.edit_note, 'Catatan KBM'),
                  const SizedBox(height: 10),
                  _card([
                    TextField(
                      controller: _materiController,
                      maxLines: 3,
                      style: const TextStyle(fontSize: 14),
                      decoration: _field('Materi Pembahasan KBM *',
                          hint: 'Tuliskan topik/materi yang dibahas hari ini…',
                          icon: Icons.library_books_outlined),
                    ),
                    const SizedBox(height: 14),
                    TextField(
                      controller: _catatanController,
                      maxLines: 2,
                      style: const TextStyle(fontSize: 14),
                      decoration: _field('Catatan / Hambatan KBM (Opsional)',
                          hint: 'Kendala atau catatan kelas (jika ada)…',
                          icon: Icons.notes_outlined),
                    ),
                  ]),
                  const SizedBox(height: 32),
                ],
              ),
            ),

      // ── Sticky Submit Button ──────────────────────────────────
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
              gradient: const LinearGradient(colors: [_kPrimary, _kPrimaryLight]),
              boxShadow: [BoxShadow(color: _kPrimary.withOpacity(0.4), blurRadius: 14, offset: const Offset(0, 5))],
            ),
            child: ElevatedButton.icon(
              onPressed: _isSubmitting ? null : _handleSubmitJurnal,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.transparent,
                shadowColor: Colors.transparent,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              icon: _isSubmitting
                  ? const SizedBox(width: 20, height: 20,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Icon(Icons.save_outlined, color: Colors.white),
              label: Text(
                _isSubmitting ? 'Menyimpan…' : 'Simpan Jurnal KBM',
                style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _sectionHeader(IconData icon, String title) => Row(children: [
        Container(
          padding: const EdgeInsets.all(6),
          decoration: BoxDecoration(color: _kPrimary.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
          child: Icon(icon, color: _kPrimary, size: 16),
        ),
        const SizedBox(width: 10),
        Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1A2B4A))),
      ]);

  Widget _card(List<Widget> children) => Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: _kCardBg,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 3))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: children),
      );
}
