<?php

namespace App\Exports;

use App\Jrekap;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JrekapkelasExport implements FromCollection, WithHeadings, WithMapping
{   
     public function collection()
    {
        return Jrekap::where('kelas',auth()->user()->name)->get();
        return back();
    }

    public function map($jrekap): array
    {
         return [
            $jrekap->kelas,
            $jrekap->j1,
            $jrekap->j2,
            $jrekap->j3,
            $jrekap->j4,
            $jrekap->j5,
            $jrekap->j6,
            $jrekap->j7,
            $jrekap->j8,
            $jrekap->j9,
            $jrekap->j10,
            $jrekap->j11,
            $jrekap->created_at->format('d M Y ')
         ];
    }

     public function headings(): array
    {
        return [
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
            'Tanggal'
        ];
    }
}

