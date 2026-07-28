<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Presensi Guru</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #002366;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #002366;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #666666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 2px 0;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .report-table th, .report-table td {
            border: 1px solid #cccccc;
            padding: 6px;
            text-align: left;
        }
        .report-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #002366;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: #ffffff;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-danger {
            background-color: #dc3545;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .footer {
            margin-top: 40px;
            width: 100%;
        }
        .footer td {
            text-align: right;
            font-size: 9px;
            color: #777777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SINALA</h2>
        <p>Laporan Rekapitulasi Presensi Kehadiran Guru</p>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 15%;"><strong>Periode Laporan</strong></td>
            <td style="width: 35%;">: {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</td>
            <td style="width: 18%;"><strong>Tahun Ajaran</strong></td>
            <td style="width: 32%;">: {{ session('tahun_ajaran') }}</td>
        </tr>
        <tr>
            <td><strong>Dicetak Oleh</strong></td>
            <td>: {{ auth()->user()->name }}</td>
            <td><strong>Semester</strong></td>
            <td>: {{ session('semester') }}</td>
        </tr>
        <tr>
            <td><strong>Waktu Cetak</strong></td>
            <td>: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }} WIB</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 22%;">Nama Guru</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 9%;">Jam Datang</th>
                <th style="width: 9%;">Jam Pulang</th>
                <th style="width: 14%;">Status Masuk</th>
                <th style="width: 11%;">Menit Terlambat</th>
                <th style="width: 11%;">Status Pulang</th>
                <th style="width: 10%;">Pulang Sebelum Waktunya (Menit)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($rekap as $r)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td><strong>{{ $r->nama }}</strong></td>
                <td class="text-center">{{ \Carbon\Carbon::parse($r->tanggal)->format('d-m-Y') }}</td>
                <td class="text-center">{{ $r->jam_datang ?? '-' }}</td>
                <td class="text-center">{{ $r->jam_pulang ?? '-' }}</td>
                <td class="text-center">
                    @if($r->status_datang == 'Terlambat')
                        <span class="badge badge-danger">Terlambat</span>
                    @else
                        <span class="badge badge-success">Tepat Waktu</span>
                    @endif
                </td>
                <td class="text-center">{{ $r->menit_terlambat > 0 ? $r->menit_terlambat . ' Menit' : '-' }}</td>
                <td class="text-center">
                    @if($r->status_pulang == 'Pulang Sebelum Waktunya')
                        <span class="badge badge-warning">Pulang Sebelum Waktunya</span>
                    @elseif($r->status_pulang == 'Selesai')
                        <span class="badge badge-success">Selesai</span>
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ $r->menit_pulang_cepat > 0 ? $r->menit_pulang_cepat . ' Menit' : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #777777;">Tidak ditemukan data rekap presensi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td>Dicetak otomatis oleh Sistem SINALA &copy; {{ date('Y') }}</td>
        </tr>
    </table>
</body>
</html>
