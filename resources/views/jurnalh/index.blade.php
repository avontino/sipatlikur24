@extends('layouts.master')

@section('content')

<script type="text/javascript">
   setTimeout(function(){
       location.reload();
   },100000);
</script>

<section class="content pt-3">
    <div class="container-fluid">
        <!-- Flash Messages -->
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

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light py-3">
                        <h3 class="fw-bold m-0" style="color: #004d1a;"><i class="fas fa-newspaper me-2"></i>Data Jurnal Harian Sekolah</h3>
                    </div>
                    
                    <div class="card-body px-4">
                        @php
                            $user = auth()->user();
                            $isAdminOrKuri = ($user->role=='admin' || $user->role=='kurikulum'
                                || $user->hasRole('admin') || $user->hasRole('kurikulum'));
                            $showSyncPanel = $isAdminOrKuri && 
                                ($user->role=='admin' || $user->hasRole('admin') 
                                 || request()->query('view') === 'kurikulum');
                            $filterOwnTeacherOnly = false; // Guru melihat semua slot jadwal per kelas (jurnal harian lengkap)
                            $cleanAuthName = trim(preg_replace('/,.*$/', '', $user->name));
                        @endphp
                        @if($showSyncPanel)
                        <div class="row gx-5 gy-4 mb-4 px-2">
                            <!-- Kolom 1: Sinkronisasi Jurnal -->
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label fw-semibold text-muted small text-uppercase mb-2 d-block">Sinkronisasi Jadwal</label>
                                <form method="GET" action="/jurnalh" class="w-100">
                                    {{-- Pastikan parameter view selalu terkirim agar controller masuk blok admin/kurikulum --}}
                                    <input type="hidden" name="view" value="kurikulum">
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="input-group input-group-sm" style="width: auto; flex-grow: 1;">
                                            <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                            <input name="tanggal_sinkron" type="date" class="form-control" value="{{ request('tanggal_sinkron', date('Y-m-d')) }}">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary px-3" name="action" value="sinkron" title="Sinkron Tanggal Ini">
                                            <i class="fas fa-sync me-1"></i> Sinkron
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-danger text-white px-3" name="action" value="hapus_sinkron" title="Hapus Sinkron Tanggal Ini" onclick="return confirm('Apakah Anda yakin ingin menghapus seluruh data sinkronisasi jurnal pada tanggal yang dipilih?')">
                                            <i class="fas fa-trash me-1"></i> Hapus
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Kolom 2: Export Excel -->
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label fw-semibold text-muted small text-uppercase mb-2 d-block">Ekspor Laporan Excel</label>
                                <form method="get" action="{{ route('jurnalh.exportExcel') }}" class="w-100">
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="input-group input-group-sm" style="width: auto; flex-grow: 1;">
                                            <span class="input-group-text">Dari</span>
                                            <input type="date" name="start_date" class="form-control" required>
                                            <span class="input-group-text">Ke</span>
                                            <input type="date" name="end_date" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-success text-white px-3">
                                            <i class="fas fa-file-excel me-1"></i> Excel
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Kolom 3: Export PDF -->
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label fw-semibold text-muted small text-uppercase mb-2 d-block">Ekspor Laporan PDF</label>
                                <form method="get" action="{{ route('jurnalh.exportPDF') }}" class="w-100">
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="input-group input-group-sm" style="width: auto; flex-grow: 1;">
                                            <span class="input-group-text">Dari</span>
                                            <input type="date" name="start_date" class="form-control" required>
                                            <span class="input-group-text">Ke</span>
                                            <input type="date" name="end_date" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-danger text-white px-3">
                                            <i class="fas fa-file-pdf me-1"></i> PDF
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @else
                        <!-- Filter Tanggal & Informasi Kelas untuk Ketua Kelas / Wali Kelas / Guru -->
                        <div class="row align-items-center mb-4 px-2">
                            <div class="col-md-7 col-12">
                                <form method="GET" action="/jurnalh" class="d-flex align-items-center gap-2 flex-wrap">
                                    @if(request()->filled('view'))
                                        <input type="hidden" name="view" value="{{ request('view') }}">
                                    @endif
                                    <label for="tanggal" class="form-label fw-bold text-secondary mb-0 small">Pilih Tanggal Jurnal:</label>
                                    <div class="input-group input-group-sm" style="width: auto;">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ request('tanggal', date('Y-m-d')) }}">
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary fw-bold">
                                        <i class="fas fa-search me-1"></i> Tampilkan
                                    </button>
                                    @if(request()->filled('tanggal') && request('tanggal') !== date('Y-m-d'))
                                        <a href="/jurnalh{{ request()->filled('view') ? '?view='.request('view') : '' }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo me-1"></i> Kembali Ke Hari Ini</a>
                                    @endif
                                </form>
                            </div>
                            <div class="col-md-5 col-12 text-md-end text-start mt-2 mt-md-0">
                                @if(request()->query('view') === 'walikelas' && isset($myClass) && $myClass)
                                    <span class="badge bg-primary fs-6 px-3 py-2">
                                        <i class="fas fa-chalkboard-teacher me-1"></i> Jurnal Perwalian Kelas: {{ $myClass }}
                                    </span>
                                @elseif(!request()->filled('view') && auth()->user()->role !== 'siswa')
                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        <i class="fas fa-book-reader me-1"></i> Jurnal Mengajar Saya ({{ auth()->user()->name }})
                                    </span>
                                @elseif(isset($myClass) && $myClass)
                                    <span class="badge bg-primary fs-6 px-3 py-2">
                                        <i class="fas fa-chalkboard-teacher me-1"></i> Jurnal Kelas: {{ $myClass }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="table-responsive">
                            <table id="jurnalh-table" class="table table-bordered table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>Kelas</th>
                <th>Jam ke-1</th>
                <th>Jam ke-2</th>
                <th>Jam ke-3</th>
                <th>Jam ke-4</th>
                <th>Jam ke-5</th>
                <th>Jam ke-6</th>
                <th>Jam ke-7</th>
                <th>Jam ke-8</th>
                <th>Jam ke-9</th>
                <th>Jam ke-10</th>
                <th>Jam ke-11</th>
                <th>Absensi Siswa</th>
                <th>Absensi Guru</th>
                <th>Tanggal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jurnalhs as $jurnalh)
            <tr>
            <td>{{ $jurnalh->kelas }}</td>
            @for ($i = 1; $i <= 11; $i++)
    @php
        $columnData = $jurnalh->{'j'.$i} ?? '';
        $displayContent = '';
        
        if (!empty($columnData)) {
            $segments = explode('<hr>', $columnData);
            
            // PERBAIKAN: Process dengan deteksi dinamis ukuran segment
            for ($j = 0; $j < count($segments);) {
                if (isset($segments[$j], $segments[$j + 1], $segments[$j + 2])) {
                    $guruSegment = $segments[$j];
                    $mapelSegment = $segments[$j + 1];
                    $materiSegment = $segments[$j + 2];
                    
                    // PERBAIKAN: Cek apakah ada badge segment (segment ke-4)
                    $badgeSegment = '';
                    $segmentSize = 3; // Default size
                    
                    if (isset($segments[$j + 3]) && 
                        (strpos($segments[$j + 3], 'badge') !== false || 
                         strpos($segments[$j + 3], 'KBM Tanpa Guru') !== false)) {
                        $badgeSegment = $segments[$j + 3];
                        $segmentSize = 4; // Ada badge, jadi 4 segment
                    }
                    
                    // Filter jam mengajar hanya milik guru yang sedang login jika tampilan guru
                    if ($filterOwnTeacherOnly) {
                        $isMySegment = (strpos(strtoupper($guruSegment), strtoupper($cleanAuthName)) !== false) || 
                                       (strpos(strtoupper($guruSegment), strtoupper($user->name)) !== false);
                        if (!$isMySegment) {
                            $j += $segmentSize;
                            continue;
                        }
                    }
                    
                    // Tentukan styling untuk segment ini
                    $segmentClass = '';
                    $hasKBMBadge = !empty($badgeSegment) && strpos($badgeSegment, 'KBM Tanpa Guru') !== false;
                    
                    if ($hasKBMBadge) {
                        // Styling untuk KBM Tanpa Guru (warna kuning)
                        $segmentClass = 'style="background-color: #fff3cd; padding: 5px; margin: 2px; border-radius: 5px; border-left: 4px solid #ffc107;"';
                    } elseif (strpos($materiSegment, 'Jam Kosong') !== false && !preg_match('/(Bab|Topik|Tugas|TP|Pelajaran)\s?\d/', $materiSegment)) {
                        // Styling untuk Jam Kosong
                        $segmentClass = 'style="background-color: #f8d7da; padding: 5px; margin: 2px; border-radius: 5px; border-left: 4px solid #dc3545;"';
                    } else {
                        // Styling untuk pembelajaran normal
                        $segmentClass = 'style="background-color: #eefdebff; padding: 5px; margin: 2px; border-radius: 5px; border-left: 4px solid #9af89fff;"';
                    }
                    
                    // Tambahkan separator jika bukan segment pertama
                    if ($j > 0) $displayContent .= '<hr style="margin: 8px 0; border-color: #dee2e6;">';
                    
                    // Build display content
                    $displayContent .= '<div ' . $segmentClass . '>';
                    $displayContent .= '<div style="font-weight: bold; color: #495057; margin-bottom: 2px;">' . $guruSegment . '</div>';
                    $displayContent .= '<div style="font-size: 0.9em; color: #6c757d; margin-bottom: 2px;">' . $mapelSegment . '</div>';
                    $displayContent .= '<div style="color: #212529;">' . $materiSegment . '</div>';
                    
                    // PERBAIKAN: Tambahkan badge jika ada
                    if (!empty($badgeSegment)) {
                        $displayContent .= '<div style="margin-top: 5px;">' . $badgeSegment . '</div>';
                    }
                    
                    $displayContent .= '</div>';
                    
                    // PERBAIKAN: Increment berdasarkan ukuran segment yang sebenarnya
                    $j += $segmentSize;
                } else {
                    // Handle segment yang tidak lengkap
                    $j++;
                }
            }
        }
    @endphp
    
    <td style="vertical-align: top; padding: 8px;">
        {!! $displayContent !!}
    </td>
@endfor

{{-- TAMBAHAN: CSS untuk styling badge yang lebih baik --}}
<style>
.badge-danger {
    background-color: #dc3545 !important;
    color: white !important;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75em;
    font-weight: bold;
    display: inline-block;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.badge-success {
    background-color: #28a745 !important;
    color: white !important;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75em;
    font-weight: bold;
    display: inline-block;
}

/* Animasi untuk badge KBM Tanpa Guru */
.badge-danger {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Styling untuk tabel */
td {
    border: 1px solid #dee2e6;
    padding: 8px;
}

/* Responsive design */
@media (max-width: 768px) {
    td {
        padding: 4px;
        font-size: 0.85em;
    }
}
</style>




            <td>    
           <!-- Tombol untuk membuka modal -->
           <button type="button" class="btn btn-primary btn-sm" 
        data-bs-toggle="modal" 
        data-bs-target="#absensiModal" 
        data-kelas="{{ $jurnalh->kelas }}" 
        data-tgl="{{ \Carbon\Carbon::parse($jurnalh->created_at)->format('Y-m-d') }}">
    Lihat Absensi
</button>



</td>
<td>    
           <!-- Tombol untuk membuka modal -->
           <button type="button" class="btn btn-primary btn-sm" 
        data-bs-toggle="modal" 
        data-bs-target="#absensiguruModal" 
        
        data-tgl="{{ \Carbon\Carbon::parse($jurnalh->created_at)->format('Y-m-d') }}">
    Lihat Absensi
</button>



</td>
            <td>{{ \Carbon\Carbon::parse($jurnalh->created_at)->format('d-m-Y') }}</td>  
            <td>
                @if((auth()->user()->role=='admin' || auth()->user()->role=='kurikulum' || auth()->user()->hasRole('admin') || auth()->user()->hasRole('kurikulum')) && request()->query('view') === 'kurikulum')
                    <a href="/jurnalh/{{ $jurnalh->id }}/delete" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data jurnal kelas ini?')">Hapus</a>
                @else
                    -
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
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable.isDataTable('#jurnalh-table')) {
        $('#jurnalh-table').DataTable().destroy();
    }

    @if(auth()->user()->role=='admin' || auth()->user()->role=='kurikulum' || auth()->user()->hasRole('admin') || auth()->user()->hasRole('kurikulum'))
    // Server-Side mode untuk admin/kurikulum
    $('#jurnalh-table').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: "{{ url('/jurnalh') }}",
            data: function(d) {
                d.view = "{{ request('view') }}";
            }
        },
        columns: [
            { data: 'kelas',         name: 'kelas' },
            { data: 'j1',            name: 'j1',  orderable: false },
            { data: 'j2',            name: 'j2',  orderable: false },
            { data: 'j3',            name: 'j3',  orderable: false },
            { data: 'j4',            name: 'j4',  orderable: false },
            { data: 'j5',            name: 'j5',  orderable: false },
            { data: 'j6',            name: 'j6',  orderable: false },
            { data: 'j7',            name: 'j7',  orderable: false },
            { data: 'j8',            name: 'j8',  orderable: false },
            { data: 'j9',            name: 'j9',  orderable: false },
            { data: 'j10',           name: 'j10', orderable: false },
            { data: 'j11',           name: 'j11', orderable: false },
            { data: 'absensi_siswa', name: 'absensi_siswa', orderable: false, searchable: false },
            { data: 'absensi_guru',  name: 'absensi_guru',  orderable: false, searchable: false },
            { data: 'tanggal',       name: 'tanggal' },
            { data: 'action',        name: 'action', orderable: false, searchable: false },
        ],
        order: [[14, 'desc']],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: "Cari Jurnal Harian:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Data tidak ditemukan"
        },
        columnDefs: [
            { targets: '_all', defaultContent: '-', render: function(data) { return data || '-'; } }
        ]
    });
    @else
    // Client-side mode untuk role lain (data sedikit)
    $('#jurnalh-table').DataTable({
        scrollX: true,
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: false,
        info: true,
        autoWidth: true,
        language: {
            search: "Cari Data:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Belum ada data jurnal harian untuk kelas {{ $myClass ?? '-' }} pada tanggal {{ \Carbon\Carbon::parse($targetDate ?? date('Y-m-d'))->format('d-m-Y') }}",
            zeroRecords: "Belum ada data jurnal harian untuk kelas {{ $myClass ?? '-' }} pada tanggal {{ \Carbon\Carbon::parse($targetDate ?? date('Y-m-d'))->format('d-m-Y') }}",
            emptyTable: "Belum ada data jurnal harian untuk kelas {{ $myClass ?? '-' }} pada tanggal {{ \Carbon\Carbon::parse($targetDate ?? date('Y-m-d'))->format('d-m-Y') }}"
        }
    });
    @endif
});
</script>

<!-- Modal untuk Menampilkan Data Absensi -->
<div class="modal fade" id="absensiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Absensi Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Menampilkan informasi kelas dan tanggal -->
        <h5>Absensi Siswa Kelas <span id="modalKelas"></span> pada tanggal <span id="modalTanggal"></span></h5>

        <!-- Tabel Absensi -->
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Nama Siswa</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody id="absensiTableBody">
            <!-- Data absensi akan dimasukkan di sini melalui AJAX -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal untuk Menampilkan Data Absensi -->
<div class="modal fade" id="absensiguruModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Absensi Guru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Menampilkan informasi kelas dan tanggal -->
        <h5>Absensi Guru pada tanggal <span id="modalTanggal"></span></h5>

        <!-- Tabel Absensi -->
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Nama Guru</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody id="absensiguruTableBody">
            <!-- Data absensi akan dimasukkan di sini melalui AJAX -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>










@endsection




