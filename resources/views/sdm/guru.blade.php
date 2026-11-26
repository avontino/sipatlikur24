@extends('layouts.master')

@section('content')
<section class="content pt-2">
  <div class="container-fluid">
    @include('sdm._nav', [
      'title' => 'Data & Laporan Dewan Guru',
      'subtitle' => 'Daftar Pendidik SMPN 24 Malang lengkap dengan NIP, Golongan, Tanggal Lahir, dan Beban Mengajar'
    ])

    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h6 class="fw-bold mb-0"><i class="fas fa-filter text-primary me-2"></i>Filter & Aksi Data Guru ({{ $guruList->count() }} Pendidik)</h6>
        <div class="d-flex flex-wrap gap-2">
          @if($canManage)
          <form action="/sdm/sync-users" method="POST" class="d-inline" onsubmit="return confirm('Sinkronkan nama guru & tendik yang belum ada dari akun SIPATLIKUR? Data yang sudah ada tidak akan tertimpa.');">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm fw-bold">
              <i class="fas fa-sync-alt me-1"></i> Tarik dari Akun SIPATLIKUR
            </button>
          </form>
          <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalGuru" onclick="openTambahGuru()">
            <i class="fas fa-plus-circle me-1"></i> Tambah Guru
          </button>
          <button type="button" class="btn btn-outline-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportGuru">
            <i class="fas fa-file-upload me-1"></i> Import Excel
          </button>
          @endif
          <a href="/sdm/guru/template" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="fas fa-download me-1"></i> Template Excel
          </a>
          <a href="/sdm/guru/export" class="btn btn-success btn-sm fw-bold">
            <i class="fas fa-file-excel me-1"></i> Export Excel
          </a>
        </div>
      </div>

      <div class="card-body">
        <form method="GET" action="/sdm/guru" class="row g-2 mb-3">
          <div class="col-md-3">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari Nama / NIP / Mapel...">
          </div>
          <div class="col-md-3">
            <select name="mapel" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">-- Semua Mata Pelajaran --</option>
              @foreach($mapelOptions as $m)
                <option value="{{ $m }}" {{ request('mapel') === $m ? 'selected' : '' }}>{{ $m }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">-- Semua Status --</option>
              @foreach(['Guru PNS', 'Guru PPPK', 'Guru Honorer', 'Guru Honorer-Non Induk'] as $st)
                <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <select name="golongan" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">-- Semua Golongan --</option>
              @foreach(['IV/d', 'IV/c', 'IV/b', 'IV/a', 'III/d', 'III/c', 'III/b', 'III/a', 'IX', '-'] as $gol)
                <option value="{{ $gol }}" {{ request('golongan') === $gol ? 'selected' : '' }}>{{ $gol === '-' ? 'Non-ASN (-)' : $gol }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-search"></i></button>
            <a href="/sdm/guru" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 table-sm">
            <thead class="table-light">
              <tr>
                <th class="text-center">No</th>
                <th>Nama Lengkap & NIP</th>
                <th>Status</th>
                <th class="text-center">Pangkat / Gol</th>
                <th>Mapel Pokok</th>
                <th>Kelas Diampu</th>
                <th class="text-center">Total JP</th>
                <th>Kontak WA</th>
                @if($canManage)
                <th class="text-center">Aksi</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($guruList as $i => $g)
              @php
                $key = mb_strtolower(trim($g->nama));
                $jpJadwal = $jadwalSummary['jp'][$key] ?? null;
                $kelasJadwal = $jadwalSummary['kelas'][$key] ?? [];
                $kelasTampil = !empty($kelasJadwal) ? $kelasJadwal : ($g->kelas_diampu ?? []);
                $jpTambahan = $g->tugasTambahan->sum('ekuivalensi_jp');
                $totalJp = ($jpJadwal !== null ? $jpJadwal : $g->beban_jp) + $jpTambahan;
              @endphp
              <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>
                  <div class="fw-bold text-dark">{{ $g->nama }}</div>
                  <div class="font-monospace small text-muted">{{ $g->nip ?: '-' }}</div>
                  @if($g->tanggal_lahir)
                  <div class="small text-muted" style="font-size: 11px;">Lahir: {{ $g->tanggal_lahir->format('d/m/Y') }} ({{ $g->usia }} thn)</div>
                  @endif
                </td>
                <td>
                  <span class="badge {{ $g->status_kepegawaian === 'Guru PNS' ? 'bg-primary' : (str_contains((string)$g->status_kepegawaian, 'PPPK') ? 'bg-info text-dark' : 'bg-warning text-dark') }}">
                    {{ $g->status_kepegawaian ?: '-' }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="fw-bold">{{ $g->golongan ?: '-' }}</div>
                  @if(!empty($g->pangkat) && $g->pangkat !== '-')
                  <div class="text-muted" style="font-size: 11px;">{{ $g->pangkat }}</div>
                  @endif
                </td>
                <td class="fw-semibold text-primary">{{ $g->mapel ?: '-' }}</td>
                <td>
                  @forelse($kelasTampil as $k)
                    <span class="badge bg-light text-dark border">{{ $k }}</span>
                  @empty
                    <span class="text-muted small">-</span>
                  @endforelse
                </td>
                <td class="text-center">
                  <span class="fw-bold">{{ $totalJp }} JP</span>
                  @if($jpJadwal !== null)
                    <div class="text-success" style="font-size: 10px;" title="Ditarik otomatis dari Jadwal">Jadwal: {{ $jpJadwal }}</div>
                  @endif
                </td>
                <td>
                  @if($g->wa_clean)
                    <a href="https://wa.me/{{ $g->wa_clean }}" target="_blank" class="text-success fw-bold text-decoration-none small">
                      <i class="fab fa-whatsapp me-1"></i>{{ $g->no_wa }}
                    </a>
                  @else
                    <span class="text-muted small">-</span>
                  @endif
                </td>
                @if($canManage)
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" title="Edit"
                      onclick='openEditGuru(@json($g))'>
                      <i class="fas fa-edit"></i>
                    </button>
                    <form action="/sdm/pegawai/{{ $g->id }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data guru {{ $g->nama }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </div>
                </td>
                @endif
              </tr>
              @empty
              <tr>
                <td colspan="{{ $canManage ? 9 : 8 }}" class="text-center py-4 text-muted">
                  Belum ada data guru di modul SDM. Klik <strong>Tarik dari Akun SIPATLIKUR</strong> atau <strong>Import Excel</strong> untuk mengisi data awal.
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

@if($canManage)
{{-- MODAL TAMBAH / EDIT GURU --}}
<div class="modal fade" id="modalGuru" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalGuruTitle"><i class="fas fa-chalkboard-teacher me-2"></i>Data Guru SDM</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="/sdm/pegawai" method="POST">
        @csrf
        <input type="hidden" name="id" id="guru-id">
        <input type="hidden" name="kategori" value="guru">
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
              <input type="text" name="nama" id="guru-nama" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">NIP (18 Digit)</label>
              <input type="text" name="nip" id="guru-nip" class="form-control" placeholder="1967... atau -">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Status Kepegawaian</label>
              <select name="status_kepegawaian" id="guru-status" class="form-select">
                <option value="Guru PNS">Guru PNS</option>
                <option value="Guru PPPK">Guru PPPK</option>
                <option value="Guru Honorer">Guru Honorer</option>
                <option value="Guru Honorer-Non Induk">Guru Honorer-Non Induk</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Pangkat</label>
              <input type="text" name="pangkat" id="guru-pangkat" class="form-control" placeholder="Pembina Tk.I / PPPK / -">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Golongan</label>
              <select name="golongan" id="guru-golongan" class="form-select">
                <option value="IV/d">IV/d</option>
                <option value="IV/c">IV/c</option>
                <option value="IV/b">IV/b</option>
                <option value="IV/a">IV/a</option>
                <option value="III/d">III/d</option>
                <option value="III/c">III/c</option>
                <option value="III/b">III/b</option>
                <option value="III/a">III/a</option>
                <option value="IX">IX (PPPK)</option>
                <option value="-">- (Non-ASN)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Jenis Kelamin</label>
              <select name="jenis_kelamin" id="guru-jk" class="form-select">
                <option value="L">Laki-Laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Tanggal Lahir (Untuk Ultah & Pensiun)</label>
              <input type="date" name="tanggal_lahir" id="guru-tgl" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Nomor WhatsApp</label>
              <input type="text" name="no_wa" id="guru-wa" class="form-control" placeholder="08xxxxxxxxxx">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Mata Pelajaran Pokok</label>
              <input type="text" name="mapel" id="guru-mapel" class="form-control" placeholder="Contoh: Matematika">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Link Akun SIPATLIKUR (Opsional)</label>
              <select name="user_id" id="guru-user-id" class="form-select">
                <option value="">-- Otomatis / Tidak Ditautkan --</option>
                @foreach($usersList as $u)
                  <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Beban Mengajar Default (JP)</label>
              <input type="number" name="beban_jp" id="guru-jp" class="form-control" value="24" min="0" max="48">
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Kelas Diampu Manual (jika belum ada di Jadwal)</label>
              <div class="row g-1">
                @foreach($rombelList as $r)
                <div class="col-3 col-sm-2">
                  <div class="form-check">
                    <input class="form-check-input guru-kelas-cb" type="checkbox" name="kelas_diampu[]" value="{{ $r }}" id="cb_{{ $r }}">
                    <label class="form-check-label small" for="cb_{{ $r }}">{{ $r }}</label>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i> Simpan Data Guru</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL IMPORT EXCEL GURU --}}
<div class="modal fade" id="modalImportGuru" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold"><i class="fas fa-file-excel me-2"></i>Import Excel Data Guru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="/sdm/guru/import" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body p-4">
          <p class="small text-muted">Gunakan <a href="/sdm/guru/template" class="fw-bold">Template Excel Guru</a> agar urutan kolom sesuai. Data NIP yang sama akan diperbarui otomatis.</p>
          <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-upload me-1"></i> Mulai Import</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function openTambahGuru() {
  document.getElementById('modalGuruTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Tambah Guru SDM';
  document.getElementById('guru-id').value = '';
  document.getElementById('guru-nama').value = '';
  document.getElementById('guru-nip').value = '-';
  document.getElementById('guru-status').value = 'Guru PNS';
  document.getElementById('guru-pangkat').value = '';
  document.getElementById('guru-golongan').value = '-';
  document.getElementById('guru-jk').value = 'L';
  document.getElementById('guru-tgl').value = '';
  document.getElementById('guru-wa').value = '';
  document.getElementById('guru-mapel').value = '';
  document.getElementById('guru-user-id').value = '';
  document.getElementById('guru-jp').value = 24;
  document.querySelectorAll('.guru-kelas-cb').forEach(cb => cb.checked = false);
}

function openEditGuru(g) {
  document.getElementById('modalGuruTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Data Guru SDM';
  document.getElementById('guru-id').value = g.id;
  document.getElementById('guru-nama').value = g.nama || '';
  document.getElementById('guru-nip').value = g.nip || '-';
  document.getElementById('guru-status').value = g.status_kepegawaian || 'Guru PNS';
  document.getElementById('guru-pangkat').value = g.pangkat || '';
  document.getElementById('guru-golongan').value = g.golongan || '-';
  document.getElementById('guru-jk').value = g.jenis_kelamin || 'L';
  document.getElementById('guru-tgl').value = g.tanggal_lahir ? g.tanggal_lahir.substring(0, 10) : '';
  document.getElementById('guru-wa').value = g.no_wa || '';
  document.getElementById('guru-mapel').value = g.mapel || '';
  document.getElementById('guru-user-id').value = g.user_id || '';
  document.getElementById('guru-jp').value = g.beban_jp ?? 24;
  const kelasArr = Array.isArray(g.kelas_diampu) ? g.kelas_diampu : [];
  document.querySelectorAll('.guru-kelas-cb').forEach(cb => {
    cb.checked = kelasArr.includes(cb.value);
  });
  new bootstrap.Modal(document.getElementById('modalGuru')).show();
}
</script>
@endsection
