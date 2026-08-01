<table>
    <thead>
        <tr>
            <th colspan="{{ count($periodDates) + 4 }}" style="font-size: 14px; font-weight: bold; text-align: center;">
                REKAPITULASI MATRIKS PRESENSI GURU & PEGAWAI SIPATLIKUR
            </th>
        </tr>
        <tr>
            <th colspan="{{ count($periodDates) + 4 }}" style="text-align: center;">
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold; background-color: #004d1a; color: #ffffff; text-align: center; border: 1px solid #000000;">No</th>
            <th style="font-weight: bold; background-color: #004d1a; color: #ffffff; text-align: left; border: 1px solid #000000;">Nama Guru / Tendik</th>
            <th style="font-weight: bold; background-color: #004d1a; color: #ffffff; text-align: center; border: 1px solid #000000;">Role</th>
            @foreach($periodDates as $dateStr)
                @php
                    $cDate = \Carbon\Carbon::parse($dateStr);
                    $isWeekend = $cDate->isWeekend();
                @endphp
                <th style="font-weight: bold; background-color: {{ $isWeekend ? '#dc3545' : '#004d1a' }}; color: #ffffff; text-align: center; border: 1px solid #000000;">
                    {{ $cDate->format('d/m') }}
                </th>
            @endforeach
            <th style="font-weight: bold; background-color: #198754; color: #ffffff; text-align: center; border: 1px solid #000000;">Total Hadir</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($matrixPegawai as $p)
        @php $totalHadir = 0; @endphp
        <tr>
            <td style="text-align: center; border: 1px solid #cccccc;">{{ $no++ }}</td>
            <td style="font-weight: bold; border: 1px solid #cccccc;">{{ $p->name }}</td>
            <td style="text-align: center; border: 1px solid #cccccc;">{{ strtoupper($p->role) }}</td>
            @foreach($periodDates as $dateStr)
                @php
                    $rec = $matrixAttendance[$p->id][$dateStr] ?? null;
                    if ($rec) $totalHadir++;
                @endphp
                <td style="text-align: center; border: 1px solid #cccccc;">
                    @if($rec)
                        D: {{ substr($rec->jam_datang, 0, 5) }} | P: {{ $rec->jam_pulang ? substr($rec->jam_pulang, 0, 5) : '--:--' }}
                    @else
                        -
                    @endif
                </td>
            @endforeach
            <td style="font-weight: bold; text-align: center; color: #198754; border: 1px solid #cccccc;">
                {{ $totalHadir }} Hari
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
