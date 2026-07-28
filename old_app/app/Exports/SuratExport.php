<?php

namespace App\Exports;

use App\Surat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class SuratExport implements FromCollection
{
    public function collection()
    {
        return Surat::all();
    }
}