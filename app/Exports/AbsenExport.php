<?php

namespace App\Exports;

use App\Models\Absen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AbsenExport implements FromCollection, WithHeadings, WithEvents
{
    protected $data;

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $records = $this->data ?: Absen::all();
        return $records->map(function ($item) {
            return [
                'ID' => isset($item->id) ? (string) $item->id : '',
                'Nama' => isset($item->nama) ? (string) $item->nama : '',
                'Kelas' => isset($item->kelas) ? (string) $item->kelas : '',
                'Keterangan' => isset($item->ket) ? (string) $item->ket : '',
                'Waktu Absen' => isset($item->created_at) ? (string) $item->created_at : '',
                'Updated At' => isset($item->updated_at) ? (string) $item->updated_at : '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'Nama', 'Kelas', 'Keterangan', 'Created At', 'Updated At'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Mengatur gaya header
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
                            'rgb' => 'FFFF00', // Warna kuning untuk header
                        ],
                    ],
                ]);

                // Mengatur ukuran kolom agar menyesuaikan konten
                foreach (range('A', 'F') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
