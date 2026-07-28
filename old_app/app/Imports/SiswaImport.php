<?php
namespace App\Imports;

use App\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class SiswaImport implements ToModel
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
        return new Siswa([
           'nis' => $row[0],
           'nama' => $row[1],
           'kelas' => $row[2],
        ]);
    }
}
