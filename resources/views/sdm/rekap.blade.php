@extends('layouts.master')

@section('content')
<section class="content pt-2">
  <div class="container-fluid">
    @include('sdm._nav', [
      'title' => 'Rekapitulasi Ketenagaan & Siswa',
      'subtitle' => 'Matriks perbandingan status kepegawaian guru dan distribusi rombel siswa per jenjang'
    ])

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fas fa-id-badge text-primary me-2"></i>Matriks Guru Berdasarkan Status & Gender</h6>
            <a href="/sdm/guru/export" class="btn btn-sm btn-success"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Status Kepegawaian</th>
                  <th class="text-center">L</th>
                  <th class="text-center">P</th>
                  <th class="text-center">Total</th>
                </tr>
              </thead>
              <tbody>
                @php $sumL = 0; $sumP = 0; @endphp
                @foreach($matriksGuru as $row)
                  @php $sumL += $row['l']; $sumP += $row['p']; @endphp
                  <tr>
                    <td class="fw-semibold">{{ $row['status'] }}</td>
                    <td class="text-center">{{ $row['l'] }}</td>
                    <td class="text-center">{{ $row['p'] }}</td>
                    <td class="text-center fw-bold text-primary">{{ $row['total'] }}</td>
                  </tr>
                @endforeach
                <tr class="table-primary fw-bold">
                  <td>TOTAL KESELURUHAN GURU</td>
                  <td class="text-center">{{ $sumL }}</td>
                  <td class="text-center">{{ $sumP }}</td>
                  <td class="text-center">{{ $sumL + $sumP }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fas fa-school text-info me-2"></i>Distribusi Siswa Per Jenjang</h6>
            <a href="/sdm/siswa/export" class="btn btn-sm btn-success"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Jenjang Kelas</th>
                  <th class="text-center">Rombel</th>
                  <th class="text-center">L</th>
                  <th class="text-center">P</th>
                  <th class="text-center">Total</th>
                </tr>
              </thead>
              <tbody>
                @php $gRombel = 0; $gL = 0; $gP = 0; $gTot = 0; @endphp
                @foreach($matriksSiswa as $row)
                  @php $gRombel += $row['rombel']; $gL += $row['l']; $gP += $row['p']; $gTot += $row['total']; @endphp
                  <tr>
                    <td class="fw-semibold">{{ $row['label'] }}</td>
                    <td class="text-center">{{ $row['rombel'] }} Rombel</td>
                    <td class="text-center">{{ $row['l'] }}</td>
                    <td class="text-center">{{ $row['p'] }}</td>
                    <td class="text-center fw-bold text-info">{{ $row['total'] }}</td>
                  </tr>
                @endforeach
                <tr class="table-info fw-bold">
                  <td>TOTAL PESERTA DIDIK</td>
                  <td class="text-center">{{ $gRombel }} Rombel</td>
                  <td class="text-center">{{ $gL }}</td>
                  <td class="text-center">{{ $gP }}</td>
                  <td class="text-center">{{ $gTot }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
