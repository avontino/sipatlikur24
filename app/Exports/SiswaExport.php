<?php

namespace App\Exports;

use App\Models\Siswa; // Pastikan namespace sesuai dengan lokasi model
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SiswaExport implements FromCollection, WithHeadings, WithEvents
{
    protected $kelas;

    public function __construct($kelas = null)
    {
        $this->kelas = $kelas;
    }

    public function collection()
    {
        $query = Siswa::where('tahun_ajaran', session('tahun_ajaran'));
        if ($this->kelas) {
            $query->where('kelas', $this->kelas);
        }
        return $query->get()->map(function ($item) {
            return [
                'ID' => isset($item->id) ? strval($item->id) : '',  // Mengonversi ke string secara eksplisit
                'NIS' => isset($item->nis) ? strval($item->nis) : '',
                'Nama' => isset($item->nama) ? strval($item->nama) : '',
                'Kelas' => isset($item->kelas) ? strval($item->kelas) : '',
                'Sakit' => isset($item->sakit) ? strval($item->sakit) : '0',
                'Ijin' => isset($item->ijin) ? strval($item->ijin) : '0',
                'Alpha' => isset($item->alpha) ? strval($item->alpha) : '0',
                'Dispen' => isset($item->dispen) ? strval($item->dispen) : '0',
                'IP' => isset($item->ip) ? strval($item->ip) : '0',
                'IB' => isset($item->ib) ? strval($item->ib) : '0',
                'IBR' => isset($item->ibr) ? strval($item->ibr) : '0',
                'IJ' => isset($item->ij) ? strval($item->ij) : '0',
                'IK' => isset($item->ik) ? strval($item->ik) : '0',
                'Created At' => isset($item->created_at) ? strval($item->created_at) : '',
                'Updated At' => isset($item->updated_at) ? strval($item->updated_at) : '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'NIS', 'Nama', 'Kelas', 'Sakit', 'Ijin', 'Alpha', 'Dispen', 'IP', 'IB', 'IBR', 'IJ', 'IK', 'Created At', 'Updated At'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Mengatur gaya header
                $sheet->getStyle('A1:O1')->applyFromArray([
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

                // Mengatur semua kolom agar menyesuaikan lebar konten
                foreach (range('A', 'O') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
