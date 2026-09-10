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

        // Ambil semua tanggal yang memiliki rekaman jurnalh pada bulan tersebut
        $datesWithJurnalh = DB::table('jurnalh')
            ->whereBetween('created_at', [$startOfMonth->toDateString() . ' 00:00:00', $endOfMonth->toDateString() . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as tgl')
            ->distinct()
            ->pluck('tgl')
            ->toArray();

        // Kumpulkan hari efektif sekolah (Senin - Jumat hingga batas evaluasi, atau tanggal di mana ada jurnalh)
        $effectiveDates = [];
        $curr = (clone $startOfMonth);
        $limitObj = Carbon::parse($limitDate);

        if (!$startOfMonth->isFuture()) {
            while ($curr->lte($limitObj)) {
                $dayOfWeek = $curr->dayOfWeekIso; // 1 = Senin, 7 = Minggu
                $dateStr = $curr->toDateString();
                if ($dayOfWeek <= 5 || in_array($dateStr, $datesWithJurnalh)) {
                    $effectiveDates[] = $dateStr;
                }
                $curr->addDay();
            }
        }
        sort($effectiveDates);
        $totalHariEfektif = count($effectiveDates);

        // Ambil daftar kelas
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

        // Ambil data jurnalh pada bulan tersebut
        $jurnalhRows = DB::table('jurnalh')
            ->whereBetween('created_at', [$startOfMonth->toDateString() . ' 00:00:00', $endOfMonth->toDateString() . ' 23:59:59'])
            ->get();

        $jurnalhGrouped = [];
        foreach ($jurnalhRows as $jh) {
            $tgl = Carbon::parse($jh->created_at)->toDateString();
            $jurnalhGrouped[$jh->kelas][$tgl] = $jh;
        }

        // Petakan Wali Kelas
        $waliMap = User::whereNotNull('walikelas_kelas')
            ->pluck('name', 'walikelas_kelas')
            ->toArray();

        // Susun data rekapitulasi berbasis blok Mata Pelajaran (Mapel)
        $rekapPerKelas = [];
        $totalMapelSemuaTerjadwal = 0;
        $totalMapelSemuaTerisi = 0;
        $totalMapelSemuaKosong = 0;

        foreach ($targetClasses as $c) {
            $classTotalMapel = 0;
            $classTerisiMapel = 0;
            $classKosongMapel = 0;
            $rincianHari = [];

            foreach ($effectiveDates as $tgl) {
                $jh = $jurnalhGrouped[$c][$tgl] ?? null;
                $tglCarbon = Carbon::parse($tgl);
                $hariIndonesia = $tglCarbon->isoFormat('dddd, D MMMM Y');

                $mapelBlocks = [];
                if ($jh) {
                    $currentBlock = null;

                    for ($i = 1; $i <= 11; $i++) {
                        $colVal = trim($jh->{'j'.$i} ?? '');
                        if (empty($colVal)) {
                            continue;
                        }

                        $segments = explode('<hr>', $colVal);
                        $guru = trim($segments[0] ?? '');
                        $mapel = trim($segments[1] ?? '');
                        $materi = trim($segments[2] ?? '');

                        $signature = $guru . '||' . $mapel;
                        $isKosong = (stripos($materi, 'Jam Kosong') !== false) && !preg_match('/(Bab|Topik|Tugas|TP|Pelajaran)\s?\d/i', $materi);

                        // Cek apakah masih blok jam yang sama dari mapel & guru tersebut
                        if ($currentBlock && $currentBlock['signature'] === $signature && $currentBlock['is_kosong'] === $isKosong) {
                            $currentBlock['end_jam'] = $i;
                            if (empty($currentBlock['materi']) && !empty($materi)) {
                                $currentBlock['materi'] = $materi;
                            }
                        } else {
                            if ($currentBlock) {
                                $mapelBlocks[] = $currentBlock;
                            }
                            $currentBlock = [
                                'signature' => $signature,
                                'guru'      => $guru,
                                'mapel'     => $mapel,
                                'materi'    => $materi,
                                'is_kosong' => $isKosong,
                                'is_terisi' => !$isKosong,
                                'start_jam' => $i,
                                'end_jam'   => $i,
                            ];
                        }
                    }

                    if ($currentBlock) {
                        $mapelBlocks[] = $currentBlock;
                    }
                }

                $totalMapelHari = count($mapelBlocks);
                $terisiMapelHari = 0;
                $kosongMapelHari = 0;

                foreach ($mapelBlocks as $b) {
                    if ($b['is_terisi']) {
                        $terisiMapelHari++;
                    } else {
                        $kosongMapelHari++;
                    }
                }

                $classTotalMapel += $totalMapelHari;
                $classTerisiMapel += $terisiMapelHari;
                $classKosongMapel += $kosongMapelHari;

                // Tentukan status & keterangan harian yang persis diminta
                if ($totalMapelHari == 0) {
                    $statusHari = 'KOSONG_TOTAL';
                    $badgeStatus = 'Tidak Ada Jadwal / Belum Sinkron';
                    $ringkasanStr = 'Tidak ada jadwal pembelajaran pada tanggal ini';
                } elseif ($kosongMapelHari == 0) {
                    $statusHari = 'LENGKAP';
                    $badgeStatus = 'Lengkap Terisi';
                    $ringkasanStr = "{$terisiMapelHari} Mapel Terisi dari {$totalMapelHari} Mapel";
                } elseif ($terisiMapelHari > 0) {
                    $statusHari = 'SEBAGIAN';
                    $badgeStatus = 'Sebagian Terisi';
                    $ringkasanStr = "{$terisiMapelHari} Mapel Terisi, {$kosongMapelHari} Mapel Kosong dari {$totalMapelHari} Mapel";
                } else {
                    $statusHari = 'KOSONG';
                    $badgeStatus = 'Semua Kosong';
                    $ringkasanStr = "{$kosongMapelHari} Mapel Kosong dari {$totalMapelHari} Mapel";
                }

                $rincianHari[] = [
                    'tanggal'        => $tgl,
                    'tanggal_format' => $hariIndonesia,
                    'status_hari'    => $statusHari,
                    'badge_status'   => $badgeStatus,
                    'total_mapel'    => $totalMapelHari,
                    'terisi_mapel'   => $terisiMapelHari,
                    'kosong_mapel'   => $kosongMapelHari,
                    'ringkasan'      => $ringkasanStr,
                    'blocks'         => $mapelBlocks,
                ];
            }

            // Balik urutan tanggal (tanggal terbaru di atas)
            usort($rincianHari, function($a, $b) {
                return strcmp($b['tanggal'], $a['tanggal']);
            });

            $persentase = ($classTotalMapel > 0) ? round(($classTerisiMapel / $classTotalMapel) * 100, 1) : 0;

            $totalMapelSemuaTerjadwal += $classTotalMapel;
            $totalMapelSemuaTerisi += $classTerisiMapel;
            $totalMapelSemuaKosong += $classKosongMapel;

            $rekapPerKelas[] = [
                'kelas'         => $c,
                'walikelas'     => $waliMap[$c] ?? '-',
                'total_hari'    => $totalHariEfektif,
                'total_mapel'   => $classTotalMapel,
                'terisi_mapel'  => $classTerisiMapel,
                'kosong_mapel'  => $classKosongMapel,
                'persentase'    => $persentase,
                'rincian'       => $rincianHari
            ];
        }

        // Statistik Ringkasan Sekolah
        $jumlahKelas = count($rekapPerKelas);
        $rataRataKepatuhan = ($totalMapelSemuaTerjadwal > 0) ? round(($totalMapelSemuaTerisi / $totalMapelSemuaTerjadwal) * 100, 1) : 0;

        // Daftar kelas untuk dropdown filter
        $allKelasList = Kelas::orderBy('kelas', 'asc')->pluck('kelas')->toArray();

        return view('jurnal.rekap_pengisian', compact(
            'rekapPerKelas',
            'selectedMonth',
            'totalHariEfektif',
            'jumlahKelas',
            'totalMapelSemuaTerjadwal',
            'totalMapelSemuaTerisi',
            'totalMapelSemuaKosong',
            'rataRataKepatuhan',
            'isOnlyWali',
            'managedClass',
            'allKelasList'
        ));
    }
}
