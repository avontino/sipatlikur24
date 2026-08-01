<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Harian PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Membatasi lebar kolom agar tetap teratur */
        }

        th, td {
            border: 1px solid black;
            padding: 4px 8px; /* Mengurangi padding untuk menghemat ruang */
            text-align: center;
            font-size: 9px;  /* Ukuran font lebih kecil untuk mencocokkan dengan halaman folio */
            word-wrap: break-word; /* Membungkus kata panjang agar tidak keluar dari kolom */
            vertical-align: top; /* Menjaga teks tetap rata atas dalam kolom */
        }

        th {
            background-color: #f2f2f2;
        }

        hr {
            border: 0;
            border-top: 0.1px solid #dee2e6; /* Garis tipis */
            margin: 10px 0;
        }

        .header {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .sub-header {
            text-align: center;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .date {
            text-align: center;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .divider {
            border-top: 2px solid #000;
            margin-bottom: 10px;
        }

        /* Mengatur halaman ke ukuran folio landscape */
        @page {
            size: 215mm 330mm landscape;
            margin: 10mm;
        }

        /* Styling untuk badge */
        .badge-danger {
            background-color: #dc3545 !important;
            color: white !important;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: bold;
            display: inline-block;
        }

        .badge-warning {
            background-color: #f0ad4e !important;
            color: black !important;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: bold;
            display: inline-block;
        }

        /* Responsivitas */
        @media (max-width: 768px) {
            th, td {
                font-size: 8px;
                padding: 4px;
            }
        }

        /* Penyesuaian lebar kolom */
        .col-kelas {
            width: 3%;
        }
        .col-jam {
            width: 6%;
        }

        /* Kolom Tanggal dengan lebar tetap */
        .col-tanggal {
            width: 5%; /* Lebar tetap untuk kolom Tanggal */
            white-space: nowrap; /* Menghindari pembungkusan teks */
        }

    </style>
</head>
<body>
    <div class="header">Rekap Jurnal Mengajar</div>
    <div class="sub-header">SMP Negeri 24 Malang</div>
    <div class="date">Tanggal: {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</div>
    <div class="divider"></div>
    <br>

    <table>
        <thead>
            <tr>
                <th class="col-kelas">Kelas</th>
                @for ($i = 1; $i <= 11; $i++)
                    <th class="col-jam">Jam ke-{{ $i }}</th>
                @endfor
                <th class="col-tanggal">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jurnalhs as $jurnalh)
                <tr>
                    <td>{{ $jurnalh->kelas }}</td>
                    @for ($i = 1; $i <= 11; $i++)
                        @php
                            $columnData = $jurnalh->{'j'.$i} ?? '';
                            $displayContent = '';
                            
                            if (!empty($columnData)) {
                                $segments = explode('<hr>', $columnData);
                                for ($j = 0; $j < count($segments);) {
                                    if (isset($segments[$j], $segments[$j + 1], $segments[$j + 2])) {
                                        $guruSegment = $segments[$j];
                                        $mapelSegment = $segments[$j + 1];
                                        $materiSegment = $segments[$j + 2];

                                        $badgeSegment = '';
                                        $segmentSize = 3;

                                        if (isset($segments[$j + 3]) && (strpos($segments[$j + 3], 'badge') !== false || strpos($segments[$j + 3], 'KBM Tanpa Guru') !== false)) {
                                            $badgeSegment = $segments[$j + 3];
                                            $segmentSize = 4;
                                        }

                                        $segmentClass = '';
                                        $hasKBMBadge = !empty($badgeSegment) && strpos($badgeSegment, 'KBM Tanpa Guru') !== false;

                                        if ($hasKBMBadge) {
                                            $segmentClass = 'style="background-color: #fff3cd; padding: 5px; margin: 2px; border-radius: 5px; border-left: 4px solid #ffc107;"';
                                        } elseif (strpos($materiSegment, 'Jam Kosong') !== false && !preg_match('/(Bab|Topik|Tugas|TP|Pelajaran)\s?\d/', $materiSegment)) {
                                            $segmentClass = 'style="background-color: #f8d7da; padding: 5px; margin: 2px; border-radius: 5px; border-left: 4px solid #dc3545;"';
                                        } else {
                                            $segmentClass = 'style="background-color: #eefdebff; padding: 5px; margin: 2px; border-radius: 5px; border-left: 4px solid #9af89fff;"';
                                        }

                                        if ($j > 0) $displayContent .= '<hr style="margin: 8px 0; border-color: #dee2e6;">';

                                        $displayContent .= '<div ' . $segmentClass . '>';
                                        $displayContent .= '<div style="font-weight: bold; color: #495057; margin-bottom: 2px;">' . $guruSegment . '</div>';
                                        $displayContent .= '<div style="font-size: 0.9em; color: #6c757d; margin-bottom: 2px;">' . $mapelSegment . '</div>';
                                        $displayContent .= '<div style="color: #212529;">' . $materiSegment . '</div>';
                                        
                                        if (!empty($badgeSegment)) {
                                            $displayContent .= '<div style="margin-top: 5px;">' . $badgeSegment . '</div>';
                                        }

                                        $displayContent .= '</div>';
                                        $j += $segmentSize;
                                    } else {
                                        $j++;
                                    }
                                }
                            }
                        @endphp
                        <td style="vertical-align: top; padding: 8px;">
                            {!! $displayContent !!}
                        </td>
                    @endfor
                    <td class="col-tanggal">{{ \Carbon\Carbon::parse($jurnalh->created_at)->format('d-m-Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
