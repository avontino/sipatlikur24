<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;

class KasusApiController extends Controller
{
    /**
     * GET /api/poin-siswa
     * Daftar siswa beserta total poin pelanggaran, prestasi, dan level SP
     */
    public function poinSiswa(Request $request)
    {
        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        $tahunAjaran = $activeTa ? $activeTa->tahun_ajaran : null;

        $query = Siswa::where('tahun_ajaran', $tahunAjaran)->orderBy('nama', 'asc');

        // Filter kelas opsional
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        // Filter pencarian nama
        if ($request->filled('search')) {
            $query->where('nama', 'LIKE', '%' . $request->search . '%');
        }

        $siswaData = $query->get()->map(function ($siswa) {
            // Hitung total poin pelanggaran
            $totalPelanggaran = DB::table('poin_siswa')
                ->join('kategori_poin', 'poin_siswa.kategori_poin_id', '=', 'kategori_poin.id')
                ->where('poin_siswa.siswa_id', $siswa->id)
                ->where('kategori_poin.jenis', 'pelanggaran')
                ->sum('kategori_poin.poin');

            // Hitung total poin prestasi
            $totalPrestasi = DB::table('poin_siswa')
                ->join('kategori_poin', 'poin_siswa.kategori_poin_id', '=', 'kategori_poin.id')
                ->where('poin_siswa.siswa_id', $siswa->id)
                ->where('kategori_poin.jenis', 'prestasi')
                ->sum('kategori_poin.poin');

            // Tentukan level SP
            $spLevel = null;
            if ($totalPelanggaran >= 200) {
                $spLevel = 'DO';
            } elseif ($totalPelanggaran >= 150) {
                $spLevel = 'SP 3';
            } elseif ($totalPelanggaran >= 100) {
                $spLevel = 'SP 2';
            } elseif ($totalPelanggaran >= 50) {
                $spLevel = 'SP 1';
            }

            // Ambil riwayat kasus terakhir
            $kasusCount = DB::table('poin_siswa')->where('siswa_id', $siswa->id)->count();

            return [
                'id'                => $siswa->id,
                'nama'              => $siswa->nama,
                'kelas'             => $siswa->kelas,
                'nis'               => $siswa->nis ?? '-',
                'total_pelanggaran' => (int) $totalPelanggaran,
                'total_prestasi'    => (int) $totalPrestasi,
                'sp_level'          => $spLevel,
                'jumlah_kasus'      => (int) $kasusCount,
            ];
        });

        // Ambil daftar kelas untuk filter
        $kelasList = Siswa::where('tahun_ajaran', $tahunAjaran)
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        return response()->json([
            'status'     => 'success',
            'kelas_list' => $kelasList,
            'data'       => $siswaData,
        ]);
    }
}
