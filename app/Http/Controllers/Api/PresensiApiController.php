<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PresensiGuru;
use App\Models\Ijin;
use Carbon\Carbon;
use App\Helpers\AuditLog;

class PresensiApiController extends Controller
{
    private function getConfig()
    {
        $config = DB::table('presensi_guru_settings')->first();
        if (!$config) {
            return (object) [
                'latitude' => -7.8012,
                'longitude' => 112.0123,
                'radius' => 100,
                'jam_masuk' => '07:00:00',
                'jam_pulang' => '14:00:00'
            ];
        }
        return $config;
    }

    private function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public function todayStatus(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();
        $config = $this->getConfig();

        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        $taName = $activeTa ? $activeTa->tahun_ajaran : '-';
        $semester = $activeTa ? $activeTa->semester : '-';

        $todayPresensi = PresensiGuru::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        $history = PresensiGuru::where('user_id', $user->id)
            ->where('tahun_ajaran', $taName)
            ->where('semester', $semester)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'config' => [
                'latitude' => $config->latitude,
                'longitude' => $config->longitude,
                'radius' => $config->radius,
                'jam_masuk' => $config->jam_masuk,
                'jam_pulang' => $config->jam_pulang
            ],
            'today' => $todayPresensi ? [
                'id' => $todayPresensi->id,
                'tanggal' => $todayPresensi->tanggal,
                'jam_datang' => $todayPresensi->jam_datang,
                'jam_pulang' => $todayPresensi->jam_pulang,
                'status_datang' => $todayPresensi->status_datang,
                'status_pulang' => $todayPresensi->status_pulang,
                'menit_terlambat' => $todayPresensi->menit_terlambat,
            ] : null,
            'history' => $history
        ]);
    }

    public function storePresensi(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'tipe' => 'required|in:datang,pulang',
            'foto' => 'required|string' // base64 string
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $config = $this->getConfig();

        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        if (!$activeTa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun ajaran aktif tidak ditemukan.'
            ], 422);
        }

        // GPS Radius Check
        $distance = $this->getDistance($request->lat, $request->lng, $config->latitude, $config->longitude);
        if ($distance > $config->radius) {
            return response()->json([
                'status' => 'error',
                'message' => 'Luar Radius. Jarak Anda: ' . round($distance) . 'm (Batas: ' . $config->radius . 'm).'
            ], 422);
        }

        // Save Base64 Photo
        $image = $request->foto;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'presensi_' . $user->id . '_' . $request->tipe . '_' . time() . '.png';

        if (!\File::exists(public_path('/storage/uploads/'))) {
            \File::makeDirectory(public_path('/storage/uploads/'), 0755, true);
        }
        \File::put(public_path('/storage/uploads/' . $imageName), base64_decode($image));
        $filePath = '/storage/uploads/' . $imageName;

        if ($request->tipe === 'datang') {
            $exists = PresensiGuru::where('user_id', $user->id)
                ->where('tanggal', $today)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah melakukan presensi datang hari ini.'
                ], 422);
            }

            $currentTime = $now->toTimeString();
            $status = $currentTime > $config->jam_masuk ? 'Terlambat' : 'Tepat Waktu';
            
            $menitTerlambat = 0;
            if ($status === 'Terlambat') {
                $scheduledTime = Carbon::parse($today . ' ' . $config->jam_masuk);
                $menitTerlambat = $now->diffInMinutes($scheduledTime);
            }

            PresensiGuru::create([
                'user_id' => $user->id,
                'nama' => $user->name,
                'tanggal' => $today,
                'jam_datang' => $currentTime,
                'foto_datang' => $filePath,
                'lat_datang' => $request->lat,
                'lng_datang' => $request->lng,
                'status_datang' => $status,
                'menit_terlambat' => $menitTerlambat,
                'tahun_ajaran' => $activeTa->tahun_ajaran,
                'semester' => $activeTa->semester
            ]);

            AuditLog::write('Melakukan presensi datang via Aplikasi Mobile');

            return response()->json([
                'status' => 'success',
                'message' => 'Presensi Datang berhasil disimpan. Status: ' . $status
            ]);
        } else {
            // Check-Out
            $presensi = PresensiGuru::where('user_id', $user->id)
                ->where('tanggal', $today)
                ->first();

            if (!$presensi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda harus melakukan presensi datang terlebih dahulu.'
                ], 422);
            }

            if ($presensi->jam_pulang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah melakukan presensi pulang hari ini.'
                ], 422);
            }

            $currentTime = $now->toTimeString();
            $statusPulang = $currentTime < $config->jam_pulang ? 'Pulang Sebelum Waktunya' : 'Selesai';
            
            $menitPulangCepat = 0;
            if ($statusPulang === 'Pulang Sebelum Waktunya') {
                $scheduledPulang = Carbon::parse($today . ' ' . $config->jam_pulang);
                $menitPulangCepat = $now->diffInMinutes($scheduledPulang);
            }

            $presensi->update([
                'jam_pulang' => $currentTime,
                'foto_pulang' => $filePath,
                'lat_pulang' => $request->lat,
                'lng_pulang' => $request->lng,
                'status_pulang' => $statusPulang,
                'menit_pulang_cepat' => $menitPulangCepat
            ]);

            AuditLog::write('Melakukan presensi pulang via Aplikasi Mobile');

            return response()->json([
                'status' => 'success',
                'message' => 'Presensi Pulang berhasil disimpan. Status: ' . $statusPulang
            ]);
        }
    }

    public function storeIzin(Request $request)
    {
        $request->validate([
            'tglmasuk' => 'required|date',
            'sia' => 'required|in:Sakit,Ijin,Alpha,Terlambat',
            'jumlah' => 'required|integer',
            'ket' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240'
        ]);

        $user = $request->user();

        // Mapel default
        $mapel = DB::table('gu_ru')->where('guru', 'LIKE', $user->name)->value('mapel') ?? '-';

        $data = $request->except('attachment');
        $data['user_id'] = $user->id;
        $data['guru'] = $user->name;
        $data['mapel'] = $mapel;
        $data['approval_status'] = 'pending';

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = 'permit_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/ijin_guru'), $fileName);
            $data['attachment'] = 'uploads/ijin_guru/' . $fileName;
        }

        Ijin::create($data);

        AuditLog::write('Mengajukan permohonan izin/sakit via Aplikasi Mobile');

        return response()->json([
            'status' => 'success',
            'message' => 'Permohonan izin berhasil dikirim.'
        ]);
    }

    public function storeIzinSiswa(Request $request)
    {
        $request->validate([
            'ijin' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240'
        ]);

        $user = $request->user();

        // Get student's class
        $siswa = DB::table('siswa')->where('nis', $user->username)->first();
        $kelas = $siswa ? $siswa->kelas : '-';

        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        $taName = $activeTa ? $activeTa->tahun_ajaran : '-';

        $data = [
            'nama' => $user->name,
            'kelas' => $kelas,
            'ketijin' => $request->ijin,
            'oksis' => 'belum',
            'okkur' => 'belum',
            'okbin' => 'belum',
            'okas' => 'belum',
            'tahun_ajaran' => $taName,
            'created_at' => now(),
            'updated_at' => now()
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = 'permit_siswa_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/uploads'), $fileName);
            $data['file_path'] = '/storage/uploads/' . $fileName;
        }

        DB::table('ijinsiswa')->insert($data);

        AuditLog::write('Mengajukan permohonan izin/pesiar siswa via Aplikasi Mobile');

        return response()->json([
            'status' => 'success',
            'message' => 'Izin siswa berhasil dikirim.'
        ]);
    }

    public function getIzinSiswaList(Request $request)
    {
        $user = $request->user();
        $list = DB::table('ijinsiswa')
            ->where('nama', $user->name)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'list' => $list
        ]);
    }

    public function getGarjas(Request $request)
    {
        $user = $request->user();
        $list = DB::table('garjas')
            ->where('nis', $user->username)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'list' => $list
        ]);
    }

    public function submitGarjas(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer',
            'lari' => 'nullable|integer',
            'up' => 'nullable|integer',
            'situp' => 'nullable|integer',
            'pushup' => 'nullable|integer',
            'shuttle' => 'nullable|numeric'
        ]);

        $user = $request->user();
        $siswa = DB::table('siswa')->where('nis', $user->username)->first();
        if (!$siswa) {
            return response()->json(['status' => 'error', 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $data = [
            'nis' => $user->username,
            'nama' => $siswa->nama,
            'kelas' => $siswa->kelas,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'lari' => $request->lari,
            'up' => $request->up,
            'situp' => $request->situp,
            'pushup' => $request->pushup,
            'shuttle' => $request->shuttle,
            'updated_at' => now()
        ];

        $exists = DB::table('garjas')
            ->where('nis', $user->username)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->first();

        if ($exists) {
            DB::table('garjas')
                ->where('id', $exists->id)
                ->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('garjas')->insert($data);
        }

        AuditLog::write('Menyimpan data Garjas via Aplikasi Mobile');

        return response()->json([
            'status' => 'success',
            'message' => 'Data Garjas berhasil disimpan.'
        ]);
    }

    public function getIzinGuruList(Request $request)
    {
        $user = $request->user();
        $list = DB::table('ijin')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'status' => 'success',
            'list' => $list
        ]);
    }
}
