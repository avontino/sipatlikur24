<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Jadwal;
use App\Models\Kelas;

class JadwalApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        if (!$activeTa) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tahun ajaran aktif tidak ditemukan.'
            ], 422);
        }

        $query = Jadwal::where('tahun_ajaran', $activeTa->tahun_ajaran)
            ->where('semester', $activeTa->semester);

        // Filter per role
        if ($user->role === 'guru') {
            $query->where('guru', 'LIKE', '%' . $user->name . '%');
        } elseif ($user->role === 'siswa') {
            // Cari kelas siswa
            $siswa = DB::table('siswa')
                ->where('nama', $user->name)
                ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                ->first();
            if ($siswa) {
                $query->where('kelas', $siswa->kelas);
            }
        }

        // Filter kelas (untuk admin/kurikulum)
        if ($request->filled('kelas') && in_array($user->role, ['admin', 'kurikulum', 'lihat'])) {
            $query->where('kelas', $request->kelas);
        }

        $schedules = $query->orderByRaw("FIELD(hari, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')")
            ->orderBy('jamke', 'asc')
            ->get();

        // Kelompokkan per hari
        $grouped = $schedules->groupBy('hari');

        // Data kelas untuk filter (admin)
        $kelasList = [];
        if (in_array($user->role, ['admin', 'kurikulum', 'lihat'])) {
            $kelasList = Kelas::orderBy('kelas')->pluck('kelas');
        }

        return response()->json([
            'status'    => 'success',
            'tahun_ajaran' => $activeTa->tahun_ajaran,
            'semester'  => $activeTa->semester,
            'kelas_list'=> $kelasList,
            'jadwal'    => $grouped,
        ]);
    }
}
