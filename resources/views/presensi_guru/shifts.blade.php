@extends('layouts.master')

@section('content')
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 3.5rem);
    }
</style>

<section class="content pt-3">
    <div class="container-fluid">
        <!-- Top Title Bar -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                <h3 class="fw-bold m-0" style="color: #002366;">
                    <i class="fas fa-business-time me-2"></i>Manajemen Shift & Roster Kerja
                </h3>
                <div class="d-flex gap-2">
                    <a href="/presensi-guru/setting" class="btn btn-outline-secondary btn-sm"><i class="fas fa-map-marker-alt me-1"></i> Pengaturan Lokasi</a>
                    <a href="/presensi-guru/rekap" class="btn btn-outline-primary btn-sm"><i class="fas fa-list me-1"></i> Rekap Presensi</a>
                </div>
            </div>
        </div>
        <!-- Flash Alert -->
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('gagal'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-1"></i> {{ session('gagal') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- BANNER PANDUAN PENGGUNAAN SHIFT & ROSTER -->
        <div class="card shadow-sm border-0 mb-4 bg-light border-start border-4 border-info">
            <div class="card-body py-3">
                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-lightbulb text-warning me-2"></i>Panduan Penggunaan Shift & Roster</h6>
                <div class="row g-3 small text-secondary">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>1. Default Shift Per Pegawai:</strong></p>
                        <span class="d-block">Dipakai sebagai shift harian standar. Misalnya Guru & Tendik diset <em>Reguler (07.00 - 16.00)</em>. Cukup diatur sekali saja pada tabel paling bawah.</span>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>2. Plotting Roster Harian (Shift Khusus):</strong></p>
                        <span class="d-block">Dipakai jika ada piket/giliran khusus pada tanggal tertentu (misal Satpam A piket Malam). <strong>Jika roster harian sudah diisi, Anda TIDAK PERLU mengubah Default Shift-nya</strong> karena roster harian otomatis mengesampingkan default pada tanggal tersebut.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- TABEL 1: MASTER DATA SHIFT -->
            <div class="col-lg-7 col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="m-0 fw-bold card-title"><i class="fas fa-clock me-2"></i>Master Data Shift Kerja</h5>
                        <button type="button" class="btn btn-light btn-sm text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahShift">
                            <i class="fas fa-plus me-1"></i> Tambah Shift
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Shift</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Pulang</th>
                                        <th class="text-center">Shift Malam?</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shifts as $s)
                                    <tr>
                                        <td>
                                            <strong class="text-dark">{{ $s->nama_shift }}</strong>
                                            @if($s->keterangan)
                                                <br><small class="text-muted">{{ $s->keterangan }}</small>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-success fs-6">{{ substr($s->jam_masuk, 0, 5) }}</span></td>
                                        <td><span class="badge bg-warning fs-6 text-dark">{{ substr($s->jam_pulang, 0, 5) }}</span></td>
                                        <td class="text-center">
                                            @if($s->is_overnight)
                                                <span class="badge bg-dark"><i class="fas fa-moon me-1"></i> Ya (H+1)</span>
                                            @else
                                                <span class="badge bg-light text-secondary border">Tidak</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-warning me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditShift{{ $s->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($s->id != 1)
                                            <a href="/presensi-guru/shifts/delete/{{ $s->id }}" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus shift ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABEL 2: ROSTER SHIFT HARIAN (PLOT SHIFT TANGGAL CERTAIN) -->
            <div class="col-lg-5 col-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="m-0 fw-bold card-title"><i class="fas fa-calendar-plus me-2"></i>Plotting Roster Shift Harian</h5>
                    </div>
                    <div class="card-body">
                        <form action="/presensi-guru/roster/store" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Pegawai / Satpam / Asrama</label>
                                <select name="user_id" id="select_user_roster" class="form-select select2-user" required>
                                    <option value="">-- Cari / Pilih Pegawai --</option>
                                    @foreach($pegawai as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ strtoupper($p->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Kerja</label>
                                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Shift</label>
                                <select name="shift_id" class="form-select" required>
                                    @foreach($shifts as $s)
                                        <option value="{{ $s->id }}">{{ $s->nama_shift }} ({{ substr($s->jam_masuk,0,5) }} - {{ substr($s->jam_pulang,0,5) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Simpan Roster Shift</button>
                        </form>
                    </div>
                </div>

                <!-- LIST ROSTER HARI INI -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="m-0 fw-bold card-title"><i class="fas fa-calendar-day me-2"></i>Roster Terjadwal Hari Ini ({{ date('d-m-Y') }})</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush small">
                            @forelse($todaySchedules as $rs)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-dark">{{ $rs->user->name ?? 'User' }}</strong>
                                    <br><span class="text-muted">{{ $rs->shift->nama_shift ?? 'Shift' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary text-white fw-bold px-2 py-1 fs-6 shadow-sm">
                                        {{ substr($rs->shift->jam_masuk ?? '', 0, 5) }} - {{ substr($rs->shift->jam_pulang ?? '', 0, 5) }}
                                    </span>
                                    <a href="/presensi-guru/roster/delete/{{ $rs->id }}" 
                                       class="btn btn-sm btn-outline-danger py-0 px-2" 
                                       title="Batalkan Roster Ini"
                                       onclick="return confirm('Apakah Anda yakin ingin membatalkan/menghapus roster untuk {{ $rs->user->name ?? 'User' }} ini?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </li>
                            @empty
                            <li class="list-group-item text-muted text-center py-3">Belum ada roster khusus hari ini (Pegawai menggunakan Default Shift).</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: DEFAULT SHIFT PEGAWAI -->
        <div class="row mt-4 mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="m-0 fw-bold card-title"><i class="fas fa-user-cog me-2"></i>Default Shift Per Pegawai / Guru / Staf</h5>
                        <div class="d-flex align-items-center">
                            <input type="text" id="searchDefaultShiftInput" class="form-control form-control-sm border-0 shadow-sm" style="min-width: 250px;" placeholder="🔍 Cari nama pegawai / role...">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0" id="tableDefaultShift">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pegawai</th>
                                        <th>Role</th>
                                        <th>Default Shift Terpasang</th>
                                        <th style="width: 250px;">Ubah Default Shift</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai as $index => $p)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong class="text-dark">{{ $p->name }}</strong></td>
                                        <td><span class="badge bg-secondary">{{ strtoupper($p->role) }}</span></td>
                                        <td>
                                            @if($p->defaultShift)
                                                <span class="badge bg-primary fs-6">{{ $p->defaultShift->nama_shift }} ({{ substr($p->defaultShift->jam_masuk,0,5) }} - {{ substr($p->defaultShift->jam_pulang,0,5) }})</span>
                                            @else
                                                <span class="badge bg-secondary">Reguler Guru & Tendik (07:00 - 16:00)</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="/presensi-guru/user-shift/update" method="POST" class="d-flex gap-2">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $p->id }}">
                                                <select name="default_shift_id" class="form-select form-select-sm" required>
                                                    @foreach($shifts as $s)
                                                        <option value="{{ $s->id }}" {{ ($p->default_shift_id == $s->id || (!$p->default_shift_id && $s->id == 1)) ? 'selected' : '' }}>
                                                            {{ $s->nama_shift }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-primary px-3">Set</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MODAL TAMBAH SHIFT BARU -->
<div class="modal fade" id="modalTambahShift" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/presensi-guru/shifts/store" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Shift Kerja Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Shift</label>
                        <input type="text" name="nama_shift" class="form-control" placeholder="Misal: Shift Malam Satpam" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Jam Masuk</label>
                            <input type="time" name="jam_masuk" class="form-control" value="07:00" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Jam Pulang</label>
                            <input type="time" name="jam_pulang" class="form-control" value="15:00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Toleransi Keterlambatan (Menit)</label>
                        <input type="number" name="toleransi_terlambat" class="form-control" value="0" min="0">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_overnight" class="form-check-input" id="checkOvernightNew">
                        <label class="form-check-label fw-semibold text-danger" for="checkOvernightNew">
                            Shift Malam (Jam pulang berada di hari berikutnya / H+1)
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tugas shift..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT SHIFT (DIPINDAHKAN KE LUAR TABEL AGAR TIDAK TERLIPAT / TERTUTUP) -->
@foreach($shifts as $s)
<div class="modal fade" id="modalEditShift{{ $s->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/presensi-guru/shifts/update/{{ $s->id }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Shift: {{ $s->nama_shift }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Shift</label>
                        <input type="text" name="nama_shift" class="form-control" value="{{ $s->nama_shift }}" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Jam Masuk</label>
                            <input type="time" name="jam_masuk" class="form-control" value="{{ substr($s->jam_masuk, 0, 5) }}" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Jam Pulang</label>
                            <input type="time" name="jam_pulang" class="form-control" value="{{ substr($s->jam_pulang, 0, 5) }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Toleransi Keterlambatan (Menit)</label>
                        <input type="number" name="toleransi_terlambat" class="form-control" value="{{ $s->toleransi_terlambat }}" min="0">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_overnight" class="form-check-input" id="checkOvernight{{ $s->id }}" {{ $s->is_overnight ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-danger" for="checkOvernight{{ $s->id }}">
                            Shift Malam (Jam pulang berada di hari berikutnya / H+1)
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ $s->keterangan }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
$(document).ready(function() {
    // 1. Initialize Select2 for Roster Pegawai Dropdown
    if (typeof $.fn.select2 !== 'undefined') {
        $('#select_user_roster').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Cari / Pilih Pegawai --',
            allowClear: true,
            width: '100%'
        });
    }

    // 2. Real-time Search for Default Shift Table
    $('#searchDefaultShiftInput').on('keyup input', function() {
        const filter = $(this).val().toLowerCase();
        $('#tableDefaultShift tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(filter) > -1);
        });
    });
});
</script>
@endpush
@endsection
