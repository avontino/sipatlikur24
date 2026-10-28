<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TkaSiswa;
use App\Models\Siswa;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportTkaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tka:import {file? : Path file Excel DIAGNOSTIK.xls}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data nilai TKA Siswa Kelas 9 (Diagnostik & TO-1) dari file Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file') ?: base_path('data_lama/DIAGNOSTIK.xls');

        if (!file_exists($filePath)) {
            $this->error("Berkas Excel tidak ditemukan di: {$filePath}");
            return 1;
        }

        $this->info("Membaca berkas: {$filePath} ...");

        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false); // agar calculated formula terbaca
            $spreadsheet = $reader->load($filePath);

            $sheet = $spreadsheet->getSheetByName('DIAGNOSTIK');
            if (!$sheet) {
                $this->error("Sheet 'DIAGNOSTIK' tidak ditemukan dalam file Excel!");
                return 1;
            }

            $highestRow = $sheet->getHighestRow();
            $this->info("Sheet 'DIAGNOSTIK' ditemukan dengan {$highestRow} baris.");

            $diagnostikCount = 0;
            $to1Count = 0;

            // Loop through all data rows
            for ($r = 2; $r <= $highestRow; $r++) {
                // --- BLOCK 1: Left Columns (IX-A, IX-B, IX-C, and part of IX-D) ---
                $nis1 = trim((string)$sheet->getCell([4, $r])->getValue()); // Col D
                $nama1 = trim((string)$sheet->getCell([5, $r])->getValue()); // Col E
                $kelas1 = trim((string)$sheet->getCell([13, $r])->getValue()); // Col M

                if (!empty($nis1) && !empty($nama1) && !empty($kelas1)) {
                    $jk1 = strtoupper(trim((string)$sheet->getCell([6, $r])->getValue())); // Col F
                    $nisnRaw1 = trim((string)$sheet->getCell([3, $r])->getValue()); // Col C
                    $nisn1 = explode('/', $nisnRaw1)[0] ?? $nisnRaw1;
                    $nisn1 = trim($nisn1);

                    $walas1 = trim((string)$sheet->getCell([11, $r])->getValue()); // Col K
                    $nip1 = trim((string)$sheet->getCell([12, $r])->getValue()); // Col L

                    // Nilai Diagnostik
                    $bindoD_val = $this->getNumeric($sheet->getCell([7, $r])->getCalculatedValue()); // Col G
                    $mtkD_val   = $this->getNumeric($sheet->getCell([8, $r])->getCalculatedValue()); // Col H
                    $bindoD_ket = strtoupper(trim((string)$sheet->getCell([9, $r])->getCalculatedValue())); // Col I
                    $mtkD_ket   = strtoupper(trim((string)$sheet->getCell([10, $r])->getCalculatedValue())); // Col J
                    $bindoD_rnd = (int)round($sheet->getCell([14, $r])->getCalculatedValue() ?: $bindoD_val); // Col N
                    $mtkD_rnd   = (int)round($sheet->getCell([15, $r])->getCalculatedValue() ?: $mtkD_val); // Col O

                    // Simpan Diagnostik Siswa 1
                    $this->saveTkaRecord([
                        'nis'               => $nis1,
                        'nisn'              => $nisn1,
                        'nama'              => $nama1,
                        'kelas'             => $kelas1,
                        'jenis_kelamin'     => in_array($jk1, ['L', 'P']) ? $jk1 : null,
                        'wali_kelas'        => $walas1,
                        'nip_wali_kelas'    => $nip1,
                        'jenis_tes'         => 'diagnostik',
                        'nama_tes'          => 'Tes Diagnostik TKA',
                        'nilai_bindo'       => $bindoD_val,
                        'nilai_bindo_bulat' => $bindoD_rnd,
                        'ket_bindo'         => $bindoD_ket,
                        'nilai_mtk'         => $mtkD_val,
                        'nilai_mtk_bulat'   => $mtkD_rnd,
                        'ket_mtk'           => $mtkD_ket,
                        'nilai_rata_rata'   => round(($bindoD_val + $mtkD_val) / 2, 2),
                        'nilai_total'       => round($bindoD_val + $mtkD_val, 2),
                    ]);
                    $diagnostikCount++;

                    // Nilai Try Out 1
                    $bindoTO_val = $this->getNumeric($sheet->getCell([16, $r])->getCalculatedValue()); // Col P
                    $mtkTO_val   = $this->getNumeric($sheet->getCell([17, $r])->getCalculatedValue()); // Col Q
                    $bindoTO_rnd = (int)round($sheet->getCell([18, $r])->getCalculatedValue() ?: $bindoTO_val); // Col R
                    $mtkTO_rnd   = (int)round($sheet->getCell([19, $r])->getCalculatedValue() ?: $mtkTO_val); // Col S
                    $bindoTO_ket = strtoupper(trim((string)$sheet->getCell([20, $r])->getCalculatedValue())); // Col T
                    $mtkTO_ket   = strtoupper(trim((string)$sheet->getCell([21, $r])->getCalculatedValue())); // Col U

                    // Simpan TO-1 Siswa 1
                    $this->saveTkaRecord([
                        'nis'               => $nis1,
                        'nisn'              => $nisn1,
                        'nama'              => $nama1,
                        'kelas'             => $kelas1,
                        'jenis_kelamin'     => in_array($jk1, ['L', 'P']) ? $jk1 : null,
                        'wali_kelas'        => $walas1,
                        'nip_wali_kelas'    => $nip1,
                        'jenis_tes'         => 'to_1',
                        'nama_tes'          => 'Try Out 1 (TO-1)',
                        'nilai_bindo'       => $bindoTO_val,
                        'nilai_bindo_bulat' => $bindoTO_rnd,
                        'ket_bindo'         => $bindoTO_ket,
                        'nilai_mtk'         => $mtkTO_val,
                        'nilai_mtk_bulat'   => $mtkTO_rnd,
                        'ket_mtk'           => $mtkTO_ket,
                        'nilai_rata_rata'   => round(($bindoTO_val + $mtkTO_val) / 2, 2),
                        'nilai_total'       => round($bindoTO_val + $mtkTO_val, 2),
                    ]);
                    $to1Count++;
                }

                // --- BLOCK 2: Right Columns (IX-D remainder, IX-E, IX-F, IX-G) ---
                $nis2 = trim((string)$sheet->getCell([23, $r])->getValue()); // Col W
                $nama2 = trim((string)$sheet->getCell([24, $r])->getValue()); // Col X
                $kelas2 = trim((string)$sheet->getCell([32, $r])->getValue()); // Col AF

                if (!empty($nis2) && !empty($nama2) && !empty($kelas2)) {
                    $jk2 = strtoupper(trim((string)$sheet->getCell([25, $r])->getValue())); // Col Y
                    $walas2 = trim((string)$sheet->getCell([30, $r])->getValue()); // Col AD
                    $nip2 = trim((string)$sheet->getCell([31, $r])->getValue()); // Col AE

                    // Nilai Diagnostik Siswa 2
                    $bindoD2_val = $this->getNumeric($sheet->getCell([26, $r])->getCalculatedValue()); // Col Z
                    $mtkD2_val   = $this->getNumeric($sheet->getCell([27, $r])->getCalculatedValue()); // Col AA
                    $bindoD2_ket = strtoupper(trim((string)$sheet->getCell([28, $r])->getCalculatedValue())); // Col AB
                    $mtkD2_ket   = strtoupper(trim((string)$sheet->getCell([29, $r])->getCalculatedValue())); // Col AC
                    $bindoD2_rnd = (int)round($sheet->getCell([34, $r])->getCalculatedValue() ?: $bindoD2_val); // Col AH
                    $mtkD2_rnd   = (int)round($sheet->getCell([35, $r])->getCalculatedValue() ?: $mtkD2_val); // Col AI

                    $this->saveTkaRecord([
                        'nis'               => $nis2,
                        'nisn'              => null,
                        'nama'              => $nama2,
                        'kelas'             => $kelas2,
                        'jenis_kelamin'     => in_array($jk2, ['L', 'P']) ? $jk2 : null,
                        'wali_kelas'        => $walas2,
                        'nip_wali_kelas'    => $nip2,
                        'jenis_tes'         => 'diagnostik',
                        'nama_tes'          => 'Tes Diagnostik TKA',
                        'nilai_bindo'       => $bindoD2_val,
                        'nilai_bindo_bulat' => $bindoD2_rnd,
                        'ket_bindo'         => $bindoD2_ket,
                        'nilai_mtk'         => $mtkD2_val,
                        'nilai_mtk_bulat'   => $mtkD2_rnd,
                        'ket_mtk'           => $mtkD2_ket,
                        'nilai_rata_rata'   => round(($bindoD2_val + $mtkD2_val) / 2, 2),
                        'nilai_total'       => round($bindoD2_val + $mtkD2_val, 2),
                    ]);
                    $diagnostikCount++;

                    // Nilai Try Out 1 Siswa 2
                    $bindoTO2_val = $this->getNumeric($sheet->getCell([36, $r])->getCalculatedValue()); // Col AJ
                    $mtkTO2_val   = $this->getNumeric($sheet->getCell([37, $r])->getCalculatedValue()); // Col AK
                    $bindoTO2_rnd = (int)round($sheet->getCell([38, $r])->getCalculatedValue() ?: $bindoTO2_val); // Col AL
                    $mtkTO2_rnd   = (int)round($sheet->getCell([39, $r])->getCalculatedValue() ?: $mtkTO2_val); // Col AM
                    $bindoTO2_ket = strtoupper(trim((string)$sheet->getCell([40, $r])->getCalculatedValue())); // Col AN
                    $mtkTO2_ket   = strtoupper(trim((string)$sheet->getCell([41, $r])->getCalculatedValue())); // Col AO

                    $this->saveTkaRecord([
                        'nis'               => $nis2,
                        'nisn'              => null,
                        'nama'              => $nama2,
                        'kelas'             => $kelas2,
                        'jenis_kelamin'     => in_array($jk2, ['L', 'P']) ? $jk2 : null,
                        'wali_kelas'        => $walas2,
                        'nip_wali_kelas'    => $nip2,
                        'jenis_tes'         => 'to_1',
                        'nama_tes'          => 'Try Out 1 (TO-1)',
                        'nilai_bindo'       => $bindoTO2_val,
                        'nilai_bindo_bulat' => $bindoTO2_rnd,
                        'ket_bindo'         => $bindoTO2_ket,
                        'nilai_mtk'         => $mtkTO2_val,
                        'nilai_mtk_bulat'   => $mtkTO2_rnd,
                        'ket_mtk'           => $mtkTO2_ket,
                        'nilai_rata_rata'   => round(($bindoTO2_val + $mtkTO2_val) / 2, 2),
                        'nilai_total'       => round($bindoTO2_val + $mtkTO2_val, 2),
                    ]);
                    $to1Count++;
                }
            }

            $this->info("Import berhasil!");
            $this->line("- Data Tes Diagnostik : {$diagnostikCount} siswa");
            $this->line("- Data Try Out 1      : {$to1Count} siswa");
            $this->line("- Total Data Nilai TKA: " . ($diagnostikCount + $to1Count) . " baris tersimpan.");

            return 0;
        } catch (\Throwable $e) {
            $this->error("Gagal melakukan import TKA: " . $e->getMessage());
            return 1;
        }
    }

    private function getNumeric($value)
    {
        if (is_numeric($value)) {
            return (float)$value;
        }
        $val = str_replace(',', '.', trim((string)$value));
        return is_numeric($val) ? (float)$val : 0.0;
    }

    private function saveTkaRecord(array $data)
    {
        TkaSiswa::updateOrCreate(
            [
                'nis'          => $data['nis'],
                'jenis_tes'    => $data['jenis_tes'],
                'tahun_ajaran' => $data['tahun_ajaran'] ?? '2026/2027',
            ],
            $data
        );
    }
}
