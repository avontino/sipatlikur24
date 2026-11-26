@extends('layouts.master')

@section('content')
<section class="content pt-2">
  <div class="container-fluid">
    @include('sdm._nav', [
      'title' => 'Input & Daftar Tugas Tambahan Guru',
      'subtitle' => 'Penugasan Jabatan Struktural (Wakil Kepala Sekolah, Wali Kelas, Kepala Lab, Pembina Ekskul, dll)'
    ])

    @if($canManage)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>Tambah Penugasan Tugas Tambahan</h6>
      </div>
      <div class="card-body">
        <form action="/sdm/tugas-tambahan" method="POST" class="row g-3">
          @csrf
          <div class="col-md-4">
            <label class="form-label small fw-bold">Pilih Guru Penerima Tugas <span class="text-danger">*</span></label>
            <select name="pegawai_id" class="form-select" required>
              <option value="">-- Pilih Guru --</option>
              @foreach($guruList as $g)
                <option value="{{ $g->id }}">{{ $g->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-bold">Nama Tugas / Jabatan <span class="text-danger">*</span></label>
            <input type="text" name="nama_tugas" class="form-control" placeholder="Contoh: Wali Kelas IX-A" required>
          </div>
          <div class="col-md-2">
            <label class="form-label small fw-bold">Ekuivalensi (JP) <span class="text-danger">*</span></label>
            <input type="number" name="ekuivalensi_jp" class="form-control" value="2" min="0" max="40" required>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-bold">Nomor SK Penugasan</label>
            <input type="text" name="no_sk" class="form-control" placeholder="800/xxx/35.73.301/2026">
          </div>
          <div class="col-md-9">
            <input type="text" name="keterangan" class="form-control" placeholder="Keterangan tambahan (opsional)">
          </div>
          <div class="col-md-3 text-end">
            <button type="submit" class="btn btn-primary fw-bold w-100"><i class="fas fa-save me-1"></i> Simpan Tugas Tambahan</button>
          </div>
        </form>
      </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-award text-warning me-2"></i>Daftar Penugasan Tugas Tambahan Aktif</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Nama Pendidik</th>
                <th>Tugas Tambahan / Jabatan</th>
                <th class="text-center">Ekuivalensi JP</th>
                <th>Nomor SK</th>
                <th>Keterangan</th>
                @if($canManage)
                <th class="text-center">Aksi</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($tugasList as $i => $tt)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td class="fw-bold">{{ $tt->pegawai->nama ?? '-' }}</td>
                <td class="fw-semibold text-primary">{{ $tt->nama_tugas }}</td>
                <td class="text-center"><span class="badge bg-info text-dark">{{ $tt->ekuivalensi_jp }} JP</span></td>
                <td class="font-monospace small">{{ $tt->no_sk ?: '-' }}</td>
                <td class="text-muted small">{{ $tt->keterangan ?: '-' }}</td>
                @if($canManage)
                <td class="text-center">
                  <form action="/sdm/tugas-tambahan/{{ $tt->id }}" method="POST" onsubmit="return confirm('Hapus tugas tambahan ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                  </form>
                </td>
                @endif
              </tr>
              @empty
              <tr>
                <td colspan="{{ $canManage ? 7 : 6 }}" class="text-center py-4 text-muted">Belum ada data tugas tambahan.</td>
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
