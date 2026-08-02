@extends('layouts.master')

@section('content')    

    <section class="content-header">
      <div class="container-fluid">
  @if(session('sukses'))
  <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <i class="fa fa-check-circle"></i> 

    {{session('sukses')}}

  </div>
  @endif
    @if(session('gagal'))
  <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <i class="fa fa-check-circle"></i> 

    {{session('gagal')}}
  </div>
   @endif

{{-- ⚠️ BLOKIR: Jurnal Harian Belum Disinkronkan --}}
@php $synced = isset($jurnalhSynced) ? $jurnalhSynced : true; @endphp

@if(!$synced)
<div id="sync-warning-banner" class="mb-3 p-0 border-0 shadow" style="
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    border-left: 5px solid #e94560 !important;
    border-radius: 12px;
    overflow: hidden;
">
    <div class="d-flex align-items-start p-4">
        <div class="me-3" style="font-size: 2.5rem; line-height: 1; filter: drop-shadow(0 0 8px #e94560);">🔒</div>
        <div class="flex-grow-1">
            <h6 class="fw-bold mb-2" style="color: #e94560; font-size: 15px; letter-spacing: 0.5px;">
                HALAMAN DIKUNCI — Jurnal Harian Belum Disinkronkan
            </h6>
            <p class="mb-2" style="color: #a8b2d8; font-size: 13px;">
                Data jadwal mengajar <strong style="color: #ccd6f6;">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</strong>
                belum disinkronkan.
                @if(isset($myClass) && $myClass)
                    Kelas <strong style="color: #64ffda;">{{ $myClass }}</strong> tidak memiliki record jurnal harian untuk hari ini.
                @endif
            </p>
            <p class="mb-3" style="color: #8892b0; font-size: 12px;">
                Seluruh fitur di halaman ini <strong style="color: #e94560;">tidak dapat digunakan</strong> sampai jurnal harian disinkronkan.
                Pengisian jurnal sebelum sinkronisasi dapat menyebabkan <strong style="color: #ffb347;">data tidak tersimpan atau hilang</strong>.
            </p>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'kurikulum')
                    <a href="/jurnalh" class="btn btn-sm fw-bold px-4 py-2" style="
                        background: linear-gradient(135deg, #e94560, #c62a47);
                        color: white; border-radius: 8px; font-size: 13px;
                        border: none; box-shadow: 0 4px 15px rgba(233,69,96,0.4);
                    ">
                        <i class="fas fa-sync me-2"></i>Buka Halaman Sinkronisasi
                    </a>
                @else
                    <div class="px-3 py-2 rounded" style="background: rgba(233,69,96,0.15); border: 1px solid rgba(233,69,96,0.3);">
                        <i class="fas fa-lock me-2" style="color: #e94560;"></i>
                        <span style="color: #a8b2d8; font-size: 12px;">
                            Hubungi <strong style="color: #64ffda;">Admin atau Kurikulum</strong> untuk melakukan sinkronisasi jurnal harian
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div style="height: 3px; background: linear-gradient(90deg, #e94560, #64ffda, #e94560); background-size: 200% 100%; animation: syncshimmer 2s infinite linear;"></div>
</div>
@endif

<style>
@keyframes syncshimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.page-locked {
    pointer-events: none !important;
    user-select: none !important;
    opacity: 0.4 !important;
    filter: grayscale(60%) blur(1px) !important;
    transition: all 0.3s ease;
    position: relative;
}
.page-locked::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(15, 20, 40, 0.3);
    z-index: 10;
    cursor: not-allowed;
    border-radius: 8px;
}
</style>

{{-- Bungkus seluruh konten dengan wrapper yang akan di-lock jika belum sinkron --}}
<div id="page-content-wrapper" class="{{ !$synced ? 'page-locked' : '' }}">

<div class="card">

            <div class="card-header">
            <div class="row">
          <div class="col-12">
             <form class="form-inline ms-auto" method="GET" action="/jurnalbaru">
                    <h3 class="mr-sm-5 card-title">Jadwal Pelajaran </h3>


            </form>
          </div>
        </div>

           
            </div>


            <!-- /.card-header -->
            <div class="card-body">
            @if($data_jadwal->isEmpty())
											<tr>
												<td colspan="11" class="text-center">Tidak ada data jadwal tersedia</td>
											</tr>
										@else
              <table id="example3" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Kelas</th>
                    <th>Jam Ke</th>
                    <th>Jumlah Jam</th>
                    <th>Mata Pelajaran</th>
                    <th>Guru</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                @foreach($data_jadwal as $jadwal)
                  <tr>
                    <td>{{$jadwal->kelas}}</td>
                    <td>{{$jadwal->jamke}}</td>
                    <td>{{$jadwal->jumlahjam}}</td>
                    <td>{{$jadwal->mapel}}</td>
                    <td>{{$jadwal->guru}}</td>
                    
                    <td>
                          <button type="button" class="btn btn-success btn-sm" 
                          data-myid="{{$jadwal->id}}"
                          data-mykelas="{{$jadwal->kelas}}"

                          data-myjamke="{{$jadwal->jamke}}"
                          data-myjumlahjam="{{$jadwal->jumlahjam}}"
                          data-mymapel="{{$jadwal->mapel}}"
                          data-myguru="{{$jadwal->guru}}"
                          data-mymateri="{{$jadwal->materi}}"
                          data-mycatatan="{{$jadwal->catatan}}"
                          data-myj1="{{$jadwal->j1}}"
                          data-myj2="{{$jadwal->j2}}"
                          data-myj3="{{$jadwal->j3}}"
                          data-myj4="{{$jadwal->j4}}"
                          data-myj5="{{$jadwal->j5}}"
                          data-myj6="{{$jadwal->j6}}"
                          data-myj7="{{$jadwal->j7}}"
                          data-myj8="{{$jadwal->j8}}"
                          data-myj9="{{$jadwal->j9}}"
                          data-myj10="{{$jadwal->j10}}"
                          data-myj11="{{$jadwal->j11}}"

                  data-bs-toggle="modal" data-bs-target="#editjadwal">Tambah Jurnal</button>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>

                </tfoot>
              </table>
            @endif
            </div>
            <!-- /.card-body -->


          </div>

<!-- Verifikasi Absensi Pagi Widget (Wali Kelas & Ketua Kelas) -->
@if(isset($managedClass) && $managedClass)
  @php
    $isNihil = ($ab_sen->count() == 0);
  @endphp
  <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: #ffffff;">
    <div class="card-header py-3" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border-radius: 12px 12px 0 0;">
      <h5 class="card-title m-0 text-white fw-bold fs-6 d-flex align-items-center">
        <i class="fas fa-clipboard-check me-2"></i> Verifikasi Absensi Pagi — Kelas {{ $managedClass }}
      </h5>
    </div>
    <div class="card-body p-4">
      @if($todayVerification)
        <div class="alert alert-success d-flex align-items-center mb-0 p-3 shadow-sm border-0" role="alert" style="background-color: #d1e7dd; border-left: 5px solid #198754 !important; border-radius: 10px; color: #0f5132;">
          <i class="fas fa-check-circle me-3" style="font-size: 28px; color: #198754;"></i>
          <div class="flex-grow-1">
            <h6 class="alert-heading fw-bold mb-1" style="font-size: 15px; color: #0f5132;">Absensi Pagi Kelas Terverifikasi!</h6>
            <p class="mb-1 small" style="font-size: 13px;">Status Kehadiran Hari Ini: <strong>{{ $todayVerification->status == 'NIHIL' ? 'NIHIL (Hadir Semua)' : 'ADA ABSEN' }}</strong></p>
            <p class="mb-1 small text-muted" style="font-size: 12px;">Rincian Kehadiran: {{ $currentDetailStr }}</p>
            <p class="mb-0 small text-secondary" style="font-size: 11px;">Diverifikasi oleh: <strong>{{ optional(\App\Models\User::find($todayVerification->verified_by))->name ?? 'Sistem' }}</strong> pada {{ \Carbon\Carbon::parse($todayVerification->updated_at)->format('H:i') }} WIB</p>
          </div>
          <div class="ms-3 text-end">
            <form action="{{ route('dashboard.verifikasi') }}" method="POST" class="d-inline m-0" onsubmit="return confirmVerifikasiNihil(event, {{ $isNihil ? 'true' : 'false' }});">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-success fw-bold px-3 py-2 shadow-sm" style="border-radius: 8px;">
                <i class="fas fa-sync me-1"></i> Perbarui Verifikasi
              </button>
            </form>
          </div>
        </div>
      @else
        <div class="alert alert-warning d-flex align-items-center mb-3 p-3 shadow-sm border-0" role="alert" style="background-color: #fff3cd; border-left: 5px solid #ffc107 !important; border-radius: 10px; color: #664d03;">
          <i class="fas fa-exclamation-triangle me-3" style="font-size: 28px; color: #ffc107;"></i>
          <div class="flex-grow-1">
            <h6 class="alert-heading fw-bold mb-1" style="font-size: 15px; color: #664d03;">Pemberitahuan: Belum Verifikasi Kehadiran Pagi</h6>
            <p class="mb-1 small" style="font-size: 13px;">Kelas Anda (<strong>Kelas {{ $managedClass }}</strong>) belum melakukan verifikasi absensi pagi untuk hari ini.</p>
            <p class="mb-0 small text-muted" style="font-size: 12px;">Rincian Data Saat Ini: {{ $currentDetailStr }}</p>
          </div>
          <div class="ms-3 text-end">
            <form action="{{ route('dashboard.verifikasi') }}" method="POST" class="d-inline m-0" onsubmit="return confirmVerifikasiNihil(event, {{ $isNihil ? 'true' : 'false' }});">
              @csrf
              <button type="submit" class="btn btn-warning btn-md fw-bold text-dark px-4 py-2 shadow-sm" style="border-radius: 8px; font-size: 14px;">
                <i class="fas fa-check-circle me-1"></i> Verifikasi Absensi Pagi
              </button>
            </form>
          </div>
        </div>
        <p class="text-muted small mb-0 ms-1" style="font-size: 11px; font-style: italic;">
          *Jika terdapat siswa yang Sakit, Izin, Terlambat, atau Alpha hari ini, harap input absensi mereka terlebih dahulu di tabel <strong>Absen Siswa</strong> di bawah sebelum mengeklik verifikasi.
        </p>
      @endif
    </div>
  </div>

  <script>
    function confirmVerifikasiNihil(e, isNihil) {
      if (isNihil) {
        e.preventDefault();
        var form = e.target;
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Konfirmasi Absensi NIHIL',
            html: 'Apakah Anda <strong>YAKIN</strong> bahwa hari ini statusnya <strong>NIHIL</strong>?<br><span class="text-muted small">(Semua siswa Kelas {{ $managedClass }} hadir tanpa ada yang Sakit, Izin, Alpha, atau Terlambat)</span>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check-circle me-1"></i> Ya, Yakin NIHIL',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Batal (Input Absen Dulu)',
            reverseButtons: true
          }).then(function(result) {
            if (result.isConfirmed) {
              form.submit();
            }
          });
        } else {
          if (confirm('Apakah Anda YAKIN bahwa hari ini statusnya NIHIL (semua siswa hadir)?')) {
            form.submit();
          }
        }
        return false;
      }
      return true;
    }
  </script>
@endif

<!--Tabel Absen SIswa-->
<div class="card">

            <div class="card-header">
            <div class="row">
          <div class="col-12">
             <form class="form-inline ms-auto" method="GET" action="/jurnalbaru">
                    <h3 class="mr-sm-5 card-title">Absen Siswa </h3>
                    <button type="button" class=" mr-sm-4 btn btn-primary btn-sm " data-bs-toggle="modal" data-bs-target="#tambahabsen">Tambah Absensi</button>


            </form>
          </div>
        </div>

           
            </div>


            <!-- /.card-header -->
            <div class="card-body">
              <table id="example4" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                @foreach($ab_sen as $absen)
                  <tr>
                    <td>{{$absen->nama}}</td>
                    <td>{{$absen->kelas}}</td>
                    <td>{{$absen->ket}}</td>

                    
                    <td>


                  <a href="/jurnalbaru/{{$absen->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>

                    </td>
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>

                </tfoot>
              </table>

            </div>
            <!-- /.card-body -->


          </div>
        </div>


        @if($data_jadwal->isEmpty())
											<tr>
												<!-- <td colspan="11" class="text-center">Tidak ada jadwal jurnal tersedia</td> -->
											</tr>
										@else
<!-- Modal Tambah Jurnal -->
<div class="modal fade" id="editjadwal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        
        <h4 class="modal-title" id="exampleModalLabel">Tambah Jurnal</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/jurnalbaru/update" method="POST"> 
                  {{csrf_field()}}
               
        <div class="form-group">
                <label >Kelas</label>

                <input name="kelas" type="text" class="form-control" id="kelas" aria-describedby="emailHelp" placeholder="Kelas" value="{{$jadwal->kelas}}" readonly>
                          


                </div>
              <div class="form-group">
                <label for="exampleFormControlSelect1">Keterangan Guru Mapel</label>
                <select name="ket_guru_mapel" class="form-control" id="exampleFormControlSelect1">
                  <option value="Hadir">Hadir</option>
                  <option value="Tidak Masuk">Tidak Masuk</option>
                </select>
              </div>          
              <div class="form-group">
                <label for="exampleFormControlSelect1">Penugasan (Diisi Jika Guru Tidak Masuk)</label>
                <select name="penugasan" class="form-control" id="exampleFormControlSelect1">
                  <option value="Tidak Ada">Tidak Ada</option>
                  <option value="Ada">Ada</option>
                </select>
              </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Jam Ke</label>
                <input name="jamke" type="text" class="form-control" id="jamke" aria-describedby="emailHelp" placeholder="Diisi 1-11" value="{{$jadwal->jamke}}" readonly>
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jumlah Jam</label>
                <input name="jumlahjam" type="text" class="form-control" id="jumlahjam" aria-describedby="emailHelp" placeholder="Jumlah Jam" value="{{$jadwal->jumlahjam}}" readonly>
              </div>
              <div class="form-group">
                <label >Mata Pelajaran</label>
                <input name="mapel" type="text" class="form-control" id="mapel" aria-describedby="emailHelp" placeholder="mapel" value="{{$jadwal->mapel}}" readonly>

                </div>
             
                <div class="form-group">
                <label >Guru</label>
                <input name="guru" type="text" class="form-control" id="guru" aria-describedby="emailHelp" placeholder="guru" value="{{$jadwal->guru}}" readonly>

              <div class="form-group">
                <label for="exampleFormControlTextarea1">Materi</label>
                <textarea name="materi" class="form-control" id="materi" rows="3" >{{$jadwal->materi}}</textarea>
              </div>
              <div class="form-group">
                <label for="exampleFormControlTextarea1">Catatan</label>
                <textarea name="catatan" class="form-control" id="catatan" rows="3" >{{$jadwal->catatan}}</textarea>
              </div>

               <div class="form-group">

                <input name="j1" type="hidden" class="form-control" id="j1" aria-describedby="emailHelp" value="{{$jadwal->j1}}" >
                </div>

                <div class="form-group">
                <input name="j2" type="hidden" class="form-control" id="j2" aria-describedby="emailHelp" value="{{$jadwal->j2}}" >
                </div>

                <div class="form-group">  
                <input name="j3" type="hidden" class="form-control" id="j3" aria-describedby="emailHelp" value="{{$jadwal->j3}}" >
                </div>

                <div class="form-group">
                <input name="j4" type="hidden" class="form-control" id="j4" aria-describedby="emailHelp" value="{{$jadwal->j4}}" >
                </div>

                <div class="form-group">
                <input name="j5" type="hidden" class="form-control" id="j5" aria-describedby="emailHelp" value="{{$jadwal->j5}}" >
                </div>

                <div class="form-group">
                <input name="j6" type="hidden" class="form-control" id="j6" aria-describedby="emailHelp" value="{{$jadwal->j6}}" >
                </div>

                <div class="form-group">
                 <input name="j7" type="hidden" class="form-control" id="j7" aria-describedby="emailHelp" value="{{$jadwal->j7}}" >
                </div>

                <div class="form-group">
                <input name="j8" type="hidden" class="form-control" id="j8" aria-describedby="emailHelp" value="{{$jadwal->j8}}" >
                </div>

                <div class="form-group">
                <input name="j9" type="hidden" class="form-control" id="j9" aria-describedby="emailHelp" value="{{$jadwal->j9}}" >
                </div>

                <div class="form-group">
                <input name="j10" type="hidden" class="form-control" id="j10" aria-describedby="emailHelp" value="{{$jadwal->j10}}" >
                </div>

                <div class="form-group">
                <input name="j11" type="hidden" class="form-control" id="j11" aria-describedby="emailHelp" value="{{$jadwal->j11}}" >
                </div>

              <div class="form-group">
                <input name="waktu" type="hidden" class="form-control" id="waktu" aria-describedby="emailHelp" placeholder="Waktu" value="{{$jadwal->waktu}}">
              </div>

                          
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Tambah Jurnal</button>
            </form>
      </div>

    </div>
  </div>
</div>
</div>
@endif
<!-- Modal Tambah Absen-->
<div class="modal fade" id="tambahabsen" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Tambah Absensi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form action="/jurnalbaru/tambahabsen" method="POST"> 
                  {{csrf_field()}}
                  <input type="hidden" name="tgl" value="{{ now()->toDateString() }}">
                  
              <div class="form-group">
                <label >Nama Siswa</label>
                <select name="nama" class="form-control" >
                @foreach($sis_wa as $siswa)
                  <option value="{{$siswa->nama}}">{{$siswa->nama}}</option>
                @endforeach                 
                </select>

              <div class="form-group">
                <label for="exampleInputEmail1">Kelas</label>
                <input name="kelas" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="{{ $myClass }}" readonly>
              </div>

                </div>
              <div class="form-group">
                <label for="exampleFormControlSelect1">Keterangan</label>
                <select name="ket" class="form-control" id="exampleFormControlSelect1">
                  <option value="Sakit">Sakit</option>
                  <option value="Ijin">Ijin</option>
                  <option value="Alpha">Alpha</option>
                  <option value="Dispen">Dispen</option>
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


</div> {{-- end page-content-wrapper --}}

<script>
// Jika halaman dikunci (belum sinkron), cegah semua modal terbuka
@if(!$synced)
document.addEventListener('DOMContentLoaded', function() {
    // Intercept semua klik tombol yang bisa trigger modal
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            // Tampilkan toast/alert kecil
            showLockAlert();
        }, true); // capture phase agar jalan sebelum Bootstrap
    });

    // Cegah semua form submit
    document.querySelectorAll('#page-content-wrapper form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            showLockAlert();
        });
    });

    // Cegah semua link aksi di dalam wrapper
    document.querySelectorAll('#page-content-wrapper a[href*="delete"]').forEach(function(a) {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            showLockAlert();
        });
    });

    function showLockAlert() {
        // Scroll ke atas untuk melihat banner
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Flash efek pada banner
        var banner = document.getElementById('sync-warning-banner');
        if (banner) {
            banner.style.transition = 'transform 0.1s ease';
            banner.style.transform = 'scale(1.02)';
            banner.style.boxShadow = '0 0 30px rgba(233,69,96,0.6)';
            setTimeout(function() {
                banner.style.transform = 'scale(1)';
                banner.style.boxShadow = '';
            }, 300);
        }
    }
});
@endif
</script>

@endsection
