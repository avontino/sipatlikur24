<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Hasil TKA - {{ $identitas->nama }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 18mm 18mm 18mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo {
            width: 75px;
            height: auto;
        }
        .kop-text {
            text-align: center;
        }
        .kop-text h4 {
            margin: 0;
            font-size: 12pt;
            font-weight: normal;
        }
        .kop-text h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: normal;
        }
        .kop-text h2 {
            margin: 2px 0;
            font-size: 15pt;
            font-weight: bold;
        }
        .kop-text p {
            margin: 2px 0 0 0;
            font-size: 9pt;
            font-style: italic;
        }
        .line-double {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-top: 4px;
            margin-bottom: 15px;
        }
        .title-block {
            text-align: center;
            margin-bottom: 15px;
        }
        .title-block h3 {
            margin: 0;
            font-size: 12.5pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .title-block p {
            margin: 2px 0 0 0;
            font-size: 10.5pt;
            font-weight: bold;
        }
        .bio-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10.5pt;
        }
        .bio-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10pt;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        
        .scale-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8.5pt;
        }
        .scale-table th, .scale-table td {
            border: 1px solid #777;
            padding: 3px 5px;
        }
        .scale-table th {
            background-color: #f7f7f7;
            text-align: center;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10.5pt;
        }
        .ttd-table td {
            vertical-align: top;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="header-table">
        <tr>
            @if(!empty($logoBase64))
            <td style="width: 80px; text-align: left;">
                <img src="{{ $logoBase64 }}" class="logo" alt="Logo SMPN 24">
            </td>
            @endif
            <td class="kop-text">
                <h4>{{ $kopHtml['pemerintah'] }}</h4>
                <h3>{{ $kopHtml['dinas'] }}</h3>
                <h2>{{ $kopHtml['sekolah'] }}</h2>
                <p>{{ $kopHtml['alamat'] }}</p>
                <p>{{ $kopHtml['kontak'] }}</p>
            </td>
        </tr>
    </table>
    <div class="line-double"></div>

    {{-- JUDUL DOKUMEN --}}
    <div class="title-block">
        <h3>REKAPITULASI HASIL TES KEMAMPUAN AKADEMIK (TKA)</h3>
        <p>TES DIAGNOSTIK & TRY OUT 1 (TO-1) • TAHUN PELAJARAN 2026/2027</p>
    </div>

    {{-- BIODATA SISWA --}}
    <table class="bio-table">
        <tr>
            <td style="width: 25%;">Nama Peserta Didik</td>
            <td style="width: 2%;">:</td>
            <td style="width: 73%;" class="fw-bold">{{ strtoupper($identitas->nama) }}</td>
        </tr>
        <tr>
            <td>Nomor Induk Siswa (NIS)</td>
            <td>:</td>
            <td>{{ $identitas->nis }}</td>
        </tr>
        <tr>
            <td>NISN</td>
            <td>:</td>
            <td>{{ $identitas->nisn ?: '-' }}</td>
        </tr>
        <tr>
            <td>Kelas / Rombel</td>
            <td>:</td>
            <td>{{ $identitas->kelas }}</td>
        </tr>
        <tr>
            <td>Jenis Kelamin</td>
            <td>:</td>
            <td>{{ $identitas->jenis_kelamin == 'L' ? 'Laki-laki' : ($identitas->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
        </tr>
    </table>

    {{-- TABEL PERBANDINGAN NILAI --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 6%;">No</th>
                <th rowspan="2" style="width: 34%;">Mata Pelajaran</th>
                <th colspan="2" style="width: 30%;">Tes Diagnostik</th>
                <th colspan="2" style="width: 30%;">Try Out 1 (TO-1)</th>
            </tr>
            <tr>
                <th style="width: 14%;">Nilai</th>
                <th style="width: 16%;">Predikat</th>
                <th style="width: 14%;">Nilai</th>
                <th style="width: 16%;">Predikat</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Bahasa Indonesia</td>
                <td class="text-center fw-bold">{{ $diagnostik ? number_format($diagnostik->nilai_bindo, 2) : '-' }}</td>
                <td class="text-center fw-bold">{{ $diagnostik->ket_bindo ?? '-' }}</td>
                <td class="text-center fw-bold">{{ $to1 ? number_format($to1->nilai_bindo, 2) : '-' }}</td>
                <td class="text-center fw-bold">{{ $to1->ket_bindo ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Matematika</td>
                <td class="text-center fw-bold">{{ $diagnostik ? number_format($diagnostik->nilai_mtk, 2) : '-' }}</td>
                <td class="text-center fw-bold">{{ $diagnostik->ket_mtk ?? '-' }}</td>
                <td class="text-center fw-bold">{{ $to1 ? number_format($to1->nilai_mtk, 2) : '-' }}</td>
                <td class="text-center fw-bold">{{ $to1->ket_mtk ?? '-' }}</td>
            </tr>
            <tr style="background-color: #fafafa;">
                <td colspan="2" class="text-center fw-bold">RATA-RATA NILAI</td>
                <td class="text-center fw-bold">{{ $diagnostik ? number_format($diagnostik->nilai_rata_rata, 2) : '-' }}</td>
                <td class="text-center">-</td>
                <td class="text-center fw-bold">{{ $to1 ? number_format($to1->nilai_rata_rata, 2) : '-' }}</td>
                <td class="text-center">-</td>
            </tr>
            <tr style="background-color: #fafafa;">
                <td colspan="2" class="text-center fw-bold">TOTAL SKOR</td>
                <td class="text-center fw-bold">{{ $diagnostik ? number_format($diagnostik->nilai_total, 2) : '-' }}</td>
                <td class="text-center">-</td>
                <td class="text-center fw-bold">{{ $to1 ? number_format($to1->nilai_total, 2) : '-' }}</td>
                <td class="text-center">-</td>
            </tr>
        </tbody>
    </table>

    {{-- KRITERIA PREDIKAT --}}
    <div style="font-size: 9.5pt; font-weight: bold; margin-bottom: 3px;">Keterangan Kategori Capaian Kemampuan Akademik:</div>
    <table class="scale-table">
        <thead>
            <tr>
                <th style="width: 22%;">Rentang Nilai</th>
                <th style="width: 20%;">Predikat</th>
                <th style="width: 58%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">95.00 – 100.00</td>
                <td class="text-center fw-bold">ISTIMEWA</td>
                <td>Kemampuan akademik sangat tinggi, menguasai konsep dan penalaran materi secara mendalam.</td>
            </tr>
            <tr>
                <td class="text-center">76.00 – 94.99</td>
                <td class="text-center fw-bold">BAIK</td>
                <td>Kemampuan akademik baik, mampu menyelesaikan persoalan literasi dan numerasi dengan tepat.</td>
            </tr>
            <tr>
                <td class="text-center">50.00 – 75.99</td>
                <td class="text-center fw-bold">MEMADAI</td>
                <td>Kemampuan akademik memadai, perlu penguatan latihan soal penalaran secara berkelanjutan.</td>
            </tr>
            <tr>
                <td class="text-center">&lt; 50.00</td>
                <td class="text-center fw-bold">KURANG</td>
                <td>Perlu bimbingan dan pendampingan belajar khusus untuk memperkuat pemahaman konsep dasar.</td>
            </tr>
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="ttd-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                Mengetahui,<br>
                Orang Tua / Wali Murid
                <br><br><br><br><br>
                ( .................................................... )
            </td>
            <td style="width: 50%; text-align: center;">
                Malang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Wali Kelas {{ $identitas->kelas }}
                <br><br><br><br><br>
                <strong><u>{{ $identitas->wali_kelas ?: 'Wali Kelas' }}</u></strong><br>
                @if($identitas->nip_wali_kelas)
                NIP. {{ $identitas->nip_wali_kelas }}
                @else
                NIP. -
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
