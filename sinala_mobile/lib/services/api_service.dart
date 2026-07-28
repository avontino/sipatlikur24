import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:image_picker/image_picker.dart';


class ApiService {
  static String? _cachedBaseUrl;
  static String get baseUrl => _cachedBaseUrl ?? "http://localhost:8000/api"; 
  
  static void updateBaseUrlCache(String newUrl) {
    _cachedBaseUrl = newUrl;
  }
  
  Future<void> saveCustomServerUrl(String url) async {
    await _storage.write(key: 'custom_server_url', value: url);
    _cachedBaseUrl = url;
  }
  
  final _storage = const FlutterSecureStorage();

  Future<bool> _hasInternet() async {
    final connectivityResult = await Connectivity().checkConnectivity();
    return !connectivityResult.contains(ConnectivityResult.none);
  }

  Future<String?> _getToken() async {
    return await _storage.read(key: 'auth_token');
  }

  Future<String?> getStoredValue(String key) async {
    return await _storage.read(key: key);
  }

  Future<Map<String, String>> _getHeaders({bool auth = true}) async {
    final headers = {
      "Content-Type": "application/json",
      "Accept": "application/json",
    };
    if (auth) {
      String? token = await _getToken();
      if (token != null) {
        headers["Authorization"] = "Bearer $token";
      }
    }
    return headers;
  }

  Future<Map<String, dynamic>> login(String username, String password, {int? tahunAjaranId}) async {
    if (!await _hasInternet()) {
      return {
        "status": 503,
        "body": {"message": "Koneksi Terputus. Pastikan internet Anda aktif."}
      };
    }
    try {
      final response = await http.post(
        Uri.parse("$baseUrl/login"),
        headers: await _getHeaders(auth: false),
        body: jsonEncode({
          "username": username,
          "password": password,
          "tahun_ajaran_id": tahunAjaranId,
        }),
      );
      return {
        "status": response.statusCode,
        "body": jsonDecode(response.body)
      };
    } catch (e) {
      return {
        "status": 500,
        "body": {"message": "Gagal terhubung ke server backend SINALA: $e"}
      };
    }
  }

  Future<Map<String, dynamic>> getSemesters() async {
    if (!await _hasInternet()) {
      return {"status": "error", "semesters": [], "message": "Koneksi terputus."};
    }
    try {
      final response = await http.get(Uri.parse("$baseUrl/semesters"));
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "semesters": []};
    }
  }

  Future<Map<String, dynamic>> getUserProfile({String? activeRole}) async {
    if (!await _hasInternet()) {
      return {"status": "error", "message": "Koneksi terputus. Pastikan internet aktif."};
    }
    try {
      String url = "$baseUrl/user-profile";
      if (activeRole != null) {
        url += "?active_role=$activeRole";
      }
      final response = await http.get(
        Uri.parse(url),
        headers: await _getHeaders(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "message": e.toString()};
    }
  }

  Future<Map<String, dynamic>> getTodayPresensi() async {
    if (!await _hasInternet()) {
      return {"status": "error", "message": "Koneksi terputus. Pastikan internet aktif."};
    }
    try {
      final response = await http.get(
        Uri.parse("$baseUrl/presensi/today"),
        headers: await _getHeaders(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "message": e.toString()};
    }
  }

  Future<Map<String, dynamic>> submitPresensi(
      double lat, double lng, String tipe, String fotoBase64) async {
    if (!await _hasInternet()) {
      return {
        "status": 503,
        "body": {"message": "Koneksi Terputus. Pastikan internet Anda aktif."}
      };
    }
    try {
      final response = await http.post(
        Uri.parse("$baseUrl/presensi/submit"),
        headers: await _getHeaders(),
        body: jsonEncode({
          "lat": lat,
          "lng": lng,
          "tipe": tipe,
          "foto": fotoBase64,
        }),
      );
      return {
        "status": response.statusCode,
        "body": jsonDecode(response.body)
      };
    } catch (e) {
      return {
        "status": 500,
        "body": {"message": "Koneksi terputus."}
      };
    }
  }

  Future<Map<String, dynamic>> submitIzin({
    required String tglmasuk,
    required String sia,
    required int jumlah,
    required String ket,
    XFile? attachment,
  }) async {
    if (!await _hasInternet()) {
      return {
        "status": 503,
        "body": {"message": "Koneksi Terputus. Pastikan internet Anda aktif."}
      };
    }
    try {
      String? token = await _getToken();
      var request = http.MultipartRequest('POST', Uri.parse("$baseUrl/izin/submit"));
      
      request.headers['Accept'] = 'application/json';
      if (token != null) {
        request.headers['Authorization'] = 'Bearer $token';
      }

      request.fields['tglmasuk'] = tglmasuk;
      request.fields['sia'] = sia;
      request.fields['jumlah'] = jumlah.toString();
      request.fields['ket'] = ket;

      if (attachment != null) {
        final bytes = await attachment.readAsBytes();
        request.files.add(http.MultipartFile.fromBytes(
          'attachment',
          bytes,
          filename: attachment.name,
        ));
      }

      var streamedResponse = await request.send();
      var response = await http.Response.fromStream(streamedResponse);
      
      return {
        "status": response.statusCode,
        "body": jsonDecode(response.body)
      };
    } catch (e) {
      return {
        "status": 500,
        "body": {"message": "Gagal mengirim izin."}
      };
    }
  }

  Future<Map<String, dynamic>> submitIzinSiswa({
    required String ijin,
    XFile? file,
  }) async {
    try {
      String? token = await _getToken();
      var request = http.MultipartRequest('POST', Uri.parse("$baseUrl/izin-siswa/submit"));
      
      request.headers['Accept'] = 'application/json';
      if (token != null) {
        request.headers['Authorization'] = 'Bearer $token';
      }

      request.fields['ijin'] = ijin;

      if (file != null) {
        final bytes = await file.readAsBytes();
        request.files.add(http.MultipartFile.fromBytes(
          'file',
          bytes,
          filename: file.name,
        ));
      }

      var streamedResponse = await request.send();
      var response = await http.Response.fromStream(streamedResponse);
      
      return {
        "status": response.statusCode,
        "body": jsonDecode(response.body)
      };
    } catch (e) {
      return {
        "status": 500,
        "body": {"message": "Gagal mengirim izin siswa: $e"}
      };
    }
  }

  Future<Map<String, dynamic>> getIzinSiswaList() async {
    try {
      final response = await http.get(
        Uri.parse("$baseUrl/izin-siswa/list"),
        headers: await _getHeaders(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "list": []};
    }
  }

  Future<Map<String, dynamic>> getIzinGuruList() async {
    try {
      final response = await http.get(
        Uri.parse("$baseUrl/izin/list"),
        headers: await _getHeaders(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "list": []};
    }
  }

  Future<Map<String, dynamic>> getSchedules() async {
    try {
      final response = await http.get(
        Uri.parse("$baseUrl/jurnal/schedules"),
        headers: await _getHeaders(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "schedules": []};
    }
  }

  Future<Map<String, dynamic>> getJournalWarnings({String? activeRole}) async {
    try {
      String url = "$baseUrl/jurnal/warnings";
      if (activeRole != null) {
        url += "?active_role=$activeRole";
      }
      final response = await http.get(
        Uri.parse(url),
        headers: await _getHeaders(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "warnings": []};
    }
  }

  Future<Map<String, dynamic>> submitJurnal({
    required String kelas,
    required String mapel,
    required String guru,
    required String materi,
    required String catatan,
    required String jamke,
    required int jumlahjam,
    required String penugasan,
    required String ketGuruMapel,
  }) async {
    try {
      final response = await http.post(
        Uri.parse("$baseUrl/jurnal/submit"),
        headers: await _getHeaders(),
        body: jsonEncode({
          "kelas": kelas,
          "mapel": mapel,
          "guru": guru,
          "materi": materi,
          "catatan": catatan,
          "jamke": jamke,
          "jumlahjam": jumlahjam,
          "penugasan": penugasan,
          "ket_guru_mapel": ketGuruMapel
        }),
      );
      return {
        "status": response.statusCode,
        "body": jsonDecode(response.body)
      };
    } catch (e) {
      return {
        "status": 500,
        "body": {"message": "Koneksi terputus."}
      };
    }
  }

  Future<void> saveFcmToken(String fcmToken, String deviceType) async {
    try {
      await http.post(
        Uri.parse("$baseUrl/save-fcm-token"),
        headers: await _getHeaders(),
        body: jsonEncode({
          "fcm_token": fcmToken,
          "device_type": deviceType,
        }),
      );
    } catch (_) {}
  }

  Future<Map<String, dynamic>> changePassword(String oldPassword, String newPassword) async {
    try {
      final response = await http.post(
        Uri.parse("$baseUrl/change-password"),
        headers: await _getHeaders(),
        body: jsonEncode({
          "old_password": oldPassword,
          "new_password": newPassword,
        }),
      );
      return {
        "status": response.statusCode,
        "body": jsonDecode(response.body)
      };
    } catch (e) {
      return {
        "status": 500,
        "body": {"message": "Gagal mengubah password: $e"}
      };
    }
  }

  Future<Map<String, dynamic>> getGarjasList() async {
    try {
      final response = await http.get(
        Uri.parse("$baseUrl/garjas"),
        headers: await _getHeaders(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "list": []};
    }
  }

  Future<Map<String, dynamic>> submitGarjas({
    required int bulan,
    required int tahun,
    int? lari,
    int? up,
    int? situp,
    int? pushup,
    double? shuttle,
  }) async {
    try {
      final response = await http.post(
        Uri.parse("$baseUrl/garjas/submit"),
        headers: await _getHeaders(),
        body: jsonEncode({
          "bulan": bulan,
          "tahun": tahun,
          "lari": lari,
          "up": up,
          "situp": situp,
          "pushup": pushup,
          "shuttle": shuttle,
        }),
      );
      return {
        "status": response.statusCode,
        "body": jsonDecode(response.body)
      };
    } catch (e) {
      return {
        "status": 500,
        "body": {"message": "Gagal menyimpan data Garjas: $e"}
      };
    }
  }

  // ============================================================
  // NATIVE SCREEN METHODS
  // ============================================================

  /// GET /api/jadwal — Jadwal pelajaran, dikelompokkan per hari
  Future<Map<String, dynamic>> getJadwal({String? kelas}) async {
    if (!await _hasInternet()) {
      return {"status": "error", "message": "Tidak ada koneksi internet."};
    }
    try {
      final headers = await _getHeaders();
      String url = '${ApiService.baseUrl}/jadwal';
      if (kelas != null && kelas.isNotEmpty) url += '?kelas=${Uri.encodeComponent(kelas)}';
      final response = await http.get(Uri.parse(url), headers: headers);
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "message": "Gagal memuat jadwal: $e"};
    }
  }

  /// GET /api/jurnal/riwayat — Riwayat jurnal, dengan filter opsional
  Future<Map<String, dynamic>> getRiwayatJurnal({String? tanggal, String? kelas}) async {
    if (!await _hasInternet()) {
      return {"status": "error", "message": "Tidak ada koneksi internet."};
    }
    try {
      final headers = await _getHeaders();
      final params = <String, String>{};
      if (tanggal != null && tanggal.isNotEmpty) params['tanggal'] = tanggal;
      if (kelas != null && kelas.isNotEmpty) params['kelas'] = kelas;
      final uri = Uri.parse('${ApiService.baseUrl}/jurnal/riwayat').replace(queryParameters: params);
      final response = await http.get(uri, headers: headers);
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "message": "Gagal memuat riwayat jurnal: $e"};
    }
  }

  /// GET /api/jurnal/rekap — Rekap kelengkapan jurnal per kelas
  Future<Map<String, dynamic>> getRekapJurnal({String? kelas}) async {
    if (!await _hasInternet()) {
      return {"status": "error", "message": "Tidak ada koneksi internet."};
    }
    try {
      final headers = await _getHeaders();
      String url = '${ApiService.baseUrl}/jurnal/rekap';
      if (kelas != null && kelas.isNotEmpty) url += '?kelas=${Uri.encodeComponent(kelas)}';
      final response = await http.get(Uri.parse(url), headers: headers);
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "message": "Gagal memuat rekap jurnal: $e"};
    }
  }

  /// GET /api/ijin-siswa/daftar — Daftar ijin siswa sesuai role
  Future<Map<String, dynamic>> getIjinSiswaDaftar({String? kelas}) async {
    if (!await _hasInternet()) {
      return {"status": "error", "message": "Tidak ada koneksi internet."};
    }
    try {
      final headers = await _getHeaders();
      String url = '${ApiService.baseUrl}/ijin-siswa/daftar';
      if (kelas != null && kelas.isNotEmpty) url += '?kelas=${Uri.encodeComponent(kelas)}';
      final response = await http.get(Uri.parse(url), headers: headers);
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "message": "Gagal memuat daftar ijin siswa: $e"};
    }
  }

  /// POST /api/ijin-siswa/verifikasi/{id} — Verifikasi/approve ijin siswa
  Future<Map<String, dynamic>> verifikasiIjinSiswa(int id, {String? activeRole}) async {
    if (!await _hasInternet()) {
      return {"status": "error", "message": "Tidak ada koneksi internet."};
    }
    try {
      final headers = await _getHeaders();
      String url = '${ApiService.baseUrl}/ijin-siswa/verifikasi/$id';
      if (activeRole != null && activeRole.isNotEmpty) {
        url += '?active_role=${Uri.encodeComponent(activeRole)}';
      }
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "message": "Gagal verifikasi ijin: $e"};
    }
  }

  /// GET /api/poin-siswa — Rekap poin pelanggaran & prestasi per siswa
  Future<Map<String, dynamic>> getPoinSiswa({String? kelas, String? search}) async {
    if (!await _hasInternet()) {
      return {"status": "error", "message": "Tidak ada koneksi internet."};
    }
    try {
      final headers = await _getHeaders();
      final params = <String, String>{};
      if (kelas != null && kelas.isNotEmpty) params['kelas'] = kelas;
      if (search != null && search.isNotEmpty) params['search'] = search;
      final uri = Uri.parse('${ApiService.baseUrl}/poin-siswa').replace(queryParameters: params);
      final response = await http.get(uri, headers: headers);
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "message": "Gagal memuat data poin siswa: $e"};
    }
  }

  /// POST /api/jurnal/update/{id} — Update/edit jurnal pelajaran KBM
  Future<Map<String, dynamic>> updateJurnalKbm(
    int id, {
    required String materi,
    required String ketGuruMapel,
    required String penugasan,
    String? catatan,
  }) async {
    if (!await _hasInternet()) {
      return {"status": "error", "message": "Tidak ada koneksi internet."};
    }
    try {
      final headers = await _getHeaders();
      final body = {
        "materi": materi,
        "ket_guru_mapel": ketGuruMapel,
        "penugasan": penugasan,
        "catatan": catatan ?? "",
      };
      final response = await http.post(
        Uri.parse('${ApiService.baseUrl}/jurnal/update/$id'),
        headers: headers,
        body: jsonEncode(body),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {"status": "error", "message": "Gagal memperbarui jurnal: $e"};
    }
  }
}

