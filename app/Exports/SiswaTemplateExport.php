<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SiswaTemplateExport implements FromArray, WithHeadings, WithEvents
{
    public function array(): array
    {
        return [
            ['12345', 'Budi Santoso', 'X-A'],
            ['12346', 'Ani Wijaya', 'X-B'],
        ];
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama',
            'Kelas'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Style the header row (yellow background, bold text)
                $sheet->getStyle('A1:C1')->applyFromArray([
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

                // Auto-fit column widths
                foreach (range('A', 'C') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
