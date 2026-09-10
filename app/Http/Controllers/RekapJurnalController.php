<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\User;

class RekapJurnalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isWali = ($user->hasRole('walikelas') || $user->walikelas_kelas || str_contains((string)$user->additional_roles, 'walikelas'));
        $isAdmin = $user->hasRole('admin') || $user->role === 'admin';
        $isKurikulum = $user->hasRole('kurikulum');
        $isKepala = $user->hasRole('kepala') || $user->hasRole('lihat');
        $isKesiswaan = $user->hasRole('kesiswaan');

        // Batasi hak akses: hanya Admin, Kurikulum, Kepala Sekolah, Kesiswaan, dan Wali Kelas
        if (!$isAdmin && !$isKurikulum && !$isKepala && !$isKesiswaan && !$isWali) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Rekap Pengisian Jurnal.');
        }

        // Cek jika user adalah Wali Kelas murni
        $isOnlyWali = $isWali && !$isAdmin && !$isKurikulum && !$isKepala && !$isKesiswaan;
        $managedClass = $user->getManagedClass() ?: ($user->walikelas_kelas ?: $user->name);

        // Filter Bulan (Default: bulan berjalan format YYYY-MM)
        $selectedMonth = $request->input('bulan', now()->format('Y-m'));
        try {
            $monthDate = Carbon::parse($selectedMonth . '-01');
        } catch (\Exception $e) {
            $selectedMonth = now()->format('Y-m');
            $monthDate = Carbon::parse($selectedMonth . '-01');
        }

        $startOfMonth = (clone $monthDate)->startOfMonth();
        $endOfMonth = (clone $monthDate)->endOfMonth();
        $today = now();

        // Batas tanggal evaluasi (jangan penalti hari di masa depan pada bulan berjalan)
        $limitDate = $startOfMonth->isCurrentMonth() ? min($today->toDateString(), $endOfMonth->toDateString()) : $endOfMonth->toDateString();
        if ($startOfMonth->isFuture()) {
            $limitDate = $startOfMonth->toDateString();
        }

        // Tanggal-tanggal di mana ada jurnal terisi di bulan tersebut
        $datesWithJurnal = DB::table('jurnal')
            ->whereBetween('created_at', [$startOfMonth->toDateString() . ' 00:00:00', $endOfMonth->toDateString() . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as tgl')
            ->distinct()
            ->pluck('tgl')
            ->toArray();

        $datesWithJurnalh = DB::table('jurnalh')
            ->whereBetween('created_at', [$startOfMonth->toDateString() . ' 00:00:00', $endOfMonth->toDateString() . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as tgl')
            ->distinct()
            ->pluck('tgl')
            ->toArray();

        $allRecordedDates = array_unique(array_merge($datesWithJurnal, $datesWithJurnalh));

        // Kumpulkan hari efektif sekolah (Senin - Jumat hingga hari ini/akhir bulan, atau tanggal di mana ada jurnal terisi)
        $effectiveDates = [];
        $curr = (clone $startOfMonth);
        $limitObj = Carbon::parse($limitDate);

        if (!$startOfMonth->isFuture()) {
            while ($curr->lte($limitObj)) {
                $dayOfWeek = $curr->dayOfWeekIso; // 1 = Senin, 7 = Minggu
                $dateStr = $curr->toDateString();
                if ($dayOfWeek <= 5 || in_array($dateStr, $allRecordedDates)) {
                    $effectiveDates[] = $dateStr;
                }
                $curr->addDay();
            }
        }
        sort($effectiveDates);
        $totalHariEfektif = count($effectiveDates);

        // Ambil kelas yang akan ditampilkan
        $classesQuery = Kelas::query();
        if ($isOnlyWali && $managedClass) {
            $classesQuery->where('kelas', $managedClass);
        } else {
            if ($request->filled('tingkat') && in_array($request->tingkat, ['7', '8', '9'])) {
                $classesQuery->where('kelas', 'LIKE', $request->tingkat . '%');
            }
            if ($request->filled('kelas') && $request->kelas !== 'all') {
                $classesQuery->where('kelas', $request->kelas);
            }
        }
        $targetClasses = $classesQuery->orderBy('kelas', 'asc')->pluck('kelas')->toArray();

        // Ambil data jurnal harian (jurnalh) pada bulan tersebut
        $jurnalhRows = DB::table('jurnalh')
            ->whereBetween('created_at', [$startOfMonth->toDateString() . ' 00:00:00', $endOfMonth->toDateString() . ' 23:59:59'])
            ->get();

        $jurnalhGrouped = [];
        foreach ($jurnalhRows as $jh) {
            $tgl = Carbon::parse($jh->created_at)->toDateString();
            $jurnalhGrouped[$jh->kelas][$tgl] = $jh;
        }

        // Ambil data detail mapel/guru (jurnal) pada bulan tersebut
        $jurnalDetailRows = DB::table('jurnal')
            ->whereBetween('created_at', [$startOfMonth->toDateString() . ' 00:00:00', $endOfMonth->toDateString() . ' 23:59:59'])
            ->orderBy('jamke', 'asc')
            ->get();

        $jurnalDetailGrouped = [];
        foreach ($jurnalDetailRows as $jd) {
            $tgl = Carbon::parse($jd->created_at)->toDateString();
            $jurnalDetailGrouped[$jd->kelas][$tgl][] = $jd;
        }

        // Peta Wali Kelas
        $waliMap = User::whereNotNull('walikelas_kelas')
            ->pluck('name', 'walikelas_kelas')
            ->toArray();

        // Susun data rekapitulasi pengisian jurnal per kelas
        $rekapPerKelas = [];
        $totalSudahSemua = 0;
        $totalTidakSemua = 0;

        foreach ($targetClasses as $c) {
            $sudahCount = 0;
            $rincianHari = [];

            foreach ($effectiveDates as $tgl) {
                $jh = $jurnalhGrouped[$c][$tgl] ?? null;
                $details = $jurnalDetailGrouped[$c][$tgl] ?? [];
                $hasJurnal = ($jh !== null) || (count($details) > 0);

                $tglCarbon = Carbon::parse($tgl);
                $hariIndonesia = $tglCarbon->isoFormat('dddd, D MMMM Y');

                if ($hasJurnal) {
                    $sudahCount++;
                    $jumlahJam = count($details);
                    $mapelList = [];
                    foreach ($details as $d) {
                        $mapelList[] = "Jam " . ($d->jamke ?: '?') . ": " . ($d->mapel ?: 'Mapel') . " (" . ($d->guru ?: 'Guru') . ")";
                    }
                    $mapelStr = count($mapelList) > 0 ? implode('; ', $mapelList) : "Jurnal Harian Terisi";

                    $rincianHari[] = [
                        'tanggal'       => $tgl,
                        'tanggal_format'=> $hariIndonesia,
                        'status'        => 'SUDAH',
                        'jumlah_jam'    => $jumlahJam > 0 ? "{$jumlahJam} Mapel/Jam Terisi" : "Jurnal Terisi",
                        'detail_mapel'  => $mapelStr,
                        'raw_details'   => $details
                    ];
                } else {
                    $rincianHari[] = [
                        'tanggal'       => $tgl,
                        'tanggal_format'=> $hariIndonesia,
                        'status'        => 'TIDAK',
                        'jumlah_jam'    => 'Belum Ada Jam Terisi',
                        'detail_mapel'  => 'Jurnal belum diisi pada tanggal ini',
                        'raw_details'   => []
                    ];
                }
            }

            // Balik urutan tanggal (tanggal terbaru di atas)
            usort($rincianHari, function($a, $b) {
                return strcmp($b['tanggal'], $a['tanggal']);
            });

            $tidakCount = max(0, $totalHariEfektif - $sudahCount);
            $persentase = ($totalHariEfektif > 0) ? round(($sudahCount / $totalHariEfektif) * 100) : 0;

            $totalSudahSemua += $sudahCount;
            $totalTidakSemua += $tidakCount;

            $rekapPerKelas[] = [
                'kelas'             => $c,
                'walikelas'         => $waliMap[$c] ?? '-',
                'total_hari'        => $totalHariEfektif,
                'sudah_mengisi'     => $sudahCount,
                'tidak_mengisi'     => $tidakCount,
                'persentase'        => $persentase,
                'rincian'           => $rincianHari
            ];
        }

        // Hitung statistik ringkasan
        $jumlahKelas = count($rekapPerKelas);
        $totalSlotEvaluasi = $jumlahKelas * $totalHariEfektif;
        $rataRataKepatuhan = ($totalSlotEvaluasi > 0) ? round(($totalSudahSemua / $totalSlotEvaluasi) * 100, 1) : 0;

        // Daftar kelas untuk dropdown filter
        $allKelasList = Kelas::orderBy('kelas', 'asc')->pluck('kelas')->toArray();

        return view('jurnal.rekap_pengisian', compact(
            'rekapPerKelas',
            'selectedMonth',
            'totalHariEfektif',
            'jumlahKelas',
            'totalSudahSemua',
            'totalTidakSemua',
            'rataRataKepatuhan',
            'isOnlyWali',
            'managedClass',
            'allKelasList'
        ));
    }
}
