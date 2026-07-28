@extends('layouts.master')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <h3>Rekap Jurnal Guru (Tanggal: {{ $startDate }} - {{ $endDate }})</h3>
    </div>
</section>

<div class="card">
    <div class="card-body">
        @if($rekapData->isEmpty())
            <p>Tidak ada data jurnal untuk rentang tanggal yang dipilih.</p>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Guru</th>
                        <th>Jam Kosong</th>
                        <th>Penugasan</th>
                        <th>Hadir (Per Hari)</th> <!-- Pastikan ini adalah 'hadir_per_hari' -->
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapData as $index => $rekap)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $rekap->guru }}</td>
                            <td>{{ $rekap->jam_kosong }}</td>
                            <td>{{ $rekap->penugasan }}</td>
                            <td>{{ $rekap->hadir_per_hari }}</td> <!-- Pastikan ini adalah 'hadir_per_hari' -->
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@endsection
