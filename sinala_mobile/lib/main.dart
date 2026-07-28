import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';
import 'services/api_service.dart';

void main() {
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
      ],
      child: const SinalaApp(),
    ),
  );
}

class SinalaApp extends StatelessWidget {
  const SinalaApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SINALA Mobile',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0F4C81),
          primary: const Color(0xFF0F4C81),
          secondary: const Color(0xFF1D6FA5),
          surface: const Color(0xFFF8FAFC),
        ),
        textTheme: GoogleFonts.outfitTextTheme(Theme.of(context).textTheme),
      ),
      home: const AuthWrapper(),
    );
  }
}

class AuthWrapper extends StatefulWidget {
  const AuthWrapper({super.key});

  @override
  State<AuthWrapper> createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  final _storage = const FlutterSecureStorage();
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _checkLoginStatus();
  }

  Future<void> _checkLoginStatus() async {
    String? customUrl = await _storage.read(key: 'custom_server_url');
    if (customUrl != null && customUrl.isNotEmpty) {
      ApiService.updateBaseUrlCache(customUrl);
    }
    String? token = await _storage.read(key: 'auth_token');
    String? role = await _storage.read(key: 'user_role');
    String? name = await _storage.read(key: 'user_name');
    String? walikelasKelas = await _storage.read(key: 'user_walikelas_kelas');
    String? activeRole = await _storage.read(key: 'user_active_role');
    String? availableRolesStr = await _storage.read(key: 'user_available_roles');
    List<String> availableRoles = availableRolesStr != null ? availableRolesStr.split(',') : [];

    String? permissionsStr = await _storage.read(key: 'user_permissions');
    List<String> permissions = permissionsStr != null && permissionsStr.isNotEmpty 
        ? permissionsStr.split(',') 
        : [];

    if (token != null && role != null && name != null) {
      if (mounted) {
        final authProvider = Provider.of<AuthProvider>(context, listen: false);
        authProvider.setSession(
          token,
          role,
          name,
          walikelasKelas: walikelasKelas,
          activeRole: activeRole,
          availableRoles: availableRoles,
          permissions: permissions,
        );
      }
    }
    setState(() {
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(),
        ),
      );
    }

    // Watch auth status
    final authProvider = Provider.of<AuthProvider>(context);
    return authProvider.isAuthenticated ? const DashboardScreen() : const LoginScreen();
  }
}

class AuthProvider with ChangeNotifier {
  String? _token;
  String? _role;
  String? _name;
  String? _walikelasKelas;
  String? _activeRole;
  List<String> _availableRoles = [];
  List<String> _permissions = [];

  bool get isAuthenticated => _token != null;
  String get role => _role ?? '';
  String get name => _name ?? '';
  String get token => _token ?? '';
  String get walikelasKelas => _walikelasKelas ?? '';
  String get activeRole => _activeRole ?? role;
  List<String> get availableRoles => _availableRoles;
  List<String> get permissions => _permissions;

  bool hasPermission(String permission) {
    if (activeRole == 'admin') return true;
    return _permissions.contains(permission);
  }

  final _storage = const FlutterSecureStorage();

  void setSession(
    String token,
    String role,
    String name, {
    String? walikelasKelas,
    String? activeRole,
    List<String>? availableRoles,
    List<String>? permissions,
  }) {
    _token = token;
    _role = role;
    _name = name;
    _walikelasKelas = walikelasKelas;
    _activeRole = activeRole ?? role;
    _availableRoles = availableRoles ?? [role];
    _permissions = permissions ?? [];
    notifyListeners();
  }

  Future<void> updatePermissions(List<String> newPermissions) async {
    await _storage.write(key: 'user_permissions', value: newPermissions.join(','));
    _permissions = newPermissions;
    notifyListeners();
  }

  Future<void> login(
    String token,
    String role,
    String name, {
    String? tahunAjaranId,
    String? walikelasKelas,
    String? activeRole,
    List<String>? availableRoles,
    List<String>? permissions,
  }) async {
    await _storage.write(key: 'auth_token', value: token);
    await _storage.write(key: 'user_role', value: role);
    await _storage.write(key: 'user_name', value: name);
    if (walikelasKelas != null) {
      await _storage.write(key: 'user_walikelas_kelas', value: walikelasKelas);
    }
    await _storage.write(key: 'user_active_role', value: activeRole ?? role);
    await _storage.write(key: 'user_available_roles', value: availableRoles?.join(',') ?? role);
    await _storage.write(key: 'user_permissions', value: permissions?.join(',') ?? '');

    if (tahunAjaranId != null) {
      await _storage.write(key: 'tahun_ajaran_id', value: tahunAjaranId);
    }
    setSession(
      token,
      role,
      name,
      walikelasKelas: walikelasKelas,
      activeRole: activeRole,
      availableRoles: availableRoles,
      permissions: permissions,
    );
  }

  Future<void> switchActiveRole(String newRole) async {
    await _storage.write(key: 'user_active_role', value: newRole);
    _activeRole = newRole;
    notifyListeners();
  }

  Future<void> logout() async {
    await _storage.delete(key: 'auth_token');
    await _storage.delete(key: 'user_role');
    await _storage.delete(key: 'user_name');
    await _storage.delete(key: 'user_walikelas_kelas');
    await _storage.delete(key: 'user_active_role');
    await _storage.delete(key: 'user_available_roles');
    await _storage.delete(key: 'user_permissions');
    await _storage.delete(key: 'tahun_ajaran_id');
    _token = null;
    _role = null;
    _name = null;
    _walikelasKelas = null;
    _activeRole = null;
    _availableRoles = [];
    _permissions = [];
    notifyListeners();
  }
}
