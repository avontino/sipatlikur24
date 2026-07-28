<?php

namespace App\Exports;

use App\Jurnal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Mapel;
use App\Guru;
use App\Kelas;
use Carbon\Carbon;


class JrekapLaporanExport implements FromCollection, WithMapping, WithHeadings
{   
    public function collection()

    {

       return Kelas::all();
      
    }

    public function map($kelas) : array {

        return [
            $kelas->kelas,
            $kelas->jk,
            $kelas->js,
            $kelas->ji,
            $kelas->ja,
            $kelas->jd
        ] ;

 

 

    }

    public function headings(): array
    {
        return [
            
            'KELAS',
            'JURNAL KOSONG',
            'TOTAL SAKIT',
            'TOTAL IJIN',
            'TOTAL ALPHA',
            'TOTAL DISPEN'
            
        ];
    }
	// use Exportable;
 //    protected $tanggal;
    
 //    public function _construct(Tanggal $tanggal)
 //    {
 //        $this->tanggal = $tanggal;
        
 //    }
 //    public function collection()
 //    {
 //        $test  = $this->tanggal;
 //        dd('yeah', $test);
 //    }

    // public function collection()
    // {
    //     return Jurnal::where('created_at','LIKE',$tanggal)->get();
    //     return back();
    // }

    // public function map($jurnal): array
    // {
    //      return [
    //         $jurnal->kelas,
    //         $jurnal->ket_guru_mapel,
    //         $jurnal->penugasan,
    //         $jurnal->jamke,
    //         $jurnal->jumlahjam,
    //         $jurnal->mapel,
    //         $jurnal->absen,
    //         $jurnal->dispen,
    //         $jurnal->materi,
    //         $jurnal->catatan,
    //         $jurnal->created_at->format('d M Y - H:i:s')
    //      ];
    // }

    //  public function headings(): array
    // {
    //     return [
            
    //         'KELAS',
    //         'KET GURU',
    //         'PENUGASAN',
    //         'JAM KE',
    //         'JUMLAH JAM',
    //         'MATA PELAJARAN',
    //         'ABSEN',
    //         'DISPENSASI',
    //         'MATERI',
    //         'CATATAN',
    //         'WAKTU'
    //     ];
    // }
}

//      public function headings(): array
//     {
//         return [
//             '#',
//             'KELAS',
//             'KET GURU',
//             'PENUGASAN',
//             'JAM KE',
//             'JUMLAH JAM',
//             'MATA PELAJARAN',
//             'GURU',
//             'ABSEN',
//             'DISPENSASI',
//             'MATERI',
//             'CATATAN',
//             'WAKTU',
//             'Created at',
//             'Updated at'
//         ];
//     }
// }

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