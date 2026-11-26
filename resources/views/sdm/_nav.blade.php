<div class="content-header py-3 px-3 mb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 16px; border: 1px solid #e2e8f0;">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-md-7">
        <div class="d-flex align-items-center">
          <div class="me-3 p-3 rounded-4 shadow-sm text-white" style="background: linear-gradient(135deg, #4f46e5, #1e3a8a);">
            <i class="fas fa-users-cog fa-lg"></i>
          </div>
          <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 mb-1 fw-bold" style="font-size: 11px;">
              MANAJEMEN SDM • SMPN 24 MALANG
            </span>
            <h3 class="m-0 text-dark fw-bolder" style="font-size: 1.4rem;">{{ $title ?? 'Manajemen Sumber Daya Manusia' }}</h3>
            @if(!empty($subtitle))
            <p class="text-muted small mb-0 mt-1">{{ $subtitle }}</p>
            @endif
          </div>
        </div>
      </div>
      <div class="col-md-5 mt-3 mt-md-0 text-md-end">
        <ol class="breadcrumb float-md-end bg-white px-3 py-2 rounded-pill shadow-sm border mb-0">
          <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none text-secondary"><i class="fas fa-home me-1"></i> Dashboard</a></li>
          <li class="breadcrumb-item active text-primary fw-bold">Manajemen SDM</li>
        </ol>
      </div>
    </div>

    {{-- Sub-navigasi cepat modul SDM --}}
    <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top border-secondary-subtle">
      <a href="/sdm" class="btn btn-sm rounded-pill {{ Request::is('sdm') ? 'btn-primary fw-bold' : 'btn-light border' }}">
        <i class="fas fa-chart-pie me-1"></i> Dashboard KPI
      </a>
      <a href="/sdm/ringkasan" class="btn btn-sm rounded-pill {{ Request::is('sdm/ringkasan') ? 'btn-primary fw-bold' : 'btn-light border' }}">
        <i class="fas fa-tachometer-alt me-1"></i> Ringkasan
      </a>
      <a href="/sdm/rekap" class="btn btn-sm rounded-pill {{ Request::is('sdm/rekap') ? 'btn-primary fw-bold' : 'btn-light border' }}">
        <i class="fas fa-table me-1"></i> Rekap SDM
      </a>
      <a href="/sdm/guru" class="btn btn-sm rounded-pill {{ Request::is('sdm/guru') ? 'btn-primary fw-bold' : 'btn-light border' }}">
        <i class="fas fa-chalkboard-teacher me-1"></i> Data Guru
      </a>
      <a href="/sdm/tendik" class="btn btn-sm rounded-pill {{ Request::is('sdm/tendik') ? 'btn-primary fw-bold' : 'btn-light border' }}">
        <i class="fas fa-user-cog me-1"></i> Data Tendik
      </a>
      <a href="/sdm/siswa" class="btn btn-sm rounded-pill {{ Request::is('sdm/siswa') ? 'btn-primary fw-bold' : 'btn-light border' }}">
        <i class="fas fa-user-graduate me-1"></i> Demografi Siswa
      </a>
      <a href="/sdm/tugas-mengajar" class="btn btn-sm rounded-pill {{ Request::is('sdm/tugas-mengajar') ? 'btn-primary fw-bold' : 'btn-light border' }}">
        <i class="fas fa-book-reader me-1"></i> Tugas Mengajar
      </a>
      <a href="/sdm/tugas-tambahan" class="btn btn-sm rounded-pill {{ Request::is('sdm/tugas-tambahan') ? 'btn-primary fw-bold' : 'btn-light border' }}">
        <i class="fas fa-award me-1"></i> Tugas Tambahan
      </a>
      <a href="/sdm/cuti" class="btn btn-sm rounded-pill {{ Request::is('sdm/cuti') ? 'btn-primary fw-bold' : 'btn-light border' }}">
        <i class="fas fa-calendar-check me-1"></i> Cuti Pegawai
      </a>
    </div>
  </div>
</div>

@if(session('sukses'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
  <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('gagal'))
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
  <i class="fas fa-exclamation-circle me-1"></i> {{ session('gagal') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
  <ul class="mb-0 ps-3">
    @foreach($errors->all() as $err)
      <li>{{ $err }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
