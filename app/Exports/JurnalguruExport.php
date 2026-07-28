<?php

namespace App\Exports;

use App\Models\Jurnal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class JurnalguruExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Jurnal::where('guru_id', auth()->user()->id);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                $this->startDate . ' 00:00:00',
                $this->endDate . ' 23:59:59'
            ]);
        }

        return $query->get();
    }

    public function map($jurnal): array
    {
         return [
            $jurnal->kelas,
            $jurnal->ket_guru_mapel,
            $jurnal->penugasan,
            $jurnal->jamke,
            $jurnal->jumlahjam,
            $jurnal->mapel,
            $jurnal->materi,
            $jurnal->catatan,
            $jurnal->created_at ? $jurnal->created_at->format('d M Y - H:i:s') : '-'
         ];
    }

     public function headings(): array
    {
        return [
            'KELAS',
            'KET GURU',
            'PENUGASAN',
            'JAM KE',
            'JUMLAH JAM',
            'MATA PELAJARAN',
            'MATERI',
            'CATATAN',
            'WAKTU'
        ];
    }
}