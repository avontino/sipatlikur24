<?php

namespace App\Imports;

use App\Tagihan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TagihanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Tagihan([
            'nis' => $row['nis'], // Sesuaikan dengan nama header di Excel
            'nama' => $row['nama'],
            'kelas' => $row['kelas'],
            'dana_komite' => $row['dana_komite'],
            'tagihan_lain' => $row['tagihan_lain'],
        ]);
    }
}
