@extends('layouts.master')

@section('content')
<style>
  .live-card {
    border-radius: 12px;
    transition: all 0.2s ease-in-out;
  }
  .poll-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
  }
  .poll-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    border-color: #cbd5e1;
  }
  .poll-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
  }
  .poll-title {
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .poll-count-badge {
    font-size: 12px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
  }
  .poll-bar-bg {
    width: 100%;
    background-color: #f1f5f9;
    height: 10px;
    border-radius: 5px;
    overflow: hidden;
  }
  .poll-bar-fill {
    height: 100%;
    border-radius: 5px;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .user-list-container {
    display: none;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px dashed #e2e8f0;
  }
  .user-item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 8px;
    border-radius: 6px;
    font-size: 13px;
    transition: background 0.15s;
  }
  .user-item-row:hover {
    background-color: #f8fafc;
  }
</style>

<section class="content pt-3">
  <div class="container-fluid">

    @if(session('sukses'))
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fa fa-check-circle me-2"></i> {{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Top Header Bar -->
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; background: linear-gradient(135deg, #004d1a 0%, #007a29 100%); color: #ffffff;">
      <div class="card-body p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div>
            <div class="badge bg-white text-success fw-bold px-2 py-1 mb-2" style="font-size: 11px;">
              <i class="fas fa-broadcast-tower me-1"></i> LIVE MONITORING KEBERADAAN GURU
            </div>
            <h3 class="fw-bold mb-1" style="letter-spacing: -0.5px;">
              {{ $hariIndonesia }}
            </h3>
            <p class="mb-0 text-white-50 small">
              <i class="fas fa-info-circle me-1 text-warning"></i>
              <strong>Abaikan formulir jika Anda hadir di sekolah.</strong> Seluruh guru yang tidak mengajukan izin otomatis tercatat <strong>Hadir di Sekolah</strong>.
            </p>
          </div>

          <!-- Date Filter Form & Action Buttons -->
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="/ijin/live" method="GET" class="d-flex gap-1 align-items-center bg-white p-1 rounded-3 shadow-sm">
              <input type="date" name="tanggal" value="{{ $targetDateStr }}" class="form-control form-control-sm border-0" style="font-size: 13px;" onchange="this.form.submit()">
              <button type="submit" class="btn btn-sm btn-success px-2 py-1" title="Lihat Tanggal Ini">
                <i class="fas fa-search"></i>
              </button>
            </form>
            <button type="button" class="btn btn-warning btn-sm text-dark fw-bold shadow-sm" data-bs-toggle="collapse" data-bs-target="#formIzinCepat" aria-expanded="false">
              <i class="fas fa-paper-plane me-1"></i> Form Izin Hari Ini
            </button>
            <a href="/ijin" class="btn btn-outline-light btn-sm shadow-sm" title="Buka Daftar Riwayat Izin">
              <i class="fas fa-list me-1"></i> Tabel Riwayat
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Collapsible Quick Izin Form -->
    <div class="collapse mb-3" id="formIzinCepat">
      <div class="card shadow-sm border-warning" style="border-radius: 12px;">
        <div class="card-header bg-warning bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
          <span class="fw-bold text-dark"><i class="fas fa-edit me-1 text-warning"></i> Formulir Pengajuan Izin Cepat</span>
          <button type="button" class="btn-close btn-sm" data-bs-toggle="collapse" data-bs-target="#formIzinCepat"></button>
        </div>
        <div class="card-body p-3">
          <form action="/ijin/create" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tglmasuk" value="{{ $targetDateStr }}">

            <div class="row g-2">
              <div class="col-md-4">
                <label class="form-label small fw-bold">Nama Guru / Pegawai</label>
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'kurikulum' || auth()->user()->role == 'pembina' || auth()->user()->role == 'kesiswaan')
                  <select name="guru_id" class="form-control form-control-sm" required>
                    @foreach($allTeachers as $u)
                      <option value="{{ $u->id }}" {{ $u->id == auth()->id() ? 'selected' : '' }}>
                        {{ $u->name }}
                      </option>
                    @endforeach
                  </select>
                @else
                  <input type="text" name="guru" value="{{ auth()->user()->name }}" class="form-control form-control-sm bg-light" readonly>
                @endif
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold">Kategori Izin</label>
                <select name="sia" class="form-control form-control-sm" id="quickSia" onchange="toggleQuickFields()" required>
                  <option value="" disabled selected>Pilih Keterangan Izin...</option>
                  <option value="Tugas Kedinasan">Tugas Kedinasan (Dinas Luar)</option>
                  <option value="Sakit">Sakit</option>
                  <option value="Izin Terlambat">Izin Terlambat</option>
                  <option value="Izin Keluar Jam Dinas">Izin Keluar Saat Jam Dinas (Kembali)</option>
                  <option value="Izin Pulang Sebelum Waktunya">Izin Pulang Sebelum Waktunya</option>
                  <option value="Keperluan Pribadi">Keperluan Pribadi</option>
                  <option value="Cuti">Cuti</option>
                </select>
              </div>

              <!-- Bidang jam keluar dinamis -->
              <div class="col-md-4" id="quickJamKeluarGroup" style="display: none;">
                <div class="row g-1">
                  <div class="col-6">
                    <label class="form-label small fw-bold">Jam Keluar</label>
                    <input type="time" name="jam_keluar" class="form-control form-control-sm">
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold">Estimasi Kembali</label>
                    <input type="time" name="jam_kembali" class="form-control form-control-sm">
                  </div>
                </div>
              </div>

              <div class="col-md-4" id="quickJamTerlambatGroup" style="display: none;">
                <label class="form-label small fw-bold">Estimasi Jam Tiba</label>
                <input type="time" name="jam_terlambat" class="form-control form-control-sm">
              </div>

              <div class="col-md-4" id="quickKetGroup">
                <label class="form-label small fw-bold">Keterangan Singkat</label>
                <input type="text" name="ket" class="form-control form-control-sm" placeholder="Contoh: Mengikuti MGMP di SMPN 1 / Sakit Demam" required>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
              <button type="submit" class="btn btn-sm btn-success px-3 fw-bold" style="background-color: #00a884; border-color: #00a884;">
                <i class="fas fa-paper-plane me-1"></i> Kirim Izin
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Overview Counters -->
    <div class="row g-2 mb-3">
      <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 p-3 text-center" style="border-radius: 10px; background: #ffffff;">
          <span class="text-muted small text-uppercase fw-semibold">Total Guru & Tendik</span>
          <h3 class="fw-bold text-dark m-0 mt-1">{{ $totalTeachers }}</h3>
          <span class="text-muted small" style="font-size: 11px;">Data Master Pegawai</span>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 p-3 text-center" style="border-radius: 10px; background: #f0fdf4; border-left: 4px solid #16a34a !important;">
          <span class="text-success small text-uppercase fw-semibold">Hadir di Sekolah</span>
          <h3 class="fw-bold text-success m-0 mt-1">{{ $totalHadir }}</h3>
          <span class="text-success small fw-bold" style="font-size: 11px;">{{ $persenHadir }}% dari Total</span>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 p-3 text-center" style="border-radius: 10px; background: #eff6ff; border-left: 4px solid #2563eb !important;">
          <span class="text-primary small text-uppercase fw-semibold">Tugas Kedinasan</span>
          <h3 class="fw-bold text-primary m-0 mt-1">{{ count($kategoriList['Tugas Kedinasan']['members']) }}</h3>
          <span class="text-primary small" style="font-size: 11px;">Dinas Luar / Diklat</span>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 p-3 text-center" style="border-radius: 10px; background: #fff7ed; border-left: 4px solid #ea580c !important;">
          <span class="text-danger small text-uppercase fw-semibold">Izin / Sakit / Cuti</span>
          <h3 class="fw-bold text-danger m-0 mt-1">{{ $totalIzinCount }}</h3>
          <span class="text-danger small" style="font-size: 11px;">Berhalangan Hari Ini</span>
        </div>
      </div>
    </div>

    <!-- POLLING & STATUS BARS (ALA GOOGLE APPS SCRIPT) -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
      <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h6 class="fw-bold text-dark m-0">
            <i class="fas fa-poll-h me-2 text-primary"></i> Status Keberadaan Guru Hari Ini
          </h6>
          <small class="text-muted">Klik pada baris kategori di bawah untuk melihat daftar nama guru yang bersangkutan.</small>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2" onclick="toggleAllLists()" id="btnToggleAll">
            <i class="fas fa-expand-alt me-1"></i> Buka Semua Rincian
          </button>
        </div>
      </div>

      <div class="card-body p-3 p-md-4">

        <!-- 1. Kategori: HADIR DI SEKOLAH -->
        <div class="poll-item" onclick="toggleList('list-hadir')">
          <div class="poll-header">
            <div class="poll-title text-dark">
              <span class="badge rounded-circle p-2 bg-success text-white"><i class="fas fa-school"></i></span>
              <span>Hadir (Di Sekolah)</span>
              <span class="text-muted small fw-normal">(Otomatis tanpa izin)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="poll-count-badge bg-success bg-opacity-10 text-success fw-bold">
                {{ $totalHadir }} Guru ({{ $persenHadir }}%)
              </span>
              <i class="fas fa-chevron-down text-muted small transition-chevron" id="chevron-list-hadir"></i>
            </div>
          </div>
          <div class="poll-bar-bg">
            <div class="poll-bar-fill bg-success" style="width: {{ $persenHadir }}%;"></div>
          </div>

          <!-- Rincian Nama Guru Hadir -->
          <div class="user-list-container" id="list-hadir" onclick="event.stopPropagation();">
            <div class="row g-2">
              @forelse($hadirList as $idx => $g)
                <div class="col-md-4 col-sm-6">
                  <div class="user-item-row bg-white border rounded p-2">
                    <div class="d-flex align-items-center gap-2 text-truncate">
                      <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 11px;">
                        {{ strtoupper(substr($g['name'], 0, 1)) }}
                      </div>
                      <span class="fw-semibold text-dark text-truncate" title="{{ $g['name'] }}">{{ $g['name'] }}</span>
                    </div>
                    <span class="badge bg-light text-success border border-success" style="font-size: 9.5px;">Di Sekolah</span>
                  </div>
                </div>
              @empty
                <div class="col-12 text-center text-muted py-2">
                  <em>Belum ada data guru hadir.</em>
                </div>
              @endforelse
            </div>
          </div>
        </div>

        <!-- 2. Kategori: SELURUH KATEGORI IZIN -->
        @foreach($kategoriList as $key => $cat)
          @php
            $count = count($cat['members']);
            $persen = $totalTeachers > 0 ? round(($count / $totalTeachers) * 100, 1) : 0;
            $slugId = 'list-' . Str::slug($key);
          @endphp

          <div class="poll-item" onclick="toggleList('{{ $slugId }}')">
            <div class="poll-header">
              <div class="poll-title text-dark">
                <span class="badge rounded-circle p-2 text-white" style="background-color: {{ $cat['color'] }};">
                  <i class="{{ $cat['icon'] }}"></i>
                </span>
                <span>{{ $cat['label'] }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="poll-count-badge" style="background-color: {{ $count > 0 ? $cat['color'] : '#94a3b8' }}; color: #ffffff;">
                  {{ $count }} Guru ({{ $persen }}%)
                </span>
                <i class="fas fa-chevron-down text-muted small transition-chevron" id="chevron-{{ $slugId }}"></i>
              </div>
            </div>
            <div class="poll-bar-bg">
              <div class="poll-bar-fill" style="background-color: {{ $cat['bar_color'] }}; width: {{ $persen }}%;"></div>
            </div>

            <!-- Rincian Nama Guru Izin -->
            <div class="user-list-container" id="{{ $slugId }}" onclick="event.stopPropagation();">
              @if($count > 0)
                <div class="row g-2">
                  @foreach($cat['members'] as $m)
                    <div class="col-md-6">
                      <div class="p-2 border rounded bg-white shadow-sm">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                          <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; background-color: {{ $cat['color'] }}; font-size: 12px;">
                              {{ strtoupper(substr($m['name'], 0, 1)) }}
                            </div>
                            <div>
                              <div class="fw-bold text-dark" style="font-size: 13.5px;">{{ $m['name'] }}</div>
                              <div class="text-muted small" style="font-size: 11px;">
                                @if($m['mapel'] && $m['mapel'] !== '-')
                                  <i class="fas fa-book me-1"></i>{{ $m['mapel'] }} &bull;
                                @endif
                                <span class="text-primary fw-semibold">{{ $m['keterangan'] ?: 'Izin tercatat' }}</span>
                              </div>
                            </div>
                          </div>

                          <div class="text-end flex-shrink-0">
                            @if($m['jam'])
                              <span class="badge bg-light text-dark border d-block mb-1" style="font-size: 10.5px;">
                                <i class="far fa-clock me-1 text-muted"></i>{{ $m['jam'] }}
                              </span>
                            @endif
                            @if($m['attachment'])
                              <a href="{{ asset($m['attachment']) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 10px;">
                                <i class="fas fa-file-alt me-1"></i> Surat
                              </a>
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="text-center text-muted py-2 small">
                  <em>Tidak ada guru yang mengajukan {{ strtolower($cat['label']) }} hari ini.</em>
                </div>
              @endif
            </div>
          </div>
        @endforeach

      </div>
    </div>

  </div>
</section>

<script>
  function toggleList(id) {
    const list = document.getElementById(id);
    const chevron = document.getElementById('chevron-' + id);
    if (list) {
      if (list.style.display === 'block') {
        list.style.display = 'none';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
      } else {
        list.style.display = 'block';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
      }
    }
  }

  let allExpanded = false;
  function toggleAllLists() {
    const lists = document.querySelectorAll('.user-list-container');
    const chevrons = document.querySelectorAll('.transition-chevron');
    const btn = document.getElementById('btnToggleAll');

    allExpanded = !allExpanded;
    lists.forEach(l => {
      l.style.display = allExpanded ? 'block' : 'none';
    });
    chevrons.forEach(c => {
      c.style.transform = allExpanded ? 'rotate(180deg)' : 'rotate(0deg)';
    });

    if (btn) {
      btn.innerHTML = allExpanded 
        ? '<i class="fas fa-compress-alt me-1"></i> Tutup Semua Rincian'
        : '<i class="fas fa-expand-alt me-1"></i> Buka Semua Rincian';
    }
  }

  function toggleQuickFields() {
    const val = document.getElementById('quickSia').value;
    const keluarGrp = document.getElementById('quickJamKeluarGroup');
    const terlambatGrp = document.getElementById('quickJamTerlambatGroup');

    keluarGrp.style.display = 'none';
    terlambatGrp.style.display = 'none';

    if (val === 'Izin Keluar Jam Dinas') {
      keluarGrp.style.display = 'block';
    } else if (val === 'Izin Terlambat' || val === 'Izin Pulang Sebelum Waktunya') {
      terlambatGrp.style.display = 'block';
    }
  }
</script>
@endsection
