<?php

namespace App\Exports;

use App\Models\Jadwal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;


class JadwalExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Jadwal::all();
    }

    // public function view(): View
    // {	
    // 	$data_siswa= \App\Models\Siswa::orderBy('created_at','desc')->paginate(10);
    //     return view('siswa.index',['data_siswa' => $data_siswa]);
    // }

    public function headings(): array
    {
        return [
            '#',
            'Kelas',
            'Jam Ke',
            'Jumlah Jam',
            'Mata Pelajaran',
            'Guru',
            'Materi',
            'Catatan',
            'Hari',
            'j1',
            'j2',
            'j3',
            'j4',
            'j5',
            'j6',
            'j7',
            'j8',
            'j9',
            'j10',
            'j11',
            'Created at',
            'Updated at'
        ];
    }
}