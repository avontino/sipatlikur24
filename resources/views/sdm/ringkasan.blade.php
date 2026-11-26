@extends('layouts.master')

@section('content')
<section class="content pt-2">
  <div class="container-fluid">
    @include('sdm._nav', [
      'title' => 'Ringkasan Umum Satuan Pendidikan',
      'subtitle' => 'Struktur Demografi Siswa, Golongan Guru, dan Rincian Tugas Tambahan Sekolah'
    ])

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">Distribusi Agama Siswa</h6>
          </div>
          <div class="card-body" style="height: 240px;">
            <canvas id="chartAgama"></canvas>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">Rasio Gender Siswa</h6>
          </div>
          <div class="card-body" style="height: 240px;">
            <canvas id="chartGender"></canvas>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">Distribusi Golongan Guru</h6>
          </div>
          <div class="card-body" style="height: 240px;">
            <canvas id="chartGolGuru"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">
          <i class="fas fa-briefcase text-primary me-2"></i>Rekapitulasi Tugas Tambahan & Jabatan Struktural
        </h6>
        <a href="/sdm/tugas-tambahan" class="btn btn-sm btn-outline-primary rounded-pill">Kelola Tugas Tambahan</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Nama Pejabat / Pendidik</th>
                <th>Jabatan Tambahan</th>
                <th class="text-center">Ekuivalensi JP</th>
                <th>Nomor SK Penugasan</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              @forelse($tugasTambahan as $tt)
              <tr>
                <td class="fw-bold">{{ $tt->pegawai->nama ?? '-' }}</td>
                <td class="fw-semibold text-primary">{{ $tt->nama_tugas }}</td>
                <td class="text-center"><span class="badge bg-info text-dark">{{ $tt->ekuivalensi_jp }} JP</span></td>
                <td class="font-monospace small">{{ $tt->no_sk ?: '-' }}</td>
                <td class="text-muted">{{ $tt->keterangan ?: '-' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">Belum ada data tugas tambahan yang diinput.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const agama = @json($agamaCounts);
  const gender = @json($genderSiswaCounts);
  const golGuru = @json($golGuruCounts);

  new Chart(document.getElementById('chartAgama'), {
    type: 'pie',
    data: {
      labels: Object.keys(agama),
      datasets: [{ data: Object.values(agama), backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#cbd5e1'] }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
  });

  new Chart(document.getElementById('chartGender'), {
    type: 'doughnut',
    data: {
      labels: Object.keys(gender),
      datasets: [{ data: Object.values(gender), backgroundColor: ['#2563eb', '#ec4899', '#cbd5e1'] }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
  });

  new Chart(document.getElementById('chartGolGuru'), {
    type: 'bar',
    data: {
      labels: Object.keys(golGuru),
      datasets: [{ label: 'Guru', data: Object.values(golGuru), backgroundColor: '#4f46e5', borderRadius: 6 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
});
</script>
@endsection
