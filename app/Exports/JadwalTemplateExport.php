<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class JadwalTemplateExport implements FromArray, WithHeadings, WithEvents
{
    public function array(): array
    {
        return [
            ['Senin', '1', 'X-A', 'Matematika', 'Drs. Ahmad Yani'],
            ['Senin', '2', 'X-A', 'Fisika', 'Siti Rahma'],
        ];
    }

    public function headings(): array
    {
        return [
            'Hari',
            'Jam Ke',
            'Kelas',
            'Mata Pelajaran',
            'Guru Pengajar'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:E1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'FFFFCC',
                        ],
                    ],
                ]);

                foreach (range('A', 'E') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
