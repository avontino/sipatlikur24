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

    {{-- REKAP TOTAL JP PER GURU --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-user-check text-primary me-2"></i>Rekap Total JP Per Guru</h6>
      </div>
      <div class="card-body p-3">
        <div class="table-responsive">
          <table id="tableRekapJpGuru" class="table table-bordered table-striped table-hover align-middle mb-0 w-100">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width: 50px;">No</th>
                <th>Nama Guru</th>
                <th>Mata Pelajaran</th>
                <th>Kelas Diampu</th>
                <th class="text-center">Total JP</th>
              </tr>
            </thead>
            <tbody>
              @php $no = 1; @endphp
              @foreach($rekapPerGuru as $r)
              <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td class="fw-bold">{{ $r['guru'] }}</td>
                <td>{{ $r['mapel']->implode(', ') }}</td>
                <td>
                  @foreach($r['kelas'] as $k)
                    <span class="badge bg-light text-dark border">{{ $k }}</span>
                  @endforeach
                </td>
                <td class="text-center"><span class="badge bg-primary fs-6">{{ $r['total_jp'] }} JP</span></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- RINCIAN ALOKASI JAM MENGAJAR PER KELAS --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-list text-success me-2"></i>Rincian Alokasi Jam Mengajar per Kelas</h6>
      </div>
      <div class="card-body p-3">
        <div class="table-responsive">
          <table id="tableRincianJadwal" class="table table-bordered table-striped table-hover align-middle mb-0 w-100">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width: 50px;">No</th>
                <th>Nama Guru</th>
                <th>Mata Pelajaran</th>
                <th class="text-center">Kelas</th>
                <th class="text-center">Beban (JP)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rows as $idx => $row)
              <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="fw-semibold">{{ $row->guru }}</td>
                <td class="text-primary fw-semibold">{{ $row->mapel }}</td>
                <td class="text-center"><span class="badge bg-secondary">{{ $row->kelas }}</span></td>
                <td class="text-center fw-bold">{{ $row->total_jp }} JP</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
  const dtLang = {
    search: "Cari:",
    lengthMenu: "Tampilkan _MENU_ data",
    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
    zeroRecords: "Tidak ada data yang cocok",
    paginate: { previous: "Sebelumnya", next: "Berikutnya" }
  };

  if ($.fn.DataTable) {
    $('#tableRekapJpGuru').DataTable({
      pageLength: 10,
      lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
      language: dtLang
    });

    $('#tableRincianJadwal').DataTable({
      pageLength: 10,
      lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
      language: dtLang
    });
  }
});
</script>
@endsection
