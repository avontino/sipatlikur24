<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class IjinsiswaExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data->map(function ($item) {
            return [
                'Nama' => $item->nama ?? '-',
                'Kelas' => $item->kelas ?? '-',
                'Keterangan Ijin' => $item->ketijin ?? '-',
                'Waktu Ijin' => $item->created_at ? $item->created_at->format('d-m-Y H:i:s') : '-',
                'Pembina' => $item->oksis ?? '-',
                'Wakakur' => $item->okkur ?? '-',
                'Walikelas' => $item->okbin ?? '-',
                'Kesehatan' => $item->okas ?? '-',
'Berangkat' => $item->cekout ? Carbon::parse($item->cekout)->format('d-m-Y H:i:s') : '-',
'Kembali' => $item->cekin ? Carbon::parse($item->cekin)->format('d-m-Y H:i:s') : '-'
		];
        })->toArray());
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Kelas',
            'Keterangan Ijin',
            'Waktu Ijin',
            'Pembina',
            'Wakakur',
            'Walikelas',
            'Kesehatan',
            'Berangkat',
            'Kembali'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Style header
                $sheet->getStyle('A1:J1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '000000'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => [
                            'rgb' => 'FFFF00'
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                        'wrapText' => true
                    ]
                ]);

                // Style all cells with border
                $sheet->getStyle('A1:J' . $sheet->getHighestRow())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                    'alignment' => [
                        'wrapText' => true
                    ]
                ]);
            },
        ];
    }
}