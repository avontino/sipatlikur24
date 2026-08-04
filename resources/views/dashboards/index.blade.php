@extends('layouts.master')

@section('content')    

<script type="text/javascript">
   setTimeout(function(){
       location.reload();
   },20000);
</script>

    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-auto">
          <div class="col-sm-6">
            <!-- <h1>Dashboard</h1> -->
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        
        <!-- Peringatan Jurnal Mengajar Guru (Baris Atas Sendiri) -->
        @if(auth()->user()->hasRole('guru'))
          @if(count($guruScheduleNotFilled) > 0)
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 12px; background: #fff3cd; color: #856404;">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Peringatan Jurnal Mengajar:</strong> Anda memiliki jadwal mengajar hari ini untuk kelas berikut tetapi belum mengisi jurnal mengajar harian: 
              <strong>{{ implode(', ', $guruScheduleNotFilled) }}</strong>.
              <a href="/jurnal" class="btn btn-warning btn-sm text-dark ms-2 fw-bold"><i class="fas fa-edit me-1"></i> Isi Jurnal</a>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        @endif

        <!-- Peringatan Jurnal Admin / Kurikulum / Lihat -->
        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('kurikulum') || auth()->user()->hasRole('lihat'))
          @if(count($classesNotFilled) > 0)
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 12px; background: #fff3cd; color: #856404;">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Peringatan Jurnal Harian:</strong> Terdapat {{ count($classesNotFilled) }} kelas yang belum mengisi jurnal harian hari ini: 
              <strong>{{ implode(', ', $classesNotFilled) }}</strong>.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        @endif

        <!-- Peringatan Jurnal Wali Kelas -->
        @if(auth()->user()->hasRole('walikelas') || auth()->user()->walikelas_kelas)
          @if($waliClassNotFilled)
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 text-white" role="alert" style="border-radius: 12px; background-color: #dc3545;">
              <i class="fas fa-exclamation-circle me-2"></i>
              <strong>Peringatan Jurnal Kelas:</strong> Kelas perwalian Anda (<strong>{{ auth()->user()->walikelas_kelas ?: auth()->user()->name }}</strong>) belum mengisi jurnal harian hari ini! Harap hubungi Ketua Kelas Anda.
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        @endif

        <!-- Peringatan Jurnal Ketua Kelas -->
        @if(auth()->user()->hasRole('ketuakelas'))
          @if(!$todayJurnalFilled)
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 text-white" role="alert" style="border-radius: 12px; background-color: #dc3545;">
              <i class="fas fa-exclamation-circle me-2"></i>
              <strong>Peringatan Jurnal:</strong> Kelas Anda belum mengisi jurnal harian hari ini! 
              <a href="/jurnalbaru" class="btn btn-light btn-sm text-danger ms-2 fw-bold"><i class="fas fa-edit me-1"></i> Isi Jurnal Sekarang</a>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        @endif

        <!-- Verification Widget (Wali Kelas and Ketua Kelas) -->
        @if(!empty($managedClass))
          <div class="card shadow mb-4">
            <div class="card-header bg-light py-2">
              <h5 class="card-title m-0 font-weight-bold text-dark" style="font-size: 14px;"><i class="fas fa-clipboard-check me-2 text-primary"></i> Verifikasi Absensi Pagi - Kelas {{ $managedClass }}</h5>
            </div>
            <div class="card-body py-3">
              @if($todayVerification)
                <div class="alert alert-success d-flex align-items-center mb-0 p-3" role="alert" style="background-color: #d1e7dd; border-color: #badbcc; color: #0f5132;">
                  <i class="fas fa-check-circle me-3" style="font-size: 24px; color: #198754;"></i>
                  <div>
                    <h6 class="alert-heading font-weight-bold mb-1" style="font-size: 14px; color: #0f5132;">Absensi Pagi Kelas Terverifikasi!</h6>
                    <p class="mb-0 small" style="font-size: 12px;">Status Kehadiran Hari Ini: <strong>{{ $todayVerification->status == 'NIHIL' ? 'NIHIL (Hadir Semua)' : 'ADA ABSEN' }}</strong></p>
                    <p class="mb-0 small mt-1" style="font-size: 12px; opacity: 0.85;">Rincian Kehadiran: {{ $currentDetailStr }}</p>
                    <p class="mb-0 small" style="font-size: 11px; opacity: 0.75;">Diverifikasi oleh: {{ optional(\App\Models\User::find($todayVerification->verified_by))->name ?? 'Sistem' }} pada {{ \Carbon\Carbon::parse($todayVerification->updated_at)->format('H:i') }} WIB</p>
                  </div>
                </div>
                
                <div class="mt-2 text-end">
                  <a href="/jurnalbaru" class="btn btn-outline-success btn-sm text-xs py-1 px-2"><i class="fas fa-sync me-1"></i> Perbarui Verifikasi</a>
                </div>
              @else
                <div class="alert alert-warning d-flex align-items-center mb-2 p-3" role="alert" style="background-color: #fff3cd; border-color: #ffecb5; color: #664d03; border-left: 5px solid #ffc107;">
                  <i class="fas fa-exclamation-triangle me-3" style="font-size: 24px; color: #ffc107;"></i>
                  <div>
                    <h6 class="alert-heading font-weight-bold mb-1" style="font-size: 14px; color: #664d03;">Pemberitahuan: Belum Verifikasi Kehadiran Pagi</h6>
                    <p class="mb-0 small" style="font-size: 12px;">Kelas Anda belum melakukan verifikasi absensi pagi untuk hari ini.</p>
                    <p class="mb-0 small mt-1" style="font-size: 12px; opacity: 0.85;">Rincian Data Saat Ini: {{ $currentDetailStr }}</p>
                  </div>
                </div>
                <p class="text-muted small mb-2" style="font-size: 11px; font-style: italic;">*Jika terdapat siswa yang Sakit, Izin, Terlambat (Dispen), atau Alpha hari ini, harap input absensi mereka terlebih dahulu di menu <strong>Absensi Siswa</strong> sebelum mengeklik verifikasi.</p>
                
                <div class="text-end">
                  <a href="/jurnalbaru" class="btn btn-warning btn-sm font-weight-bold text-dark px-3 py-1 text-xs"><i class="fas fa-check-circle me-1"></i> Verifikasi Absensi Pagi</a>
                </div>
              @endif
            </div>
          </div>
        @endif

        {{-- Status Presensi Hari Ini (Guru dan Tendik) Disembunyikan --}}

        <!-- Small boxes (Stat box) -->
         @if(auth()->user()->hasRole('siswa'))
          <div class="row g-4 row-cols-1 row-cols-md-4 mb-4">
         @else
          <div class="row g-4 row-cols-1 row-cols-md-3 mb-4">
         @endif
         @if(auth()->user()->hasRole('siswa'))
            <div class="col">
            <!-- small box -->
            <div class="small-box bg-dark">
              <div class="inner">
                <h3>{{$status}}</h3>

                <p>Hari Ini </p>
              </div>
              <div class="icon">
                <i class="fas fa-id-badge"></i>
              </div>
            </div>
          </div>
           
           {{-- Tagihan Komite & Tagihan Lain Disembunyikan --}}

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>0</h3>

                <p>Total Poin Pelanggaran</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-shield"></i>
              </div>
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3>0</h3>

                <p>Total Poin Prestasi</p>
              </div>
              <div class="icon">
                <i class="fas fa-trophy"></i>
              </div>
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>0</h3>

                <p>Total Poin Siswa</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-check"></i>
              </div>
            </div>
          </div>
          @endif
          <div class="col">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{$sakit}}</h3>

                <p>Total Siswa Sakit </p>
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

                <p>Total Siswa Ijin </p>
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

                <p>Total Siswa Alpha</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-times"></i>
              </div>
            </div>
          </div>

        </div> 

        <div class="row g-4 row-cols-1 row-cols-md-3 row-cols-lg-{{ auth()->user()->hasRole('guru') ? '6' : '5' }} mb-4">
            
        @if(auth()->user()->hasRole('guru'))
          <div class="col">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3>{{ is_numeric($absenguru) ? $absenguru : 0 }} <sup style="font-size: 20px">Hari</sup></h3>

                <p>{{auth()->user()->name}} Tidak Masuk</p>
              </div>
              <div class="icon">
                <i class="fas fa-chalkboard-teacher"></i>
              </div>
            </div>
          </div>
        @endif
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <!-- Verification Summary Slider & Table -->
        @if(isset($verifikasiRekap) && count($verifikasiRekap) > 0)
          <div class="card shadow mb-4">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h5 class="card-title m-0 font-weight-bold text-dark" style="font-size: 14px;">
                <i class="fas fa-list-alt me-2 text-primary"></i> Status Verifikasi Absensi Pagi Seluruh Kelas
              </h5>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success px-2 py-1">{{ $totalVerified }} Sudah</span>
                <span class="badge bg-danger px-2 py-1 me-2">{{ $totalUnverified }} Belum</span>
                <!-- Slider Nav Controls -->
                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="scrollVerifikasiSlider(-250)" title="Geser Kiri"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="scrollVerifikasiSlider(250)" title="Geser Kanan"><i class="fas fa-chevron-right"></i></button>
              </div>
            </div>
            
            <!-- Horizontal Sliding Class Cards Carousel Track -->
            <div class="card-body bg-light border-bottom py-3 px-2">
              <div id="verifikasiCardSlider" class="d-flex overflow-auto pb-2 px-1 gap-2" style="scroll-behavior: smooth; -webkit-overflow-scrolling: touch;">
                @foreach($verifikasiRekap as $rekap)
                  <div class="card border shadow-sm flex-shrink-0" style="min-width: 220px; max-width: 240px; border-radius: 12px; background: #ffffff;">
                    <div class="card-body p-2">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark" style="font-size: 14px;">Kelas {{ $rekap['kelas'] }}</span>
                        @if($rekap['status'] === 'Sudah Verifikasi')
                          <span class="badge bg-success" style="font-size: 10px;"><i class="fas fa-check-circle me-1"></i> Selesai</span>
                        @else
                          <span class="badge bg-danger" style="font-size: 10px;"><i class="fas fa-clock me-1"></i> Belum</span>
                        @endif
                      </div>

                      <!-- LARGE EYE-CATCHING ATTENDANCE DISPLAY -->
                      <div class="py-1 px-2 mb-2 text-center rounded border" style="background-color: #f8fafc; border-color: #cbd5e1 !important; border-radius: 8px !important;">
                        <div class="d-flex align-items-center justify-content-center gap-1">
                          <span class="fw-bold text-success" style="font-size: 22px; font-weight: 800 !important; line-height: 1;">{{ $rekap['hadir'] }}</span>
                          <span class="fw-bold text-dark" style="font-size: 15px;">/ {{ $rekap['total'] }}</span>
                          <span class="fw-bold text-muted ms-1" style="font-size: 11px; text-transform: uppercase;">Hadir</span>
                        </div>
                      </div>

                      <div class="text-truncate small text-secondary mb-1" style="font-size: 11px;" title="{{ $rekap['detail'] }}">
                        {{ $rekap['detail'] }}
                      </div>
                      <div class="d-flex justify-content-between text-muted pt-1 border-top" style="font-size: 10px;">
                        <span><i class="fas fa-user me-1"></i>{{ Str::limit($rekap['verified_by'], 12) }}</span>
                        <span><i class="far fa-clock me-1"></i>{{ $rekap['time'] }}</span>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>

            <!-- Table View (With Horizontal Scroll/Slide Support for Mobile) -->
            <div class="card-body p-0">
              <div class="table-responsive" style="max-height: 350px; overflow-x: auto !important; overflow-y: auto !important; -webkit-overflow-scrolling: touch;">
                <table class="table table-bordered table-striped table-hover table-sm m-0" style="font-size: 12px; min-width: 680px;">
                  <thead class="table-light sticky-top" style="z-index: 1;">
                    <tr>
                      <th class="py-2 text-center" style="width: 15%;">Kelas</th>
                      <th class="py-2 text-center" style="width: 25%;">Status Verifikasi</th>
                      <th class="py-2" style="width: 35%;">Keterangan Detail Kehadiran</th>
                      <th class="py-2 text-center" style="width: 15%;">Diverifikasi Oleh</th>
                      <th class="py-2 text-center" style="width: 10%;">Jam</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($verifikasiRekap as $rekap)
                      <tr>
                        <td class="py-2 text-center font-weight-bold text-dark">{{ $rekap['kelas'] }}</td>
                        <td class="py-2 text-center">
                          @if($rekap['status'] === 'Sudah Verifikasi')
                            <span class="badge bg-success" style="font-size: 10px;"><i class="fas fa-check-circle me-1"></i> Sudah Verifikasi</span>
                          @else
                            <span class="badge bg-danger" style="font-size: 10px;"><i class="fas fa-minus-circle me-1"></i> Belum Verifikasi</span>
                          @endif
                        </td>
                        <td class="py-2"><span class="badge bg-primary me-2 fw-bold" style="font-size: 11px;">{{ $rekap['hadir'] }} / {{ $rekap['total'] }} Hadir</span> {{ $rekap['detail'] }}</td>
                        <td class="py-2 text-center text-muted">{{ $rekap['verified_by'] }}</td>
                        <td class="py-2 text-center text-muted">{{ $rekap['time'] }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <script>
          function scrollVerifikasiSlider(amount) {
            const container = document.getElementById('verifikasiCardSlider');
            if (container) {
              container.scrollBy({ left: amount, behavior: 'smooth' });
            }
          }
          </script>
        @endif

      </div>
    </section>

@endsection