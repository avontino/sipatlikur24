@extends('layouts.master')

@section('content')
<section class="content">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4><i class="icon fa fa-check"></i> Berhasil!</h4>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4><i class="icon fa fa-times"></i> Error!</h4>
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4><i class="icon fa fa-times"></i> Terdapat kesalahan:</h4>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <!-- Filter dan Sync -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa fa-filter"></i> Filter Data
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('garjas.index') }}" class="form-inline">
            <div class="form-group me-3">
                <label for="bulan" class="me-2">Bulan:</label>
                <select name="bulan" id="bulan" class="form-control">
                    @foreach(['1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April', '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $key => $value)
                        <option value="{{ $key }}" {{ $bulan == $key ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group me-3">
                <label for="tahun" class="me-2">Tahun:</label>
                <select name="tahun" id="tahun" class="form-control">
                    @for($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            @if(Auth::user()->hasRole('pembina') || Auth::user()->hasRole('admin'))
            <div class="form-group me-3">
                <label for="kelas" class="me-2">Kelas:</label>
                <select name="kelas" id="kelas" class="form-control">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kelasItem)
                        <option value="{{ $kelasItem }}" {{ $kelas == $kelasItem ? 'selected' : '' }}>{{ $kelasItem }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <button type="submit" class="btn btn-primary me-2">
                <i class="fa fa-search"></i> Filter
            </button>
            
            @if(Auth::user()->hasRole('pembina') || Auth::user()->hasRole('admin'))
            <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#syncModal">
                <i class="fa fa-sync"></i> Sinkronisasi Siswa
            </button>
            
            <!-- Export Buttons -->
            <div class="btn-group me-2">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-download"></i> Export
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('garjas.export.excel', ['bulan' => $bulan, 'tahun' => $tahun, 'kelas' => $kelas]) }}">
                        <i class="fa fa-file-excel"></i> Export Excel
                    </a>
                    <a class="dropdown-item" href="{{ route('garjas.export.pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'kelas' => $kelas]) }}">
                        <i class="fa fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
            @endif
        </form>
    </div>
</div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-center w-100">
                        <strong>GARJAS TA. {{ $tahun }}-{{ $tahun+1 }}</strong><br>
                        @if($kelas) 
                            <strong>KELAS {{ $kelas }}</strong>
                        @else
                            <strong>SEMUA KELAS</strong>
                        @endif
                    </h3>
                    <div class="card-tools">
                        <button class="btn btn-success btn-sm" id="saveAllBtn" style="display: none;">
                            <i class="fa fa-save"></i> Simpan Semua Perubahan
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        @if(Auth::user()->hasRole('siswa'))
                        <strong>Petunjuk untuk Siswa:</strong> Klik pada kolom data latihan untuk mengedit. Data yang sudah dinilai pembina tidak dapat diubah.
                        @else
                        <strong>Petunjuk untuk {{ Auth::user()->hasRole('admin') ? 'Admin' : 'Pembina' }}:</strong> Klik pada kolom nilai untuk mengedit. Data latihan siswa ditampilkan sebagai referensi.
                        @endif
                        Data akan tersimpan otomatis setelah 2 detik tidak ada perubahan atau tekan Enter.<br>
                        <small class="text-muted">
                            <i class="fa fa-calculator"></i> 
                            <strong>Rumus Perhitungan:</strong> 
                            Nilai Garjas B = (Nilai UP + Nilai SIT UP + Nilai PUSH UP + Nilai SHUTTLE) ÷ 4 | 
                            Total Nilai = (Nilai Lari + Nilai Garjas B) ÷ 2
                        </small>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="garjasTable">
                            <thead class="bg-light">
                                <!-- Header Row 1 -->
                                <tr>
                                    <th rowspan="2" class="text-center align-middle" style="width: 3%;">NO</th>
                                    <th rowspan="2" class="text-center align-middle" style="width: 18%;">NAMA</th>
                                    <th colspan="2" class="text-center" style="background-color: #e3f2fd;">BATERAI A</th>
                                    <th colspan="8" class="text-center" style="background-color: #f3e5f5;">BATERAI B</th>
                                    <th rowspan="2" class="text-center align-middle" style="width: 6%; background-color: #fff3e0;">NILAI<br>GARJAS B</th>
                                    <th rowspan="2" class="text-center align-middle" style="width: 6%; background-color: #e8f5e8;">TOTAL<br>NILAI</th>
                                    <th rowspan="2" class="text-center align-middle" style="width: 6%;">AKSI</th>
                                </tr>
                                <!-- Header Row 2 -->
                                <tr>
                                    <!-- Baterai A -->
                                    <th class="text-center" style="width: 6%; background-color: #e3f2fd;">LARI</th>
                                    <th class="text-center" style="width: 6%; background-color: #e3f2fd;">NILAI</th>
                                    <!-- Baterai B -->
                                    <th class="text-center" style="width: 6%; background-color: #f3e5f5;">UP/CHIN</th>
                                    <th class="text-center" style="width: 6%; background-color: #f3e5f5;">NILAI</th>
                                    <th class="text-center" style="width: 6%; background-color: #f3e5f5;">SIT UP</th>
                                    <th class="text-center" style="width: 6%; background-color: #f3e5f5;">NILAI</th>
                                    <th class="text-center" style="width: 6%; background-color: #f3e5f5;">PUSH UP</th>
                                    <th class="text-center" style="width: 6%; background-color: #f3e5f5;">NILAI</th>
                                    <th class="text-center" style="width: 6%; background-color: #f3e5f5;">SHUTTLE R</th>
                                    <th class="text-center" style="width: 6%; background-color: #f3e5f5;">NILAI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($garjas as $key => $item)
                                <tr data-id="{{ $item->id }}">
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td><strong>{{ $item->nama }}</strong></td>
                                    
                                    @if(Auth::user()->hasRole('siswa'))
                                        <!-- BATERAI A - Data untuk Siswa -->
                                        <td class="text-center" style="background-color: #e3f2fd;">
                                            @if($item->canEditByStudent('lari'))
                                            <input type="number" class="form-control form-control-sm editable-field" 
                                                   data-field="lari" data-id="{{ $item->id }}" 
                                                   value="{{ $item->lari }}" placeholder="0" style="width: 60px;">
                                            @else
                                            <span class="text-muted">{{ $item->lari ?? '-' }}</span>
                                            <small class="text-danger d-block" style="font-size: 9px;">Sudah dinilai</small>
                                            @endif
                                        </td>
                                        <td class="text-center" style="background-color: #e3f2fd;">
                                            <span class="badge badge-info">{{ $item->nlari ?? 0 }}</span>
                                        </td>
                                        
                                        <!-- BATERAI B - Data untuk Siswa -->
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            @if($item->canEditByStudent('up'))
                                            <input type="number" class="form-control form-control-sm editable-field" 
                                                   data-field="up" data-id="{{ $item->id }}" 
                                                   value="{{ $item->up }}" placeholder="0" style="width: 60px;">
                                            @else
                                            <span class="text-muted">{{ $item->up ?? '-' }}</span>
                                            <small class="text-danger d-block" style="font-size: 9px;">Sudah dinilai</small>
                                            @endif
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <span class="badge badge-info">{{ $item->nup ?? 0 }}</span>
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            @if($item->canEditByStudent('situp'))
                                            <input type="number" class="form-control form-control-sm editable-field" 
                                                   data-field="situp" data-id="{{ $item->id }}" 
                                                   value="{{ $item->situp }}" placeholder="0" style="width: 60px;">
                                            @else
                                            <span class="text-muted">{{ $item->situp ?? '-' }}</span>
                                            <small class="text-danger d-block" style="font-size: 9px;">Sudah dinilai</small>
                                            @endif
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <span class="badge badge-info">{{ $item->nsitup ?? 0 }}</span>
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            @if($item->canEditByStudent('pushup'))
                                            <input type="number" class="form-control form-control-sm editable-field" 
                                                   data-field="pushup" data-id="{{ $item->id }}" 
                                                   value="{{ $item->pushup }}" placeholder="0" style="width: 60px;">
                                            @else
                                            <span class="text-muted">{{ $item->pushup ?? '-' }}</span>
                                            <small class="text-danger d-block" style="font-size: 9px;">Sudah dinilai</small>
                                            @endif
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <span class="badge badge-info">{{ $item->npushup ?? 0 }}</span>
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            @if($item->canEditByStudent('shuttle'))
                                            <input type="number" step="0.01" class="form-control form-control-sm editable-field" 
                                                   data-field="shuttle" data-id="{{ $item->id }}" 
                                                   value="{{ $item->shuttle }}" placeholder="0.00" style="width: 60px;">
                                            @else
                                            <span class="text-muted">{{ $item->shuttle ?? '-' }}</span>
                                            <small class="text-danger d-block" style="font-size: 9px;">Sudah dinilai</small>
                                            @endif
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <span class="badge badge-info">{{ $item->nshuttle ?? 0 }}</span>
                                        </td>
                                        
                                        <!-- Nilai Garjas B -->
                                        <td class="text-center" style="background-color: #fff3e0;">
                                            <span class="badge badge-warning nb-badge">{{ number_format($item->nb ?? 0, 2) }}</span>
                                            <small class="text-info d-block" style="font-size: 8px;">Auto</small>
                                        </td>
                                        
                                        <!-- Total Nilai -->
                                        <td class="text-center" style="background-color: #e8f5e8;">
                                            <span class="badge badge-success badge-lg total-badge">{{ number_format($item->total ?? 0, 2) }}</span>
                                            <small class="text-info d-block" style="font-size: 8px;">Auto</small>
                                        </td>
                                    @else
                                        <!-- BATERAI A - View untuk Pembina/Admin -->
                                        <td class="text-center" style="background-color: #e3f2fd;">
                                            <span class="text-muted small">{{ $item->lari ?? '-' }}</span>
                                        </td>
                                        <td class="text-center" style="background-color: #e3f2fd;">
                                            <input type="number" class="form-control form-control-sm editable-field" 
                                                   data-field="nlari" data-id="{{ $item->id }}" 
                                                   value="{{ $item->nlari }}" placeholder="0" 
                                                   min="0" max="100" style="width: 50px;">
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <span class="text-muted small">{{ $item->up ?? '-' }}</span>
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <input type="number" class="form-control form-control-sm editable-field" 
                                                   data-field="nup" data-id="{{ $item->id }}" 
                                                   value="{{ $item->nup }}" placeholder="0" 
                                                   min="0" max="100" style="width: 50px;">
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <span class="text-muted small">{{ $item->situp ?? '-' }}</span>
                                        </td>
                                        
                                        <!-- BATERAI B - View untuk Pembina/Admin -->
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <input type="number" class="form-control form-control-sm editable-field" 
                                                   data-field="nsitup" data-id="{{ $item->id }}" 
                                                   value="{{ $item->nsitup }}" placeholder="0" 
                                                   min="0" max="100" style="width: 50px;">
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <span class="text-muted small">{{ $item->pushup ?? '-' }}</span>
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <input type="number" class="form-control form-control-sm editable-field" 
                                                   data-field="npushup" data-id="{{ $item->id }}" 
                                                   value="{{ $item->npushup }}" placeholder="0" 
                                                   min="0" max="100" style="width: 50px;">
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <span class="text-muted small">{{ $item->shuttle ?? '-' }}</span>
                                        </td>
                                        <td class="text-center" style="background-color: #f3e5f5;">
                                            <input type="number" class="form-control form-control-sm editable-field" 
                                                   data-field="nshuttle" data-id="{{ $item->id }}" 
                                                   value="{{ $item->nshuttle }}" placeholder="0" 
                                                   min="0" max="100" style="width: 50px;">
                                        </td>
                                        
                                        <!-- Nilai Garjas B -->
                                        <td class="text-center" style="background-color: #fff3e0;">
                                            <span class="badge badge-warning">{{ number_format($item->nb ?? 0, 2) }}</span>
                                            <small class="text-info d-block" style="font-size: 8px;">Auto</small>
                                        </td>
                                        
                                        <!-- Total Nilai -->
                                        <td class="text-center" style="background-color: #e8f5e8;">
                                            <span class="badge badge-success badge-lg total-badge">{{ number_format($item->total ?? 0, 2) }}</span>
                                            <small class="text-info d-block" style="font-size: 8px;">Auto</small>
                                        </td>
                                    @endif
                                    
                                    <!-- Aksi -->
                                    <td class="text-center">
                                        @if(Auth::user()->hasRole('pembina') || Auth::user()->hasRole('admin') || (Auth::user()->hasRole('siswa') && $item->nis == Auth::user()->username))
                                        <div class="btn-group">
                                            <button class="btn btn-success btn-xs save-row-btn" 
                                                    data-id="{{ $item->id }}" 
                                                    style="display: none;"
                                                    title="Simpan">
                                                <i class="fa fa-save"></i>
                                            </button>
                                            @if(Auth::user()->hasRole('pembina') || Auth::user()->hasRole('admin'))
                                            <form action="{{ route('garjas.destroy', $item->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs" 
                                                    onclick="return confirm('Yakin ingin menghapus data {{ $item->nama }}?')"
                                                    title="Hapus">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" class="text-center text-muted">
                                        <i class="fa fa-info-circle"></i> Tidak ada data tersedia
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Sync Siswa -->
@if(Auth::user()->hasRole('pembina') || Auth::user()->hasRole('admin'))
<div class="modal fade" id="syncModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('garjas.sync') }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <button type="button" class="close text-white" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title text-white">
                        <i class="fa fa-sync"></i> Sinkronisasi Data Siswa
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        Sinkronisasi akan mengambil semua data siswa dan membuat record garjas kosong untuk periode yang dipilih.
                    </div>
                    <div class="form-group">
                        <label for="sync_bulan">Bulan</label>
                        <select name="bulan" id="sync_bulan" class="form-control" required>
                            @foreach(['1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April', '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $key => $value)
                                <option value="{{ $key }}" {{ $bulan == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sync_tahun">Tahun</label>
                        <select name="tahun" id="sync_tahun" class="form-control" required>
                            @for($i = date('Y'); $i >= 2020; $i--)
                                <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sync_kelas">Kelas (Opsional)</label>
                        <select name="kelas" id="sync_kelas" class="form-control">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelasItem)
                                <option value="{{ $kelasItem }}">{{ $kelasItem }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-sync"></i> Sinkronisasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Custom Styles -->
<style>
/* === TABLE LAYOUT === */
.table-responsive {
    font-size: 14px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

#garjasTable {
    min-width: 1500px;
}

.table thead th {
    border: 2px solid #333;
    font-weight: bold;
    font-size: 13px;
    padding: 8px 6px;
    text-align: center;
    vertical-align: middle;
    background-color: #f8f9fa;
}

.table tbody td {
    border: 1px solid #666;
    padding: 6px 4px;
    font-size: 14px;
}

/* === STICKY COLUMNS === */
#garjasTable thead th:nth-child(1),
#garjasTable thead th:nth-child(2),
#garjasTable tbody td:nth-child(1),
#garjasTable tbody td:nth-child(2) {
    position: sticky;
    background-color: #fff;
    z-index: 10;
}

#garjasTable thead th:nth-child(1),
#garjasTable tbody td:nth-child(1) {
    left: 0;
}

#garjasTable thead th:nth-child(2),
#garjasTable tbody td:nth-child(2) {
    left: 50px;
}

/* === INPUT FIELDS === */
.editable-field {
    text-align: center;
    border: 1px solid #ddd;
    transition: all 0.3s ease;
    font-size: 14px;
    padding: 4px 6px;
    height: auto;
    min-width: 70px;
    width: 75px;
    margin: 0 auto;
}

.editable-field:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    background-color: #fff;
}

.editable-field:hover {
    border-color: #80bdff;
    background-color: #fff;
}

/* Styling untuk field nilai pembina */
.editable-field[data-field^="n"] {
    font-weight: 600;
    background-color: #e3f2fd;
    border: 2px solid #bbdefb;
}

.editable-field[data-field^="n"]:focus {
    background-color: #fff;
    border-color: #2196f3;
    box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
}

/* === BADGES === */
.badge {
    font-size: 13px;
    padding: 4px 8px;
    min-width: 35px;
}

/* === BUTTONS === */
.btn-xs {
    padding: 3px 8px;
    font-size: 11px;
    border-radius: 3px;
}

/* === TEXT STYLING === */
.text-muted.small {
    font-size: 14px;
    display: block;
    color: #6c757d;
    font-weight: 500;
    background-color: #f8f9fa;
    padding: 4px 6px;
    border-radius: 3px;
    text-align: center;
    min-height: 30px;
    line-height: 22px;
}

.text-danger[style*="font-size: 9px"] {
    font-size: 10px !important;
    margin-top: 2px;
    font-weight: 500;
}

/* === INTERACTIVE STATES === */
.field-changed {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
    background-color: #fff3cd;
}

.saving-row {
    background-color: rgba(0, 123, 255, 0.1) !important;
    opacity: 0.8;
    animation: pulse 1s infinite;
}

.save-success {
    background-color: rgba(40, 167, 69, 0.2) !important;
    transition: background-color 0.3s ease;
}

.save-error {
    background-color: rgba(220, 53, 69, 0.2) !important;
    transition: background-color 0.3s ease;
}

/* === ANIMATIONS === */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 12px;
    }
    
    .editable-field {
        width: 50px !important;
        font-size: 11px;
        min-width: 45px;
    }
    
    .table thead th,
    .table tbody td {
        padding: 4px 2px;
    }
    
    .badge {
        font-size: 10px;
        padding: 2px 6px;
    }
}
</style>

@endsection