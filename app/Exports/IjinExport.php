<?php

namespace App\Exports;

use App\Models\Ijin;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IjinExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Ijin::all();
    }

     public function headings(): array
    {
        return [
            '#',
            'TANGGAL',
            'NAMA GURU',
            'MATA PELAJARAN',
            'S/I/A',
            'JUMLAH HARI',
            'Created at',
            'Updated at'
        ];
    }
}

// class JurnalExport implements FromView
// {

//     public function view(): View
//     {
//         return view('jurnal.index', [
//             'data_jurnal' => Jurnal::paginate()
//         ]);
//     }
// }