<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Garjas {{ $bulanNama }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 5px 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-table td {
            padding: 3px;
            font-size: 11px;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .main-table th,
        .main-table td {
            border: 1px solid #333;
            padding: 4px;
            text-align: center;
            font-size: 9px;
        }
        
        .main-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .main-table .nama {
            text-align: left;
        }
        
        .baterai-a {
            background-color: #e3f2fd;
        }
        
        .baterai-b {
            background-color: #f3e5f5;
        }
        
        .nilai-garjas-b {
            background-color: #fff3e0;
        }
        
        .total-nilai {
            background-color: #e8f5e8;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            font-size: 9px;
        }
        

    </style>
</head>
<body>
    <div class="header">
        <h1>DATA GARJAS TAHUN AKADEMIK {{ $tahun }}-{{ $tahun + 1 }}</h1>
        <h2>{{ strtoupper($kelasText) }} - {{ strtoupper($bulanNama) }} {{ $tahun }}</h2>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Periode:</strong> {{ $bulanNama }} {{ $tahun }}</td>
            <td><strong>Kelas:</strong> {{ $kelas ?: 'Semua Kelas' }}</td>
            <td><strong>Total Siswa:</strong> {{ $garjas->count() }}</td>
            <td><strong>Dicetak:</strong> {{ date('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2">NO</th>
                <th rowspan="2">NIS</th>
                <th rowspan="2">NAMA</th>
                <th rowspan="2">KELAS</th>
                <th colspan="2" class="baterai-a">BATERAI A</th>
                <th colspan="8" class="baterai-b">BATERAI B</th>
                <th rowspan="2" class="nilai-garjas-b">NILAI<br>GARJAS B</th>
                <th rowspan="2" class="total-nilai">TOTAL<br>NILAI</th>
                
            </tr>
            <tr>
                <!-- Baterai A -->
                <th class="baterai-a">LARI</th>
                <th class="baterai-a">NILAI</th>
                <!-- Baterai B -->
                <th class="baterai-b">UP/CHIN</th>
                <th class="baterai-b">NILAI</th>
                <th class="baterai-b">SIT UP</th>
                <th class="baterai-b">NILAI</th>
                <th class="baterai-b">PUSH UP</th>
                <th class="baterai-b">NILAI</th>
                <th class="baterai-b">SHUTTLE R</th>
                <th class="baterai-b">NILAI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($garjas as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item->nis }}</td>
                <td class="nama">{{ $item->nama }}</td>
                <td>{{ $item->kelas }}</td>
                
                <!-- Baterai A -->
                <td class="baterai-a">{{ $item->lari ?? '-' }}</td>
                <td class="baterai-a">{{ $item->nlari ?? 0 }}</td>
                
                <!-- Baterai B -->
                <td class="baterai-b">{{ $item->up ?? '-' }}</td>
                <td class="baterai-b">{{ $item->nup ?? 0 }}</td>
                <td class="baterai-b">{{ $item->situp ?? '-' }}</td>
                <td class="baterai-b">{{ $item->nsitup ?? 0 }}</td>
                <td class="baterai-b">{{ $item->pushup ?? '-' }}</td>
                <td class="baterai-b">{{ $item->npushup ?? 0 }}</td>
                <td class="baterai-b">{{ $item->shuttle ?? '-' }}</td>
                <td class="baterai-b">{{ $item->nshuttle ?? 0 }}</td>
                
                <!-- Nilai Garjas B -->
                <td class="nilai-garjas-b">{{ number_format($item->nb ?? 0, 2) }}</td>
                
                <!-- Total Nilai -->
                <td class="total-nilai">{{ number_format($item->total ?? 0, 2) }}</td>
                
                
            </tr>
            @empty
            <tr>
                <td colspan="17">Tidak ada data tersedia</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Keterangan:</strong></p>
        <p>• Rumus Nilai Garjas B = (Nilai UP + Nilai SIT UP + Nilai PUSH UP + Nilai SHUTTLE) ÷ 4</p>
        <p>• Rumus Total Nilai = (Nilai Lari + Nilai Garjas B) ÷ 2</p>
       
    </div>
</body>
</html>