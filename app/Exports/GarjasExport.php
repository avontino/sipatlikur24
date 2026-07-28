<?php

namespace App\Exports;

use App\Models\Garjas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GarjasExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $bulan;
    protected $tahun;
    protected $kelas;
    protected $bulanNama;

    public function __construct($bulan, $tahun, $kelas = null)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->kelas = $kelas;
        
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $this->bulanNama = $bulanNames[$bulan];
    }

    public function collection()
    {
        $query = Garjas::byPeriod($this->bulan, $this->tahun)
                      ->orderBy('kelas')
                      ->orderBy('nama');

        if ($this->kelas) {
            $query->byKelas($this->kelas);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'NO',
            'NIS',
            'NAMA',
            'KELAS',
            'LARI (detik)',
            'NILAI LARI',
            'UP/CHIN (kali)',
            'NILAI UP',
            'SIT UP (kali)',
            'NILAI SIT UP',
            'PUSH UP (kali)',
            'NILAI PUSH UP',
            'SHUTTLE R (detik)',
            'NILAI SHUTTLE',
            'NILAI GARJAS B',
            'TOTAL NILAI',
            
        ];
    }

    public function map($garjas): array
    {
        static $no = 1;
        
        return [
            $no++,
            $garjas->nis,
            $garjas->nama,
            $garjas->kelas,
            $garjas->lari ?? '-',
            $garjas->nlari ?? 0,
            $garjas->up ?? '-',
            $garjas->nup ?? 0,
            $garjas->situp ?? '-',
            $garjas->nsitup ?? 0,
            $garjas->pushup ?? '-',
            $garjas->npushup ?? 0,
            $garjas->shuttle ?? '-',
            $garjas->nshuttle ?? 0,
            number_format($garjas->nb ?? 0, 2),
            number_format($garjas->total ?? 0, 2),
            
        ];
    }

    public function title(): string
    {
        $kelasText = $this->kelas ? "Kelas {$this->kelas}" : 'Semua Kelas';
        return "Garjas {$this->bulanNama} {$this->tahun} - {$kelasText}";
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header styling
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FF4CAF50',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Data alignment
            'A:Q' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Name column alignment
            'C' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ],
        ];
    }
}