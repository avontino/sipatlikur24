@extends('layouts.master')

@section('content')
<section class="content pt-2">
  <div class="container-fluid">
    @include('sdm._nav', [
      'title' => 'Pembagian Beban Tugas Mengajar',
      'subtitle' => 'Ditarik otomatis secara real-time dari Jadwal Pelajaran aktif — jika jadwal diubah, beban mengajar otomatis menyesuaikan'
    ])

    <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <i class="fas fa-sync-alt me-2"></i>
        <strong>Tersinkron Otomatis dengan Jadwal Pelajaran!</strong> Data di bawah dihitung langsung dari menu <a href="/jadwal" class="fw-bold text-decoration-underline">Jadwal Pelajaran</a> (TA: {{ session('tahun_ajaran') ?: 'Aktif' }}, Semester: {{ session('semester') ?: 'Aktif' }}).
      </div>
      <a href="/jadwal" class="btn btn-sm btn-primary rounded-pill">
        <i class="fas fa-calendar-alt me-1"></i> Kelola Jadwal Pelajaran
      </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-white py-3">
        <form method="GET" action="/sdm/tugas-mengajar" class="row g-2 align-items-center">
          <div class="col-md-5">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama guru, mata pelajaran, atau kelas...">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i> Cari</button>
            <a href="/sdm/tugas-mengajar" class="btn btn-outline-secondary btn-sm">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="row g-4">
      {{-- REKAP TOTAL JP PER GURU --}}
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-user-check text-primary me-2"></i>Rekap Total JP Per Guru</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 550px;">
              <table class="table table-hover align-middle mb-0 table-sm">
                <thead class="table-light sticky-top">
                  <tr>
                    <th class="ps-3">Nama Guru</th>
                    <th>Mapel</th>
                    <th>Kelas</th>
                    <th class="text-center">Total JP</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($rekapPerGuru as $r)
                  <tr>
                    <td class="ps-3 fw-bold">{{ $r['guru'] }}</td>
                    <td class="small">{{ $r['mapel']->implode(', ') }}</td>
                    <td>
                      @foreach($r['kelas'] as $k)
                        <span class="badge bg-light text-dark border">{{ $k }}</span>
                      @endforeach
                    </td>
                    <td class="text-center"><span class="badge bg-primary">{{ $r['total_jp'] }} JP</span></td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center py-4 text-muted">Belum ada data jadwal pelajaran.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- RINCIAN PER KELAS --}}
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-list text-success me-2"></i>Rincian Alokasi Jam Mengajar per Kelas</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 550px;">
              <table class="table table-hover align-middle mb-0 table-sm">
                <thead class="table-light sticky-top">
                  <tr>
                    <th class="ps-3">Nama Guru</th>
                    <th>Mata Pelajaran</th>
                    <th class="text-center">Kelas</th>
                    <th class="text-center">Beban (JP)</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($rows as $row)
                  <tr>
                    <td class="ps-3 fw-semibold">{{ $row->guru }}</td>
                    <td class="text-primary fw-semibold">{{ $row->mapel }}</td>
                    <td class="text-center"><span class="badge bg-secondary">{{ $row->kelas }}</span></td>
                    <td class="text-center fw-bold">{{ $row->total_jp }} JP</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center py-4 text-muted">Belum ada data jadwal pelajaran yang ditemukan.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
