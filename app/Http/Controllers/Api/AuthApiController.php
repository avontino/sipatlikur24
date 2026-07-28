<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserFcmToken;
use App\Helpers\AuditLog;

class AuthApiController extends Controller
{
    public function getSemesters()
    {
        $semesters = \Illuminate\Support\Facades\DB::table('tahun_ajaran')
            ->where('status', 1)
            ->get();
        return response()->json([
            'status' => 'success',
            'semesters' => $semesters
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'tahun_ajaran_id' => 'nullable|integer'
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah.'
            ], 401);
        }

        $user = Auth::user();
        
        // Generate Sanctum token
        $token = $user->createToken('mobile_auth_token')->plainTextToken;

        // Find selected tahun ajaran
        $ta = null;
        if ($request->tahun_ajaran_id) {
            $ta = \Illuminate\Support\Facades\DB::table('tahun_ajaran')->find($request->tahun_ajaran_id);
        }
        if (!$ta) {
            $ta = \Illuminate\Support\Facades\DB::table('tahun_ajaran')->where('status', 1)->first();
        }

        $semesterInfo = $ta ? [
            'id' => $ta->id,
            'tahun_ajaran' => $ta->tahun_ajaran,
            'semester' => $ta->semester,
        ] : null;

        AuditLog::write('Berhasil login via Aplikasi Mobile');

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'needs_password_change' => $user->needs_password_change,
                'walikelas_kelas' => $user->walikelas_kelas,
                'available_roles' => $user->getAvailableRoles(),
                'permissions' => $user->getPermissionsForRole($user->role),
            ],
            'semester' => $semesterInfo
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        
        $activeRole = $request->query('active_role');
        if ($activeRole && in_array($activeRole, $user->getAvailableRoles())) {
            $user->role = $activeRole;
        }

        $stats = [];

        if ($user->role === 'siswa') {
            // Check in absen table for today status
            $hadir = \Illuminate\Support\Facades\DB::table('absen')
                ->where('nama', $user->name)
                ->whereDate('created_at', now())
                ->first();
            $stats['status'] = is_null($hadir) ? 'MASUK' : 'TIDAK MASUK';

            // Tagihan
            $tagihan_komite = \Illuminate\Support\Facades\DB::table('tagihan')
                ->where('nis', $user->username)
                ->value('dana_komite') ?? 0;
            $tagihan_lain = \Illuminate\Support\Facades\DB::table('tagihan')
                ->where('nis', $user->username)
                ->value('tagihan_lain') ?? 0;
            
            $stats['tagihan_komite'] = 'Rp. ' . number_format($tagihan_komite, 0, ',', '.');
            $stats['tagihan_lain'] = 'Rp. ' . number_format($tagihan_lain, 0, ',', '.');

            // Poin Pelanggaran & Prestasi
            $siswa = \App\Models\Siswa::where('nis', $user->username)->first();
            $stats['poin_pelanggaran'] = $siswa ? $siswa->totalPoinPelanggaran() : 0;
            $stats['poin_prestasi'] = $siswa ? $siswa->totalPoinPrestasi() : 0;
            $stats['poin_siswa'] = $stats['poin_prestasi'] - $stats['poin_pelanggaran'];

            // Kuota Ijin Siswa
            $stats['kelas'] = $siswa ? $siswa->kelas : '-';
            $stats['ip'] = $siswa ? $siswa->ip : 0;
            $stats['ib'] = $siswa ? $siswa->ib : 0;
            $stats['ibr'] = $siswa ? $siswa->ibr : 0;
            $stats['ij'] = $siswa ? $siswa->ij : 0;
            $stats['ik'] = $siswa ? $siswa->ik : 0;
        } elseif ($user->role === 'guru') {
            // Siswa counts today
            $stats['total_siswa_sakit'] = \App\Models\Absen::where('ket', 'Sakit')->whereDate('created_at', now())->count();
            $stats['total_siswa_ijin'] = \App\Models\Absen::where('ket', 'Ijin')->whereDate('created_at', now())->count();
            $stats['total_siswa_alpha'] = \App\Models\Absen::where('ket', 'Alpha')->whereDate('created_at', now())->count();
            
            // Total leave days for this teacher
            $stats['total_izin_guru'] = \Illuminate\Support\Facades\DB::table('ijin')
                ->where('guru', $user->name)
                ->sum('jumlah') ?? 0;
        }

        $todaySchedules = [];
        if ($user->role === 'guru') {
            $activeTa = \Illuminate\Support\Facades\DB::table('tahun_ajaran')->where('status', 1)->first();
            if (!$activeTa) {
                $activeTa = (object)[
                    'tahun_ajaran' => '2025/2026',
                    'semester' => 'Genap',
                ];
            }

            $cleanName = trim(explode(',', $user->name)[0]);
            $firstTwoWords = implode(' ', array_slice(explode(' ', $cleanName), 0, 2));

            $hariIni = now()->format('l');
            $schedules = \Illuminate\Support\Facades\DB::table('jadwal')
                ->where(function($q) use ($user, $firstTwoWords) {
                    $q->where('guru', 'LIKE', '%' . $user->name . '%')
                      ->orWhere('guru', 'LIKE', '%' . $firstTwoWords . '%');
                })
                ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                ->where('semester', $activeTa->semester)
                ->where('hari', $hariIni)
                ->orderBy('jamke')
                ->get();

            foreach ($schedules as $s) {
                // Cari record di jurnal hari ini
                $jurnal = \Illuminate\Support\Facades\DB::table('jurnal')
                    ->where('kelas', $s->kelas)
                    ->where('mapel', $s->mapel)
                    ->where('jamke', $s->jamke)
                    ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                    ->where('semester', $activeTa->semester)
                    ->whereDate('created_at', now())
                    ->where(function($q) use ($user, $firstTwoWords) {
                        $q->where('guru_id', $user->id)
                          ->orWhere('guru', 'LIKE', '%' . $user->name . '%')
                          ->orWhere('guru', 'LIKE', '%' . $firstTwoWords . '%');
                    })
                    ->first();

                // Status diisi: jika ada record di tabel jurnal AND materi bukanlah "Jam Kosong"
                $isFilled = $jurnal && trim($jurnal->materi) !== 'Jam Kosong';

                // Status sinkron admin: cek keberadaan jurnalh untuk kelas ini hari ini
                $isSynced = \Illuminate\Support\Facades\DB::table('jurnalh')
                    ->where('kelas', $s->kelas)
                    ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                    ->where('semester', $activeTa->semester)
                    ->whereDate('created_at', now())
                    ->exists();

                $todaySchedules[] = [
                    'kelas' => $s->kelas,
                    'mapel' => $s->mapel,
                    'jamke' => $s->jamke,
                    'materi' => $jurnal ? $jurnal->materi : 'Jam Kosong',
                    'is_filled' => $isFilled,
                    'is_synced' => $isSynced,
                ];
            }
        }

        $presensiStatus = 'Belum Presensi';
        if ($user->role === 'guru') {
            $today = \Carbon\Carbon::today()->toDateString();
            $todayPresensi = \App\Models\PresensiGuru::where('user_id', $user->id)
                ->where('tanggal', $today)
                ->first();
            if ($todayPresensi) {
                if ($todayPresensi->jam_pulang) {
                    $presensiStatus = 'Sudah Presensi Pulang (' . $todayPresensi->jam_pulang . ')';
                } elseif ($todayPresensi->jam_datang) {
                    $presensiStatus = 'Sudah Presensi Datang (' . $todayPresensi->jam_datang . ')';
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'walikelas_kelas' => $user->walikelas_kelas,
                'available_roles' => $user->getAvailableRoles(),
                'permissions' => $user->getPermissionsForRole($user->role),
            ],
            'stats' => (object) $stats,
            'today_schedules' => $todaySchedules,
            'presensi_status' => $presensiStatus
        ]);
    }

    public function logout(Request $request)
    {
        AuditLog::write('Melakukan logout dari Aplikasi Mobile');
        
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout.'
        ]);
    }

    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'nullable|string',
        ]);

        $user = $request->user();

        // Update or insert the device token mapping
        UserFcmToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'fcm_token' => $request->fcm_token,
            ],
            [
                'device_type' => $request->device_type,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'FCM Token berhasil disimpan.'
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $user = $request->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password lama tidak cocok.'
            ], 400);
        }

        \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password),
            'needs_password_change' => false,
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah.'
        ]);
    }
}
