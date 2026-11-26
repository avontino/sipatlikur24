@extends('layouts.master')

@section('content')
<section class="content pt-2">
  <div class="container-fluid">
    @include('sdm._nav', [
      'title' => 'Laporan & Demografi Peserta Didik',
      'subtitle' => 'Terhubung langsung dengan database Siswa SIPATLIKUR (dilengkapi NISN, L/P, Agama, dan Nama Orang Tua)'
    ])

    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <form method="GET" action="/sdm/siswa" class="d-flex flex-wrap gap-2">
          <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" style="width: 220px;" placeholder="Cari Nama / NIS / NISN...">
          <select name="kelas" class="form-select form-select-sm" style="width: 150px;" onchange="this.form.submit()">
            <option value="">Semua Kelas</option>
            @foreach($kelasOptions as $k)
              <option value="{{ $k }}" {{ request('kelas') === $k ? 'selected' : '' }}>{{ $k }}</option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        </form>
        <div class="d-flex flex-wrap gap-2">
          @if($canManage)
          <button type="button" class="btn btn-outline-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportSiswaSdm">
            <i class="fas fa-file-upload me-1"></i> Import Demografi Excel
          </button>
          @endif
          <a href="/sdm/siswa/template" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="fas fa-download me-1"></i> Template Excel
          </a>
          <a href="/sdm/siswa/export" class="btn btn-success btn-sm fw-bold">
            <i class="fas fa-file-excel me-1"></i> Export Excel
          </a>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 table-sm">
            <thead class="table-light">
              <tr>
                <th class="text-center">No</th>
                <th>NIS / NISN</th>
                <th>Nama Siswa</th>
                <th class="text-center">Kelas</th>
                <th class="text-center">L/P</th>
                <th>Agama</th>
                <th>Nama Orang Tua</th>
                @if($canManage)
                <th class="text-center">Edit</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($siswaList as $i => $s)
              <tr>
                <td class="text-center">{{ $siswaList->firstItem() + $i }}</td>
                <td class="font-monospace small">
                  <div>NIS: {{ $s->nis ?: '-' }}</div>
                  <div class="text-muted">NISN: {{ $s->nisn ?: '-' }}</div>
                </td>
                <td class="fw-bold">{{ $s->nama }}</td>
                <td class="text-center"><span class="badge bg-primary">{{ $s->kelas }}</span></td>
                <td class="text-center fw-bold">{{ $s->jenis_kelamin ?: '-' }}</td>
                <td>{{ $s->agama ?: '-' }}</td>
                <td>{{ $s->nama_ortu ?: '-' }}</td>
                @if($canManage)
                <td class="text-center">
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick='openEditSiswaSdm(@json($s))'>
                    <i class="fas fa-edit"></i>
                  </button>
                </td>
                @endif
              </tr>
              @empty
              <tr>
                <td colspan="{{ $canManage ? 8 : 7 }}" class="text-center py-4 text-muted">Data siswa tidak ditemukan.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Menampilkan {{ $siswaList->firstItem() ?: 0 }} - {{ $siswaList->lastItem() ?: 0 }} dari {{ $siswaList->total() }} siswa</small>
        {{ $siswaList->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</section>

@if($canManage)
<div class="modal fade" id="modalEditSiswaSdm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Lengkapi Data Demografi Siswa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="formEditSiswaSdm" method="POST">
        @csrf
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small fw-bold">Nama Siswa</label>
            <input type="text" id="es-nama" class="form-control bg-light" readonly>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold">NISN</label>
              <input type="text" name="nisn" id="es-nisn" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Jenis Kelamin</label>
              <select name="jenis_kelamin" id="es-jk" class="form-select">
                <option value="">-- Pilih --</option>
                <option value="L">Laki-Laki (L)</option>
                <option value="P">Perempuan (P)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Agama</label>
              <select name="agama" id="es-agama" class="form-select">
                <option value="">-- Pilih --</option>
                <option value="Islam">Islam</option>
                <option value="Kristen">Kristen</option>
                <option value="Katolik">Katolik</option>
                <option value="Hindu">Hindu</option>
                <option value="Buddha">Buddha</option>
                <option value="Konghucu">Konghucu</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Nama Orang Tua / Wali</label>
              <input type="text" name="nama_ortu" id="es-ortu" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary fw-bold">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalImportSiswaSdm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold">Import Excel Demografi Siswa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="/sdm/siswa/import" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body p-4">
          <p class="small text-muted">Unduh <a href="/sdm/siswa/template" class="fw-bold">Template Excel Siswa</a>. Sistem mencocokkan NIS/Nama untuk melengkapi NISN, Gender, Agama, dan Nama Ortu tanpa merusak data absensi/poin siswa.</p>
          <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success fw-bold">Mulai Import</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function openEditSiswaSdm(s) {
  document.getElementById('formEditSiswaSdm').action = '/sdm/siswa/' + s.id + '/update';
  document.getElementById('es-nama').value = s.nama + ' (' + s.kelas + ')';
  document.getElementById('es-nisn').value = s.nisn || '';
  document.getElementById('es-jk').value = s.jenis_kelamin || '';
  document.getElementById('es-agama').value = s.agama || '';
  document.getElementById('es-ortu').value = s.nama_ortu || '';
  new bootstrap.Modal(document.getElementById('modalEditSiswaSdm')).show();
}
</script>
@endsection
