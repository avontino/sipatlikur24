@extends('layouts.master')

@section('content')  
</br>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Absensi Siswa</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row g-4 row-cols-1 row-cols-md-3 mb-4">
            <div class="col">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{$sakit}}</h3>
                        <p>Total Siswa Sakit Sampai Hari Ini</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-head-side-cough"></i>
                    </div>
                </div>
            </div>

            <div class="col">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{$ijin}}</h3>
                        <p>Total Siswa Ijin Sampai Hari Ini</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-signature"></i>
                    </div>
                </div>
            </div>

            <div class="col">
                <!-- small box -->
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{$alpha}}</h3>
                        <p>Total Siswa Alpha Sampai Hari Ini</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
            </div>
        </div>  

        <!-- Flash Messages -->
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{session('sukses')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        @if(session('gagal'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-1"></i> {{session('gagal')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!--Tabel Absen Siswa-->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title m-0">Absen Siswa</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                 @php
                                    $isWaliView = (request()->query('view') === 'walikelas' || (auth()->user()->hasRole('walikelas') || auth()->user()->walikelas_kelas || auth()->user()->role === 'ketuakelas') && !(auth()->user()->hasRole('kurikulum') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('lihat')));
                                @endphp
                                <form class="d-flex flex-wrap gap-2 gap-md-3 align-items-center" method="GET" action="/absen">
                                    @if(request()->has('view'))
                                        <input type="hidden" name="view" value="{{ request('view') }}">
                                    @endif
                                    <div class="input-group input-group-sm" style="width: auto;">
                                        <input name="crtgl" type="date" class="form-control" value="{{ request('crtgl') }}">
                                        <button type="submit" class="btn btn-primary" name="action" value="tanggal">Filter Tanggal</button>
                                    </div>
                                    
                                    @if(!$isWaliView && auth()->user()->role != 'siswa')
                                        <div class="input-group input-group-sm" style="width: auto;">
                                            <select name="kelas" class="form-select">
                                                @foreach($ke_las as $kelas)
                                                    <option value="{{$kelas->kelas}}" {{ request('kelas') == $kelas->kelas ? 'selected' : '' }}>{{$kelas->kelas}}</option>
                                                @endforeach                 
                                            </select>        
                                            <button type="submit" class="btn btn-primary" name="action" value="kelas">Filter Kelas</button>
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-outline-primary" name="action" value="kelastgl">Filter Kelas & Tanggal</button>
                                    @endif

                                    @if(auth()->user()->role=='admin' || auth()->user()->role=='kesiswaan' || auth()->user()->role=='walikelas' || auth()->user()->role=='ketuakelas' || auth()->user()->hasRole('walikelas') || auth()->user()->hasRole('ketuakelas') || (auth()->user()->role=='guru' && auth()->user()->walikelas_kelas))
                                     <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahabsen">
                                         <i class="fas fa-plus me-1"></i> Tambah Absensi Siswa
                                     </button>
                                     @endif
                                    
                                    @if(auth()->user()->role != 'siswa')
                                    <button type="button" class="btn btn-sm btn-success text-white" data-bs-toggle="modal" data-bs-target="#exportAbsenModal">
                                         <i class="fas fa-file-excel me-1"></i> Export Data
                                     </button>
                                    @endif
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="example3" class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                <tr>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Keterangan</th>
                    <th>Waktu</th>
                    @if(auth()->user()->role=='admin' || auth()->user()->role=='walikelas' || auth()->user()->role=='ketuakelas' || (auth()->user()->role=='guru' && auth()->user()->walikelas_kelas))
                    <th>Aksi</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                @foreach($ab_sen as $absen)
                  <tr>
                    <td>{{$absen->nama}}</td>
                    <td>{{$absen->kelas}}</td>
                    <td>{{$absen->ket}}</td>
                    <td>{{$absen->created_at->format ('d-m-Y H:m:s')}}</td>

                    @if(auth()->user()->role=='admin' || auth()->user()->role=='walikelas' || auth()->user()->role=='ketuakelas' || (auth()->user()->role=='guru' && auth()->user()->walikelas_kelas))
                    <td>


                  <a href="/absen/{{$absen->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>

                    </td>
                    @endif
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>

                </tfoot>
                            </table>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah Absen-->
<div class="modal fade" id="tambahabsen" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Tambah Absensi Siswa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form action="/absen/create" method="POST"> 
                  {{csrf_field()}}
              
              <div class="form-group mb-3">
                <label for="tgl" class="form-label fw-bold">Tanggal Absensi</label>
                <input name="tgl" type="date" class="form-control" id="tgl" value="{{ request('crtgl', date('Y-m-d')) }}" required>
                <small class="form-text text-muted">*Pilih tanggal (bisa tanggal lampau jika lupa menginput absensi).</small>
              </div>

             <!-- Dropdown Kelas -->
          <div class="form-group">
            <label for="kelasSelectModal">Kelas</label>
            @php
              $userManagedClass = auth()->user()->getManagedClass();
            @endphp
            <select name="kelas" id="kelasSelectModal" class="form-control" required>
              @if($userManagedClass)
                <option value="{{ $userManagedClass }}" selected>{{ $userManagedClass }}</option>
              @else
                <option value="">Pilih Kelas</option>
                @foreach($ke_las as $kelas)
                  <option value="{{ $kelas->kelas }}">{{ $kelas->kelas }}</option>
                @endforeach
              @endif
            </select>
          </div>

          <!-- Dropdown Nama Siswa -->
          <div class="form-group">
            <label for="studentSelectModal">Nama Siswa</label>
            <select name="nama" id="studentSelectModal" class="form-control" required>
              <option value="">Pilih Nama Siswa</option>
              @foreach($sis_wa as $siswa)
                <option value="{{ $siswa->nama }}" data-kelas="{{ $siswa->kelas }}">{{ $siswa->nama }}</option>
              @endforeach                 
            </select>
          </div>
          
              <div class="form-group">
                <label for="exampleFormControlSelect1">Keterangan</label>
                <select name="ket" class="form-control" id="exampleFormControlSelect1">
                  <option value="Sakit">Sakit</option>
                  <option value="Ijin">Ijin</option>
                  <option value="Alpha">Alpha</option>
                  <option value="Dispen">Terlambat</option>
                </select>
              </div>          

              

              
              
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Tambah</button>
            </form>
      </div>
     </div>
  </div>
</div>


<!-- Modal Export Absen -->
<div class="modal fade" id="exportAbsenModal" tabindex="-1" role="dialog" aria-labelledby="exportAbsenModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="exportAbsenModalLabel"><i class="fas fa-file-excel me-2"></i>Export Data Absensi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/absen/export" method="GET">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="start_date_absen" class="form-label fw-bold">Dari Tanggal</label>
                        <input type="date" class="form-control" id="start_date_absen" name="start_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="end_date_absen" class="form-label fw-bold">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="end_date_absen" name="end_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="kelas_filter_absen" class="form-label fw-bold">Kelas (Opsional)</label>
                        <select name="kelas" id="kelas_filter_absen" class="form-select">
                            <option value="all">-- Semua Kelas --</option>
                            @foreach($ke_las as $kelas)
                                <option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-1" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-download me-1"></i> Export Excel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var studentSelectModal = document.getElementById('studentSelectModal');
    if (!studentSelectModal) return;
    
    var $studentSelect = $('#studentSelectModal');
    
    function filterStudentsByKelas(selectedKelas) {
        $studentSelect.find('option').each(function() {
            var optKelas = $(this).data('kelas');
            if (!optKelas || optKelas == selectedKelas) {
                $(this).show().prop('disabled', false);
            } else {
                $(this).hide().prop('disabled', true);
            }
        });
        $studentSelect.val('').prop('disabled', false);
    }

    // Saat kelas berubah (untuk admin)
    $('#kelasSelectModal').on('change', function() {
        var selectedKelas = $(this).val();
        if (selectedKelas) {
            filterStudentsByKelas(selectedKelas);
        } else {
            $('#studentSelectModal').find('option').show().prop('disabled', false);
            $('#studentSelectModal').prop('disabled', false);
        }
    });

    // Jika kelas sudah terpilih saat modal dibuka (wali kelas / ketua kelas)
    var preselectedKelas = $('#kelasSelectModal').val();
    if (preselectedKelas) {
        filterStudentsByKelas(preselectedKelas);
    }

    // Re-run filter saat modal dibuka
    $('#tambahabsen').on('show.bs.modal', function() {
        var kelas = $('#kelasSelectModal').val();
        if (kelas) {
            filterStudentsByKelas(kelas);
        }
    });
});
</script>
@endsection
