<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Jurnal;
use App\Models\Jurnalh;
use App\Models\Siswa;
use App\Models\Jadwal;
use Carbon\Carbon;
use App\Helpers\AuditLog;

class JurnalApiController extends Controller
{
    private function getHariIni()
    {
        $dayOfWeek = Carbon::now()->dayOfWeek;
        $hariEng = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ];
        return $hariEng[$dayOfWeek] ?? 'Monday';
    }

    public function getJadwalToday(Request $request)
    {
        $user = $request->user();
        $hariIni = $this->getHariIni();

        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        if (!$activeTa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun ajaran aktif tidak ditemukan.'
            ], 422);
        }

        $query = Jadwal::where('tahun_ajaran', $activeTa->tahun_ajaran)
            ->where('semester', $activeTa->semester)
            ->where('hari', $hariIni);

        if ($user->role === 'guru') {
            $query->where('guru', 'LIKE', '%' . $user->name . '%');
        } elseif ($user->role === 'siswa') {
            $query->where('kelas', $user->name);
        }

        $schedules = $query->orderBy('jamke', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'hari' => $hariIni,
            'schedules' => $schedules
        ]);
    }

    public function getJournalWarnings(Request $request)
    {
        $user = $request->user();
        $todayStr = Carbon::today()->toDateString();
        $hariIni = $this->getHariIni();

        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        if (!$activeTa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun ajaran aktif tidak ditemukan.'
            ], 422);
        }

        $warnings = [];
        $role = $request->query('active_role', $user->role);

        if ($role === 'admin' || $role === 'kurikulum') {
            // All classes missing journals today
            $allClasses = DB::table('ke_las')->pluck('kelas')->toArray();
            $missing = [];
            foreach ($allClasses as $kls) {
                $hasJournal = DB::table('jurnal')
                    ->where('kelas', $kls)
                    ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                    ->where('semester', $activeTa->semester)
                    ->whereDate('created_at', $todayStr)
                    ->exists();
                if (!$hasJournal) {
                    $missing[] = $kls;
                }
            }
            $warnings = $missing;
        } elseif ($role === 'siswa' || $role === 'ketuakelas') {
            // Check if class has filled journal today
            $kelasName = $user->name;
            $hasJournal = DB::table('jurnal')
                ->where('kelas', $kelasName)
                ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                ->where('semester', $activeTa->semester)
                ->whereDate('created_at', $todayStr)
                ->exists();
            if (!$hasJournal) {
                $warnings[] = "Jurnal kelas " . $kelasName . " belum terisi hari ini.";
            }
        } elseif ($role === 'walikelas') {
            // Check if perwalian class has filled journal today
            $kelasName = $user->walikelas_kelas;
            if (!empty($kelasName)) {
                $hasJournal = DB::table('jurnal')
                    ->where('kelas', $kelasName)
                    ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                    ->where('semester', $activeTa->semester)
                    ->whereDate('created_at', $todayStr)
                    ->exists();
                if (!$hasJournal) {
                    $warnings[] = "Jurnal kelas " . $kelasName . " belum terisi hari ini.";
                }
            }
        } elseif ($role === 'guru') {
            // Check if teacher has filled their schedule journals today
            $cleanName = trim(explode(',', $user->name)[0]);
            $firstTwoWords = implode(' ', array_slice(explode(' ', $cleanName), 0, 2));

            $mySchedules = Jadwal::where(function($q) use ($user, $firstTwoWords) {
                    $q->where('guru', 'LIKE', '%' . $user->name . '%')
                      ->orWhere('guru', 'LIKE', '%' . $firstTwoWords . '%');
                })
                ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                ->where('semester', $activeTa->semester)
                ->where('hari', $hariIni)
                ->get();

            foreach ($mySchedules as $sch) {
                $filled = Jurnal::where('kelas', $sch->kelas)
                    ->where('mapel', $sch->mapel)
                    ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                    ->where('semester', $activeTa->semester)
                    ->whereDate('created_at', $todayStr)
                    ->where(function($q) use ($user, $firstTwoWords) {
                        $q->where('guru_id', $user->id)
                          ->orWhere('guru', 'LIKE', '%' . $user->name . '%')
                          ->orWhere('guru', 'LIKE', '%' . $firstTwoWords . '%');
                    })
                    ->exists();
                if (!$filled) {
                    $warnings[] = $sch->kelas . " (" . $sch->mapel . ")";
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'warnings' => array_unique($warnings)
        ]);
    }

    public function storeJurnal(Request $request)
    {
        $request->validate([
            'kelas' => 'required|string',
            'mapel' => 'required|string',
            'guru' => 'required|string',
            'materi' => 'required|string',
            'catatan' => 'nullable|string',
            'jamke' => 'required|string', // e.g. "1-3" or "2"
            'jumlahjam' => 'required|integer',
            'penugasan' => 'required|in:Ada,Tidak',
            'ket_guru_mapel' => 'required|in:Hadir,Tidak Masuk'
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        if (!$activeTa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun ajaran aktif tidak ditemukan.'
            ], 422);
        }

        // 1. Resolve guru user
        $guruUser = DB::table('users')->where('name', 'LIKE', '%' . $request->guru . '%')->first();
        $guruId = $guruUser ? $guruUser->id : $user->id;

        // 2. Prepare Jurnalh Column updates
        $formattedMateri = strip_tags($request->materi);
        if ($request->penugasan == 'Ada') {
            $jurnalValue = $request->guru . "<hr>" . $request->mapel . "<hr>" . $formattedMateri . "<hr>" . "<span class='badge badge-danger'>KBM Tanpa Guru</span>";
        } else {
            $jurnalValue = $request->guru . "<hr>" . $request->mapel . "<hr>" . $formattedMateri;
        }

        // 3. Find or Create Jurnalh (daily class journal log)
        $jurnalh = Jurnalh::where('kelas', $request->kelas)
            ->where('tahun_ajaran', $activeTa->tahun_ajaran)
            ->where('semester', $activeTa->semester)
            ->whereDate('created_at', $today)
            ->first();

        if (!$jurnalh) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan jurnal. Jadwal hari ini belum disinkron oleh Admin!'
            ], 422);
        }

        $jamkeParts = explode('-', $request->jamke);
        $startJamke = (int)$jamkeParts[0];
        $endJamke = (int)($jamkeParts[1] ?? $startJamke);

        for ($i = $startJamke; $i <= $endJamke; $i++) {
            if ($i >= 1 && $i <= 11) {
                $col = 'j' . $i;
                $jurnalh->{$col} = $jurnalValue;
            }
        }
        $jurnalh->save();

        // 4. Insert or Update detailed Jurnal record
        $existingJurnal = DB::table('jurnal')
            ->where('kelas', $request->kelas)
            ->where('guru', 'LIKE', '%' . $request->guru . '%')
            ->where('mapel', $request->mapel)
            ->where('tahun_ajaran', $activeTa->tahun_ajaran)
            ->where('semester', $activeTa->semester)
            ->whereDate('created_at', $today)
            ->first();

        if (!$existingJurnal) {
            DB::table('jurnal')->insert([
                'kelas' => $request->kelas,
                'ket_guru_mapel' => $request->ket_guru_mapel,
                'penugasan' => $request->penugasan,
                'jamke' => $request->jamke,
                'jumlahjam' => $request->jumlahjam,
                'mapel' => $request->mapel,
                'guru' => $request->guru,
                'materi' => $request->materi,
                'catatan' => $request->catatan,
                'guru_id' => $guruId,
                'tahun_ajaran' => $activeTa->tahun_ajaran,
                'semester' => $activeTa->semester,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            DB::table('jurnal')
                ->where('id', $existingJurnal->id)
                ->update([
                    'ket_guru_mapel' => $request->ket_guru_mapel,
                    'penugasan' => $request->penugasan,
                    'jamke' => $request->jamke,
                    'jumlahjam' => $request->jumlahjam,
                    'materi' => $request->materi,
                    'catatan' => $request->catatan,
                    'updated_at' => now()
                ]);
        }

        // 5. Update or insert Jrekap (attending log check columns)
        $rekap = DB::table('jrekap')
            ->where('kelas', $request->kelas)
            ->whereDate('created_at', $today)
            ->first();

        $jamkeParts = explode('-', $request->jamke);
        $startJamke = (int)$jamkeParts[0];
        $endJamke = (int)($jamkeParts[1] ?? $startJamke);

        $rekapData = [];
        for ($i = 1; $i <= 11; $i++) {
            if ($i >= $startJamke && $i <= $endJamke) {
                $rekapData['j' . $i] = 'ok';
            }
        }

        if (!$rekap) {
            $insertData = array_merge([
                'kelas' => $request->kelas,
                'created_at' => now(),
                'updated_at' => now()
            ], $rekapData);
            DB::table('jrekap')->insert($insertData);
        } else {
            DB::table('jrekap')
                ->where('id', $rekap->id)
                ->update(array_merge([
                    'updated_at' => now()
                ], $rekapData));
        }

        // 6. Automatically insert permission if teacher is absent
        $tm = DB::table('ijin')
            ->where('guru', 'LIKE', '%' . $request->guru . '%')
            ->whereDate('created_at', $today)
            ->first();

        if ($request->ket_guru_mapel === 'Tidak Masuk' && !$tm) {
            DB::table('ijin')->insert([
                'tglmasuk' => now(),
                'mapel' => $request->mapel,
                'guru' => $request->guru,
                'sia' => 'Ijin',
                'jumlah' => 1,
                'ket' => 'KBM Tanpa Guru (Terdeteksi via Jurnal)',
                'approval_status' => 'approved',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        AuditLog::write('Mengisi jurnal pelajaran via Aplikasi Mobile. Kelas: ' . $request->kelas . ' (' . $request->mapel . ')');

        return response()->json([
            'status' => 'success',
            'message' => 'Jurnal harian berhasil disimpan.'
        ]);
    }

    /**
     * GET /api/jurnal/riwayat
     * Riwayat jurnal sesuai role, support filter ?tanggal= dan ?kelas=
     */
    public function getRiwayatJurnal(Request $request)
    {
        $user = $request->user();

        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        if (!$activeTa) {
            return response()->json(['status' => 'error', 'message' => 'Tahun ajaran tidak ditemukan.'], 422);
        }

        $query = Jurnal::where('tahun_ajaran', $activeTa->tahun_ajaran)
            ->where('semester', $activeTa->semester)
            ->orderBy('created_at', 'desc');

        $role = $user->role;
        $isWaliKelas = ($role === 'walikelas') || ($role === 'guru' && $user->walikelas_kelas);

        if ($role === 'siswa') {
            $siswa = DB::table('siswa')
                ->where('nama', $user->name)
                ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                ->first();
            if ($siswa) {
                $query->where('kelas', $siswa->kelas);
            }
        } elseif ($isWaliKelas) {
            $kelas = ($role === 'walikelas') ? $user->name : $user->walikelas_kelas;
            $query->where('kelas', $kelas);
        } elseif ($role === 'guru') {
            $query->where('guru_id', $user->id);
        }
        // Admin, Kurikulum, Lihat → lihat semua

        // Filter opsional
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }
        if ($request->filled('kelas') && in_array($role, ['admin', 'kurikulum', 'lihat'])) {
            $query->where('kelas', $request->kelas);
        }

        $data = $query->get()->map(function ($j) {
            // Parse URL dari materi
            $materiText = $j->materi;
            $materiUrl  = null;
            $urlPos = strrpos($j->materi ?? '', 'http');
            if ($urlPos !== false) {
                $materiText = substr($j->materi, 0, $urlPos);
                $materiUrl  = substr($j->materi, $urlPos);
            }

            // Ambil absensi siswa kelas ini pada tanggal ini
            $jDate = \Carbon\Carbon::parse($j->created_at)->toDateString();
            $absensiSiswaList = DB::table('absen')
                ->where('kelas', $j->kelas)
                ->whereDate('created_at', $jDate)
                ->get();
            $siswaTidakMasuk = $absensiSiswaList->map(function ($a) {
                return $a->nama . ' (' . $a->ket . ')';
            })->implode(', ');

            // Ambil absensi guru pada tanggal ini
            $absensiGuruList = DB::table('ijin')
                ->whereDate('created_at', $jDate)
                ->get();
            $guruTidakMasuk = $absensiGuruList->map(function ($g) {
                return $g->guru . ' (' . $g->sia . ')';
            })->implode(', ');

            return array_merge($j->toArray(), [
                'materi_text' => trim($materiText),
                'materi_url'  => $materiUrl,
                'siswa_tidak_masuk' => !empty($siswaTidakMasuk) ? $siswaTidakMasuk : 'Nihil',
                'guru_tidak_masuk'  => !empty($guruTidakMasuk) ? $guruTidakMasuk : 'Nihil',
            ]);
        });

        // Daftar kelas untuk filter
        $kelasList = [];
        if (in_array($role, ['admin', 'kurikulum', 'lihat'])) {
            $kelasList = DB::table('ke_las')->orderBy('kelas')->pluck('kelas');
        } elseif ($role === 'guru') {
            $kelasList = DB::table('jadwal')
                ->where('guru', 'LIKE', '%' . $user->name . '%')
                ->where('tahun_ajaran', $activeTa->tahun_ajaran)
                ->where('semester', $activeTa->semester)
                ->distinct()
                ->orderBy('kelas')
                ->pluck('kelas');
        }

        return response()->json([
            'status'     => 'success',
            'kelas_list' => $kelasList,
            'data'       => $data,
        ]);
    }

    /**
     * GET /api/jurnal/rekap
     * Rekap kelengkapan jurnal per kelas (j1-j11), sesuai role
     */
    public function getRekapJurnal(Request $request)
    {
        $user = $request->user();

        $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
        if (!$activeTa) {
            return response()->json(['status' => 'error', 'message' => 'Tahun ajaran tidak ditemukan.'], 422);
        }

        $role = $user->role;
        $isWaliKelas = ($role === 'walikelas') || ($role === 'guru' && $user->walikelas_kelas);

        $baseQuery = DB::table('jrekap');

        // Batasi kelas jika wali kelas
        if ($isWaliKelas) {
            $kelas = ($role === 'walikelas') ? $user->name : $user->walikelas_kelas;
            $baseQuery->where('kelas', $kelas);
        } elseif ($role === 'ketuakelas') {
            $baseQuery->where('kelas', $user->name);
        }

        // Filter kelas opsional (admin)
        if ($request->filled('kelas') && in_array($role, ['admin', 'kurikulum', 'lihat'])) {
            $baseQuery->where('kelas', $request->kelas);
        }

        // Hitung total kosong per jam
        $kosong = (clone $baseQuery)
            ->selectRaw("count(case when j1='0' then 1 end) as n1")
            ->selectRaw("count(case when j2='0' then 1 end) as n2")
            ->selectRaw("count(case when j3='0' then 1 end) as n3")
            ->selectRaw("count(case when j4='0' then 1 end) as n4")
            ->selectRaw("count(case when j5='0' then 1 end) as n5")
            ->selectRaw("count(case when j6='0' then 1 end) as n6")
            ->selectRaw("count(case when j7='0' then 1 end) as n7")
            ->selectRaw("count(case when j8='0' then 1 end) as n8")
            ->selectRaw("count(case when j9='0' then 1 end) as n9")
            ->selectRaw("count(case when j10='0' then 1 end) as n10")
            ->selectRaw("count(case when j11='0' then 1 end) as n11")
            ->first();

        $ok = (clone $baseQuery)
            ->selectRaw("count(case when j1='ok' then 1 end) as n1")
            ->selectRaw("count(case when j2='ok' then 1 end) as n2")
            ->selectRaw("count(case when j3='ok' then 1 end) as n3")
            ->selectRaw("count(case when j4='ok' then 1 end) as n4")
            ->selectRaw("count(case when j5='ok' then 1 end) as n5")
            ->selectRaw("count(case when j6='ok' then 1 end) as n6")
            ->selectRaw("count(case when j7='ok' then 1 end) as n7")
            ->selectRaw("count(case when j8='ok' then 1 end) as n8")
            ->selectRaw("count(case when j9='ok' then 1 end) as n9")
            ->selectRaw("count(case when j10='ok' then 1 end) as n10")
            ->selectRaw("count(case when j11='ok' then 1 end) as n11")
            ->first();

        // Data rekap per kelas (rows)
        $rows = (clone $baseQuery)
            ->select('kelas', 'j1','j2','j3','j4','j5','j6','j7','j8','j9','j10','j11')
            ->orderBy('kelas')
            ->get();

        $kelasList = DB::table('ke_las')->orderBy('kelas')->pluck('kelas');

        return response()->json([
            'status'       => 'success',
            'kosong'       => $kosong,
            'ok'           => $ok,
            'kelas_list'   => $kelasList,
            'rows'         => $rows,
        ]);
    }

    /**
     * POST /api/jurnal/update/{id}
     * Update/edit jurnal KBM (biasanya diisi/diedit oleh guru)
     */
    public function updateJurnal(Request $request, $id)
    {
        $jurnal = Jurnal::findOrFail($id);

        $request->validate([
            'materi'         => 'required|string',
            'ket_guru_mapel' => 'required|string',
            'penugasan'      => 'required|string',
            'catatan'        => 'nullable|string',
        ]);

        $jurnal->update([
            'materi'         => $request->input('materi'),
            'ket_guru_mapel' => $request->input('ket_guru_mapel'),
            'penugasan'      => $request->input('penugasan'),
            'catatan'        => $request->input('catatan') ?? '',
            'updated_at'     => now(),
        ]);

        AuditLog::write('Mengupdate jurnal KBM via Aplikasi Mobile. Kelas: ' . $jurnal->kelas . ' (' . $jurnal->mapel . ')');

        return response()->json([
            'status'  => 'success',
            'message' => 'Jurnal berhasil diperbarui.',
        ]);
    }
}
