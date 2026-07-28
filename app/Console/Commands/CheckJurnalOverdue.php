<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use DB;

class CheckJurnalOverdue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jurnal:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for unfilled journals at 17:00 and send overdue alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $todayStr = now()->toDateString();
        $dayOfWeek = now()->dayOfWeek; // 0 (Sun) - 6 (Sat)
        
        // Skip weekend check
        if ($dayOfWeek == 0 || $dayOfWeek == 6) {
            $this->info('Weekend. Skipped overdue check.');
            return 0;
        }

        $hariEng = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
        ];
        $hariIni = $hariEng[$dayOfWeek] ?? null;
        if (!$hariIni) {
            return 0;
        }

        // Get active school year and semester
        $activeYear = TahunAjaran::where('status', 1)->orderBy('id', 'desc')->first();
        if (!$activeYear) {
            $this->error('No active school year configuration found.');
            return 1;
        }

        $tahun_ajaran = $activeYear->tahun_ajaran;
        $semester = $activeYear->semester;

        // Get all schedules for today
        $schedules = Jadwal::where('hari', $hariIni)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester)
            ->get();

        $unfilledSchedules = [];
        $classesWithUnfilled = [];
        $guruToAlert = []; // format: [guru_name => [array of schedule objects]]

        foreach ($schedules as $sch) {
            $filled = Jurnal::where('kelas', $sch->kelas)
                ->where('mapel', $sch->mapel)
                ->where('guru', $sch->guru)
                ->whereDate('created_at', $todayStr)
                ->exists();

            if (!$filled) {
                $unfilledSchedules[] = $sch;
                $classesWithUnfilled[$sch->kelas] = true;
                $guruToAlert[$sch->guru][] = $sch;
            }
        }

        if (empty($unfilledSchedules)) {
            $this->info('All scheduled KBM hours are filled today.');
            return 0;
        }

        $this->info('Found ' . count($unfilledSchedules) . ' unfilled schedules. Sending alerts...');

        // 1. Alert Ketua Kelas
        foreach (array_keys($classesWithUnfilled) as $kelas) {
            $ketuaKelasUsers = User::where('role', 'ketuakelas')->where('name', $kelas)->get();
            foreach ($ketuaKelasUsers as $u) {
                $u->sendNotification(
                    '⚠️ Jurnal KBM Belum Lengkap',
                    'Anda belum melengkapi pengisian Jurnal KBM hari ini. Harap segera isi.',
                    '/jurnalbaru',
                    'jurnal'
                );
            }
        }

        // 2. Alert Wali Kelas
        foreach (array_keys($classesWithUnfilled) as $kelas) {
            $waliKelasUsers = User::where(function($q) use ($kelas) {
                $q->where('role', 'walikelas')->where('name', $kelas);
            })->orWhere(function($q) use ($kelas) {
                $q->where('role', 'guru')->where('walikelas_kelas', $kelas);
            })->get();

            foreach ($waliKelasUsers as $u) {
                $u->sendNotification(
                    '⚠️ Jurnal Kelas Belum Lengkap',
                    'Terdapat jurnal KBM kelas perwalian Anda yang belum lengkap hari ini. Harap pantau/bantu isi di menu Jurnal Harian.',
                    '/jurnalh?view=walikelas',
                    'jurnal'
                );
            }
        }

        // 3. Alert Guru Mapel
        foreach ($guruToAlert as $guruName => $schList) {
            $guruUsers = User::where('role', 'guru')->where('name', $guruName)->get();
            foreach ($guruUsers as $u) {
                $details = [];
                foreach ($schList as $sch) {
                    $details[] = "{$sch->mapel} di Kelas {$sch->kelas}";
                }
                $detailsStr = implode(', ', array_unique($details));

                $u->sendNotification(
                    '⚠️ Jurnal Mengajar Belum Lengkap',
                    "Jurnal KBM untuk jam mengajar Anda hari ini ({$detailsStr}) belum dilengkapi oleh Ketua Kelas. Harap hubungi ketua kelas atau bantu lengkapi di Riwayat Jurnal.",
                    '/jurnalbaru',
                    'jurnal'
                );
            }
        }

        // 4. Alert Kurikulum & Admin
        $kurikulumAndAdmin = User::whereIn('role', ['admin', 'kurikulum'])->get();
        $totalClassesUnfilled = count($classesWithUnfilled);
        foreach ($kurikulumAndAdmin as $u) {
            $u->sendNotification(
                '⚠️ Peringatan Jurnal Harian',
                "Terdapat {$totalClassesUnfilled} kelas yang belum menyelesaikan pengisian jurnal KBM hari ini.",
                '/jurnalh?view=kurikulum',
                'jurnal'
            );
        }

        $this->info('Overdue alerts sent successfully.');
        return 0;
    }
}
