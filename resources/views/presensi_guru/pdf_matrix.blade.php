<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Matriks Presensi Guru & Pegawai</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
            color: #333333;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #002366;
            padding-bottom: 5px;
        }
        .header h2 {
            margin: 0;
            color: #002366;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #666666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
        }
        .report-table th, .report-table td {
            border: 1px solid #aaaaaa;
            padding: 3px 2px;
            text-align: center;
        }
        .report-table th {
            background-color: #002366;
            color: #ffffff;
            font-weight: bold;
        }
        .th-weekend {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }
        .nama-col {
            text-align: left !important;
            font-weight: bold;
        }
        .text-success {
            color: #198754;
            font-weight: bold;
        }
        .text-danger {
            color: #dc3545;
            font-weight: bold;
        }
        .footer {
            margin-top: 15px;
            width: 100%;
            font-size: 8px;
            color: #777777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SINALA - REKAPITULASI MATRIKS PRESENSI GURU & PEGAWAI</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }} | Tahun Ajaran: {{ session('tahun_ajaran') }} - {{ session('semester') }}</p>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 20px;">No</th>
                <th style="width: 120px;" class="nama-col">Nama Guru / Tendik</th>
                <th style="width: 45px;">Role</th>
                @foreach($periodDates as $dateStr)
                    @php
                        $cDate = \Carbon\Carbon::parse($dateStr);
                        $isWeekend = $cDate->isWeekend();
                    @endphp
                    <th class="{{ $isWeekend ? 'th-weekend' : '' }}">
                        {{ $cDate->format('d/m') }}
                    </th>
                @endforeach
                <th style="width: 45px; background-color: #198754; color: #ffffff;">Hadir</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($matrixPegawai as $p)
            @php $totalHadir = 0; @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td class="nama-col">{{ $p->name }}</td>
                <td>{{ strtoupper($p->role) }}</td>
                @foreach($periodDates as $dateStr)
                    @php
                        $rec = $matrixAttendance[$p->id][$dateStr] ?? null;
                        if ($rec) $totalHadir++;
                    @endphp
                    <td>
                        @if($rec)
                            <span class="text-success">{{ substr($rec->jam_datang, 0, 5) }}</span><br>
                            <span class="text-danger">{{ $rec->jam_pulang ? substr($rec->jam_pulang, 0, 5) : '--:--' }}</span>
                        @else
                            -
                        @endif
                    </td>
                @endforeach
                <td style="font-weight: bold; background-color: #f8f9fa;">
                    {{ $totalHadir }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td style="text-align: left;">* D: Jam Datang (Masuk), P: Jam Pulang (Keluar)</td>
            <td style="text-align: right;">Dicetak pada: {{ date('d-m-Y H:i') }} WIB | Oleh: {{ auth()->user()->name }}</td>
        </tr>
    </table>
</body>
</html>
