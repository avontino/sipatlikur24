<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ijin;
use App\Models\Jadwal;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SyncIjinGas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ijin:sync-gas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data izin guru dari spreadsheet bahan/GAS/Untitled spreadsheet.xlsx ke database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = base_path('bahan/GAS/Untitled spreadsheet.xlsx');
        if (!file_exists($file)) {
            $this->error("Berkas spreadsheet tidak ditemukan di: $file");
            return 1;
        }

        $this->info("Membaca berkas: $file");
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheetByName('Data Izin');

        if (!$sheet) {
            $this->error("Sheet 'Data Izin' tidak ditemukan dalam spreadsheet.");
            return 1;
        }

        $data = $sheet->toArray();
        array_shift($data); // Hapus header

        $this->info("Menemukan " . count($data) . " baris data izin. Memulai sinkronisasi...");

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data as $idx => $r) {
            $rawTimestamp = trim($r[0] ?? '');
            $rawDate = trim($r[1] ?? '');
            $rawNama = trim($r[2] ?? '');
            $rawReason = trim($r[3] ?? '');

            if (!$rawNama || !$rawDate) {
                $skipped++;
                continue;
            }

            // Normalisasi nama
            $lookupName = str_replace(
                ['`', 'Adiprayitno', 'Tri H. Ayu S', 'M.Pd'],
                ["'", 'Adi Prayitno', 'Tri Hartatik Ayu S', 'S.Pd'],
                $rawNama
            );

            $user = User::where('name', $lookupName)->first();
            if (!$user) {
                $user = User::whereRaw('LOWER(name) = ?', [strtolower($lookupName)])->first();
            }
            if (!$user) {
                $clean = trim(preg_replace('/,.*$/', '', $lookupName));
                $user = User::where('name', 'LIKE', '%' . $clean . '%')->first();
            }

            if (!$user) {
                $this->warn("User tidak ditemukan untuk nama: $rawNama");
                $skipped++;
                continue;
            }

            // Pemetaan kategori izin
            $sia = match ($rawReason) {
                'Cuti' => 'Cuti',
                'Sakit' => 'Sakit',
                'Tugas kedinasan' => 'Tugas Kedinasan',
                'Izin keluar saat jam dinas (keperluan penting, dan kembali ke sekolah)' => 'Izin Keluar Jam Dinas',
                'Izin terlambat' => 'Izin Terlambat',
                'Keperluan pribadi' => 'Keperluan Pribadi',
                default => $rawReason,
            };

            $jumlah = in_array($sia, ['Izin Terlambat', 'Izin Keluar Jam Dinas', 'Izin Pulang Sebelum Waktunya']) ? '0' : '1';

            try {
                $tglmasuk = Carbon::parse($rawDate)->toDateString();
            } catch (\Exception $e) {
                $skipped++;
                continue;
            }

            try {
                $createdAt = !empty($rawTimestamp) ? Carbon::parse($rawTimestamp) : Carbon::parse($tglmasuk . ' 07:00:00');
            } catch (\Exception $e) {
                $createdAt = Carbon::parse($tglmasuk . ' 07:00:00');
            }

            $month = (int)Carbon::parse($tglmasuk)->format('m');
            $year = (int)Carbon::parse($tglmasuk)->format('Y');
            if ($month >= 7) {
                $tahunAjaran = $year . '/' . ($year + 1);
                $semester = 'Ganjil';
            } else {
                $tahunAjaran = ($year - 1) . '/' . $year;
                $semester = 'Genap';
            }

            $cleanName = preg_replace('/[,.].*$/', '', $user->name);
            $mapel = Jadwal::where('guru', $user->name)
                ->orWhere('guru', 'LIKE', '%' . $cleanName . '%')
                ->value('mapel') ?: '-';

            $existing = Ijin::where('user_id', $user->id)
                ->whereDate('tglmasuk', $tglmasuk)
                ->where('sia', $sia)
                ->first();

            if ($existing) {
                $existing->update([
                    'guru' => $user->name,
                    'mapel' => $mapel,
                    'jumlah' => $jumlah,
                    'ket' => $rawReason,
                    'tahun_ajaran' => $tahunAjaran,
                    'semester' => $semester,
                    'approval_status' => 'approved',
                ]);
                $updated++;
            } else {
                $newIjin = new Ijin();
                $newIjin->user_id = $user->id;
                $newIjin->guru = $user->name;
                $newIjin->tglmasuk = $tglmasuk;
                $newIjin->mapel = $mapel;
                $newIjin->sia = $sia;
                $newIjin->jumlah = $jumlah;
                $newIjin->ket = $rawReason;
                $newIjin->tahun_ajaran = $tahunAjaran;
                $newIjin->semester = $semester;
                $newIjin->approval_status = 'approved';
                $newIjin->created_at = $createdAt;
                $newIjin->updated_at = $createdAt;
                $newIjin->save();
                $inserted++;
            }
        }

        $this->info("==========================================");
        $this->info("Sinkronisasi Selesai!");
        $this->line("Data Baru Ditambahkan: <info>$inserted</info>");
        $this->line("Data Diperbarui      : <info>$updated</info>");
        $this->line("Data Dilewati        : <comment>$skipped</comment>");
        $this->info("Total data izin di database sekarang: " . Ijin::count());
        $this->info("==========================================");

        return 0;
    }
}
