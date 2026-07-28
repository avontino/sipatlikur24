<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Peringatan {{ $level }} - {{ $siswa->nama }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 40px;
            color: #000;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .doc-title {
            text-align: center;
            margin: 30px 0;
        }
        .doc-title h3 {
            margin: 0;
            font-size: 18px;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .doc-title p {
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .student-info {
            margin-bottom: 25px;
        }
        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-info td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 15px;
        }
        .content {
            font-size: 15px;
            text-align: justify;
            margin-bottom: 25px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table-data th, .table-data td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 14px;
            text-align: left;
        }
        .table-data th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }
        .signature-block {
            text-align: center;
            width: 250px;
        }
        .signature-space {
            height: 80px;
        }
        @media print {
            body {
                margin: 20px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #fff3cd; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 15px; font-size: 14px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            Cetak Surat Peringatan
        </button>
    </div>

    <div class="header">
        <h2>Pemerintah Provinsi Jawa Timur</h2>
        <h2>Dinas Pendidikan</h2>
        <h2>SMAN Taruna Nala Jawa Timur</h2>
        <p>Jl. Raya Tlogowaru No. 66, Tlogowaru, Kec. Kedungkandang, Kota Malang, Jawa Timur 65133</p>
    </div>

    <div class="doc-title">
        <h3>Surat Peringatan {{ $level }} (SP {{ $level }})</h3>
        <p>Nomor: SP/{{ $level }}/{{ date('Y') }}/{{ $siswa->nis }}</p>
    </div>

    <div class="content">
        <p>Surat Peringatan ini diberikan kepada siswa yang tertera di bawah ini karena telah melakukan serangkaian tindakan pelanggaran disiplin tata tertib sekolah dengan akumulasi poin pelanggaran melebihi batas toleransi minimal yang telah ditentukan oleh sekolah (minimal 50 poin pelanggaran).</p>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td style="width: 180px;">Nama Siswa</td>
                <td style="width: 20px;">:</td>
                <td style="font-weight: bold;">{{ $siswa->nama }}</td>
            </tr>
            <tr>
                <td>NIS / NISN</td>
                <td>:</td>
                <td>{{ $siswa->nis }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>:</td>
                <td>{{ $siswa->kelas }}</td>
            </tr>
            <tr>
                <td>Total Poin Pelanggaran</td>
                <td>:</td>
                <td style="color: red; font-weight: bold;">{{ $total_poin }} Poin</td>
            </tr>
        </table>
    </div>

    <div class="content">
        <p>Berikut adalah rincian pelanggaran kedisiplinan yang telah dilakukan:</p>
        
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th style="width: 150px;">Tanggal</th>
                    <th>Nama Pelanggaran</th>
                    <th>Detail Kejadian</th>
                    <th style="width: 100px; text-align: center;">Poin</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @foreach($pelanggaran as $p)
                <tr>
                    <td style="text-align: center;">{{ $i++ }}</td>
                    <td>{{ $p->created_at->format('d M Y') }}</td>
                    <td>{{ $p->kategoriPoin->nama ?? 'Pelanggaran Umum' }}</td>
                    <td>{{ $p->kejadian }}</td>
                    <td style="text-align: center; font-weight: bold; color: red;">{{ $p->poin }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p>Demikian surat peringatan ini dikeluarkan agar menjadi perhatian serius bagi siswa yang bersangkutan serta orang tua/wali siswa. Siswa diharapkan segera melakukan perbaikan perilaku demi menjaga ketertiban proses belajar mengajar di SMAN Taruna Nala Jawa Timur.</p>
    </div>

    <div class="footer">
        <div class="signature-block">
            <p>Mengetahui,</p>
            <p>Wali Kelas {{ $siswa->kelas }}</p>
            <div class="signature-space"></div>
            <p style="text-decoration: underline; font-weight: bold;">..................................................</p>
            <p>NIP. ..........................................</p>
        </div>
        <div class="signature-block">
            <p>Malang, {{ date('d M Y') }}</p>
            <p>Kepala SMAN Taruna Nala Jawa Timur</p>
            <div class="signature-space"></div>
            <p style="text-decoration: underline; font-weight: bold;">Drs. Hari Wahyono, M.Pd</p>
            <p>NIP. 196805211994031008</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
