@extends('layouts.master')

@section('content')
<div class="content-header py-4 px-3 mb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-7">
        <div class="d-flex align-items-center">
          <div class="me-3 p-3 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #009638, #004d1a); color: #fff;">
            <i class="fas fa-graduation-cap fa-2x"></i>
          </div>
          <div>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 mb-1 fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">
              <i class="fas fa-chalkboard-teacher me-1 text-warning"></i> MANAJEMEN ASESMEN
            </span>
            <h2 class="m-0 text-dark fw-bolder" style="letter-spacing: -0.5px; font-size: 1.65rem;">
              Data Nilai TKA Siswa Kelas 9
            </h2>
            <p class="text-muted small mb-0 mt-1">Kelola nilai Diagnostik & Try Out, filter kelas, import data Excel, dan cetak rapor</p>
          </div>
        </div>
      </div>
      <div class="col-sm-5 mt-3 mt-sm-0 text-sm-end">
        <ol class="breadcrumb float-sm-end bg-white px-3 py-2 rounded-pill shadow-sm border mb-0">
          <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none text-secondary"><i class="fas fa-home me-1"></i> Dashboard</a></li>
          <li class="breadcrumb-item active text-success fw-bold">Data TKA Kelas 9</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('gagal'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-1"></i> {{ session('gagal') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- STATISTIK WIDGET --}}
    <div class="row mb-3">
      <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0">
          <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
          <div class="info-box-content">
            <span class="info-box-text text-muted">Total Siswa Kelas 9</span>
            <span class="info-box-number fs-4 text-dark">{{ $totalSiswa }} <small>Siswa</small></span>
          </div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0">
          <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-clipboard-check"></i></span>
          <div class="info-box-content">
            <span class="info-box-text text-muted">Data Tes Diagnostik</span>
            <span class="info-box-number fs-4 text-dark">{{ $totalDiagnostik }} <small>Data</small></span>
          </div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0">
          <span class="info-box-icon bg-success elevation-1"><i class="fas fa-pencil-alt"></i></span>
          <div class="info-box-content">
            <span class="info-box-text text-muted">Data Try Out 1 (TO-1)</span>
            <span class="info-box-number fs-4 text-dark">{{ $totalTO1 }} <small>Data</small></span>
          </div>
        </div>
      </div>
    </div>

    {{-- CARD TABEL & FILTER --}}
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h5 class="fw-bold mb-0 text-dark">
            <i class="fas fa-table me-2 text-primary"></i>Rekapitulasi Nilai TKA (Diagnostik & TO-1)
          </h5>

          @if(in_array(auth()->user()->role, ['admin', 'kurikulum']))
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahManual">
              <i class="fas fa-plus-circle me-1"></i> Tambah Nilai Manual
            </button>
            <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
              <i class="fas fa-file-excel me-1"></i> Import / Update Nilai Excel
            </button>
          </div>
          @endif
        </div>
      </div>

      <div class="card-body">
        {{-- FORM FILTER --}}
        <form method="GET" action="/tka" class="row g-2 mb-4 align-items-center">
          <div class="col-md-3 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">Filter Kelas:</label>
            <select name="kelas" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="all" {{ $selectedKelas === 'all' ? 'selected' : '' }}>-- Semua Kelas 9 --</option>
              @foreach($kelasList as $k)
                <option value="{{ $k }}" {{ $selectedKelas === $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">Jenis Asesmen:</label>
            <select name="jenis_tes" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="all" {{ $selectedTes === 'all' ? 'selected' : '' }}>-- Semua Jenis Tes --</option>
              <option value="diagnostik" {{ $selectedTes === 'diagnostik' ? 'selected' : '' }}>Tes Diagnostik</option>
              <option value="to_1" {{ $selectedTes === 'to_1' ? 'selected' : '' }}>Try Out 1 (TO-1)</option>
            </select>
          </div>

          <div class="col-md-4 col-sm-8">
            <label class="form-label small fw-bold text-muted mb-1">Cari Siswa:</label>
            <div class="input-group input-group-sm">
              <input type="text" name="search" class="form-control" placeholder="Ketik Nama / NIS..." value="{{ $search }}">
              <button class="btn btn-primary" type="submit">
                <i class="fas fa-search me-1"></i> Cari
              </button>
              @if(!empty($search) || $selectedKelas !== 'all' || $selectedTes !== 'all')
              <a href="/tka" class="btn btn-outline-secondary" title="Reset Filter">
                <i class="fas fa-redo"></i>
              </a>
              @endif
            </div>
          </div>
        </form>

        {{-- TABEL DATA --}}
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle mb-3">
            <thead class="table-light text-center">
              <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 9%;">NIS</th>
                <th style="width: 25%;" class="text-start">Nama Siswa</th>
                <th style="width: 8%;">Kelas</th>
                <th style="width: 14%;">Jenis Tes</th>
                <th style="width: 12%;">Bahasa Indonesia</th>
                <th style="width: 12%;">Matematika</th>
                <th style="width: 8%;">Rata-rata</th>
                <th style="width: 8%;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($data_tka as $index => $tka)
              <tr>
                <td class="text-center">{{ $data_tka->firstItem() + $index }}</td>
                <td class="text-center font-monospace">{{ $tka->nis }}</td>
                <td class="fw-bold text-dark">{{ $tka->nama }}</td>
                <td class="text-center"><span class="badge bg-secondary">{{ $tka->kelas }}</span></td>
                <td class="text-center">
                  @if($tka->jenis_tes === 'diagnostik')
                    <span class="badge bg-primary">Diagnostik</span>
                  @elseif($tka->jenis_tes === 'to_1')
                    <span class="badge bg-success">Try Out 1</span>
                  @else
                    <span class="badge bg-info">{{ $tka->nama_tes }}</span>
                  @endif
                </td>
                <td class="text-center">
                  <div class="fw-bold">{{ $tka->nilai_bindo }}</div>
                  <span class="badge {{ $tka->badge_class_bindo }} px-2 py-0" style="font-size: 10px;">
                    {{ $tka->ket_bindo ?: '-' }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="fw-bold">{{ $tka->nilai_mtk }}</div>
                  <span class="badge {{ $tka->badge_class_mtk }} px-2 py-0" style="font-size: 10px;">
                    {{ $tka->ket_mtk ?: '-' }}
                  </span>
                </td>
                <td class="text-center fw-bold text-primary">{{ $tka->nilai_rata_rata }}</td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="/tka/{{ $tka->id }}/pdf" class="btn btn-outline-danger" title="Download PDF Tes Ini">
                      <i class="fas fa-file-pdf"></i>
                    </a>
                    <a href="/tka/rekap/{{ $tka->nis }}/pdf" class="btn btn-outline-warning text-dark" title="Download Rekap Lengkap Siswa">
                      <i class="fas fa-print"></i>
                    </a>
                    @if(in_array(auth()->user()->role, ['admin', 'kurikulum']))
                    <button type="button" class="btn btn-outline-primary" title="Edit Nilai"
                            data-bs-toggle="modal" data-bs-target="#modalEditNilai{{ $tka->id }}">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger" title="Hapus Nilai"
                            onclick="if(confirm('Apakah Anda yakin ingin menghapus data nilai TKA untuk {{ $tka->nama }} ({{ $tka->nama_tes }})?')) { document.getElementById('delete-tka-{{ $tka->id }}').submit(); }">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                    <form id="delete-tka-{{ $tka->id }}" action="/tka/{{ $tka->id }}" method="POST" style="display: none;">
                      @csrf
                      @method('DELETE')
                    </form>
                    @endif
                  </div>

                  @if(in_array(auth()->user()->role, ['admin', 'kurikulum']))
                  {{-- MODAL EDIT PER SISWA --}}
                  <div class="modal fade text-start" id="modalEditNilai{{ $tka->id }}" tabindex="-1" aria-labelledby="modalEditLabel{{ $tka->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                          <h6 class="modal-title fw-bold" id="modalEditLabel{{ $tka->id }}">
                            <i class="fas fa-edit me-1"></i> Edit Nilai TKA - {{ $tka->nama }}
                          </h6>
                          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="/tka/{{ $tka->id }}" method="POST">
                          @csrf
                          @method('PUT')
                          <div class="modal-body p-4">
                            <div class="bg-light p-3 rounded mb-3">
                              <div class="small text-muted">Siswa: <strong>{{ $tka->nama }}</strong> (NIS: {{ $tka->nis }})</div>
                              <div class="small text-muted">Kelas: <strong>{{ $tka->kelas }}</strong> • Tes: <strong>{{ $tka->nama_tes }}</strong></div>
                            </div>
                            <div class="row g-3">
                              <div class="col-6">
                                <label class="form-label fw-bold small">Nilai Bahasa Indonesia <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="100" name="nilai_bindo" class="form-control" value="{{ $tka->nilai_bindo }}" required>
                              </div>
                              <div class="col-6">
                                <label class="form-label fw-bold small">Nilai Matematika <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="100" name="nilai_mtk" class="form-control" value="{{ $tka->nilai_mtk }}" required>
                              </div>
                            </div>
                          </div>
                          <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm fw-bold">
                              <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                  <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                  Tidak ditemukan data TKA sesuai filter yang dipilih.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div class="small text-muted">
            Menampilkan {{ $data_tka->firstItem() ?: 0 }} s/d {{ $data_tka->lastItem() ?: 0 }} dari {{ $data_tka->total() }} data
          </div>
          <div>
            {{ $data_tka->links('pagination::bootstrap-5') }}
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

{{-- MODAL TAMBAH NILAI MANUAL --}}
@if(in_array(auth()->user()->role, ['admin', 'kurikulum']))
<div class="modal fade" id="modalTambahManual" tabindex="-1" aria-labelledby="modalTambahManualLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalTambahManualLabel">
          <i class="fas fa-plus-circle me-2"></i>Tambah Nilai TKA Manual
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="/tka" method="POST">
        @csrf
        <div class="modal-body p-4">
          <div class="alert alert-primary py-2 px-3 small d-flex align-items-center mb-3">
            <i class="fas fa-info-circle fa-lg me-2"></i>
            <div>Gunakan form ini untuk menginput nilai siswa susulan atau siswa yang belum terdata di Excel.</div>
          </div>

          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label fw-bold small">NIS Siswa <span class="text-danger">*</span></label>
              <input type="text" name="nis" class="form-control" placeholder="Contoh: 12345" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-bold small">Kelas <span class="text-danger">*</span></label>
              <select name="kelas" class="form-select" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelasList as $k)
                  <option value="{{ $k }}">Kelas {{ $k }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold small">Nama Lengkap Siswa <span class="text-danger">*</span></label>
              <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap Siswa" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold small">Jenis Asesmen <span class="text-danger">*</span></label>
              <select name="jenis_tes" class="form-select" required>
                <option value="diagnostik">Tes Diagnostik TKA</option>
                <option value="to_1">Try Out 1 (TO-1)</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-bold small">Nilai Bahasa Indonesia <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" max="100" name="nilai_bindo" class="form-control" placeholder="0.00 - 100" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-bold small">Nilai Matematika <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" max="100" name="nilai_mtk" class="form-control" placeholder="0.00 - 100" required>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary fw-bold">
            <i class="fas fa-save me-1"></i> Simpan Data Siswa
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL IMPORT EXCEL --}}
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold" id="modalImportExcelLabel">
          <i class="fas fa-file-excel me-2"></i>Import / Update Nilai TKA Excel
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="/tka/import" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body p-4">
          <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3">
            <i class="fas fa-info-circle fa-lg me-2"></i>
            <div>
              Unggah file Excel <strong>DIAGNOSTIK.xls</strong> atau format serupa yang berisi sheet <code>DIAGNOSTIK</code> untuk memperbarui seluruh nilai siswa kelas 9 secara otomatis.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Pilih File Excel (.xls / .xlsx) <span class="text-danger">*</span></label>
            <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
            <div class="form-text text-muted">Maksimal ukuran file: 10MB.</div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success fw-bold">
            <i class="fas fa-upload me-1"></i> Mulai Import Data
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endsection
