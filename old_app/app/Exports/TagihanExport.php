<?php

namespace App\Exports;

use App\Tagihan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class TagihanExport implements FromCollection, WithHeadings, WithEvents
{
    public function collection()
    {
        return Tagihan::all()->map(function ($item) {
            return [
                'ID' => (string) $item->id,
                'NIS' => (string) $item->nis,
                'Nama' => (string) $item->nama,
                'Kelas' => (string) $item->kelas,
                'Dana Komite' => (string) $item->dana_komite,
                'Tagihan Lain' => (string) $item->tagihan_lain,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIS',
            'Nama',
            'Kelas',
            'Dana Komite',
            'Tagihan Lain'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Membuat header tebal, rata tengah, dan berwarna kuning
                $sheet->getStyle('A1:F1')->applyFromArray([
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
                            'rgb' => 'FFFF00', // Warna kuning
                        ],
                    ],
                ]);

                // Merapikan kolom Nama menjadi rata kiri
                $sheet->getStyle('C2:C' . $sheet->getHighestRow())->getAlignment()->setHorizontal('left');

                // Mengatur kolom Dana Komite dan Tagihan Lain menjadi rata kanan
                $sheet->getStyle('E2:E' . $sheet->getHighestRow())->getAlignment()->setHorizontal('right');
                $sheet->getStyle('F2:F' . $sheet->getHighestRow())->getAlignment()->setHorizontal('right');

                // Mengatur ukuran kolom agar otomatis menyesuaikan
                foreach (range('A', 'F') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
