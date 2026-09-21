<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ijin;
use App\Models\Jadwal;
use App\Models\User;
use Carbon\Carbon;

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
        $dataFile = database_path('data_ijin_gas.php');
        if (!file_exists($dataFile)) {
            $this->error("Berkas data izin tidak ditemukan di: $dataFile");
            return 1;
        }

        $records = require $dataFile;
        $this->info("Memuat " . count($records) . " data izin guru. Memulai sinkronisasi ke database...");

        $inserted = 0;
        $updated = 0;

        foreach ($records as $item) {
            $existing = Ijin::where('user_id', $item['user_id'])
                ->whereDate('tglmasuk', $item['tglmasuk'])
                ->where('sia', $item['sia'])
                ->first();

            if ($existing) {
                $existing->update([
                    'guru' => $item['guru'],
                    'mapel' => $item['mapel'],
                    'jumlah' => $item['jumlah'],
                    'ket' => $item['ket'],
                    'tahun_ajaran' => $item['tahun_ajaran'],
                    'semester' => $item['semester'],
                    'approval_status' => $item['approval_status'],
                ]);
                $updated++;
            } else {
                $newIjin = new Ijin();
                $newIjin->user_id = $item['user_id'];
                $newIjin->guru = $item['guru'];
                $newIjin->tglmasuk = $item['tglmasuk'];
                $newIjin->mapel = $item['mapel'];
                $newIjin->sia = $item['sia'];
                $newIjin->jumlah = $item['jumlah'];
                $newIjin->ket = $item['ket'];
                $newIjin->tahun_ajaran = $item['tahun_ajaran'];
                $newIjin->semester = $item['semester'];
                $newIjin->approval_status = $item['approval_status'];
                $newIjin->created_at = $item['created_at'];
                $newIjin->updated_at = $item['created_at'];
                $newIjin->save();
                $inserted++;
            }
        }

        $this->info("==========================================");
        $this->info("Sinkronisasi Selesai!");
        $this->line("Data Baru Ditambahkan: <info>$inserted</info>");
        $this->line("Data Diperbarui      : <info>$updated</info>");
        $this->info("Total data izin di database sekarang: " . Ijin::count());
        $this->info("==========================================");

        return 0;
    }
}
