<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\User;

class VerifikasiAbsensiController extends Controller
{
    public function rekap(Request $request)
    {
        $user = auth()->user();
        $isWali = ($user->hasRole('walikelas') || $user->walikelas_kelas || str_contains((string)$user->additional_roles, 'walikelas'));
        $isAdmin = $user->hasRole('admin') || $user->role === 'admin';
        $isKurikulum = $user->hasRole('kurikulum');
        $isKepala = $user->hasRole('kepala') || $user->hasRole('lihat');
        $isKesiswaan = $user->hasRole('kesiswaan');

        // Batasi akses: hanya Admin, Kurikulum, Kepala Sekolah, Kesiswaan, dan Wali Kelas
        if (!$isAdmin && !$isKurikulum && !$isKepala && !$isKesiswaan && !$isWali) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Rekap Verifikasi Absensi Pagi.');
        }

        // Cek apakah user adalah Wali Kelas murni (bukan admin/kurikulum/kepala/kesiswaan)
        $isOnlyWali = $isWali && !$isAdmin && !$isKurikulum && !$isKepala && !$isKesiswaan;
        $managedClass = $user->getManagedClass() ?: ($user->walikelas_kelas ?: $user->name);

        // Filter Bulan (Default bulan berjalan: YYYY-MM)
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

        // Tentukan batas akhir evaluasi hari (jangan evaluasi hari di masa depan pada bulan berjalan)
        $limitDate = $startOfMonth->isCurrentMonth() ? min($today->toDateString(), $endOfMonth->toDateString()) : $endOfMonth->toDateString();
        if ($startOfMonth->isFuture()) {
            $limitDate = $startOfMonth->toDateString();
        }

        // Ambil semua tanggal verifikasi aktual yang ada di bulan tersebut
        $datesWithVerif = DB::table('verifikasi_absensi')
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->distinct()
            ->pluck('tanggal')
            ->toArray();

        // Kumpulkan hari-hari efektif sekolah (Senin - Jumat hingga hari ini / akhir bulan, atau tanggal di mana ada verifikasi)
        $effectiveDates = [];
        $curr = (clone $startOfMonth);
        $limitObj = Carbon::parse($limitDate);

        if (!$startOfMonth->isFuture()) {
            while ($curr->lte($limitObj)) {
                $dayOfWeek = $curr->dayOfWeekIso; // 1 = Senin, 7 = Minggu
                $dateStr = $curr->toDateString();
                // Senin s/d Jumat (1-5) atau jika ada verifikasi tercatat (misal Sabtu kegiatan)
                if ($dayOfWeek <= 5 || in_array($dateStr, $datesWithVerif)) {
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

        // Ambil semua record verifikasi pada bulan tersebut
        $verifData = DB::table('verifikasi_absensi')
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        // Ambil nama-nama user verifikator
        $userIds = $verifData->pluck('verified_by')->unique()->filter()->toArray();
        $userMap = User::whereIn('id', $userIds)->pluck('name', 'id')->toArray();

        // Grouping data verifikasi per kelas & tanggal
        $verifGrouped = [];
        foreach ($verifData as $row) {
            $verifGrouped[$row->kelas][$row->tanggal] = $row;
        }

        // Petakan Wali Kelas untuk setiap kelas (khusus Guru / Wali Kelas, kecualikan Ketua Kelas & Siswa)
        $waliMap = User::whereNotNull('walikelas_kelas')
            ->whereNotIn('role', ['ketuakelas', 'siswa'])
            ->where(function ($q) {
                $q->whereNull('additional_roles')
                  ->orWhere(function ($q2) {
                      $q2->where('additional_roles', 'NOT LIKE', '%ketuakelas%')
                         ->where('additional_roles', 'NOT LIKE', '%siswa%');
                  });
            })
            ->pluck('name', 'walikelas_kelas')
            ->toArray();

        // Fallback mapping wali kelas jika ada kelas yang belum terisi di DB
        $walisDictionary = [
            '7A' => ['Dwi Rahmawati'],
            '7B' => ['Elsye', 'Sandra'],
            '7C' => ['Widyatama'],
            '7D' => ['Erri Endah', 'Listiani'],
            '7E' => ['Dyah Amelia'],
            '7F' => ['Fernanda'],
            '7G' => ['Sriatin'],
            '8A' => ['Siti Rohmawati'],
            '8B' => ['Made Argita', 'Argita'],
            '8C' => ['Lina Setyaningrum'],
            '8D' => ['Titik Dewi'],
            '8E' => ['Ainur Romlah'],
            '8F' => ['Noveriana'],
            '8G' => ['Umi Farah'],
            '9A' => ['Maria Ignatia'],
            '9B' => ['Ida Fitriyah'],
            '9C' => ['Wega'],
            '9D' => ['Sri Hartati'],
            '9E' => ['Endah Suci'],
            '9F' => ['Muflihatul', 'Habibah', "A'im"],
            '9G' => ['Vita Arwidiah', 'Vita'],
        ];

        foreach ($walisDictionary as $kls => $keywords) {
            if (empty($waliMap[$kls])) {
                $foundTeacher = User::whereNotIn('role', ['ketuakelas', 'siswa'])
                    ->where(function ($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->orWhere('name', 'LIKE', "%{$kw}%");
                        }
                    })
                    ->value('name');
                if ($foundTeacher) {
                    $waliMap[$kls] = $foundTeacher;
                }
            }
        }

        // Susun data rekap per kelas
        $rekapPerKelas = [];
        $totalSudahSemua = 0;
        $totalTidakSemua = 0;

        foreach ($targetClasses as $c) {
            $sudahCount = 0;
            $rincianHari = [];

            foreach ($effectiveDates as $tgl) {
                $v = $verifGrouped[$c][$tgl] ?? null;
                $tglCarbon = Carbon::parse($tgl);
                $hariIndonesia = $tglCarbon->isoFormat('dddd, D MMMM Y');

                if ($v) {
                    $sudahCount++;
                    $rincianHari[] = [
                        'tanggal'       => $tgl,
                        'tanggal_format'=> $hariIndonesia,
                        'status_verif'  => 'SUDAH',
                        'jam'           => $v->updated_at ? Carbon::parse($v->updated_at)->format('H:i') : '-',
                        'verified_by'   => $userMap[$v->verified_by] ?? 'Sistem',
                        'status_absen'  => $v->status,
                        'hadir'         => $v->hadir,
                        'total'         => $v->total,
                        'detail'        => ($v->status == 'NIHIL') ? "NIHIL (Hadir Semua)" : "{$v->sakit} Sakit, {$v->izin} Izin, {$v->alpha} Alpha, {$v->dispen} Dispen"
                    ];
                } else {
                    $rincianHari[] = [
                        'tanggal'       => $tgl,
                        'tanggal_format'=> $hariIndonesia,
                        'status_verif'  => 'TIDAK',
                        'jam'           => '-',
                        'verified_by'   => '-',
                        'status_absen'  => 'Belum Verifikasi',
                        'hadir'         => '-',
                        'total'         => '-',
                        'detail'        => 'Tidak melakukan verifikasi absensi'
                    ];
                }
            }

            // Balik urutan rincian tanggal (tanggal terbaru di atas)
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
                'sudah_verifikasi'  => $sudahCount,
                'tidak_verifikasi'  => $tidakCount,
                'persentase'        => $persentase,
                'rincian'           => $rincianHari
            ];
        }

        // Hitung statistik ringkasan
        $jumlahKelas = count($rekapPerKelas);
        $totalSlotEvaluasi = $jumlahKelas * $totalHariEfektif;
        $rataRataKepatuhan = ($totalSlotEvaluasi > 0) ? round(($totalSudahSemua / $totalSlotEvaluasi) * 100, 1) : 0;

        // Daftar semua kelas untuk pilihan dropdown filter
        $allKelasList = Kelas::orderBy('kelas', 'asc')->pluck('kelas')->toArray();

        return view('verifikasi.rekap', compact(
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
