<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\DB;

class RekapJurnalExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Ambil data jurnal berdasarkan rentang tanggal yang diberikan
        return DB::table('jurnal')
            ->select(DB::raw('guru, 
                COUNT(DISTINCT CASE WHEN ket_guru_mapel = "Hadir" THEN created_at END) AS hadir_per_hari, 
                SUM(CASE WHEN penugasan = "Ada" THEN 1 ELSE 0 END) AS penugasan, 
                SUM(CASE WHEN materi = "Jam Kosong" THEN 1 ELSE 0 END) AS jam_kosong'))
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy('guru')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',              // Kolom untuk nomor urut
            'Nama Guru',       // Kolom untuk Nama Guru
            'Jam Kosong',      // Kolom untuk jumlah Jam Kosong
            'Penugasan',       // Kolom untuk jumlah Penugasan
            'Hadir (Per Hari)',// Kolom untuk jumlah Hadir per hari
        ];
    }

    public function map($row): array
    {
        static $no = 1; // Menggunakan variabel statis untuk nomor urut
        return [
            // Kolom No (Nomor Urut)
            $no++, // Increment nomor urut setiap baris
            // Kolom Nama Guru
            $row->guru,
            // Kolom Jam Kosong
            $row->jam_kosong,
            // Kolom Penugasan
            $row->penugasan,
            // Kolom Hadir (Per Hari)
            $row->hadir_per_hari,
        ];
    }

    // Gaya tampilan Excel (penataan header dan data)
    public function styles($sheet)
    {
        // Gaya untuk header
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFont()->setSize(12);
        $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00'); // Background kuning

        // Gaya untuk kolom data
        $sheet->getStyle('A2:E' . ($sheet->getHighestRow()))->getFont()->setSize(10);
        $sheet->getStyle('A2:E' . ($sheet->getHighestRow()))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:E' . ($sheet->getHighestRow()))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Set border untuk semua kolom
        $sheet->getStyle('A1:E' . ($sheet->getHighestRow()))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Set lebar kolom otomatis
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
    }

    // Format kolom agar data numerik tampil lebih rapi
    public function columnFormats(): array
    {
        return [
            'A' => '#', // Format nomor urut (no)
            'B' => '0', // Nama Guru (biasanya teks)
            'C' => '0', // Jam Kosong
            'D' => '0', // Penugasan
            'E' => '0', // Hadir (Per Hari)
        ];
    }
}
