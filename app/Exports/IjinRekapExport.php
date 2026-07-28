<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Ijin;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class IjinRekapExport implements FromCollection, WithMapping, WithHeadings, WithStyles, WithTitle, WithCustomStartCell, WithColumnWidths
{   
    public function collection()
    {
        // Ambil semua user dengan role guru, diurutkan berdasarkan nama
        return User::where('role', 'guru')->orderBy('name', 'asc')->get();
    }

    public function map($user): array 
    {
        $tanggalawal = Carbon::parse(request()->input('tglawal'))->startOfDay();
        $tanggalakhir = Carbon::parse(request()->input('tglakhir'))->endOfDay();
        
        $namaGuru = $user->name;
        
        // Hitung berapa kali sakit
        $totalSakit = Ijin::where('guru', $namaGuru)
            ->where('sia', 'Sakit')
            ->whereBetween('created_at', [$tanggalawal, $tanggalakhir])
            ->sum('jumlah');
            
        // Hitung berapa kali ijin
        $totalIjin = Ijin::where('guru', $namaGuru)
            ->where('sia', 'Ijin')
            ->whereBetween('created_at', [$tanggalawal, $tanggalakhir])
            ->sum('jumlah');
            
        // Hitung berapa kali alpha
        $totalAlpha = Ijin::where('guru', $namaGuru)
            ->where('sia', 'Alpha')
            ->whereBetween('created_at', [$tanggalawal, $tanggalakhir])
            ->sum('jumlah');
            
        // Hitung total hari tidak hadir
        $tidakHadir = $totalSakit + $totalIjin + $totalAlpha;
            
        // Hitung berapa kali terlambat
        $totalTerlambat = Ijin::where('guru', $namaGuru)
            ->where('sia', 'Terlambat')
            ->whereBetween('created_at', [$tanggalawal, $tanggalakhir])
            ->count();

        return [
            $namaGuru,
            $totalSakit ?: 0,
            $totalIjin ?: 0, 
            $totalAlpha ?: 0,
            $tidakHadir ?: 0,
            $totalTerlambat ?: 0,
        ];
    }

    public function headings(): array
    {
        return [
            'NAMA GURU',
            'SAKIT',
            'IJIN', 
            'ALPHA',
            'TOTAL TIDAK HADIR',
            'TERLAMBAT'
        ];
    }

    public function startCell(): string
    {
        return 'A5'; // Data dimulai dari baris 5
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,  // NAMA GURU - lebar untuk menampung nama panjang
            'B' => 12,  // SAKIT - lebar sedang untuk angka
            'C' => 12,  // IJIN - lebar sedang untuk angka
            'D' => 12,  // ALPHA - lebar sedang untuk angka
            'E' => 20,  // TOTAL TIDAK HADIR - lebar untuk header panjang
            'F' => 15,  // TERLAMBAT - lebar sedang
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $tanggalawal = Carbon::parse(request()->input('tglawal'))->format('d/m/Y');
        $tanggalakhir = Carbon::parse(request()->input('tglakhir'))->format('d/m/Y');
        
        // Judul utama
        $sheet->setCellValue('A1', 'REKAP KEHADIRAN GURU');
        $sheet->setCellValue('A2', 'SMAN TARUNA NALA MALANG');
        $sheet->setCellValue('A3', 'Periode: ' . $tanggalawal . ' - ' . $tanggalakhir);
        
        // Merge cells untuk judul
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');
        
        // Hitung jumlah baris data
        $dataCount = User::where('role', 'guru')->count();
        $lastRow = 5 + $dataCount; // 5 adalah start row + jumlah data
        
        return [
            // Style untuk judul utama (baris 1)
            'A1' => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Style untuk subtitle (baris 2)
            'A2' => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Style untuk periode (baris 3)
            'A3' => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Style untuk header tabel (baris 5)
            '5' => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ],
            
            // Style untuk semua data (dari baris 6 sampai terakhir)
            'A6:F' . $lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Style khusus untuk kolom nama (rata kiri)
            'A6:A' . $lastRow => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        $tanggalawal = Carbon::parse(request()->input('tglawal'))->format('d-m-Y');
        $tanggalakhir = Carbon::parse(request()->input('tglakhir'))->format('d-m-Y');
        
        return 'Rekap Kehadiran ' . $tanggalawal . ' - ' . $tanggalakhir;
    }
}