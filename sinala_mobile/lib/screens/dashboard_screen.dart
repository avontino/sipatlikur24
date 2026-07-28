import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../main.dart';
import '../services/api_service.dart';
import 'presensi_screen.dart';
import 'jurnal_screen.dart';
import 'jadwal_screen.dart';
import 'riwayat_jurnal_screen.dart';
import 'rekap_jurnal_screen.dart';
import 'ijin_siswa_screen.dart';
import 'poin_siswa_screen.dart';
import 'package:image_picker/image_picker.dart';
import 'web_module_screen.dart';
import 'package:url_launcher/url_launcher.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final _apiService = ApiService();
  List<dynamic> _todaySchedules = [];
  String _presensiStatus = 'Belum Presensi';
  bool _isLoading = true;

  Map<String, dynamic>? _stats;

  @override
  void initState() {
    super.initState();
    _fetchDashboardData();
  }

  Future<void> _fetchDashboardData() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final String activeRole = authProvider.activeRole;
    final profileResponse = await _apiService.getUserProfile(activeRole: activeRole);
    
    if (profileResponse['status'] == 'success' && profileResponse['user'] != null) {
      final userMap = profileResponse['user'];
      final List<String> newPerms = userMap['permissions'] != null 
          ? List<String>.from(userMap['permissions']) 
          : <String>[];
      await authProvider.updatePermissions(newPerms);
    }

    if (mounted) {
      setState(() {
        if (profileResponse['status'] == 'success') {
          if (profileResponse['stats'] != null && profileResponse['stats'] is Map) {
            _stats = Map<String, dynamic>.from(profileResponse['stats']);
          }
          _todaySchedules = profileResponse['today_schedules'] != null
              ? List<dynamic>.from(profileResponse['today_schedules'])
              : [];
          _presensiStatus = profileResponse['presensi_status'] ?? 'Belum Presensi';
        }
        _isLoading = false;
      });
    }
  }

  Future<void> _openWebModule(String redirectPath, String title, {String? asRole}) async {
    final String? token = await _apiService.getStoredValue('auth_token');
    final String? tahunAjaranId = await _apiService.getStoredValue('tahun_ajaran_id');
    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    if (token == null) return;

    // Extract base URL (without /api suffix)
    String webBase = ApiService.baseUrl.replaceAll('/api', '');

    // Construct the auto-login URL
    String autoLoginUrl = "$webBase/auth/auto-login?token=$token&redirect=$redirectPath";
    if (tahunAjaranId != null) {
      autoLoginUrl += "&tahun_ajaran_id=$tahunAjaranId";
    }
    
    final targetRole = asRole ?? authProvider.activeRole;
    autoLoginUrl += "&as_role=$targetRole";

    if (mounted) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => WebModuleScreen(
            url: autoLoginUrl,
            title: title,
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final grouped = _buildMenuCardsGrouped(authProvider.activeRole);
    final guruCards = grouped['guru'] ?? [];
    final waliKelasCards = grouped['walikelas'] ?? [];
    final kurikulumCards = grouped['kurikulum'] ?? [];

    return Scaffold(
      backgroundColor: const Color(0xFFF4F7FB),
      appBar: AppBar(
        title: const Text('SINALA Mobile',
            style: TextStyle(fontWeight: FontWeight.w800, color: Colors.white, fontSize: 18, letterSpacing: 0.5)),
        backgroundColor: const Color(0xFF0F4C81),
        elevation: 0,
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
                colors: [Color(0xFF0F4C81), Color(0xFF1565C0)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight),
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            tooltip: 'Refresh',
            onPressed: _fetchDashboardData,
          ),
          IconButton(
            icon: const Icon(Icons.logout_rounded, color: Colors.white),
            tooltip: 'Keluar',
            onPressed: () async => authProvider.logout(),
          ),
        ],
      ),
      body: _isLoading
          ? _buildDashboardShimmer(context)
          : RefreshIndicator(
              onRefresh: _fetchDashboardData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Welcome Header Card
                    Container(
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                            colors: [Color(0xFF0D47A1), Color(0xFF1976D2)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight),
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                              color: const Color(0xFF0F4C81).withValues(alpha: 0.35),
                              blurRadius: 20,
                              offset: const Offset(0, 8)),
                        ],
                      ),
                      padding: const EdgeInsets.all(20),
                      child: Row(
                        children: [
                          Container(
                            width: 62,
                            height: 62,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: Colors.white.withValues(alpha: 0.15),
                              border: Border.all(color: Colors.white30, width: 2),
                            ),
                            child: const Icon(Icons.person_rounded, size: 36, color: Colors.white),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  authProvider.name,
                                  style: const TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 4),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.18),
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(
                                    authProvider.activeRole.toUpperCase(),
                                    style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: 1.0),
                                  ),
                                ),
                                if (authProvider.availableRoles.length > 1) ...[
                                  const SizedBox(height: 8),
                                  GestureDetector(
                                    onTap: () => _showRoleSwitcher(context, authProvider),
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: Colors.white24,
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: const Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(Icons.swap_horiz, color: Colors.white, size: 16),
                                          SizedBox(width: 4),
                                          Text(
                                            "Ganti Peran",
                                            style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Statistik
                    if (_stats != null) ...[
                      _sectionLabel(Icons.bar_chart_rounded, 'Statistik Hari Ini'),
                      const SizedBox(height: 10),
                      if (authProvider.activeRole == 'siswa') ...[
                        GridView.count(
                          crossAxisCount: 2,
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                          childAspectRatio: 1.3,
                          children: [
                            _buildStatBox(
                              title: _stats!['status'] ?? 'MASUK',
                              subtitle: 'Hari Ini',
                              color: const Color(0xFF343A40),
                              icon: Icons.badge,
                            ),
                            _buildStatBox(
                              title: _stats!['tagihan_komite'] ?? 'Rp. 0',
                              subtitle: 'Dana Komite',
                              color: const Color(0xFFDC3545),
                              icon: Icons.wallet,
                            ),
                            _buildStatBox(
                              title: _stats!['tagihan_lain'] ?? 'Rp. 0',
                              subtitle: 'Tagihan Lain',
                              color: const Color(0xFFDC3545),
                              icon: Icons.monetization_on,
                            ),
                            _buildStatBox(
                              title: "${_stats!['poin_pelanggaran'] ?? 0}",
                              subtitle: 'Poin Pelanggaran',
                              color: const Color(0xFFDC3545),
                              icon: Icons.gavel,
                            ),
                            _buildStatBox(
                              title: "${_stats!['poin_prestasi'] ?? 0}",
                              subtitle: 'Poin Prestasi',
                              color: const Color(0xFF007BFF),
                              icon: Icons.emoji_events,
                            ),
                            _buildStatBox(
                              title: "${_stats!['poin_siswa'] ?? 0}",
                              subtitle: 'Total Poin Siswa',
                              color: const Color(0xFF28A745),
                              icon: Icons.check_circle,
                            ),
                          ],
                        ),
                      ] else ...[
                        GridView.count(
                          crossAxisCount: 3,
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          crossAxisSpacing: 10,
                          mainAxisSpacing: 10,
                          childAspectRatio: 1.1,
                          children: [
                            _buildStatBox(
                              title: "${_stats!['total_siswa_sakit'] ?? 0}",
                              subtitle: 'Siswa Sakit',
                              color: const Color(0xFF17A2B8),
                              icon: Icons.sick,
                              compact: true,
                            ),
                            _buildStatBox(
                              title: "${_stats!['total_siswa_ijin'] ?? 0}",
                              subtitle: 'Siswa Ijin',
                              color: const Color(0xFFFFC107),
                              icon: Icons.note_alt,
                              compact: true,
                            ),
                            _buildStatBox(
                              title: "${_stats!['total_siswa_alpha'] ?? 0}",
                              subtitle: 'Siswa Alfa',
                              color: const Color(0xFFDC3545),
                              icon: Icons.cancel,
                              compact: true,
                            ),
                          ],
                        ),
                        if (authProvider.activeRole == 'guru') ...[
                          const SizedBox(height: 12),
                          Card(
                            elevation: 1,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            child: ListTile(
                              leading: const CircleAvatar(
                                backgroundColor: Color(0xFF6F42C1),
                                child: Icon(Icons.directions_walk, color: Colors.white),
                              ),
                              title: Text(
                                "${_stats!['total_izin_guru'] ?? 0} Hari",
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                              ),
                              subtitle: const Text('Akumulasi Cuti/Izin Guru Anda'),
                            ),
                          ),
                        ],
                      ],
                      // Presensi Guru Status Widget
                      if (authProvider.activeRole == 'guru') ...[
                        const SizedBox(height: 20),
                        _sectionLabel(Icons.fingerprint_rounded, 'Status Presensi Hari Ini'),
                        const SizedBox(height: 10),
                        Card(
                          elevation: 1,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                            side: BorderSide(
                              color: _presensiStatus.contains('Sudah') ? Colors.green.shade200 : Colors.orange.shade200,
                              width: 1,
                            ),
                          ),
                          child: ListTile(
                            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                            leading: Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: _presensiStatus.contains('Sudah') ? Colors.green.shade50 : Colors.orange.shade50,
                                shape: BoxShape.circle,
                              ),
                              child: Icon(
                                _presensiStatus.contains('Sudah') ? Icons.check_circle : Icons.warning_amber_rounded,
                                color: _presensiStatus.contains('Sudah') ? Colors.green : Colors.orange,
                              ),
                            ),
                            title: Text(
                              _presensiStatus,
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            subtitle: const Text(
                              'Lakukan presensi Datang/Pulang via menu Presensi GPS',
                              style: TextStyle(fontSize: 11, color: Colors.black54),
                            ),
                          ),
                        ),
                      ],

                      // Today's schedules widget (Only for Guru)
                      if (authProvider.activeRole == 'guru') ...[
                        const SizedBox(height: 20),
                        _sectionLabel(Icons.calendar_today_rounded, 'Jadwal Mengajar Hari Ini'),
                        const SizedBox(height: 10),
                        if (_todaySchedules.isEmpty)
                          Card(
                            elevation: 0,
                            color: Colors.blueGrey.shade50.withValues(alpha: 0.5),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                              side: BorderSide(color: Colors.blueGrey.shade100, width: 1),
                            ),
                            child: Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                              child: Row(
                                children: [
                                  Icon(Icons.info_outline_rounded, color: Colors.blueGrey.shade600, size: 22),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Text(
                                      'Tidak ada jadwal mengajar hari ini.',
                                      style: TextStyle(
                                        color: Colors.blueGrey.shade700,
                                        fontSize: 13,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          )
                        else
                          ..._todaySchedules.map((s) {
                            final isFilled = s['is_filled'] == true;
                            final isSynced = s['is_synced'] == true;
                            return Card(
                              margin: const EdgeInsets.only(bottom: 8),
                              elevation: 1,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                                side: BorderSide(
                                  color: isFilled ? Colors.green.shade200 : Colors.red.shade200,
                                  width: 1,
                                ),
                              ),
                              child: ListTile(
                                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                                leading: Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: isFilled ? Colors.green.shade50 : Colors.red.shade50,
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    isFilled ? Icons.check_circle : Icons.error_outline,
                                    color: isFilled ? Colors.green : Colors.red,
                                  ),
                                ),
                                title: Text(
                                  "${s['kelas']} - ${s['mapel']}",
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                ),
                                subtitle: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const SizedBox(height: 4),
                                    Text("Jam Ke: ${s['jamke']}", style: const TextStyle(fontSize: 12, color: Colors.black54)),
                                    const SizedBox(height: 2),
                                    Text("Materi: ${s['materi']}", style: const TextStyle(fontSize: 12, fontStyle: FontStyle.italic, color: Colors.black54)),
                                    const SizedBox(height: 4),
                                    Row(
                                      children: [
                                        Icon(
                                          isSynced ? Icons.sync : Icons.sync_disabled,
                                          size: 13,
                                          color: isSynced ? Colors.blue.shade700 : Colors.orange.shade700,
                                        ),
                                        const SizedBox(width: 4),
                                        Text(
                                          isSynced ? "Sudah disinkron Admin" : "Belum disinkron Admin",
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w600,
                                            color: isSynced ? Colors.blue.shade800 : Colors.orange.shade800,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                                trailing: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: isFilled ? Colors.green.shade100 : Colors.red.shade100,
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(
                                    isFilled ? 'Sudah Diisi' : 'Belum Diisi',
                                    style: TextStyle(
                                      color: isFilled ? Colors.green.shade800 : Colors.red.shade800,
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                              ),
                            );
                          }),
                      ],
                      const SizedBox(height: 20),
                    ],

                    // 1. Menu Guru & Staf
                    if (guruCards.isNotEmpty) ...[
                      _sectionLabel(Icons.school, 'Menu Guru & Staf', color: const Color(0xFF1E3A8A)),
                      const SizedBox(height: 12),
                      GridView.count(
                        crossAxisCount: 2,
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        crossAxisSpacing: 16,
                        mainAxisSpacing: 16,
                        children: guruCards,
                      ),
                      const SizedBox(height: 24),
                    ],

                    // 2. Menu Wali Kelas
                    if (waliKelasCards.isNotEmpty) ...[
                      _sectionLabel(Icons.assignment_ind, 'Menu Wali Kelas', color: const Color(0xFF15803D)),
                      const SizedBox(height: 12),
                      GridView.count(
                        crossAxisCount: 2,
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        crossAxisSpacing: 16,
                        mainAxisSpacing: 16,
                        children: waliKelasCards,
                      ),
                      const SizedBox(height: 24),
                    ],

                    // 3. Menu Kurikulum & Admin
                    if (kurikulumCards.isNotEmpty) ...[
                      _sectionLabel(Icons.admin_panel_settings, 'Menu Kurikulum & Admin', color: const Color(0xFFC2410C)),
                      const SizedBox(height: 12),
                      GridView.count(
                        crossAxisCount: 2,
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        crossAxisSpacing: 16,
                        mainAxisSpacing: 16,
                        children: kurikulumCards,
                      ),
                      const SizedBox(height: 24),
                    ],

                    // 4. Pengaturan Akun
                    _sectionLabel(Icons.manage_accounts, 'Pengaturan Akun'),
                    const SizedBox(height: 12),
                    GridView.count(
                      crossAxisCount: 2,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      crossAxisSpacing: 16,
                      mainAxisSpacing: 16,
                      children: [
                        _buildMenuCard(
                          context: context,
                          icon: Icons.lock_open,
                          color: Colors.deepPurple,
                          title: 'Ganti Password',
                          subtitle: 'Keamanan Akun',
                          onTap: () {
                            _showGantiPasswordDialog(context);
                          },
                        ),

                        _buildMenuCard(
                          context: context,
                          icon: Icons.logout,
                          color: Colors.redAccent,
                          title: 'Keluar',
                          subtitle: 'Log out Akun',
                          onTap: () async {
                            await authProvider.logout();
                          },
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _sectionLabel(IconData icon, String label, {Color color = const Color(0xFF0F4C81)}) =>
      Row(children: [
        Container(
          padding: const EdgeInsets.all(6),
          decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
          child: Icon(icon, color: color, size: 16),
        ),
        const SizedBox(width: 10),
        Text(label, style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: color == Colors.red ? Colors.red : const Color(0xFF1A2B4A))),
      ]);

  Widget _buildMenuCard({
    required BuildContext context,
    required IconData icon,
    required Color color,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return _InteractiveMenuCard(
      icon: icon,
      color: color,
      title: title,
      subtitle: subtitle,
      onTap: onTap,
    );
  }

  void _showIzinBottomSheet(BuildContext context) {
    final tglController = TextEditingController(text: DateTime.now().toString().split(' ')[0]);
    final ketController = TextEditingController();
    String sia = 'Sakit';
    int jumlah = 1;
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
                left: 20,
                right: 20,
                top: 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Text(
                      'Ajukan Surat Izin / Sakit',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 20),
                    
                    // Date
                    TextField(
                      controller: tglController,
                      decoration: const InputDecoration(
                        labelText: 'Tanggal Mulai Izin',
                        prefixIcon: Icon(Icons.calendar_today),
                        border: OutlineInputBorder(),
                      ),
                      readOnly: true,
                    ),
                    const SizedBox(height: 16),

                    // Type
                    DropdownButtonFormField<String>(
                      initialValue: sia,
                      decoration: const InputDecoration(
                        labelText: 'Jenis Izin',
                        border: OutlineInputBorder(),
                      ),
                      items: ['Sakit', 'Ijin'].map((v) {
                        return DropdownMenuItem(value: v, child: Text(v));
                      }).toList(),
                      onChanged: (val) {
                        setModalState(() {
                          sia = val!;
                        });
                      },
                    ),
                    const SizedBox(height: 16),

                    // Days count
                    TextField(
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Jumlah Hari',
                        prefixIcon: Icon(Icons.timelapse),
                        border: OutlineInputBorder(),
                      ),
                      onChanged: (val) {
                        setModalState(() {
                          jumlah = int.tryParse(val) ?? 1;
                        });
                      },
                    ),
                    const SizedBox(height: 16),

                    // Details Description
                    TextField(
                      controller: ketController,
                      maxLines: 2,
                      decoration: const InputDecoration(
                        labelText: 'Keterangan Izin',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Action Button
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0F4C81),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                      onPressed: isSubmitting
                          ? null
                          : () async {
                              setModalState(() {
                                isSubmitting = true;
                              });
                              final res = await _apiService.submitIzin(
                                tglmasuk: tglController.text,
                                sia: sia,
                                jumlah: jumlah,
                                ket: ketController.text,
                              );
                              if (context.mounted) {
                                Navigator.pop(context);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(res['body']['message'] ?? 'Permohonan terkirim!'),
                                    backgroundColor: res['status'] == 200 ? Colors.green : Colors.red,
                                  ),
                                );
                              }
                            },
                      child: isSubmitting
                          ? const CircularProgressIndicator(color: Colors.white)
                          : const Text('Kirim Pengajuan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                    ),
                    const SizedBox(height: 20),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildStatBox({
    required String title,
    required String subtitle,
    required Color color,
    required IconData icon,
    bool compact = false,
  }) {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
            colors: [color, color.withValues(alpha: 0.78)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: color.withValues(alpha: 0.3), blurRadius: 10, offset: const Offset(0, 4))],
      ),
      padding: EdgeInsets.all(compact ? 10.0 : 14.0),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            padding: EdgeInsets.all(compact ? 6 : 8),
            decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.18), shape: BoxShape.circle),
            child: Icon(icon, color: Colors.white, size: compact ? 18 : 22),
          ),
          SizedBox(height: compact ? 6 : 8),
          Text(
            title,
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: compact ? 14 : 18),
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 2),
          Text(
            subtitle,
            style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: compact ? 9 : 11, fontWeight: FontWeight.w500),
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  void _showIzinSiswaBottomSheet(BuildContext context) {
    String selectedIjin = 'Ijin Pesiar';
    bool isSubmitting = false;
    XFile? selectedFile;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        final authProvider = Provider.of<AuthProvider>(context, listen: false);
        return StatefulBuilder(
          builder: (context, setModalState) {
            final String studentClass = (_stats != null ? _stats!['kelas'] ?? '-' : '-');
            final bool isFileRequired = (selectedIjin == 'Ijin Bermalam Wajib' || selectedIjin == 'Ijin Bermalam Resmi');

            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
                left: 20,
                right: 20,
                top: 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Text(
                      'Ajukan Izin Siswa',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 20),

                    // Nama Siswa (Readonly)
                    TextField(
                      controller: TextEditingController(text: authProvider.name),
                      decoration: const InputDecoration(
                        labelText: 'Nama Siswa',
                        border: OutlineInputBorder(),
                        filled: true,
                        fillColor: Color(0xFFF1F3F5),
                      ),
                      readOnly: true,
                    ),
                    const SizedBox(height: 16),

                    // Kelas (Readonly)
                    TextField(
                      controller: TextEditingController(text: studentClass),
                      decoration: const InputDecoration(
                        labelText: 'Kelas',
                        border: OutlineInputBorder(),
                        filled: true,
                        fillColor: Color(0xFFF1F3F5),
                      ),
                      readOnly: true,
                    ),
                    const SizedBox(height: 16),
                    
                    // Keperluan Izin Dropdown
                    DropdownButtonFormField<String>(
                      initialValue: selectedIjin,
                      decoration: const InputDecoration(
                        labelText: 'Keperluan Izin',
                        border: OutlineInputBorder(),
                      ),
                      items: [
                        'Ijin Pesiar',
                        'Ijin Bermalam Wajib',
                        'Ijin Bermalam Resmi',
                        'Ijin Jalan',
                        'Ijin Khusus'
                      ].map((v) {
                        return DropdownMenuItem(value: v, child: Text(v));
                      }).toList(),
                      onChanged: (val) {
                        setModalState(() {
                          selectedIjin = val!;
                        });
                      },
                    ),

                    // Sisa Ijin Info Box
                    () {
                      final keyMap = {
                        'Ijin Pesiar': 'ip',
                        'Ijin Bermalam Wajib': 'ib',
                        'Ijin Bermalam Resmi': 'ibr',
                        'Ijin Jalan': 'ij',
                        'Ijin Khusus': 'ik',
                      };
                      final key = keyMap[selectedIjin];
                      final sisa = (_stats != null && key != null) ? _stats![key] ?? 0 : 0;
                      return Container(
                        margin: const EdgeInsets.only(top: 12, bottom: 20),
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE6F0FA),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFF0A3D91).withValues(alpha: 0.2)),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.info, color: Color(0xFF0A3D91), size: 20),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                "Sisa $selectedIjin: $sisa",
                                style: const TextStyle(
                                  color: Color(0xFF002366),
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                ),
                              ),
                            ),
                          ],
                        ),
                      );
                    }(),

                    // File Upload (Simulasi)
                    Container(
                      margin: const EdgeInsets.only(bottom: 20),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade400),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isFileRequired 
                                ? "Upload File (Gambar JPG/JPEG/PNG) *Wajib" 
                                : "Upload File (Gambar JPG/JPEG/PNG) (Opsional)",
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                              color: isFileRequired ? Colors.red : Colors.black87,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  selectedFile?.name ?? "Belum ada file terpilih",
                                  style: TextStyle(
                                    color: selectedFile != null ? Colors.black87 : Colors.black38,
                                    fontSize: 13,
                                  ),
                                ),
                              ),
                              ElevatedButton.icon(
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFFE6F0FA),
                                  foregroundColor: const Color(0xFF0A3D91),
                                  elevation: 0,
                                ),
                                icon: const Icon(Icons.upload_file, size: 18),
                                label: const Text("Pilih File", style: TextStyle(fontSize: 12)),
                                onPressed: () async {
                                  final ImagePicker picker = ImagePicker();
                                  final XFile? image = await picker.pickImage(source: ImageSource.gallery);
                                  if (image != null) {
                                    final int sizeInBytes = await image.length();
                                    if (sizeInBytes > 10 * 1024 * 1024) {
                                      if (context.mounted) {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          const SnackBar(
                                            content: Text('Ukuran file tidak boleh melebihi 10MB!'),
                                            backgroundColor: Colors.red,
                                          ),
                                        );
                                      }
                                      return;
                                    }
                                    setModalState(() {
                                      selectedFile = image;
                                    });
                                  }
                                },
                              )
                            ],
                          ),
                        ],
                      ),
                    ),

                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0F4C81),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                      onPressed: isSubmitting
                          ? null
                          : () async {
                              if (isFileRequired && selectedFile == null) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text('Upload file surat keterangan wajib diisi!'),
                                    backgroundColor: Colors.red,
                                  ),
                                );
                                return;
                              }

                              setModalState(() {
                                isSubmitting = true;
                              });

                              final res = await _apiService.submitIzinSiswa(
                                ijin: selectedIjin,
                                file: selectedFile,
                              );
                              if (context.mounted) {
                                Navigator.pop(context);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(res['body']['message'] ?? 'Permohonan izin siswa terkirim!'),
                                    backgroundColor: res['status'] == 200 ? Colors.green : Colors.red,
                                  ),
                                );
                              }
                            },
                      child: isSubmitting
                          ? const CircularProgressIndicator(color: Colors.white)
                          : const Text('Kirim Pengajuan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                    ),
                    const SizedBox(height: 20),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showDaftarIzinSiswaDialog(BuildContext context) async {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Daftar Izin Anda'),
          content: FutureBuilder<Map<String, dynamic>>(
            future: _apiService.getIzinSiswaList(),
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const SizedBox(
                  height: 100,
                  child: Center(child: CircularProgressIndicator()),
                );
              }
              if (!snapshot.hasData || snapshot.data!['list'] == null || (snapshot.data!['list'] as List).isEmpty) {
                return const SizedBox(
                  height: 60,
                  child: Center(child: Text('Belum ada riwayat izin.')),
                );
              }

              final list = snapshot.data!['list'] as List;

              return SizedBox(
                width: double.maxFinite,
                height: 300,
                child: ListView.builder(
                  shrinkWrap: true,
                  itemCount: list.length,
                  itemBuilder: (context, index) {
                    final item = list[index];
                    return Card(
                      child: ListTile(
                        title: Text(item['ketijin'] ?? '-'),
                        subtitle: Text(
                          "Status:\n- Siswa: ${item['oksis']}\n- Kurikulum: ${item['okkur']}\n- Pembina: ${item['okbin']}\n- Asrama: ${item['okas']}",
                          style: const TextStyle(fontSize: 12),
                        ),
                        trailing: Icon(
                          (item['oksis'] == 'ok' && item['okkur'] == 'ok' && item['okbin'] == 'ok' && item['okas'] == 'ok')
                              ? Icons.check_circle
                              : Icons.pending,
                          color: (item['oksis'] == 'ok' && item['okkur'] == 'ok' && item['okbin'] == 'ok' && item['okas'] == 'ok')
                              ? Colors.green
                              : Colors.orange,
                        ),
                      ),
                    );
                  },
                ),
              );
            },
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Tutup'),
            )
          ],
        );
      },
    );
  }

  void _showDaftarIzinGuruDialog(BuildContext context) async {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Daftar Izin Cuti Anda'),
          content: FutureBuilder<Map<String, dynamic>>(
            future: _apiService.getIzinGuruList(),
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const SizedBox(
                  height: 100,
                  child: Center(child: CircularProgressIndicator()),
                );
              }
              if (!snapshot.hasData || snapshot.data!['list'] == null || (snapshot.data!['list'] as List).isEmpty) {
                return const SizedBox(
                  height: 60,
                  child: Center(child: Text('Belum ada riwayat izin.')),
                );
              }

              final list = snapshot.data!['list'] as List;

              return SizedBox(
                width: double.maxFinite,
                height: 300,
                child: ListView.builder(
                  shrinkWrap: true,
                  itemCount: list.length,
                  itemBuilder: (context, index) {
                    final item = list[index];
                    return Card(
                      child: ListTile(
                        title: Text(item['ket'] ?? '-'),
                        subtitle: Text(
                          "Jenis: ${item['sia']}\nJumlah: ${item['jumlah']} Hari\nTanggal Mulai: ${item['tglmasuk']}",
                          style: const TextStyle(fontSize: 12),
                        ),
                        trailing: const Icon(
                          Icons.check_circle,
                          color: Colors.green,
                        ),
                      ),
                    );
                  },
                ),
              );
            },
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Tutup'),
            )
          ],
        );
      },
    );
  }

  void _showGantiPasswordDialog(BuildContext context) {
    final oldPasswordController = TextEditingController();
    final newPasswordController = TextEditingController();
    final confirmPasswordController = TextEditingController();
    bool isSubmitting = false;

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return AlertDialog(
              title: const Text('Ganti Password'),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextField(
                      controller: oldPasswordController,
                      obscureText: true,
                      decoration: const InputDecoration(
                        labelText: 'Password Lama',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: newPasswordController,
                      obscureText: true,
                      decoration: const InputDecoration(
                        labelText: 'Password Baru',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: confirmPasswordController,
                      obscureText: true,
                      decoration: const InputDecoration(
                        labelText: 'Konfirmasi Password Baru',
                        border: OutlineInputBorder(),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Batal'),
                ),
                ElevatedButton(
                  onPressed: isSubmitting
                      ? null
                      : () async {
                          final oldPass = oldPasswordController.text;
                          final newPass = newPasswordController.text;
                          final confirmPass = confirmPasswordController.text;

                          if (oldPass.isEmpty || newPass.isEmpty || confirmPass.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Semua field wajib diisi.')),
                            );
                            return;
                          }

                          if (newPass != confirmPass) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Password baru dan konfirmasi tidak cocok.')),
                            );
                            return;
                          }

                          setModalState(() {
                            isSubmitting = true;
                          });

                          final res = await _apiService.changePassword(oldPass, newPass);

                          setModalState(() {
                            isSubmitting = false;
                          });

                          if (context.mounted) {
                            Navigator.pop(context);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(res['body']['message'] ?? 'Password berhasil diubah.'),
                                backgroundColor: res['status'] == 200 ? Colors.green : Colors.red,
                              ),
                            );
                          }
                        },
                  child: isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('Simpan'),
                )
              ],
            );
          },
        );
      },
    );
  }

  void _showGarjasDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Kesamaptaan Jasmani (Garjas)'),
          content: FutureBuilder<Map<String, dynamic>>(
            future: _apiService.getGarjasList(),
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const SizedBox(
                  height: 100,
                  child: Center(child: CircularProgressIndicator()),
                );
              }
              final list = (snapshot.data != null && snapshot.data!['list'] != null)
                  ? snapshot.data!['list'] as List
                  : [];

              return SizedBox(
                width: double.maxFinite,
                height: 400,
                child: Column(
                  children: [
                    ElevatedButton.icon(
                      icon: const Icon(Icons.add),
                      label: const Text('Input Data Garjas'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0F4C81),
                        foregroundColor: Colors.white,
                      ),
                      onPressed: () {
                        Navigator.pop(context);
                        _showInputGarjasDialog(context);
                      },
                    ),
                    const SizedBox(height: 12),
                    Expanded(
                      child: list.isEmpty
                          ? const Center(child: Text('Belum ada riwayat Garjas.'))
                          : ListView.builder(
                              itemCount: list.length,
                              itemBuilder: (context, index) {
                                final item = list[index];
                                final int bulanNum = item['bulan'] ?? 1;
                                final List<String> namaBulan = [
                                  '', 'Januari', 'Februari', 'Maret', 'April', 'Mei',
                                  'Juni', 'Juli', 'Agustus', 'September', 'Oktober',
                                  'November', 'Desember'
                                ];
                                final String bulanName = (bulanNum >= 1 && bulanNum <= 12) ? namaBulan[bulanNum] : '';
                                
                                return Card(
                                  margin: const EdgeInsets.symmetric(vertical: 6),
                                  child: Padding(
                                    padding: const EdgeInsets.all(12.0),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              "$bulanName ${item['tahun']}",
                                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                            ),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                              decoration: BoxDecoration(
                                                color: const Color(0xFFE6F0FA),
                                                borderRadius: BorderRadius.circular(12),
                                              ),
                                              child: Text(
                                                "Nilai Akhir: ${item['nb'] ?? 'Belum Dinilai'}",
                                                style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 11,
                                                  color: Color(0xFF0A3D91),
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const Divider(),
                                        Text("Lari (A): ${item['lari'] ?? 0} meter (Nilai: ${item['nlari'] ?? '-'})"),
                                        Text("Pull Up (B1): ${item['up'] ?? 0} kali (Nilai: ${item['nup'] ?? '-'})"),
                                        Text("Sit Up (B2): ${item['situp'] ?? 0} kali (Nilai: ${item['nsitup'] ?? '-'})"),
                                        Text("Push Up (B3): ${item['pushup'] ?? 0} kali (Nilai: ${item['npushup'] ?? '-'})"),
                                        Text("Shuttle Run (B4): ${item['shuttle'] ?? 0} detik (Nilai: ${item['nshuttle'] ?? '-'})"),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                    ),
                  ],
                ),
              );
            },
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Tutup'),
            )
          ],
        );
      },
    );
  }

  void _showInputGarjasDialog(BuildContext context) {
    int selectedBulan = DateTime.now().month;
    int selectedTahun = DateTime.now().year;
    final lariController = TextEditingController();
    final upController = TextEditingController();
    final situpController = TextEditingController();
    final pushupController = TextEditingController();
    final shuttleController = TextEditingController();
    bool isSubmitting = false;

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return AlertDialog(
              title: const Text('Input Data Garjas'),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<int>(
                            initialValue: selectedBulan,
                            decoration: const InputDecoration(labelText: 'Bulan'),
                            items: List.generate(12, (i) => i + 1).map((m) {
                              final List<String> namaBulan = [
                                '', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                                'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                              ];
                              return DropdownMenuItem(value: m, child: Text(namaBulan[m]));
                            }).toList(),
                            onChanged: (val) {
                              setModalState(() {
                                selectedBulan = val!;
                              });
                            },
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: DropdownButtonFormField<int>(
                            initialValue: selectedTahun,
                            decoration: const InputDecoration(labelText: 'Tahun'),
                            items: [selectedTahun - 1, selectedTahun, selectedTahun + 1].map((y) {
                              return DropdownMenuItem(value: y, child: Text("$y"));
                            }).toList(),
                            onChanged: (val) {
                              setModalState(() {
                                selectedTahun = val!;
                              });
                            },
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: lariController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Lari (meter)',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: upController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Pull Up (kali)',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: situpController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Sit Up (kali)',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: pushupController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Push Up (kali)',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: shuttleController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: const InputDecoration(
                        labelText: 'Shuttle Run (detik)',
                        border: OutlineInputBorder(),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _showGarjasDialog(context);
                  },
                  child: const Text('Batal'),
                ),
                ElevatedButton(
                  onPressed: isSubmitting
                      ? null
                      : () async {
                          setModalState(() {
                            isSubmitting = true;
                          });

                          final res = await _apiService.submitGarjas(
                            bulan: selectedBulan,
                            tahun: selectedTahun,
                            lari: int.tryParse(lariController.text),
                            up: int.tryParse(upController.text),
                            situp: int.tryParse(situpController.text),
                            pushup: int.tryParse(pushupController.text),
                            shuttle: double.tryParse(shuttleController.text),
                          );

                          setModalState(() {
                            isSubmitting = false;
                          });

                          if (context.mounted) {
                            Navigator.pop(context);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(res['body']['message'] ?? 'Data Garjas disimpan.'),
                                backgroundColor: res['status'] == 200 ? Colors.green : Colors.red,
                              ),
                            );
                            _showGarjasDialog(context);
                          }
                        },
                  child: isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('Simpan'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _showDownloadJurnalDialog(BuildContext context) {
    DateTime? startDate;
    DateTime? endDate;
    bool isSubmitting = false;

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: const Row(
                children: [
                  Icon(Icons.file_download, color: Color(0xFF0F4C81)),
                  SizedBox(width: 10),
                  Text('Unduh Jurnal Guru', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                ],
              ),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'Pilih rentang tanggal untuk mengekspor rekap jurnal mengajar Anda ke format Excel.',
                    style: TextStyle(fontSize: 12, color: Colors.black54),
                  ),
                  const SizedBox(height: 16),
                  
                  // Start Date Button
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      alignment: Alignment.centerLeft,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    icon: const Icon(Icons.calendar_today, size: 18),
                    label: Text(
                      startDate == null 
                          ? 'Dari Tanggal *' 
                          : 'Dari: ${startDate!.day}/${startDate!.month}/${startDate!.year}',
                      style: TextStyle(color: startDate == null ? Colors.black38 : Colors.black87, fontSize: 13),
                    ),
                    onPressed: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: startDate ?? DateTime.now(),
                        firstDate: DateTime(2020),
                        lastDate: DateTime.now(),
                      );
                      if (date != null) {
                        setModalState(() {
                          startDate = date;
                        });
                      }
                    },
                  ),
                  const SizedBox(height: 12),

                  // End Date Button
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      alignment: Alignment.centerLeft,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    icon: const Icon(Icons.calendar_today, size: 18),
                    label: Text(
                      endDate == null 
                          ? 'Sampai Tanggal *' 
                          : 'Sampai: ${endDate!.day}/${endDate!.month}/${endDate!.year}',
                      style: TextStyle(color: endDate == null ? Colors.black38 : Colors.black87, fontSize: 13),
                    ),
                    onPressed: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: endDate ?? DateTime.now(),
                        firstDate: startDate ?? DateTime(2020),
                        lastDate: DateTime.now(),
                      );
                      if (date != null) {
                        setModalState(() {
                          endDate = date;
                        });
                      }
                    },
                  ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Batal'),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0F4C81),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: isSubmitting
                      ? null
                      : () async {
                          if (startDate == null || endDate == null) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Silakan pilih tanggal awal dan akhir!')),
                            );
                            return;
                          }

                          if (endDate!.isBefore(startDate!)) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Tanggal akhir tidak boleh mendahului tanggal awal!')),
                            );
                            return;
                          }

                          setModalState(() {
                            isSubmitting = true;
                          });

                          final String? token = await _apiService.getStoredValue('auth_token');
                          final String? tahunAjaranId = await _apiService.getStoredValue('tahun_ajaran_id');
                          if (token == null) {
                            if (context.mounted) Navigator.pop(context);
                            return;
                          }

                          // Format dates to YYYY-MM-DD
                          final String startStr = '${startDate!.year}-${startDate!.month.toString().padLeft(2, '0')}-${startDate!.day.toString().padLeft(2, '0')}';
                          final String endStr = '${endDate!.year}-${endDate!.month.toString().padLeft(2, '0')}-${endDate!.day.toString().padLeft(2, '0')}';

                          // Extract base web URL
                          String webBase = ApiService.baseUrl.replaceAll('/api', '');

                          // Construct redirect path
                          String redirectPath = '/jurnalguru/export?start_date=$startStr&end_date=$endStr';
                          String encodedRedirect = Uri.encodeComponent(redirectPath);

                          // Construct auto login URL
                          String autoLoginUrl = "$webBase/auth/auto-login?token=$token&redirect=$encodedRedirect";
                          if (tahunAjaranId != null) {
                            autoLoginUrl += "&tahun_ajaran_id=$tahunAjaranId";
                          }
                          autoLoginUrl += "&as_role=guru";

                          final uri = Uri.parse(autoLoginUrl);
                          
                          if (await canLaunchUrl(uri)) {
                            await launchUrl(uri, mode: LaunchMode.externalApplication);
                          } else {
                            if (context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Gagal membuka browser untuk mengunduh rekap.')),
                              );
                            }
                          }

                          if (context.mounted) {
                            Navigator.pop(context);
                          }
                        },
                  child: isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text('Unduh Excel', style: TextStyle(color: Colors.white)),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Map<String, List<Widget>> _buildMenuCardsGrouped(String role) {
    final List<Widget> guruCards = [];
    final List<Widget> waliKelasCards = [];
    final List<Widget> kurikulumCards = [];
    
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final isActuallyWaliKelas = role == 'walikelas' || (role == 'guru' && authProvider.walikelasKelas.isNotEmpty);

    // ────────────────────────────────────────────────────────
    // 1. MENU GURU & STAF
    // ────────────────────────────────────────────────────────
    
    // Presensi GPS (Presensi Guru)
    if (authProvider.hasPermission('presensi_view') && role != 'siswa') {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.camera_alt,
        color: Colors.teal,
        title: 'Presensi GPS',
        subtitle: 'Checkin / Checkout',
        onTap: () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const PresensiScreen()));
        },
      ));
    }

    // Izin / Cuti
    if (authProvider.hasPermission('izin_create') && role != 'siswa') {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.assignment_turned_in,
        color: Colors.amber,
        title: 'Izin / Cuti',
        subtitle: 'Ajukan Izin Digital',
        onTap: () {
          _showIzinBottomSheet(context);
        },
      ));
    }

    // Ijin Guru (Daftar Cuti)
    if (authProvider.hasPermission('izin_view') && role != 'siswa') {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.list_alt,
        color: Colors.orange,
        title: 'Ijin Guru',
        subtitle: 'Daftar Pengajuan Cuti',
        onTap: () {
          _showDaftarIzinGuruDialog(context);
        },
      ));
    }

    // Garjas
    if (role == 'siswa' || role == 'kesiswaan' || role == 'pembina' || role == 'admin') {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.directions_run,
        color: Colors.blueAccent,
        title: 'Garjas',
        subtitle: 'Uji Kesamaptaan Jasmani',
        onTap: () {
          _showGarjasDialog(context);
        },
      ));
    }

    // Isi Jurnal (For non-guru/ketua kelas)
    if ((authProvider.hasPermission('jurnal_create') || authProvider.hasPermission('jurnal_edit')) && role != 'guru') {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.book,
        color: Colors.blueAccent,
        title: 'Isi Jurnal',
        subtitle: 'Catat KBM Hari Ini',
        onTap: () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const JurnalScreen()));
        },
      ));
    }

    // Tambah Ijin Siswa (if student)
    if (authProvider.hasPermission('izin_create') && role == 'siswa') {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.note_add,
        color: Colors.teal,
        title: 'Tambah Ijin',
        subtitle: 'Ajukan Izin Siswa',
        onTap: () {
          _showIzinSiswaBottomSheet(context);
        },
      ));
    }

    // Ijin Siswa status (if student)
    if (authProvider.hasPermission('izin_view') && role == 'siswa') {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.list_alt,
        color: Colors.amber,
        title: 'Ijin Siswa',
        subtitle: 'Daftar Pengajuan Izin',
        onTap: () {
          _showDaftarIzinSiswaDialog(context);
        },
      ));
    }

    // Jadwal Pelajaran
    if (authProvider.hasPermission('jurnal_view') && role != 'siswa') {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.calendar_month,
        color: Colors.purple,
        title: 'Jadwal Pelajaran',
        subtitle: 'Jadwal Mengajar Anda',
        onTap: () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const JadwalScreen()));
        },
      ));
    }

    // Riwayat Jurnal
    if (authProvider.hasPermission('jurnal_view')) {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.library_books,
        color: Colors.cyan,
        title: 'Riwayat Jurnal',
        subtitle: 'Daftar Riwayat Jurnal',
        onTap: () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const RiwayatJurnalScreen()));
        },
      ));
    }

    // Unduh Jurnal (Excel)
    if (role == 'guru') {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.file_download,
        color: Colors.orange,
        title: 'Unduh Jurnal',
        subtitle: 'Ekspor Rekap Excel',
        onTap: () {
          _showDownloadJurnalDialog(context);
        },
      ));
    }

    // Perangkat Pembelajaran
    if (authProvider.hasPermission('jurnal_view') && role != 'siswa') {
      guruCards.add(_buildMenuCard(
        context: context,
        icon: Icons.folder_shared,
        color: Colors.brown,
        title: 'Perangkat Pembelajaran',
        subtitle: 'Syllabus & RPP Guru',
        onTap: () {
          _openWebModule('/perangkat', 'Perangkat Pembelajaran');
        },
      ));
    }

    // ────────────────────────────────────────────────────────
    // 2. MENU WALI KELAS
    // ────────────────────────────────────────────────────────
    if (isActuallyWaliKelas || role == 'admin') {
      // Absensi Siswa
      if (authProvider.hasPermission('presensi_view')) {
        waliKelasCards.add(_buildMenuCard(
          context: context,
          icon: Icons.how_to_reg,
          color: Colors.teal,
          title: 'Absensi Siswa',
          subtitle: 'Input Absensi Kelas',
          onTap: () {
            _openWebModule('/absen', 'Absensi Siswa', asRole: isActuallyWaliKelas ? 'walikelas' : null);
          },
        ));
      }



      // Rekap Absen Siswa
      if (authProvider.hasPermission('presensi_view')) {
        waliKelasCards.add(_buildMenuCard(
          context: context,
          icon: Icons.assessment,
          color: Colors.teal.shade700,
          title: 'Rekap Absen Siswa',
          subtitle: 'Persentase Absensi',
          onTap: () {
            _openWebModule('/siswa', 'Rekap Absen Siswa', asRole: isActuallyWaliKelas ? 'walikelas' : null);
          },
        ));
      }

      // Tambah Kasus
      if (authProvider.hasPermission('poin_view') && role != 'siswa') {
        waliKelasCards.add(_buildMenuCard(
          context: context,
          icon: Icons.gavel,
          color: Colors.redAccent,
          title: 'Tambah Kasus',
          subtitle: 'Laporkan Pelanggaran',
          onTap: () {
            _openWebModule('/tambahkasus', 'Tambah Kasus');
          },
        ));
      }

      // Lihat Kasus
      if (authProvider.hasPermission('poin_view')) {
        waliKelasCards.add(_buildMenuCard(
          context: context,
          icon: Icons.search,
          color: Colors.red.shade800,
          title: 'Lihat Kasus',
          subtitle: 'Daftar Kasus Siswa',
          onTap: () {
            _openWebModule('/lihatkasus', 'Lihat Kasus');
          },
        ));
      }

      // Poin & SP Siswa
      if (authProvider.hasPermission('poin_view')) {
        waliKelasCards.add(_buildMenuCard(
          context: context,
          icon: Icons.stars,
          color: Colors.amber.shade800,
          title: 'Poin & SP Siswa',
          subtitle: 'Sanksi Disiplin Siswa',
          onTap: () {
            Navigator.push(context, MaterialPageRoute(builder: (_) => const PoinSiswaScreen()));
          },
        ));
      }
    }

    // Ijin Siswa (Persetujuan)
    if (authProvider.hasPermission('izin_view') && role != 'siswa') {
      final card = _buildMenuCard(
        context: context,
        icon: Icons.co_present,
        color: Colors.deepOrange,
        title: 'Ijin Siswa',
        subtitle: 'Persetujuan Izin Siswa',
        onTap: () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const IjinSiswaScreen()));
        },
      );

      if (role == 'kurikulum' || role == 'admin') {
        kurikulumCards.add(card);
      }
      if (role == 'walikelas' || (role == 'guru' && authProvider.walikelasKelas.isNotEmpty)) {
        waliKelasCards.add(card);
      }
    }

    // ────────────────────────────────────────────────────────
    // 3. MENU KURIKULUM & ADMIN
    // ────────────────────────────────────────────────────────
    
    // Jurnal Harian
    if (authProvider.hasPermission('jurnal_view') && role != 'guru') {
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.today,
        color: Colors.indigo,
        title: 'Jurnal Harian',
        subtitle: 'Daftar Jurnal KBM',
        onTap: () {
          _openWebModule('/jurnalh', 'Jurnal Harian');
        },
      ));
    }

    // Rekap Jurnal
    if (authProvider.hasPermission('jurnal_view')) {
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.pie_chart,
        color: Colors.pink,
        title: 'Rekap Jurnal',
        subtitle: 'Kelengkapan Jurnal',
        onTap: () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const RekapJurnalScreen()));
        },
      ));
    }

    // Jurnal Susulan
    if (authProvider.hasPermission('jurnal_create') || authProvider.hasPermission('jurnal_edit')) {
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.update,
        color: Colors.deepPurpleAccent,
        title: 'Jurnal Susulan',
        subtitle: 'Isi Jurnal Lampau',
        onTap: () {
          _openWebModule('/susulan', 'Jurnal Susulan', asRole: isActuallyWaliKelas ? 'walikelas' : null);
        },
      ));
    }

    // Rekap Presensi Guru
    if (authProvider.hasPermission('presensi_view') && role != 'siswa' && role != 'guru') {
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.calendar_month,
        color: Colors.indigoAccent,
        title: 'Rekap Presensi Guru',
        subtitle: 'Daftar Kehadiran Guru',
        onTap: () {
          _openWebModule('/presensi-guru/rekap', 'Rekap Presensi Guru');
        },
      ));
    }

    // Pengaturan Lokasi
    if (authProvider.hasPermission('operator_view')) {
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.map,
        color: Colors.redAccent,
        title: 'Pengaturan Lokasi',
        subtitle: 'Radius Lokasi Absen',
        onTap: () {
          _openWebModule('/presensi-guru/setting', 'Pengaturan Lokasi');
        },
      ));
    }

    // Kategori Poin
    if (authProvider.hasPermission('poin_view') && role == 'admin') {
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.list,
        color: Colors.blueGrey,
        title: 'Kategori Poin',
        subtitle: 'Master Poin Pelanggaran',
        onTap: () {
          _openWebModule('/kategori-poin', 'Kategori Poin');
        },
      ));
    }

    // Kasus Lengkap
    if (authProvider.hasPermission('poin_view') && role == 'admin') {
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.folder_copy,
        color: Colors.blueGrey.shade800,
        title: 'Kasus Lengkap',
        subtitle: 'Arsip Laporan Kasus',
        onTap: () {
          _openWebModule('/kasus', 'Kasus Lengkap');
        },
      ));
    }

    // Tahun Ajaran, Data Kelas, Data Mapel
    if (authProvider.hasPermission('master_view')) {
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.calendar_today,
        color: Colors.blueGrey,
        title: 'Tahun Ajaran',
        subtitle: 'Master Tahun Ajaran',
        onTap: () {
          _openWebModule('/tahun-ajaran', 'Tahun Ajaran');
        },
      ));
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.meeting_room,
        color: Colors.blueGrey,
        title: 'Data Kelas',
        subtitle: 'Master Data Kelas',
        onTap: () {
          _openWebModule('/kelas', 'Data Kelas');
        },
      ));
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.menu_book,
        color: Colors.blueGrey,
        title: 'Data Mapel',
        subtitle: 'Master Mata Pelajaran',
        onTap: () {
          _openWebModule('/mapel', 'Data Mapel');
        },
      ));
    }

    // Data Operator, Logs, Backup
    if (authProvider.hasPermission('operator_view')) {
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.manage_accounts,
        color: Colors.blueGrey,
        title: 'Data Operator',
        subtitle: 'Manajemen Akun Operator',
        onTap: () {
          _openWebModule('/operator', 'Data Operator');
        },
      ));
      if (role == 'admin') {
        kurikulumCards.add(_buildMenuCard(
          context: context,
          icon: Icons.history,
          color: Colors.blueGrey,
          title: 'Log Aktivitas',
          subtitle: 'Audit Log Sistem',
          onTap: () {
            _openWebModule('/admin/logs', 'Log Aktivitas');
          },
        ));
        kurikulumCards.add(_buildMenuCard(
          context: context,
          icon: Icons.settings_backup_restore,
          color: Colors.blueGrey,
          title: 'Backup & Restore',
          subtitle: 'Pencadangan Database',
          onTap: () {
            _openWebModule('/admin/backup', 'Backup & Restore');
          },
        ));
      }
    }

    // Tagihan Siswa
    if (authProvider.hasPermission('tagihan_view')) {
      kurikulumCards.add(_buildMenuCard(
        context: context,
        icon: Icons.receipt_long,
        color: Colors.green.shade700,
        title: 'Tagihan Siswa',
        subtitle: 'Spp & Pembayaran',
        onTap: () {
          _openWebModule('/tagihan', 'Tagihan Siswa');
        },
      ));
    }

    return {
      'guru': guruCards,
      'walikelas': waliKelasCards,
      'kurikulum': kurikulumCards,
    };
  }


  void _showRoleSwitcher(BuildContext context, AuthProvider authProvider) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Padding(
                padding: EdgeInsets.all(16.0),
                child: Text(
                  "Pilih Peran Aktif",
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ),
              const Divider(),
              ...authProvider.availableRoles.map((r) => ListTile(
                    leading: const Icon(Icons.account_circle),
                    title: Text(r.toUpperCase()),
                    trailing: authProvider.activeRole == r
                        ? const Icon(Icons.check, color: Colors.green)
                        : null,
                    onTap: () async {
                      Navigator.pop(context);
                      setState(() {
                        _isLoading = true;
                      });
                      await authProvider.switchActiveRole(r);
                      await _fetchDashboardData();
                    },
                  )),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDashboardShimmer(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    final isSiswa = Provider.of<AuthProvider>(context, listen: false).role == 'siswa';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header Profile Banner Shimmer
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.04),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                )
              ],
            ),
            child: Row(
              children: [
                const _ShimmerContainer(width: 56, height: 56, borderRadius: 28),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _ShimmerContainer(width: screenWidth * 0.4, height: 16, borderRadius: 4),
                      const SizedBox(height: 8),
                      _ShimmerContainer(width: screenWidth * 0.25, height: 12, borderRadius: 4),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // Statistik Shimmer
          Row(
            children: [
              const _ShimmerContainer(width: 16, height: 16, borderRadius: 4),
              const SizedBox(width: 8),
              _ShimmerContainer(width: screenWidth * 0.35, height: 14, borderRadius: 4),
            ],
          ),
          const SizedBox(height: 12),
          GridView.count(
            crossAxisCount: isSiswa ? 2 : 3,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: isSiswa ? 1.3 : 1.1,
            children: List.generate(
              isSiswa ? 6 : 3,
              (index) => const _ShimmerContainer(width: double.infinity, height: double.infinity, borderRadius: 16),
            ),
          ),
          const SizedBox(height: 24),

          // Menu Utama Shimmer
          Row(
            children: [
              const _ShimmerContainer(width: 16, height: 16, borderRadius: 4),
              const SizedBox(width: 8),
              _ShimmerContainer(width: screenWidth * 0.3, height: 14, borderRadius: 4),
            ],
          ),
          const SizedBox(height: 12),
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
            children: List.generate(
              4,
              (index) => const _ShimmerContainer(width: double.infinity, height: double.infinity, borderRadius: 18),
            ),
          ),
        ],
      ),
    );
  }
}

class _ShimmerContainer extends StatefulWidget {
  final double width;
  final double height;
  final double borderRadius;
  final EdgeInsetsGeometry? margin;

  const _ShimmerContainer({
    required this.width,
    required this.height,
    this.borderRadius = 8,
    this.margin,
  });

  @override
  State<_ShimmerContainer> createState() => _ShimmerContainerState();
}

class _ShimmerContainerState extends State<_ShimmerContainer> with SingleTickerProviderStateMixin {
  late AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return Container(
          width: widget.width,
          height: widget.height,
          margin: widget.margin,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(widget.borderRadius),
            gradient: LinearGradient(
              colors: [
                const Color(0xFFE2E8F0),
                const Color(0xFFF1F5F9),
                const Color(0xFFE2E8F0),
              ],
              stops: const [0.1, 0.5, 0.9],
              begin: Alignment(-1.0 + _controller.value * 2, -0.3),
              end: Alignment(1.0 + _controller.value * 2, 0.3),
            ),
          ),
        );
      },
    );
  }
}

class _InteractiveMenuCard extends StatefulWidget {
  final IconData icon;
  final Color color;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  const _InteractiveMenuCard({
    required this.icon,
    required this.color,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  @override
  State<_InteractiveMenuCard> createState() => _InteractiveMenuCardState();
}

class _InteractiveMenuCardState extends State<_InteractiveMenuCard> with SingleTickerProviderStateMixin {
  late double _scale;
  late AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 100),
      lowerBound: 0.0,
      upperBound: 0.03, // Scale down by 3%
    )..addListener(() {
        setState(() {});
      });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    _scale = 1.0 - _controller.value;

    return Transform.scale(
      scale: _scale,
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        child: InkWell(
          onTap: widget.onTap,
          onTapDown: (_) => _controller.forward(),
          onTapCancel: () => _controller.reverse(),
          borderRadius: BorderRadius.circular(18),
          child: Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(18),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.04),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                )
              ],
            ),
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(11),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                          colors: [widget.color, widget.color.withValues(alpha: 0.75)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight),
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color: widget.color.withValues(alpha: 0.3),
                          blurRadius: 8,
                          offset: const Offset(0, 4),
                        )
                      ],
                    ),
                    child: Icon(widget.icon, color: Colors.white, size: 26),
                  ),
                  const SizedBox(height: 14),
                  Text(
                    widget.title,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1A2B4A)),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    widget.subtitle,
                    style: const TextStyle(fontSize: 10, color: Color(0xFF8899AA)),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
