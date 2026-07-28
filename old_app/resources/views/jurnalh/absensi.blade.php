@extends('layouts.master')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5>Absensi Siswa Kelas {{ $kelas }} pada tanggal {{ \Carbon\Carbon::parse($tgl)->format('d-m-Y') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($absensi as $siswa)
                            <tr>
                                <td>{{ $siswa->nama }}</td>
                                <td>{{ $siswa->ket }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">Tidak ada absensi untuk kelas ini pada tanggal tersebut.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
