@extends('layouts.master')

@section('content')

<script type="text/javascript">
   setTimeout(function(){
       location.reload();
   },100000);
</script>

<section class="content-header">
    <div class="container-fluid">
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <i class="fa fa-check-circle"></i> 
            {{ session('sukses') }}
        </div>
        @endif

        <div class="card">
            <div class="row">
                <div class="col-md-12">
                    
                        <div class="card-header">
                            <h3 class="panel-title">Jurnal Harian</h3>
                        </div>
                        
                <div class="card-body">
                @if(auth()->user()->role=='admin' OR auth()->user()->role=='kurikulum')
                <form class="form-inline ml-auto" action="/jurnalh" method="get">
                         
                            <button type="submit" class="btn btn-sm btn-primary mr-sm-5" name="action" value="sinkron">Sinkron</button>
                    </form>
                    <form method="get" action="{{ route('jurnalh.exportExcel') }}">
    <label for="start_date">Mulai:</label>
    <input type="date" name="start_date" required>
    <label for="end_date">Akhir:</label>
    <input type="date" name="end_date" required>
    <button type="submit" class="btn btn-success">Export Excel</button>
</form>

<form method="get" action="{{ route('jurnalh.exportPDF') }}">
    <label for="start_date">Mulai:</label>
    <input type="date" name="start_date" required>
    <label for="end_date">Akhir:</label>
    <input type="date" name="end_date" required>
    <button type="submit" class="btn btn-danger">Export PDF</button>
</form>

@endif

@if(auth()->user()->role=='admin' OR auth()->user()->role=='kurikulum')
    <table id="example3" class="table table-bordered">
@else
    <table id="example4" class="table table-bordered">
@endif
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
        data-toggle="modal" 
        data-target="#absensiModal" 
        data-kelas="{{ $jurnalh->kelas }}" 
        data-tgl="{{ \Carbon\Carbon::parse($jurnalh->created_at)->format('Y-m-d') }}">
    Lihat Absensi
</button>



</td>
<td>    
           <!-- Tombol untuk membuka modal -->
           <button type="button" class="btn btn-primary btn-sm" 
        data-toggle="modal" 
        data-target="#absensiguruModal" 
        
        data-tgl="{{ \Carbon\Carbon::parse($jurnalh->created_at)->format('Y-m-d') }}">
    Lihat Absensi
</button>



</td>
            <td>{{ \Carbon\Carbon::parse($jurnalh->created_at)->format('d-m-Y') }}</td>  
            <td>
                
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

    
</section>

<!-- Modal untuk Menampilkan Data Absensi -->
<div class="modal fade" id="absensiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Absensi Siswa</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>










@endsection




