<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class JurnalhExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $jurnalhs;

    public function __construct($jurnalhs)
    {
        $this->jurnalhs = $jurnalhs;
    }

    public function collection()
    {
        return $this->jurnalhs;
    }

    public function headings(): array
    {
        return [
            'Kelas',
            'Jam ke-1',
            'Jam ke-2',
            'Jam ke-3',
            'Jam ke-4',
            'Jam ke-5',
            'Jam ke-6',
            'Jam ke-7',
            'Jam ke-8',
            'Jam ke-9',
            'Jam ke-10',
            'Jam ke-11',
            'Tanggal',
        ];
    }

    // Fungsi map() yang dipanggil oleh Maatwebsite Excel untuk mengekspor data
public function map($jurnalh): array
{
    return [
        $jurnalh->kelas,
        $this->cleanText($jurnalh->j1),  // Menggunakan cleanText untuk membersihkan HTML dari j1 hingga j11
        $this->cleanText($jurnalh->j2),
        $this->cleanText($jurnalh->j3),
        $this->cleanText($jurnalh->j4),
        $this->cleanText($jurnalh->j5),
        $this->cleanText($jurnalh->j6),
        $this->cleanText($jurnalh->j7),
        $this->cleanText($jurnalh->j8),
        $this->cleanText($jurnalh->j9),
        $this->cleanText($jurnalh->j10),
        $this->cleanText($jurnalh->j11),
        \Carbon\Carbon::parse($jurnalh->created_at)->format('d-m-Y'), // Format tanggal yang benar
    ];
}


// Fungsi untuk membersihkan teks dan mengganti <br> dengan new line
private function cleanText($text)
{
    // Ganti <br> dengan newline untuk membuat baris baru di Excel
    $text = str_replace('<br>', PHP_EOL, $text);

    // Ganti <hr> dengan newline untuk memindahkan ke baris baru
    $text = str_replace('<hr>', PHP_EOL, $text);

    // Hapus semua tag HTML (untuk bagian selain <a>)
    $text = strip_tags($text);

    // Menangani hyperlink <a> dengan href
    $text = preg_replace_callback('/<a\s+href=["\'](.*?)["\']>(.*?)<\/a>/', function ($matches) {
        return $matches[2] . ' (' . $matches[1] . ')';  // Menampilkan teks link dan URL
    }, $text);

    return $text;
}



    // Menambahkan event untuk mengatur lebar kolom dan tinggi baris
    // Menambahkan event untuk mengatur lebar kolom dan tinggi baris
public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {

            
            // Mengatur lebar kolom (pastikan lebar kolom sesuai dengan data yang ada)
            $event->sheet->getColumnDimension('A')->setWidth(10); // Kelas
            $event->sheet->getColumnDimension('B')->setWidth(25); // Jam ke-1
            $event->sheet->getColumnDimension('C')->setWidth(25); // Jam ke-2
            $event->sheet->getColumnDimension('D')->setWidth(25); // Jam ke-3
            $event->sheet->getColumnDimension('E')->setWidth(25); // Jam ke-4
            $event->sheet->getColumnDimension('F')->setWidth(25); // Jam ke-5
            $event->sheet->getColumnDimension('G')->setWidth(25); // Jam ke-6
            $event->sheet->getColumnDimension('H')->setWidth(25); // Jam ke-7
            $event->sheet->getColumnDimension('I')->setWidth(25); // Jam ke-8
            $event->sheet->getColumnDimension('J')->setWidth(25); // Jam ke-9
            $event->sheet->getColumnDimension('K')->setWidth(25); // Jam ke-10
            $event->sheet->getColumnDimension('L')->setWidth(25); // Jam ke-11
            $event->sheet->getColumnDimension('M')->setWidth(15); // Tanggal

            // Mengatur tinggi baris (jika diperlukan)
            $event->sheet->getRowDimension(1)->setRowHeight(20); // Set height for header row

            // Mengatur font untuk seluruh tabel
            $event->sheet->getStyle('A1:L1000')->getFont()->setSize(10);

            // Menambahkan wrapText untuk setiap kolom agar \n bisa diinterpretasikan
            $event->sheet->getStyle('A1:L1000')->getAlignment()->setWrapText(true);
        },
    ];
}

}


