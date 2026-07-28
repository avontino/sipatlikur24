<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Jurnal; // Sesuaikan dengan model yang digunakan

class JurnalTanggalExport implements FromQuery
{
    use Exportable;

    protected $tanggal;

    // Konstruktor untuk menerima parameter $tanggal
    public function __construct($tanggal)
    {
        $this->tanggal = $tanggal;
    }

    // Query untuk mengambil data berdasarkan tanggal
    public function query()
    {
        return Jurnal::query()->whereDate('created_at', $this->tanggal);
    }
}
