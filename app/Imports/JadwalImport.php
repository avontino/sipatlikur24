<?php
namespace App\Imports;

use App\Models\Jadwal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class JadwalImport implements ToModel
{
    // public function collection(Collection $rows)
    // {
    //     foreach ($rows as $row) 
    //     {
    //         Siswa::create([
    //             'nis' => $row[0],
    //             'nama' => $row[1],
    //             'kelas' => $row[2],
    //             'kelamin' => $row[3],
    //         ]);
    //     }
    // }
    public function model(array $row)
    {
        return new Jadwal([
           'kelas' => $row[0],
           'jamke' => $row[1],
           'jumlahjam' => $row[2],
           'mapel' => $row[3],
           'guru' => $row[4],
           'hari' => $row[5],
           'j1' => $row[6],
           'j2' => $row[7],
           'j3' => $row[8],
           'j4' => $row[9],
           'j5' => $row[10],
           'j6' => $row[11],
           'j7' => $row[12],
           'j8' => $row[13],
           'j9' => $row[14],
           'j10' => $row[15],
           'j11' => $row[16],
           'tahun_ajaran' => session('tahun_ajaran'),
           'semester' => session('semester'),
        ]);
    }
}
