<?php

namespace App\Exports;

use App\Jurnal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Mapel;
use App\Guru;
use App\Kelas;
use Maatwebsite\Excel\Concerns\FromQuery;

class JurnalExport implements FromQuery, WithHeadings
{   
    public function query()
    {
        return Jurnal::query();
        
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
            'KET GURU',
            'PENUGASAN',
            'JAM KE',
            'JUMLAH JAM',
            'MATA PELAJARAN',
            'GURU',
            'MATERI',
            'CATATAN',
            'WAKTU',
            'KODE GURU',
            'Created at',
            'Updated at'
        ];
    }
}

// class JurnalExport implements FromView
// {

//     public function view(): View
//     {	
//     	$ma_pel=Mapel::all();
//     	$gu_ru=Guru::all();
//     	$ke_las=Kelas::all();
//         return view('jurnal.index', [
//             'data_jurnal' => Jurnal::all()
//         ],compact('ma_pel','gu_ru','ke_las'));
//     }
// }