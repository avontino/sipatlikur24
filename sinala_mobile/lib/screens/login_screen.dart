import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../main.dart';
import '../services/api_service.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> with TickerProviderStateMixin {
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _apiService = ApiService();
  bool _isLoading = false;
  bool _obscurePassword = true;
  String? _errorMessage;
  List<dynamic> _semesters = [];
  int? _selectedSemesterId;
  int _logoTapCount = 0;

  void _showServerConfigDialog() {
    final controller = TextEditingController(text: ApiService.baseUrl);
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Konfigurasi Server API', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('Masukkan alamat URL API SINALA (contoh: http://192.168.1.50:8000/api):', style: TextStyle(fontSize: 12)),
              const SizedBox(height: 12),
              TextField(
                controller: controller,
                style: const TextStyle(fontSize: 13),
                decoration: const InputDecoration(
                  labelText: 'URL API',
                  border: OutlineInputBorder(),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              onPressed: () async {
                final url = controller.text.trim();
                if (url.isNotEmpty) {
                  await _apiService.saveCustomServerUrl(url);
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Server URL berhasil diubah ke: $url')),
                  );
                  _fetchSemesters();
                }
              },
              child: const Text('Simpan'),
            ),
          ],
        );
      },
    );
  }

  late AnimationController _fadeCtrl;
  late Animation<double> _fadeAnim;
  late AnimationController _slideCtrl;
  late Animation<Offset> _slideAnim;

  @override
  void initState() {
    super.initState();
    _fadeCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 800));
    _fadeAnim = CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeOut);
    _slideCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 700));
    _slideAnim = Tween<Offset>(begin: const Offset(0, 0.12), end: Offset.zero)
        .animate(CurvedAnimation(parent: _slideCtrl, curve: Curves.easeOut));
    _fadeCtrl.forward();
    _slideCtrl.forward();
    _fetchSemesters();
  }

  @override
  void dispose() {
    _fadeCtrl.dispose();
    _slideCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchSemesters() async {
    final res = await _apiService.getSemesters();
    if (mounted && res['status'] == 'success' && res['semesters'] != null) {
      setState(() {
        _semesters = res['semesters'];
        if (_semesters.isNotEmpty) _selectedSemesterId = _semesters.first['id'];
      });
    }
  }

  Future<void> _handleLogin() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text.trim();
    if (username.isEmpty || password.isEmpty) {
      setState(() => _errorMessage = 'Username dan password tidak boleh kosong.');
      return;
    }
    if (_selectedSemesterId == null) {
      setState(() => _errorMessage = 'Silakan pilih tahun ajaran / semester.');
      return;
    }
    setState(() { _isLoading = true; _errorMessage = null; });
    final response = await _apiService.login(username, password, tahunAjaranId: _selectedSemesterId);
    setState(() => _isLoading = false);

    if (response['status'] == 200) {
      final body = response['body'];
      final token = body['token'];
      final user = body['user'];
      final name = user['name'];
      final role = user['role'];
      final walikelasKelas = user['walikelas_kelas'];
      final List<String> availableRoles = user['available_roles'] != null
          ? List<String>.from(user['available_roles']) : [role];
      final List<String> permissions = user['permissions'] != null
          ? List<String>.from(user['permissions']) : <String>[];
      final semester = body['semester'];
      final String? semesterId = semester != null ? semester['id']?.toString() : null;
      if (mounted) {
        final authProvider = Provider.of<AuthProvider>(context, listen: false);
        await authProvider.login(token, role, name,
            tahunAjaranId: semesterId,
            walikelasKelas: walikelasKelas,
            availableRoles: availableRoles,
            permissions: permissions);
      }
    } else {
      setState(() => _errorMessage = response['body']['message'] ?? 'Gagal melakukan autentikasi.');
    }
  }

  InputDecoration _inputDecoration(String hint, IconData icon) => InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: Color(0xFF9EA5B4), fontSize: 14),
        prefixIcon: Icon(icon, color: const Color(0xFF0F4C81), size: 20),
        contentPadding: const EdgeInsets.symmetric(vertical: 17, horizontal: 20),
        filled: true,
        fillColor: const Color(0xFFF5F8FF),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Color(0xFFDDE3EF)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Color(0xFFDDE3EF)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Color(0xFF0F4C81), width: 1.8),
        ),
      );

  @override
  Widget build(BuildContext context) {
    final logoUrl = ApiService.baseUrl.replaceAll('/api', '/adminlte/img/user2.png');
    final backgroundUrl = ApiService.baseUrl.replaceAll('/api', '/adminlte/img/background.png');

    return Scaffold(
      body: Stack(
        children: [
          // ── Gradient Background ─────────────────────────────
          Container(
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0A3D91), Color(0xFF002366)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              image: DecorationImage(
                image: const AssetImage('assets/images/background.png'),
                fit: BoxFit.cover,
                colorFilter: ColorFilter.mode(
                  const Color(0xFF002366).withValues(alpha: 0.85),
                  BlendMode.srcOver,
                ),
              ),
            ),
          ),

          // ── Decorative circles ──────────────────────────────
          Positioned(top: -60, right: -60,
            child: Container(width: 220, height: 220,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.06),
              ),
            ),
          ),
          Positioned(bottom: 40, left: -80,
            child: Container(width: 300, height: 300,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.05),
              ),
            ),
          ),

          // ── Main content ─────────────────────────────────────
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
                child: FadeTransition(
                  opacity: _fadeAnim,
                  child: SlideTransition(
                    position: _slideAnim,
                    child: Container(
                      constraints: const BoxConstraints(maxWidth: 420),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(28),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.18),
                            blurRadius: 40,
                            offset: const Offset(0, 16),
                          ),
                        ],
                      ),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 30, vertical: 38),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            // Logo
                            GestureDetector(
                              onTap: () {
                                setState(() {
                                    _logoTapCount++;
                                    if (_logoTapCount >= 5) {
                                      _logoTapCount = 0;
                                      _showServerConfigDialog();
                                    }
                                });
                              },
                              child: Image.asset(
                                'assets/images/logo.png',
                                width: 95,
                                height: 95,
                                fit: BoxFit.contain,
                                errorBuilder: (context, error, stackTrace) {
                                  return Container(
                                    width: 86,
                                    height: 86,
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      gradient: const LinearGradient(
                                        colors: [Color(0xFF0F4C81), Color(0xFF1A73E8)],
                                        begin: Alignment.topLeft,
                                        end: Alignment.bottomRight,
                                      ),
                                      boxShadow: [
                                        BoxShadow(
                                          color: const Color(0xFF0F4C81).withValues(alpha: 0.35),
                                          blurRadius: 20,
                                          offset: const Offset(0, 8),
                                        ),
                                      ],
                                    ),
                                    child: const Icon(Icons.school, size: 44, color: Colors.white),
                                  );
                                },
                              ),
                            ),
                            const SizedBox(height: 20),

                            const Text('SINALA',
                                style: TextStyle(
                                    fontSize: 30,
                                    fontWeight: FontWeight.w900,
                                    color: Color(0xFF0F4C81),
                                    letterSpacing: 2.5)),
                            const SizedBox(height: 4),
                            const Text('SMAN TARUNA NALA MALANG',
                                style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF8899AA),
                                    letterSpacing: 1.2),
                                textAlign: TextAlign.center),
                            const SizedBox(height: 8),
                            Container(
                              width: 40,
                              height: 3,
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                    colors: [Color(0xFF0F4C81), Color(0xFF4FC3F7)]),
                                borderRadius: BorderRadius.circular(2),
                              ),
                            ),
                            const SizedBox(height: 32),

                            // Username
                            TextField(
                              controller: _usernameController,
                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
                              decoration: _inputDecoration('NIP / Username', Icons.person_outline),
                            ),
                            const SizedBox(height: 16),

                            // Password
                            TextField(
                              controller: _passwordController,
                              obscureText: _obscurePassword,
                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
                              decoration: _inputDecoration('Password', Icons.lock_outline).copyWith(
                                suffixIcon: IconButton(
                                  icon: Icon(
                                      _obscurePassword
                                          ? Icons.visibility_off_outlined
                                          : Icons.visibility_outlined,
                                      color: const Color(0xFF8899AA),
                                      size: 20),
                                  onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                                ),
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Semester Picker
                            if (_semesters.isNotEmpty)
                              DropdownButtonFormField<int>(
                                value: _selectedSemesterId,
                                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500, color: Colors.black87),
                                decoration: _inputDecoration('Tahun Ajaran', Icons.calendar_month_outlined),
                                items: _semesters.map((s) => DropdownMenuItem<int>(
                                  value: s['id'],
                                  child: Text("${s['tahun_ajaran']} – ${s['semester']}",
                                      style: const TextStyle(fontSize: 13)),
                                )).toList(),
                                onChanged: (val) => setState(() => _selectedSemesterId = val),
                              )
                            else
                              Container(
                                padding: const EdgeInsets.symmetric(vertical: 17, horizontal: 16),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF5F8FF),
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(color: const Color(0xFFDDE3EF)),
                                ),
                                child: const Row(children: [
                                  SizedBox(width: 4),
                                  Icon(Icons.calendar_month_outlined, color: Color(0xFF0F4C81), size: 20),
                                  SizedBox(width: 12),
                                  Text('Memuat tahun ajaran…', style: TextStyle(color: Color(0xFF9EA5B4), fontSize: 14)),
                                ]),
                              ),

                            // Error
                            if (_errorMessage != null) ...[
                              const SizedBox(height: 16),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFEECEC),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFFFFCDD2)),
                                ),
                                child: Row(children: [
                                  const Icon(Icons.error_outline, color: Colors.red, size: 18),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Text(_errorMessage!,
                                        style: const TextStyle(color: Color(0xFFC62828), fontSize: 13)),
                                  ),
                                ]),
                              ),
                            ],
                            const SizedBox(height: 28),

                            // Login Button
                            SizedBox(
                              width: double.infinity,
                              height: 52,
                              child: DecoratedBox(
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(14),
                                  gradient: const LinearGradient(
                                      colors: [Color(0xFF0F4C81), Color(0xFF1565C0)]),
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xFF0F4C81).withOpacity(0.4),
                                      blurRadius: 16,
                                      offset: const Offset(0, 6),
                                    ),
                                  ],
                                ),
                                child: ElevatedButton(
                                  onPressed: _isLoading ? null : _handleLogin,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.transparent,
                                    shadowColor: Colors.transparent,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                  ),
                                  child: _isLoading
                                      ? const SizedBox(width: 22, height: 22,
                                          child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                                      : const Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Icon(Icons.login, color: Colors.white, size: 18),
                                            SizedBox(width: 8),
                                            Text('MASUK', style: TextStyle(color: Colors.white, fontSize: 16,
                                                fontWeight: FontWeight.bold, letterSpacing: 1.2)),
                                          ],
                                        ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 20),

                            // Footer
                            Text('© ${DateTime.now().year} SMAN Taruna Nala Malang',
                                style: const TextStyle(color: Color(0xFFBBCCDD), fontSize: 11)),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
