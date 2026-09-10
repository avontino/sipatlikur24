@extends('layouts.master')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark font-weight-bold" style="font-size: 20px;">
          <i class="fas fa-book-reader text-primary me-2"></i> Rekap Pengisian Jurnal Per Kelas
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Rekap Pengisian Jurnal</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- Filter & Statistik Card -->
    <div class="card shadow-sm border mb-4">
      <div class="card-header bg-light py-2">
        <h5 class="card-title m-0 fw-bold text-dark" style="font-size: 14px;">
          <i class="fas fa-filter text-primary me-1"></i> Filter Periode & Kelas
        </h5>
      </div>
      <div class="card-body py-3 px-3">
        <form method="GET" action="/rekap-pengisian-jurnal" class="row g-2 align-items-end">
          
          <!-- Filter Bulan -->
          <div class="col-md-3 col-sm-6">
            <label class="form-label small fw-bold text-secondary mb-1">Pilih Bulan & Tahun</label>
            <input type="month" name="bulan" class="form-control form-control-sm" value="{{ $selectedMonth }}">
          </div>

          @if(!$isOnlyWali)
            <!-- Filter Tingkat -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-secondary mb-1">Tingkat Kelas</label>
              <select name="tingkat" class="form-select form-select-sm">
                <option value="all" {{ request('tingkat') == 'all' ? 'selected' : '' }}>Semua Tingkat</option>
                <option value="7" {{ request('tingkat') == '7' ? 'selected' : '' }}>Kelas 7</option>
                <option value="8" {{ request('tingkat') == '8' ? 'selected' : '' }}>Kelas 8</option>
                <option value="9" {{ request('tingkat') == '9' ? 'selected' : '' }}>Kelas 9</option>
              </select>
            </div>

            <!-- Filter Kelas Spesifik -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-secondary mb-1">Pilih Kelas</label>
              <select name="kelas" class="form-select form-select-sm">
                <option value="all">Semua Kelas</option>
                @foreach($allKelasList as $kls)
                  <option value="{{ $kls }}" {{ request('kelas') == $kls ? 'selected' : '' }}>Kelas {{ $kls }}</option>
                @endforeach
              </select>
            </div>
          @else
            <div class="col-md-3 col-sm-6">
              <label class="form-label small fw-bold text-secondary mb-1">Kelas Perwalian</label>
              <input type="text" class="form-control form-control-sm bg-light" value="Kelas {{ $managedClass }}" readonly>
            </div>
          @endif

          <div class="col-md-3 col-sm-6 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">
              <i class="fas fa-search me-1"></i> Tampilkan
            </button>
            <a href="/rekap-pengisian-jurnal" class="btn btn-outline-secondary btn-sm px-2" title="Reset Filter">
              <i class="fas fa-undo"></i>
            </a>
          </div>
        </form>
      </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-3 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-white-50 small fw-bold text-uppercase">Hari Efektif KBM</div>
                <div class="fs-4 fw-bold mt-1">{{ $totalHariEfektif }} Hari</div>
                <div class="small text-white-50 mt-1">{{ \Carbon\Carbon::parse($selectedMonth . '-01')->isoFormat('MMMM Y') }}</div>
              </div>
              <div>
                <i class="far fa-calendar-alt fa-2x opacity-50"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-3 text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-white-50 small fw-bold text-uppercase">Total Jurnal Terisi</div>
                <div class="fs-4 fw-bold mt-1">{{ $totalSudahSemua }} Kali</div>
                <div class="small text-white-50 mt-1">Hari KBM Terisi</div>
              </div>
              <div>
                <i class="fas fa-book-open fa-2x opacity-50"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-3 text-white" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-white-50 small fw-bold text-uppercase">Total Jurnal Kosong</div>
                <div class="fs-4 fw-bold mt-1">{{ $totalTidakSemua }} Kali</div>
                <div class="small text-white-50 mt-1">Hari Terlewat / Belum Diisi</div>
              </div>
              <div>
                <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-3 text-white" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-white-50 small fw-bold text-uppercase">Tingkat Kepatuhan</div>
                <div class="fs-4 fw-bold mt-1">{{ $rataRataKepatuhan }}%</div>
                <div class="small text-white-50 mt-1">Kepatuhan Pengisian Jurnal</div>
              </div>
              <div>
                <i class="fas fa-chart-line fa-2x opacity-50"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow mb-4">
      <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title m-0 fw-bold text-dark" style="font-size: 15px;">
          <i class="fas fa-list-alt text-primary me-2"></i> Rekapitulasi Pengisian Jurnal Per Kelas
        </h5>
        <span class="badge bg-primary px-3 py-1">
          Periode: {{ \Carbon\Carbon::parse($selectedMonth . '-01')->isoFormat('MMMM Y') }}
        </span>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover align-middle m-0" style="font-size: 12.5px;">
            <thead class="table-light">
              <tr class="text-center">
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Kelas</th>
                <th style="width: 20%;">Wali Kelas</th>
                <th style="width: 13%;">Hari Efektif</th>
                <th style="width: 15%;">Sudah Mengisi</th>
                <th style="width: 15%;">Tidak/Belum Mengisi</th>
                <th style="width: 12%;">% Kepatuhan</th>
                <th style="width: 8%;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rekapPerKelas as $idx => $r)
                @php
                  $modalId = 'modalRincianJurnal_' . Str::slug($r['kelas']);
                  $progressColor = 'bg-danger';
                  if ($r['persentase'] >= 85) {
                    $progressColor = 'bg-success';
                  } elseif ($r['persentase'] >= 70) {
                    $progressColor = 'bg-info';
                  } elseif ($r['persentase'] >= 50) {
                    $progressColor = 'bg-warning text-dark';
                  }
                @endphp
                <tr>
                  <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                  <td class="text-center fw-bold text-dark" style="font-size: 14px;">Kelas {{ $r['kelas'] }}</td>
                  <td>{{ $r['walikelas'] }}</td>
                  <td class="text-center fw-semibold">{{ $r['total_hari'] }} Hari</td>
                  <td class="text-center">
                    <span class="badge bg-success px-2 py-1" style="font-size: 12px;">
                      <i class="fas fa-check-circle me-1"></i> {{ $r['sudah_mengisi'] }} Kali
                    </span>
                  </td>
                  <td class="text-center">
                    @if($r['tidak_mengisi'] > 0)
                      <span class="badge bg-danger px-2 py-1" style="font-size: 12px;">
                        <i class="fas fa-times-circle me-1"></i> {{ $r['tidak_mengisi'] }} Kali
                      </span>
                    @else
                      <span class="badge bg-secondary px-2 py-1" style="font-size: 12px;">
                        <i class="fas fa-check me-1"></i> 0 Kali (Nihil)
                      </span>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="progress flex-grow-1" style="height: 8px;">
                        <div class="progress-bar {{ $progressColor }}" role="progressbar" style="width: {{ $r['persentase'] }}%;" aria-valuenow="{{ $r['persentase'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <span class="fw-bold small" style="width: 38px;">{{ $r['persentase'] }}%</span>
                    </div>
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size: 11px; border-radius: 6px;"
                            data-bs-toggle="modal" data-bs-target="#{{ $modalId }}"
                            data-toggle="modal" data-target="#{{ $modalId }}"
                            title="Lihat rincian tanggal pengisian jurnal">
                      <i class="fas fa-calendar-alt me-1"></i> Rincian
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i> Tidak ada data kelas untuk ditampilkan pada periode ini.
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

<!-- Modals Rincian Tanggal Per Kelas -->
@foreach($rekapPerKelas as $r)
  @php
    $modalId = 'modalRincianJurnal_' . Str::slug($r['kelas']);
  @endphp
  <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white py-2 px-3">
          <h6 class="modal-title font-weight-bold mb-0" id="{{ $modalId }}Label" style="font-size: 15px;">
            <i class="fas fa-book-open me-2"></i> Rincian Harian Pengisian Jurnal - Kelas {{ $r['kelas'] }}
          </h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body p-3">
          <!-- Mini Stats Header -->
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-2 mb-3 rounded bg-light border">
            <div>
              <span class="fw-bold text-dark me-2">Wali Kelas:</span>
              <span class="text-muted">{{ $r['walikelas'] }}</span>
              <span class="mx-2 text-muted">|</span>
              <span class="fw-bold text-dark me-2">Periode:</span>
              <span class="text-primary fw-semibold">{{ \Carbon\Carbon::parse($selectedMonth . '-01')->isoFormat('MMMM Y') }}</span>
            </div>
            <div class="d-flex gap-2">
              <span class="badge bg-success px-2 py-1" style="font-size: 12px;">Sudah: {{ $r['sudah_mengisi'] }} Hari</span>
              <span class="badge bg-danger px-2 py-1" style="font-size: 12px;">Tidak: {{ $r['tidak_mengisi'] }} Hari</span>
              <span class="badge px-2 py-1" style="background-color: #7c3aed; color: #ffffff !important; font-size: 12px; font-weight: 700;">Kepatuhan: {{ $r['persentase'] }}%</span>
            </div>
          </div>

          <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
            <table class="table table-bordered table-striped table-hover table-sm align-middle m-0" style="font-size: 12px;">
              <thead class="table-light sticky-top" style="z-index: 1;">
                <tr class="text-center">
                  <th style="width: 5%;">No</th>
                  <th style="width: 25%;">Hari & Tanggal</th>
                  <th style="width: 18%;">Status Pengisian</th>
                  <th style="width: 18%;">Jumlah Jam/Mapel</th>
                  <th style="width: 34%;">Rincian Mapel & Guru yang Mengajar</th>
                </tr>
              </thead>
              <tbody>
                @foreach($r['rincian'] as $i => $hari)
                  <tr class="{{ $hari['status'] === 'TIDAK' ? 'table-warning' : '' }}">
                    <td class="text-center fw-bold">{{ $i + 1 }}</td>
                    <td class="fw-semibold text-dark">{{ $hari['tanggal_format'] }}</td>
                    <td class="text-center">
                      @if($hari['status'] === 'SUDAH')
                        <span class="badge bg-success px-2 py-1" style="font-size: 11px;">
                          <i class="fas fa-check-circle me-1"></i> Jurnal Terisi
                        </span>
                      @else
                        <span class="badge bg-danger px-2 py-1" style="font-size: 11px;">
                          <i class="fas fa-times-circle me-1"></i> Belum Diisi / Kosong
                        </span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($hari['status'] === 'SUDAH')
                        <span class="badge bg-primary px-2 py-1" style="font-size: 11px;">
                          {{ $hari['jumlah_jam'] }}
                        </span>
                      @else
                        <span class="text-muted small">-</span>
                      @endif
                    </td>
                    <td>
                      @if($hari['status'] === 'SUDAH')
                        <span class="small text-dark">{{ $hari['detail_mapel'] }}</span>
                      @else
                        <span class="text-danger small fst-italic"><i class="fas fa-exclamation-triangle me-1"></i>Jurnal kelas belum/tidak diisi</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer py-2 px-3 bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
@endforeach

@endsection
