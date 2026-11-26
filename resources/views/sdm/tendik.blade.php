@extends('layouts.master')

@section('content')
<section class="content pt-2">
  <div class="container-fluid">
    @include('sdm._nav', [
      'title' => 'Data & Laporan Tenaga Kependidikan (Tendik)',
      'subtitle' => 'Daftar Pegawai Tata Usaha, Administrasi, Perpustakaan, Lab, Keamanan, dan Pramubakti'
    ])

    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <form method="GET" action="/sdm/tendik" class="d-flex gap-2">
          <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari Nama / NIP / Jabatan...">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        </form>
        <div class="d-flex flex-wrap gap-2">
          @if($canManage)
          <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTendik" onclick="openTambahTendik()">
            <i class="fas fa-plus-circle me-1"></i> Tambah Tendik
          </button>
          <button type="button" class="btn btn-outline-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportTendik">
            <i class="fas fa-file-upload me-1"></i> Import Excel
          </button>
          @endif
          <a href="/sdm/tendik/template" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="fas fa-download me-1"></i> Template Excel
          </a>
          <a href="/sdm/tendik/export" class="btn btn-success btn-sm fw-bold">
            <i class="fas fa-file-excel me-1"></i> Export Excel
          </a>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="text-center">No</th>
                <th>Nama Lengkap</th>
                <th>NIP</th>
                <th>Status</th>
                <th class="text-center">Pangkat / Gol</th>
                <th>Jabatan</th>
                <th>Tanggal Lahir</th>
                <th>No. WhatsApp</th>
                @if($canManage)
                <th class="text-center">Aksi</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($tendikList as $i => $t)
              <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="fw-bold">{{ $t->nama }}</td>
                <td class="font-monospace small">{{ $t->nip ?: '-' }}</td>
                <td><span class="badge bg-info text-dark">{{ $t->status_kepegawaian ?: '-' }}</span></td>
                <td class="text-center">
                  <div class="fw-bold">{{ $t->golongan ?: '-' }}</div>
                  @if(!empty($t->pangkat) && $t->pangkat !== '-')
                  <div class="text-muted" style="font-size: 11px;">{{ $t->pangkat }}</div>
                  @endif
                </td>
                <td class="fw-semibold text-primary">{{ $t->jabatan ?: '-' }}</td>
                <td class="small">{{ $t->tanggal_lahir ? $t->tanggal_lahir->format('d/m/Y') . ' (' . $t->usia . ' thn)' : '-' }}</td>
                <td>
                  @if($t->wa_clean)
                    <a href="https://wa.me/{{ $t->wa_clean }}" target="_blank" class="text-success fw-bold text-decoration-none small">
                      <i class="fab fa-whatsapp me-1"></i>{{ $t->no_wa }}
                    </a>
                  @else
                    <span class="text-muted small">-</span>
                  @endif
                </td>
                @if($canManage)
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" onclick='openEditTendik(@json($t))'><i class="fas fa-edit"></i></button>
                    <form action="/sdm/pegawai/{{ $t->id }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data tendik {{ $t->nama }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                    </form>
                  </div>
                </td>
                @endif
              </tr>
              @empty
              <tr>
                <td colspan="{{ $canManage ? 9 : 8 }}" class="text-center py-4 text-muted">Belum ada data tendik.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

@if($canManage)
<div class="modal fade" id="modalTendik" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalTendikTitle">Data Tendik SDM</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="/sdm/pegawai" method="POST">
        @csrf
        <input type="hidden" name="id" id="td-id">
        <input type="hidden" name="kategori" value="tendik">
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="nama" id="td-nama" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">NIP</label>
              <input type="text" name="nip" id="td-nip" class="form-control" value="-">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Status Kepegawaian</label>
              <select name="status_kepegawaian" id="td-status" class="form-select">
                <option value="Tendik PPPK">Tendik PPPK</option>
                <option value="Tendik PNS">Tendik PNS</option>
                <option value="Tendik Honorer">Tendik Honorer / PTT</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Jabatan</label>
              <select name="jabatan" id="td-jabatan" class="form-select">
                <option value="Tenaga Administrasi">Tenaga Administrasi</option>
                <option value="Pramubakti">Pramubakti</option>
                <option value="Pengadministrasi Kesiswaan">Pengadministrasi Kesiswaan</option>
                <option value="Pengadministrasi Kepegawaian">Pengadministrasi Kepegawaian</option>
                <option value="Pengelola Perpustakaan">Pengelola Perpustakaan</option>
                <option value="Pengelola Laboratorium">Pengelola Laboratorium</option>
                <option value="Petugas Keamanan">Petugas Keamanan</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Golongan</label>
              <select name="golongan" id="td-golongan" class="form-select">
                <option value="IX">IX (PPPK)</option>
                <option value="V">V (PPPK)</option>
                <option value="III/a">III/a</option>
                <option value="II/c">II/c</option>
                <option value="II/a">II/a</option>
                <option value="-">- (Non-ASN)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Jenis Kelamin</label>
              <select name="jenis_kelamin" id="td-jk" class="form-select">
                <option value="L">Laki-Laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Tanggal Lahir</label>
              <input type="date" name="tanggal_lahir" id="td-tgl" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Nomor WhatsApp</label>
              <input type="text" name="no_wa" id="td-wa" class="form-control">
            </div>
            <div class="col-md-12">
              <label class="form-label small fw-bold">Link Akun SIPATLIKUR (Opsional)</label>
              <select name="user_id" id="td-user-id" class="form-select">
                <option value="">-- Tidak Ditautkan --</option>
                @foreach($usersList as $u)
                  <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary fw-bold">Simpan Data Tendik</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalImportTendik" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold">Import Excel Data Tendik</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="/sdm/tendik/import" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body p-4">
          <p class="small text-muted">Unduh <a href="/sdm/tendik/template" class="fw-bold">Template Excel Tendik</a> terlebih dahulu untuk memastikan format kolom tepat.</p>
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
function openTambahTendik() {
  document.getElementById('modalTendikTitle').innerText = 'Tambah Tendik SDM';
  document.getElementById('td-id').value = '';
  document.getElementById('td-nama').value = '';
  document.getElementById('td-nip').value = '-';
  document.getElementById('td-status').value = 'Tendik PPPK';
  document.getElementById('td-jabatan').value = 'Tenaga Administrasi';
  document.getElementById('td-golongan').value = 'IX';
  document.getElementById('td-jk').value = 'P';
  document.getElementById('td-tgl').value = '';
  document.getElementById('td-wa').value = '';
  document.getElementById('td-user-id').value = '';
}

function openEditTendik(t) {
  document.getElementById('modalTendikTitle').innerText = 'Edit Data Tendik SDM';
  document.getElementById('td-id').value = t.id;
  document.getElementById('td-nama').value = t.nama || '';
  document.getElementById('td-nip').value = t.nip || '-';
  document.getElementById('td-status').value = t.status_kepegawaian || 'Tendik PPPK';
  document.getElementById('td-jabatan').value = t.jabatan || 'Tenaga Administrasi';
  document.getElementById('td-golongan').value = t.golongan || '-';
  document.getElementById('td-jk').value = t.jenis_kelamin || 'P';
  document.getElementById('td-tgl').value = t.tanggal_lahir ? t.tanggal_lahir.substring(0, 10) : '';
  document.getElementById('td-wa').value = t.no_wa || '';
  document.getElementById('td-user-id').value = t.user_id || '';
  new bootstrap.Modal(document.getElementById('modalTendik')).show();
}
</script>
@endsection
