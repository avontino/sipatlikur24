<!DOCTYPE html>
<html>
<head>
    <title>Rekap Jurnal Guru</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
     <h3>Rekap Jurnal Guru (Tanggal: {{ $startDateFormatted }} - {{ $endDateFormatted }})</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Guru</th>
                <th>Jam Kosong</th>
                <th>Penugasan</th>
                <th>Hadir (Per Hari)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapData as $index => $rekap)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $rekap->guru }}</td>
                    <td>{{ $rekap->jam_kosong }}</td>
                    <td>{{ $rekap->penugasan }}</td>
                    <td>{{ $rekap->hadir_per_hari }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
