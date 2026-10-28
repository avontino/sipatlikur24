<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil TKA - {{ $tka->nama }} - {{ $tka->nama_tes }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 20mm 20mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
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
            width: 80px;
            height: auto;
        }
        .kop-text {
            text-align: center;
        }
        .kop-text h4 {
            margin: 0;
            font-size: 13pt;
            font-weight: normal;
            letter-spacing: 0.5px;
        }
        .kop-text h3 {
            margin: 0;
            font-size: 14pt;
            font-weight: normal;
        }
        .kop-text h2 {
            margin: 2px 0;
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .kop-text p {
            margin: 2px 0 0 0;
            font-size: 9.5pt;
            font-style: italic;
        }
        .line-double {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-top: 5px;
            margin-bottom: 18px;
        }
        .title-block {
            text-align: center;
            margin-bottom: 20px;
        }
        .title-block h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .title-block p {
            margin: 3px 0 0 0;
            font-size: 11pt;
            font-weight: bold;
        }
        .bio-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 11pt;
        }
        .bio-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 7px 10px;
            font-size: 11pt;
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
            margin-bottom: 25px;
            font-size: 9pt;
        }
        .scale-table th, .scale-table td {
            border: 1px solid #777;
            padding: 4px 6px;
        }
        .scale-table th {
            background-color: #f7f7f7;
            text-align: center;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            font-size: 11pt;
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
            <td style="width: 80px; text-align: left; vertical-align: middle;">
                @if(!empty($logoBase64))
                <img src="{{ $logoBase64 }}" class="logo" alt="Logo SMPN 24">
                @endif
            </td>
            <td class="kop-text" style="vertical-align: middle;">
                <h4>{{ $kopHtml['pemerintah'] }}</h4>
                <h3>{{ $kopHtml['dinas'] }}</h3>
                <h2>{{ $kopHtml['sekolah'] }}</h2>
                <p>{{ $kopHtml['alamat'] }}</p>
                <p>{{ $kopHtml['kontak'] }}</p>
            </td>
            <td style="width: 80px; text-align: right; vertical-align: middle;">
                @if(!empty($logoPemkotBase64))
                <img src="{{ $logoPemkotBase64 }}" class="logo" alt="Logo Pemkot Malang">
                @endif
            </td>
        </tr>
    </table>
    <div class="line-double"></div>

    {{-- JUDUL DOKUMEN --}}
    <div class="title-block">
        <h3>SURAT KETERANGAN HASIL TES KEMAMPUAN AKADEMIK (TKA)</h3>
        <p>{{ strtoupper($tka->nama_tes) }} • TAHUN PELAJARAN {{ $tka->tahun_ajaran }}</p>
    </div>

    {{-- BIODATA SISWA --}}
    <table class="bio-table">
        <tr>
            <td style="width: 25%;">Nama Peserta Didik</td>
            <td style="width: 2%;">:</td>
            <td style="width: 73%;" class="fw-bold">{{ strtoupper($tka->nama) }}</td>
        </tr>
        <tr>
            <td>Nomor Induk Siswa (NIS)</td>
            <td>:</td>
            <td>{{ $tka->nis }}</td>
        </tr>
        <tr>
            <td>NISN</td>
            <td>:</td>
            <td>{{ $tka->nisn ?: '-' }}</td>
        </tr>
        <tr>
            <td>Kelas / Rombel</td>
            <td>:</td>
            <td>{{ $tka->kelas }}</td>
        </tr>
        <tr>
            <td>Jenis Kelamin</td>
            <td>:</td>
            <td>{{ $tka->jenis_kelamin == 'L' ? 'Laki-laki' : ($tka->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
        </tr>
    </table>

    {{-- TABEL HASIL NILAI --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 50%;">Mata Pelajaran yang Diujikan</th>
                <th style="width: 18%;">Nilai Angka</th>
                <th style="width: 24%;">Kategori Capaian</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Bahasa Indonesia</td>
                <td class="text-center fw-bold">{{ number_format($tka->nilai_bindo, 2) }}</td>
                <td class="text-center fw-bold">{{ $tka->ket_bindo ?: '-' }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Matematika</td>
                <td class="text-center fw-bold">{{ number_format($tka->nilai_mtk, 2) }}</td>
                <td class="text-center fw-bold">{{ $tka->ket_mtk ?: '-' }}</td>
            </tr>
            <tr style="background-color: #fafafa;">
                <td colspan="2" class="text-center fw-bold">RATA-RATA NILAI</td>
                <td class="text-center fw-bold">{{ number_format($tka->nilai_rata_rata, 2) }}</td>
                <td class="text-center">-</td>
            </tr>
            <tr style="background-color: #fafafa;">
                <td colspan="2" class="text-center fw-bold">TOTAL SKOR NILAI</td>
                <td class="text-center fw-bold">{{ number_format($tka->nilai_total, 2) }}</td>
                <td class="text-center">-</td>
            </tr>
        </tbody>
    </table>

    {{-- KRITERIA PREDIKAT --}}
    <div style="font-size: 10pt; font-weight: bold; margin-bottom: 4px;">Keterangan Kategori Capaian Kemampuan Akademik:</div>
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
                Wali Kelas {{ $tka->kelas }}
                <br><br><br><br><br>
                <strong><u>{{ $tka->wali_kelas ?: 'Wali Kelas' }}</u></strong><br>
                @if($tka->nip_wali_kelas)
                NIP. {{ $tka->nip_wali_kelas }}
                @else
                NIP. -
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
