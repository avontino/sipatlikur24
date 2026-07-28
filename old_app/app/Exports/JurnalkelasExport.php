<?php

namespace App\Exports;

use App\Jurnal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class JurnalkelasExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Jurnal::where('kelas',auth()->user()->name)->get();
        return back();
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
            $jurnal->guru,
            $jurnal->materi,
            $jurnal->created_at->format('d M Y - H:i:s')
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
            'GURU',
            'MATERI',
            'WAKTU'
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