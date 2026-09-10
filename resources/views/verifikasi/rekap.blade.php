@extends('layouts.master')

@section('content')
<section class="content pt-3">
  <div class="container-fluid">

    <!-- Page Header Banner Card -->
    <div class="card shadow-sm border-0 rounded-3 mb-3 text-white" style="background: linear-gradient(135deg, #004d1a 0%, #006622 50%, #009638 100%);">
      <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: rgba(255,255,255,0.2);">
            <i class="fas fa-calendar-check fa-lg text-white"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0 text-white" style="font-size: 19px;">Rekap Verifikasi Absensi Pagi</h4>
            <p class="text-white-50 small mb-0">Monitoring kedisiplinan dan kepatuhan verifikasi kehadiran siswa setiap pagi per kelas</p>
          </div>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 px-3 py-1 rounded-pill" style="background: rgba(0,0,0,0.18); font-size: 12px;">
              <li class="breadcrumb-item"><a href="/dashboard" class="text-white text-decoration-none opacity-75"><i class="fas fa-home me-1"></i> Dashboard</a></li>
              <li class="breadcrumb-item active text-white fw-bold">Rekap Verifikasi Pagi</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>

    <!-- Filter & Statistik Card -->
    <div class="card shadow-sm border mb-4">
      <div class="card-header bg-light py-2">
        <h5 class="card-title m-0 fw-bold text-dark" style="font-size: 14px;">
          <i class="fas fa-filter text-primary me-1"></i> Filter Periode & Kelas
        </h5>
      </div>
      <div class="card-body py-3 px-3">
        <form method="GET" action="/rekap-verifikasi" class="row g-2 align-items-end">
          
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
            <a href="/rekap-verifikasi" class="btn btn-outline-secondary btn-sm px-2" title="Reset Filter">
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
                <div class="text-white-50 small fw-bold text-uppercase">Total Terverifikasi</div>
                <div class="fs-4 fw-bold mt-1">{{ $totalSudahSemua }} Kali</div>
                <div class="small text-white-50 mt-1">Oleh Wali / Ketua Kelas</div>
              </div>
              <div>
                <i class="fas fa-check-double fa-2x opacity-50"></i>
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
                <div class="text-white-50 small fw-bold text-uppercase">Tidak / Belum Verif</div>
                <div class="fs-4 fw-bold mt-1">{{ $totalTidakSemua }} Kali</div>
                <div class="small text-white-50 mt-1">Hari Terlewat / Lupa</div>
              </div>
              <div>
                <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
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
                <div class="text-white-50 small fw-bold text-uppercase">Tingkat Kedisiplinan</div>
                <div class="fs-4 fw-bold mt-1">{{ $rataRataKepatuhan }}%</div>
                <div class="small text-white-50 mt-1">Kepatuhan Verifikasi Pagi</div>
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
          <i class="fas fa-list-alt text-primary me-2"></i> Rekapitulasi Kedisiplinan Verifikasi Absensi Per Kelas
        </h5>
        <span class="badge bg-primary px-3 py-1">
          Periode: {{ \Carbon\Carbon::parse($selectedMonth . '-01')->isoFormat('MMMM Y') }}
        </span>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive p-2">
          <table id="tableRekapVerifikasi" class="table table-bordered table-striped table-hover align-middle m-0" style="font-size: 12.5px; width: 100%;">
            <thead class="table-light">
              <tr class="text-center">
                <th style="width: 5%;">No</th>
                <th style="width: 12%; cursor: pointer;" title="Klik untuk mengurutkan kelas">Kelas <i class="fas fa-sort ms-1 small text-muted"></i></th>
                <th style="width: 20%; cursor: pointer;" title="Klik untuk mengurutkan wali kelas">Wali Kelas <i class="fas fa-sort ms-1 small text-muted"></i></th>
                <th style="width: 13%; cursor: pointer;" title="Klik untuk mengurutkan hari efektif">Hari Efektif <i class="fas fa-sort ms-1 small text-muted"></i></th>
                <th style="width: 15%; cursor: pointer;" title="Klik untuk mengurutkan verifikasi selesai">Sudah Verifikasi <i class="fas fa-sort ms-1 small text-muted"></i></th>
                <th style="width: 15%; cursor: pointer;" title="Klik untuk mengurutkan tidak/belum verifikasi">Tidak/Belum Verif <i class="fas fa-sort ms-1 small text-muted"></i></th>
                <th style="width: 12%; cursor: pointer;" title="Klik untuk mengurutkan persentase kepatuhan">% Kepatuhan <i class="fas fa-sort ms-1 small text-muted"></i></th>
                <th style="width: 8%;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rekapPerKelas as $idx => $r)
                @php
                  $modalId = 'modalRincian_' . Str::slug($r['kelas']);
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
                  <td class="text-center fw-bold text-dark" style="font-size: 14px;" data-order="{{ $r['kelas'] }}">Kelas {{ $r['kelas'] }}</td>
                  <td data-order="{{ $r['walikelas'] }}">{{ $r['walikelas'] }}</td>
                  <td class="text-center fw-semibold" data-order="{{ $r['total_hari'] }}">{{ $r['total_hari'] }} Hari</td>
                  <td class="text-center" data-order="{{ $r['sudah_verifikasi'] }}">
                    <span class="badge bg-success px-2 py-1" style="font-size: 12px;">
                      <i class="fas fa-check-circle me-1"></i> {{ $r['sudah_verifikasi'] }} Kali
                    </span>
                  </td>
                  <td class="text-center" data-order="{{ $r['tidak_verifikasi'] }}">
                    @if($r['tidak_verifikasi'] > 0)
                      <span class="badge bg-danger px-2 py-1" style="font-size: 12px;">
                        <i class="fas fa-times-circle me-1"></i> {{ $r['tidak_verifikasi'] }} Kali
                      </span>
                    @else
                      <span class="badge bg-secondary px-2 py-1" style="font-size: 12px;">
                        <i class="fas fa-check me-1"></i> 0 Kali (Nihil)
                      </span>
                    @endif
                  </td>
                  <td data-order="{{ $r['persentase'] }}">
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
                            title="Lihat rincian tanggal per kelas">
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
    $modalId = 'modalRincian_' . Str::slug($r['kelas']);
  @endphp
  <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white py-2 px-3">
          <h6 class="modal-title font-weight-bold mb-0" id="{{ $modalId }}Label" style="font-size: 15px;">
            <i class="fas fa-calendar-check me-2"></i> Rincian Tanggal Verifikasi Absensi - Kelas {{ $r['kelas'] }}
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
              <span class="badge bg-success px-2 py-1" style="font-size: 12px;">Sudah: {{ $r['sudah_verifikasi'] }} Hari</span>
              <span class="badge bg-danger px-2 py-1" style="font-size: 12px;">Tidak: {{ $r['tidak_verifikasi'] }} Hari</span>
              <span class="badge px-2 py-1" style="background-color: #7c3aed; color: #ffffff !important; font-size: 12px; font-weight: 700;">Kepatuhan: {{ $r['persentase'] }}%</span>
            </div>
          </div>

          <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
            <table class="table table-bordered table-striped table-hover table-sm align-middle m-0" style="font-size: 12px;">
              <thead class="table-light sticky-top" style="z-index: 1;">
                <tr class="text-center">
                  <th style="width: 5%;">No</th>
                  <th style="width: 25%;">Hari & Tanggal</th>
                  <th style="width: 18%;">Status Verifikasi</th>
                  <th style="width: 10%;">Jam</th>
                  <th style="width: 18%;">Diverifikasi Oleh</th>
                  <th style="width: 24%;">Detail Kehadiran</th>
                </tr>
              </thead>
              <tbody>
                @foreach($r['rincian'] as $i => $hari)
                  <tr class="{{ $hari['status_verif'] === 'TIDAK' ? 'table-warning' : '' }}">
                    <td class="text-center fw-bold">{{ $i + 1 }}</td>
                    <td class="fw-semibold text-dark">{{ $hari['tanggal_format'] }}</td>
                    <td class="text-center">
                      @if($hari['status_verif'] === 'SUDAH')
                        <span class="badge bg-success px-2 py-1" style="font-size: 11px;">
                          <i class="fas fa-check-circle me-1"></i> Sudah Verifikasi
                        </span>
                      @else
                        <span class="badge bg-danger px-2 py-1" style="font-size: 11px;">
                          <i class="fas fa-times-circle me-1"></i> Belum / Tidak Verif
                        </span>
                      @endif
                    </td>
                    <td class="text-center text-muted">{{ $hari['jam'] }}</td>
                    <td class="text-center">{{ $hari['verified_by'] }}</td>
                    <td>
                      @if($hari['status_verif'] === 'SUDAH')
                        @if($hari['status_absen'] === 'NIHIL')
                          <span class="badge bg-success me-1">Hadir Semua</span>
                        @else
                          <span class="badge bg-primary me-1">{{ $hari['hadir'] }} / {{ $hari['total'] }} Hadir</span>
                        @endif
                        <span class="small text-secondary">{{ $hari['detail'] }}</span>
                      @else
                        <span class="text-danger small fst-italic"><i class="fas fa-exclamation-triangle me-1"></i>Tidak melakukan verifikasi pagi</span>
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

@push('scripts')
<style>
  #tableRekapVerifikasi thead th {
    user-select: none;
    -webkit-user-select: none;
  }
  #tableRekapVerifikasi thead th:not(:first-child):not(:last-child) {
    cursor: pointer !important;
    transition: background-color 0.15s ease;
  }
  #tableRekapVerifikasi thead th:not(:first-child):not(:last-child):hover {
    background-color: #e2e8f0 !important;
  }
</style>

<script>
$(document).ready(function() {
  initRekapVerifikasiTable();
});

function initRekapVerifikasiTable() {
  if (typeof $.fn.DataTable !== 'undefined') {
    if ($.fn.DataTable.isDataTable('#tableRekapVerifikasi')) {
      $('#tableRekapVerifikasi').DataTable().destroy();
    }
    var table = $('#tableRekapVerifikasi').DataTable({
      paging: false,
      searching: true,
      info: false,
      order: [[6, 'desc']], // Default: urut dari % Kepatuhan tertinggi (kelas paling rajin)
      columnDefs: [
        { orderable: false, targets: [0, 7] }, // Kolom No dan Aksi tidak disortir
        { targets: [3, 4, 5, 6], type: 'num' }
      ],
      language: {
        search: "<i class='fas fa-search me-1 text-secondary'></i> Cari Kelas / Wali:",
        searchPlaceholder: "Ketik nama kelas / wali..."
      }
    });

    // Perbarui nomor urut saat tabel disortir atau dicari
    table.on('order.dt search.dt', function () {
      table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
        cell.innerHTML = '<strong>' + (i + 1) + '</strong>';
      });
    });
  } else {
    // Fallback sorting Vanilla JS murni jika DataTables belum siap
    initFallbackSortingVerif('tableRekapVerifikasi');
  }
}

function initFallbackSortingVerif(tableId) {
  var table = document.getElementById(tableId);
  if (!table) return;
  var headers = table.querySelectorAll('thead th');
  var tbody = table.querySelector('tbody');
  if (!tbody) return;

  var currentSortCol = 6;
  var isAsc = false;

  headers.forEach(function(th, colIdx) {
    if (colIdx === 0 || colIdx === 7) return; // Lewati kolom No & Aksi
    th.addEventListener('click', function() {
      if (currentSortCol === colIdx) {
        isAsc = !isAsc;
      } else {
        currentSortCol = colIdx;
        isAsc = false; // Default klik pertama: tertinggi / rajin (desc)
      }

      var rows = Array.from(tbody.querySelectorAll('tr'));
      if (rows.length <= 1) return;

      rows.sort(function(a, b) {
        var cellA = a.children[colIdx];
        var cellB = b.children[colIdx];
        if (!cellA || !cellB) return 0;

        var valA = cellA.getAttribute('data-order') !== null ? cellA.getAttribute('data-order') : cellA.innerText.trim();
        var valB = cellB.getAttribute('data-order') !== null ? cellB.getAttribute('data-order') : cellB.innerText.trim();

        var numA = parseFloat(valA);
        var numB = parseFloat(valB);

        var comparison = 0;
        if (!isNaN(numA) && !isNaN(numB)) {
          comparison = numA - numB;
        } else {
          comparison = valA.localeCompare(valB, 'id', { numeric: true });
        }
        return isAsc ? comparison : -comparison;
      });

      rows.forEach(function(row, idx) {
        tbody.appendChild(row);
        if (row.children[0]) {
          row.children[0].innerHTML = '<strong>' + (idx + 1) + '</strong>';
        }
      });
    });
  });
}
</script>
@endpush
