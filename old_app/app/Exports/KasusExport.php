<?php

namespace App\Exports;

use App\Kasus;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KasusExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Kasus::all();
    }


    public function headings(): array
    {
        return [
            '#',
            'Pelapor',
            'Kejadian',
            'Tempat',
            'Created at',
            'Updated at'
        ];
    }
}

// class KasusExport implements FromView
// {

//     public function view(): View
//     {
//         return view('kasus.index', [
//             'data_kasus' => Kasus::all()
//         ]);
//     }
// }