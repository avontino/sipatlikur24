<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ijinsiswa;
use App\Helpers\AuditLog;

class IjinSiswaApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        $tahunAjaran = $activeTa ? $activeTa->tahun_ajaran : null;

        $query = Ijinsiswa::query()->where('tahun_ajaran', $tahunAjaran)->orderBy('created_at', 'desc');

        // Filter berdasarkan role
        $role = $user->role;
        if ($user->hasRole('walikelas') || $user->walikelas_kelas) {
            $role = 'walikelas';
        }

        if ($role === 'siswa') {
            $query->where('nama', $user->name);
        } elseif ($role === 'walikelas') {
            $kelas = $user->walikelas_kelas ?: $user->name;
            $query->where('kelas', $kelas);
        }
        // admin, kurikulum, pembina, kesehatan → lihat semua

        // Filter opsional dari query param
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }
        if ($request->filled('status')) {
            // status: menunggu, disetujui, dll
            $query->where('status', $request->status);
        }

        $data = $query->get()->map(function ($item) use ($user) {
            $effectiveRole = $user->role;
            if ($user->hasRole('walikelas') || $user->walikelas_kelas) {
                $effectiveRole = 'walikelas';
            }

            // Tentukan apakah user ini bisa verifikasi record ini
            $canVerify = $this->canVerify($item, $effectiveRole);

            $arr = $item->toArray();
            // Backward compatibility mapping for mobile app client
            $arr['oksis'] = $item->ok_pembina;
            $arr['okkur'] = $item->ok_kurikulum;
            $arr['okbin'] = $item->ok_walikelas;
            $arr['okas'] = $item->ok_kesehatan;

            return array_merge($arr, [
                'can_verify' => $canVerify,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Tentukan apakah role tertentu bisa memverifikasi ijin ini
     */
    private function canVerify($ijin, string $role): bool
    {
        $jenis = $ijin->ketijin;

        // Mapping: jenis ijin → role yang bisa verifikasi & field yang diisi
        $matrix = [
            'Ijin Pesiar'         => ['pembina', 'kesehatan'],
            'Ijin Bermalam Wajib' => ['walikelas', 'kurikulum', 'pembina'],
            'Ijin Bermalam'       => ['walikelas', 'kurikulum', 'pembina'],
            'Ijin Bermalam Resmi' => ['walikelas', 'kurikulum', 'pembina'],
            'Sakit'               => ['walikelas', 'kesehatan'],
            'Ijin'                => ['walikelas'],
        ];

        $allowedRoles = $matrix[$jenis] ?? [];
        return in_array($role, $allowedRoles);
    }

    public function verifikasi(Request $request, $id)
    {
        $user = $request->user();
        $ijinsiswa = Ijinsiswa::findOrFail($id);

        $role = $request->input('active_role', $request->query('active_role', $user->role));
        if ($role === 'guru' && $user->walikelas_kelas) {
            $role = 'walikelas';
        }

        // Jalankan logika approval sesuai role & jenis ijin
        $updated = false;

        switch ($ijinsiswa->ketijin) {
            case 'Ijin Pesiar':
                if ($role === 'pembina') {
                    $ijinsiswa->update(['ok_pembina' => 'ok', 'ok_kurikulum' => 'ok']);
                    $updated = true;
                } elseif ($role === 'kesehatan') {
                    $ijinsiswa->update(['ok_walikelas' => 'ok', 'ok_kesehatan' => 'ok']);
                    $updated = true;
                }
                break;

            case 'Ijin Bermalam Wajib':
            case 'Ijin Bermalam':
            case 'Ijin Bermalam Resmi':
                if ($role === 'walikelas') {
                    $ijinsiswa->update(['ok_walikelas' => 'ok', 'ok_kesehatan' => 'ok']);
                    $updated = true;
                } elseif ($role === 'kurikulum') {
                    $ijinsiswa->update(['ok_kurikulum' => 'ok']);
                    $updated = true;
                } elseif ($role === 'pembina') {
                    $ijinsiswa->update(['ok_pembina' => 'ok']);
                    $updated = true;
                }
                break;

            case 'Sakit':
                if ($role === 'walikelas') {
                    $ijinsiswa->update(['ok_walikelas' => 'ok', 'ok_kesehatan' => 'ok']);
                    $updated = true;
                } elseif ($role === 'kesehatan') {
                    $ijinsiswa->update(['ok_kurikulum' => 'ok', 'ok_pembina' => 'ok']);
                    $updated = true;
                }
                break;

            case 'Ijin':
                if ($role === 'walikelas') {
                    $ijinsiswa->update(['ok_walikelas' => 'ok', 'ok_kesehatan' => 'ok', 'ok_kurikulum' => 'ok', 'ok_pembina' => 'ok']);
                    $updated = true;
                }
                break;
        }

        if (!$updated) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Peran Anda tidak berwenang memverifikasi jenis ijin ini.'
            ], 403);
        }

        AuditLog::write('Verifikasi ijin siswa: ' . $ijinsiswa->nama . ' (' . $ijinsiswa->ketijin . ')');

        return response()->json([
            'status'  => 'success',
            'message' => 'Verifikasi berhasil.',
        ]);
    }

    public function tolak(Request $request, $id)
    {
        $ijinsiswa = Ijinsiswa::findOrFail($id);
        $ijinsiswa->update(['status' => 'ditolak', 'keterangan_tolak' => $request->alasan]);

        AuditLog::write('Menolak ijin siswa: ' . $ijinsiswa->nama . ' (' . $ijinsiswa->ketijin . ')');

        return response()->json([
            'status'  => 'success',
            'message' => 'Ijin berhasil ditolak.',
        ]);
    }
}
