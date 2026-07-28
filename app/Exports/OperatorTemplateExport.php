<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class OperatorTemplateExport implements FromArray, WithHeadings, WithEvents
{
    public function array(): array
    {
        return [
            ['guru', 'Drs. Ahmad Yani', 'ahmadyani', 'password123'],
            ['tendik', 'Siti Rahma', 'sitirahma', 'password123'],
        ];
    }

    public function headings(): array
    {
        return [
            'Role (admin/guru/tendik)',
            'Nama Lengkap',
            'Username',
            'Password'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:D1')->applyFromArray([
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

                foreach (range('A', 'D') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
