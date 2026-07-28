<?php

namespace App\Exports;

use App\Models\Kasus;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KasusExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Kasus::select('id', 'pelapor', 'kejadian', 'tempat', 'created_at', 'updated_at')->get();
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