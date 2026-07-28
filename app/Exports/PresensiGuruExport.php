<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PresensiGuruExport implements FromCollection, WithHeadings, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item, $key) {
            return [
                'No' => $key + 1,
                'Nama Guru' => $item->nama,
                'Tanggal' => $item->tanggal,
                'Jam Datang' => $item->jam_datang ?? '-',
                'Jam Pulang' => $item->jam_pulang ?? '-',
                'Status Datang' => $item->status_datang ?? '-',
                'Menit Terlambat' => $item->menit_terlambat > 0 ? $item->menit_terlambat . ' Menit' : '-',
                'Status Pulang' => $item->status_pulang ?? '-',
                'Pulang Sebelum Waktunya (Menit)' => $item->menit_pulang_cepat > 0 ? $item->menit_pulang_cepat . ' Menit' : '-',
                'Jarak Datang' => ($item->lat_datang && $item->lng_datang) ? 'Terverifikasi (GPS)' : '-',
                'Jarak Pulang' => ($item->lat_pulang && $item->lng_pulang) ? 'Terverifikasi (GPS)' : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Guru',
            'Tanggal',
            'Jam Datang',
            'Jam Pulang',
            'Status Kedatangan',
            'Menit Terlambat',
            'Status Pulang',
            'Pulang Sebelum Waktunya (Menit)',
            'Verifikasi Lokasi Datang',
            'Verifikasi Lokasi Pulang',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Style the header row (yellow background, bold text)
                $sheet->getStyle('A1:K1')->applyFromArray([
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
                            'rgb' => 'FFFF00',
                        ],
                    ],
                ]);

                // Auto-fit column widths (from A to K)
                foreach (range('A', 'K') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
