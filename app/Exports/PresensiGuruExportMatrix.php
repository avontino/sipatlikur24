<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PresensiGuruExportMatrix implements FromView, ShouldAutoSize
{
    protected $matrixPegawai;
    protected $periodDates;
    protected $matrixAttendance;
    protected $startDate;
    protected $endDate;

    public function __construct($matrixPegawai, $periodDates, $matrixAttendance, $startDate, $endDate)
    {
        $this->matrixPegawai = $matrixPegawai;
        $this->periodDates = $periodDates;
        $this->matrixAttendance = $matrixAttendance;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('presensi_guru.excel_matrix', [
            'matrixPegawai' => $this->matrixPegawai,
            'periodDates' => $this->periodDates,
            'matrixAttendance' => $this->matrixAttendance,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }
}
