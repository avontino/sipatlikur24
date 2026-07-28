@extends('layouts.master')

@section('content')
<style>
    .matrix-table-wrapper {
        max-height: 70vh;
        overflow: auto !important;
        position: relative;
    }
    
    .matrix-table-wrapper table {
        border-collapse: separate;
        border-spacing: 0;
    }

    /* Sticky Header Row */
    .matrix-table-wrapper thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #002366 !important;
        color: #ffffff !important;
        border-bottom: 2px solid #0a3d91;
    }

    .matrix-table-wrapper thead th.th-weekend {
        background-color: #dc3545 !important;
    }

    /* Sticky First & Second Column (No, Nama Guru) */
    .matrix-table-wrapper th.sticky-col-no,
    .matrix-table-wrapper td.sticky-col-no {
        position: sticky;
        left: 0;
        z-index: 11;
        background-color: #ffffff;
    }

    .matrix-table-wrapper th.sticky-col-nama,
    .matrix-table-wrapper td.sticky-col-nama {
        position: sticky;
        left: 40px;
        z-index: 11;
        background-color: #ffffff;
        border-right: 2px solid #dee2e6 !important;
    }

    .matrix-table-wrapper thead th.sticky-col-no {
        z-index: 20;
        background-color: #002366 !important;
    }

    .matrix-table-wrapper thead th.sticky-col-nama {
        z-index: 20;
        background-color: #002366 !important;
        border-right: 2px solid #0a3d91 !important;
    }
</style>

<section class="content pt-3">
    <div class="container-fluid">
        @if(session('sukses'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                <h3 class="fw-bold m-0" style="color: #002366;">
                    <i class="fas fa-list-alt me-2"></i>Rekap Presensi Guru & Pegawai
                </h3>
                <div class="d-flex gap-2">
                    <a href="/presensi-guru/shifts" class="btn btn-outline-primary btn-sm"><i class="fas fa-business-time me-1"></i> Kelola Shift & Roster</a>
                    <a href="/presensi-guru/setting" class="btn btn-outline-secondary btn-sm"><i class="fas fa-map-marker-alt me-1"></i> Pengaturan Lokasi</a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="/presensi-guru/rekap" class="row align-items-end g-3">
                    <input type="hidden" name="view_mode" value="{{ $viewMode }}">
                    <div class="col-md-2 col-sm-6 col-12">
                        <label for="start_date" class="form-label small fw-bold text-secondary">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-2 col-sm-6 col-12">
                        <label for="end_date" class="form-label small fw-bold text-secondary">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <label for="role_filter" class="form-label small fw-bold text-secondary">Kategori Role</label>
                        <select name="role_filter" id="role_filter" class="form-select">
                            <option value="all" {{ $roleFilter === 'all' ? 'selected' : '' }}>Semua (Guru & Tendik)</option>
                            <option value="guru" {{ $roleFilter === 'guru' ? 'selected' : '' }}>Hanya Guru</option>
                            <option value="tendik" {{ $roleFilter === 'tendik' ? 'selected' : '' }}>Hanya Tendik</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <label for="guru_id" class="form-label small fw-bold text-secondary">Pilih Nama Pegawai</label>
                        <select name="guru_id" id="guru_id" class="form-select select2">
                            <option value="all" {{ $guruId === 'all' ? 'selected' : '' }}>Semua Nama</option>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>{{ $g->name }} ({{ strtoupper($g->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill fw-bold">
                            <i class="fas fa-search me-1"></i> Tampilkan
                        </button>
                        <a href="/presensi-guru/rekap" class="btn btn-secondary" title="Reset Filter"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rekap Table Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="m-0 fw-bold text-dark card-title"><i class="fas fa-table me-2"></i> Data Presensi Guru</h5>
                    
                    <!-- View Mode Switcher -->
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="?view_mode=matrix&start_date={{ $startDate }}&end_date={{ $endDate }}&role_filter={{ $roleFilter }}&guru_id={{ $guruId }}" 
                           class="btn {{ $viewMode === 'matrix' ? 'btn-primary active' : 'btn-outline-primary' }}">
                            <i class="fas fa-th me-1"></i> Matriks Per Tanggal
                        </a>
                        <a href="?view_mode=table&start_date={{ $startDate }}&end_date={{ $endDate }}&role_filter={{ $roleFilter }}&guru_id={{ $guruId }}" 
                           class="btn {{ $viewMode === 'table' ? 'btn-primary active' : 'btn-outline-primary' }}">
                            <i class="fas fa-list me-1"></i> Log Detail
                        </a>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="/presensi-guru/export-excel?start_date={{ $startDate }}&end_date={{ $endDate }}&role_filter={{ $roleFilter }}&guru_id={{ $guruId }}&view_mode={{ $viewMode }}" class="btn btn-sm btn-success text-white">
                        <i class="fas fa-file-excel me-1"></i> Export Excel ({{ $viewMode === 'matrix' ? 'Matriks' : 'Log' }})
                    </a>
                    <a href="/presensi-guru/export-pdf?start_date={{ $startDate }}&end_date={{ $endDate }}&role_filter={{ $roleFilter }}&guru_id={{ $guruId }}&view_mode={{ $viewMode }}" class="btn btn-sm btn-danger text-white">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF ({{ $viewMode === 'matrix' ? 'Matriks' : 'Log' }})
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                @if($viewMode === 'matrix')
                <!-- TAMPILAN MATRIKS PER TANGGAL (ROW: PEGAWAI, COL: TANGGAL) -->
                <div class="matrix-table-wrapper">
                    <table class="table table-bordered table-hover align-middle mb-0 text-nowrap" style="font-size: 13px;">
                        <thead class="text-center align-middle text-white" style="background-color: #002366;">
                            <tr>
                                <th style="width: 40px;" class="sticky-col-no">No</th>
                                <th style="min-width: 200px;" class="sticky-col-nama">Nama Guru / Tendik</th>
                                <th style="width: 80px;">Role</th>
                                @foreach($periodDates as $dateStr)
                                    @php
                                        $cDate = \Carbon\Carbon::parse($dateStr);
                                        $isWeekend = $cDate->isWeekend();
                                    @endphp
                                    <th style="min-width: 75px;" class="{{ $isWeekend ? 'th-weekend' : '' }}">
                                        <div>{{ $cDate->format('d/m') }}</div>
                                        <small style="font-size: 10px; font-weight: normal;">{{ $cDate->locale('id')->isoFormat('dd') }}</small>
                                    </th>
                                @endforeach
                                <th style="width: 80px;" class="bg-success text-white">Total Hadir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $noMatrix = 1; @endphp
                            @forelse($matrixPegawai as $p)
                            @php $totalHadir = 0; @endphp
                            <tr>
                                <td class="text-center sticky-col-no">{{ $noMatrix++ }}</td>
                                <td class="fw-bold text-dark sticky-col-nama">
                                    {{ $p->name }}
                                </td>
                                <td class="text-center"><span class="badge bg-secondary" style="font-size: 10px;">{{ strtoupper($p->role) }}</span></td>
                                
                                @foreach($periodDates as $dateStr)
                                    @php
                                        $rec = $matrixAttendance[$p->id][$dateStr] ?? null;
                                        if ($rec) $totalHadir++;
                                    @endphp
                                    <td class="text-center p-1 align-middle">
                                        @if($rec)
                                            <div class="px-1 py-1 rounded shadow-xs border bg-light" style="line-height: 1.2;">
                                                <!-- Jam Datang -->
                                                <div class="fw-bold text-success font-monospace" style="font-size: 11px;" title="Jam Datang (Masuk)">
                                                    <i class="fas fa-arrow-down text-success" style="font-size: 9px;"></i> {{ substr($rec->jam_datang, 0, 5) }}
                                                </div>
                                                <div class="border-top my-1"></div>
                                                <!-- Jam Pulang -->
                                                <div class="fw-bold text-danger font-monospace" style="font-size: 11px;" title="Jam Pulang (Keluar)">
                                                    <i class="fas fa-arrow-up text-danger" style="font-size: 9px;"></i> {{ $rec->jam_pulang ? substr($rec->jam_pulang, 0, 5) : '--:--' }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="text-center fw-bold text-success fs-6 bg-light">
                                    {{ $totalHadir }} Hari
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($periodDates) + 4 }}" class="text-center text-muted py-4">Tidak ditemukan data pegawai untuk periode ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @else
                <!-- TAMPILAN TABEL LOG DETAIL (DATATABLES SERVER-SIDE AJAX) -->
                <div class="table-responsive p-3">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tableLogDetail" style="width: 100%;">
                        <thead class="text-center align-middle text-white" style="background-color: #002366;">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Guru / Tendik</th>
                                <th>Tanggal</th>
                                <th>Jam Datang</th>
                                <th>Jam Pulang</th>
                                <th>Status Masuk</th>
                                <th>Menit Terlambat</th>
                                <th>Status Pulang</th>
                                <th>Pulang Cepat (Menit)</th>
                                <th>Foto Datang</th>
                                <th>Foto Pulang</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Edit Presensi Modal -->
<div class="modal fade" id="editPresensiModal" tabindex="-1" aria-labelledby="editPresensiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="editPresensiModalLabel"><i class="fas fa-edit me-2"></i> Edit Presensi: <span id="edit-guru-nama" class="text-dark fw-bold"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/presensi-guru/update" method="POST">
                @csrf
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-tanggal" class="form-label small fw-bold text-secondary">Tanggal</label>
                        <input type="date" name="tanggal" id="edit-tanggal" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit-datang" class="form-label small fw-bold text-secondary">Jam Datang</label>
                            <input type="time" step="1" name="jam_datang" id="edit-datang" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="edit-pulang" class="form-label small fw-bold text-secondary">Jam Pulang</label>
                            <input type="time" step="1" name="jam_pulang" id="edit-pulang" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit-status" class="form-label small fw-bold text-secondary">Status Masuk</label>
                            <select name="status_datang" id="edit-status" class="form-select" required>
                                <option value="Tepat Waktu">Tepat Waktu</option>
                                <option value="Terlambat">Terlambat</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="edit-menit" class="form-label small fw-bold text-secondary">Menit Terlambat</label>
                            <input type="number" name="menit_terlambat" id="edit-menit" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit-status-pulang" class="form-label small fw-bold text-secondary">Status Pulang</label>
                            <select name="status_pulang" id="edit-status-pulang" class="form-select" required>
                                <option value="Selesai">Selesai</option>
                                <option value="Pulang Sebelum Waktunya">Pulang Sebelum Waktunya</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="edit-menit-pulang-cepat" class="form-label small fw-bold text-secondary">Menit Pulang Cepat</label>
                            <input type="number" name="menit_pulang_cepat" id="edit-menit-pulang-cepat" class="form-control" min="0" required>
                        </div>
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

<!-- Photo Viewer Modal -->
<div class="modal fade" id="photoViewerModal" tabindex="-1" aria-labelledby="photoViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="photoViewerModalLabel">Bukti Foto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-dark rounded-bottom p-3">
                <img id="modal-img-source" src="" alt="Bukti Foto" class="img-fluid rounded border shadow-sm" style="max-height: 500px;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const configJamMasuk = "{{ $config->jam_masuk }}";
    const configJamPulang = "{{ $config->jam_pulang }}";

    function populateEditModal(btn) {
        document.getElementById('edit-id').value = btn.getAttribute('data-id');
        document.getElementById('edit-guru-nama').innerText = btn.getAttribute('data-nama');
        document.getElementById('edit-tanggal').value = btn.getAttribute('data-tanggal');
        
        let datang = btn.getAttribute('data-datang') || '';
        document.getElementById('edit-datang').value = datang;

        let pulang = btn.getAttribute('data-pulang') || '';
        document.getElementById('edit-pulang').value = pulang;

        document.getElementById('edit-status').value = btn.getAttribute('data-status') || 'Tepat Waktu';
        document.getElementById('edit-menit').value = btn.getAttribute('data-menit') || 0;

        document.getElementById('edit-status-pulang').value = btn.getAttribute('data-statuspulang') || 'Selesai';
        document.getElementById('edit-menit-pulang-cepat').value = btn.getAttribute('data-menitpulangcepat') || 0;
    }

    function showPhoto(imgUrl, title) {
        document.getElementById('modal-img-source').src = imgUrl;
        document.getElementById('photoViewerModalLabel').innerText = title;
        
        const photoModal = new bootstrap.Modal(document.getElementById('photoViewerModal'));
        photoModal.show();
    }

    $(document).ready(function() {
        if ($('#tableLogDetail').length > 0) {
            $('#tableLogDetail').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/presensi-guru/rekap/data-log',
                    data: function(d) {
                        d.start_date = '{{ $startDate }}';
                        d.end_date = '{{ $endDate }}';
                        d.role_filter = '{{ $roleFilter }}';
                        d.guru_id = '{{ $guruId }}';
                    }
                },
                columns: [
                    { data: 'no', name: 'no', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nama', name: 'nama' },
                    { data: 'tanggal', name: 'tanggal', className: 'text-center' },
                    { data: 'jam_datang', name: 'jam_datang', className: 'text-center font-monospace' },
                    { data: 'jam_pulang', name: 'jam_pulang', className: 'text-center font-monospace' },
                    { data: 'status_datang', name: 'status_datang', className: 'text-center' },
                    { data: 'menit_terlambat', name: 'menit_terlambat', className: 'text-center font-monospace' },
                    { data: 'status_pulang', name: 'status_pulang', className: 'text-center' },
                    { data: 'menit_pulang_cepat', name: 'menit_pulang_cepat', className: 'text-center font-monospace' },
                    { data: 'foto_datang', name: 'foto_datang', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'foto_pulang', name: 'foto_pulang', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    processing: '<div class="d-flex justify-content-center py-3"><div class="spinner-border text-primary" role="status"></div></div>',
                    search: "Cari Data Log:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    zeroRecords: "Tidak ditemukan data presensi untuk periode dan filter ini."
                }
            });
        }
    });
</script>
@endpush
@endsection
