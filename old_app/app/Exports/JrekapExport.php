<?php

namespace App\Exports;

use App\Jrekap;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;

class JrekapExport implements FromQuery, WithHeadings
{   
    public function query()
    {
        return Jrekap::query();
        
    }

    // public function collection()
    // {
    //     return Jurnal::all();
    // }

     public function headings(): array
    {
        return [
            '#',
            'KELAS',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            'Created at',
            'Updated at'
        ];
    }
}

