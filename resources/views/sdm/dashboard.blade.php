@extends('layouts.master')

@section('content')
<section class="content pt-2">
  <div class="container-fluid">
    @include('sdm._nav', [
      'title' => 'Dashboard Analitik SDM',
      'subtitle' => 'Pemantauan KPI Ketenagaan, Notifikasi Ulang Tahun (H-5 s.d. Hari H), dan Batas Usia Pensiun'
    ])

    {{-- 4 KPI SUMMARY CARDS --}}
    <div class="row g-3 mb-4">
      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="p-3 rounded-4 bg-primary bg-opacity-10 text-primary">
              <i class="fas fa-chalkboard-teacher fa-2x"></i>
            </div>
            <div>
              <div class="text-muted small fw-semibold">Total Tenaga Pendidik</div>
              <div class="fs-3 fw-bolder text-dark">{{ $totalGuru }}</div>
              <span class="badge bg-success-subtle text-success">Guru Terdata</span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="p-3 rounded-4 bg-info bg-opacity-10 text-info">
              <i class="fas fa-user-cog fa-2x"></i>
            </div>
            <div>
              <div class="text-muted small fw-semibold">Tenaga Kependidikan</div>
              <div class="fs-3 fw-bolder text-dark">{{ $totalTendik }}</div>
              <span class="badge bg-info-subtle text-info">Tendik Terdata</span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="p-3 rounded-4 bg-warning bg-opacity-10 text-warning">
              <i class="fas fa-user-graduate fa-2x"></i>
            </div>
            <div>
              <div class="text-muted small fw-semibold">Total Peserta Didik</div>
              <div class="fs-3 fw-bolder text-dark">{{ $totalSiswa }}</div>
              <span class="badge bg-warning-subtle text-dark">Siswa Aktif</span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="p-3 rounded-4 bg-danger bg-opacity-10 text-danger">
              <i class="fas fa-hourglass-half fa-2x"></i>
            </div>
            <div>
              <div class="text-muted small fw-semibold">Mendekati Pensiun</div>
              <div class="fs-3 fw-bolder text-dark">{{ $totalMendekatiPensiun }}</div>
              <span class="badge bg-danger-subtle text-danger">Rentang &le; 2 Tahun</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- HIGHLIGHT ULANG TAHUN GURU & TENDIK (H-5 S.D. HARI H) --}}
    <div class="card border-0 shadow rounded-4 mb-4 text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #1d4ed8 100%);">
      <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
          <div class="d-flex align-items-center gap-3">
            <div class="p-3 rounded-4 bg-white bg-opacity-25 text-warning">
              <i class="fas fa-birthday-cake fa-2x"></i>
            </div>
            <div>
              <h5 class="fw-bold mb-0 text-white">Highlight Ulang Tahun Pendidik & Tenaga Kependidikan</h5>
              <p class="small text-white-50 mb-0">Menampilkan personil yang berulang tahun mulai H-5 sampai Hari H</p>
            </div>
          </div>
          <span class="badge bg-white bg-opacity-25 text-white px-3 py-2 rounded-pill">
            <i class="fab fa-whatsapp me-1"></i> Terintegrasi WhatsApp
          </span>
        </div>

        <div class="row g-3">
          @forelse($birthdays as $b)
          <div class="col-md-4">
            <div class="p-3 rounded-4 h-100 d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);">
              <div>
                @if($b->diffDays === 0)
                  <span class="badge bg-danger text-white mb-2">HARI INI!</span>
                @elseif($b->diffDays === 1)
                  <span class="badge bg-warning text-dark mb-2">Besok (H-1)</span>
                @else
                  <span class="badge bg-warning text-dark mb-2">H-{{ $b->diffDays }} ({{ $b->diffDays }} hari lagi)</span>
                @endif
                <h6 class="fw-bold text-white mb-1">{{ $b->pegawai->nama }}</h6>
                <p class="small text-white-50 mb-3">
                  {{ ucfirst($b->pegawai->kategori) }} &bull; Tgl Lahir: {{ $b->tglShort }} (Ke-{{ $b->turningAge }} Thn)
                </p>
              </div>
              @if($b->waUrl)
                <a href="{{ $b->waUrl }}" target="_blank" class="btn btn-success btn-sm fw-bold rounded-pill w-100">
                  <i class="fab fa-whatsapp me-1"></i> Kirim Ucapan WhatsApp
                </a>
              @else
                <button type="button" class="btn btn-light btn-sm rounded-pill w-100 disabled">No. WA belum diisi</button>
              @endif
            </div>
          </div>
          @empty
          <div class="col-12">
            <div class="p-3 rounded-4 small" style="background: rgba(255,255,255,0.12);">
              <i class="far fa-calendar me-2 text-warning"></i>
              Tidak ada pendidik maupun tenaga kependidikan yang berulang tahun dalam rentang H-5 sampai Hari H saat ini (atau tanggal lahir belum diisi di Data Guru/Tendik).
            </div>
          </div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- 2 CHARTS: KOMPOSISI STATUS & GOLONGAN --}}
    <div class="row g-3 mb-4">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Komposisi Status Ketenagaan</h6>
            <span class="text-muted small">PNS, PPPK & Honorer</span>
          </div>
          <div class="card-body" style="height: 280px;">
            <canvas id="sdmStatusChart"></canvas>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-bar text-primary me-2"></i>Distribusi Pangkat & Golongan ASN</h6>
            <span class="text-muted small">Golongan IV, III & PPPK</span>
          </div>
          <div class="card-body" style="height: 280px;">
            <canvas id="sdmGolonganChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    {{-- TABEL PREDIKSI PENSIUN --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
          <i class="fas fa-user-clock text-warning me-2"></i>Prediksi & Peringatan Batas Pensiun Guru (BUP 60 Thn) & Tendik (58 Thn)
        </h6>
        <span class="text-muted small">Kalkulasi Otomatis Berdasarkan Tanggal Lahir</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Nama Pegawai</th>
                <th>NIP</th>
                <th>Kategori</th>
                <th>Tanggal Lahir</th>
                <th>Usia Saat Ini</th>
                <th>Tahun Pensiun</th>
                <th>Sisa Masa Kerja</th>
                <th class="text-center">Status Peringatan</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pensiunList as $p)
              <tr>
                <td class="fw-bold">{{ $p->nama }}</td>
                <td class="font-monospace small">{{ $p->nip ?: '-' }}</td>
                <td><span class="badge bg-secondary">{{ ucfirst($p->kategori) }}</span></td>
                <td>{{ $p->tanggal_lahir ? $p->tanggal_lahir->format('Y-m-d') : '-' }}</td>
                <td class="fw-bold">{{ $p->usia }} Thn</td>
                <td class="fw-bold text-primary">{{ $p->tahun_pensiun }}</td>
                <td class="fw-semibold">{{ $p->sisa_tahun_pensiun <= 0 ? 'Tahun Ini / Lewat' : $p->sisa_tahun_pensiun . ' Tahun' }}</td>
                <td class="text-center">
                  @if($p->sisa_tahun_pensiun <= 1)
                    <span class="badge bg-danger">Segera Pensiun</span>
                  @else
                    <span class="badge bg-warning text-dark">Persiapan BUP</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                  Tidak ada pegawai yang memasuki masa pensiun dalam 3 tahun ke depan (pastikan tanggal lahir pegawai sudah diisi).
                </td>
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
  const statusData = @json($statusCounts);
  const golData = @json($golonganCounts);

  new Chart(document.getElementById('sdmStatusChart'), {
    type: 'doughnut',
    data: {
      labels: Object.keys(statusData),
      datasets: [{
        data: Object.values(statusData),
        backgroundColor: ['#4f46e5', '#06b6d4', '#f59e0b'],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom' } }
    }
  });

  new Chart(document.getElementById('sdmGolonganChart'), {
    type: 'bar',
    data: {
      labels: Object.keys(golData),
      datasets: [{
        label: 'Jumlah Personil',
        data: Object.values(golData),
        backgroundColor: ['#4338ca', '#6366f1', '#0ea5e9', '#14b8a6', '#f59e0b'],
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });
});
</script>
@endsection
