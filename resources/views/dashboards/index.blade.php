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
        
        <!-- Verification Widget (Wali Kelas and Ketua Kelas) -->
        @if(isset($managedClass))
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
                    <p class="mb-0 small" style="font-size: 11px; opacity: 0.75;">Diverifikasi oleh: {{ \App\Models\User::find($todayVerification->verified_by)->name ?? 'Sistem' }} pada {{ \Carbon\Carbon::parse($todayVerification->updated_at)->format('H:i') }} WIB</p>
                  </div>
                </div>
                
                <div class="mt-2 text-end">
                  <form action="{{ route('dashboard.verifikasi') }}" method="POST" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-sm text-xs py-1 px-2"><i class="fas fa-sync me-1"></i> Perbarui Verifikasi</button>
                  </form>
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
                  <form action="{{ route('dashboard.verifikasi') }}" method="POST" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm font-weight-bold text-dark px-3 py-1 text-xs"><i class="fas fa-check-circle me-1"></i> Verifikasi Absensi Pagi</button>
                  </form>
                </div>
              @endif
            </div>
          </div>
        @endif

        <!-- Status Presensi Hari Ini (Guru dan Tendik) -->
        @if(auth()->user()->hasRole('guru') || auth()->user()->hasRole('tendik'))
          @php
            $todayPresensi = \App\Models\PresensiGuru::where('user_id', auth()->id())
                ->where('tanggal', date('Y-m-d'))
                ->first();
            $config = \DB::table('presensi_guru_settings')->first() ?? (object) [
                'jam_masuk' => '07:00:00',
                'jam_pulang' => '14:00:00'
            ];
            $nowTime = date('H:i:s');
          @endphp
          
          <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-light py-2">
              <h5 class="card-title m-0 font-weight-bold text-dark" style="font-size: 14px;">
                <i class="fas fa-user-clock me-2 text-primary"></i> Status Presensi Hari Ini - {{ date('d F Y') }}
              </h5>
            </div>
            <div class="card-body py-3">
              <div class="row align-items-center">
                <!-- Presensi Datang -->
                <div class="col-md-6 mb-3 mb-md-0 border-end border-light">
                  <div class="d-flex align-items-center">
                    @if($todayPresensi)
                      <div class="p-3 rounded-circle me-3" style="background-color: #d1e7dd; color: #198754; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                      </div>
                      <div>
                        <h6 class="mb-0 font-weight-bold" style="font-size: 14px; color: #0f5132;">Presensi Datang BERHASIL</h6>
                        <p class="mb-0 text-muted small mt-1" style="font-size: 12px;">
                          Jam Masuk: <strong>{{ substr($todayPresensi->jam_datang, 0, 5) }} WIB</strong>
                          <span class="badge {{ $todayPresensi->status_datang == 'Terlambat' ? 'bg-danger' : 'bg-success' }} ms-1">
                            {{ $todayPresensi->status_datang }}
                          </span>
                        </p>
                      </div>
                    @else
                      <div class="p-3 rounded-circle me-3" style="background-color: #f8d7da; color: #dc3545; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 24px;"></i>
                      </div>
                      <div>
                        <h6 class="mb-0 font-weight-bold" style="font-size: 14px; color: #842029;">Presensi Datang BELUM</h6>
                        <p class="mb-0 text-muted small mt-1" style="font-size: 12px;">
                          Batas Jam Masuk: <strong>{{ substr($config->jam_masuk, 0, 5) }} WIB</strong>
                        </p>
                        <a href="/presensi-guru" class="btn btn-danger btn-xs mt-2 py-0 px-2 fw-bold" style="font-size: 11px;">
                          <i class="fas fa-camera me-1"></i> Presensi Datang Sekarang
                        </a>
                      </div>
                    @endif
                  </div>
                </div>
                
                <!-- Presensi Pulang -->
                <div class="col-md-6 ps-md-4">
                  <div class="d-flex align-items-center">
                    @if($todayPresensi && $todayPresensi->jam_pulang)
                      <div class="p-3 rounded-circle me-3" style="background-color: #d1e7dd; color: #198754; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-double" style="font-size: 24px;"></i>
                      </div>
                      <div>
                        <h6 class="mb-0 font-weight-bold" style="font-size: 14px; color: #0f5132;">Presensi Pulang BERHASIL</h6>
                        <p class="mb-0 text-muted small mt-1" style="font-size: 12px;">
                          Jam Pulang: <strong>{{ substr($todayPresensi->jam_pulang, 0, 5) }} WIB</strong>
                          <span class="badge {{ $todayPresensi->status_pulang == 'Pulang Cepat' ? 'bg-warning text-dark' : 'bg-success' }} ms-1">
                            {{ $todayPresensi->status_pulang }}
                          </span>
                        </p>
                      </div>
                    @else
                      <div class="p-3 rounded-circle me-3" style="background-color: #e2e3e5; color: #6c757d; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-sign-out-alt" style="font-size: 24px;"></i>
                      </div>
                      <div>
                        <h6 class="mb-0 font-weight-bold" style="font-size: 14px; color: #41464b;">Presensi Pulang BELUM</h6>
                        <p class="mb-0 text-muted small mt-1" style="font-size: 12px;">
                          @if($nowTime < $config->jam_pulang)
                            Presensi pulang dibuka jam: <strong>{{ substr($config->jam_pulang, 0, 5) }} WIB</strong>
                          @else
                            Sudah masuk jam pulang sejak: <strong>{{ substr($config->jam_pulang, 0, 5) }} WIB</strong>
                          @endif
                        </p>
                        @if($nowTime >= $config->jam_pulang && $todayPresensi)
                          <a href="/presensi-guru" class="btn btn-warning btn-xs mt-2 py-0 px-2 fw-bold text-dark" style="font-size: 11px;">
                            <i class="fas fa-camera me-1"></i> Presensi Pulang Sekarang
                          </a>
                        @endif
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif

        <!-- Small boxes (Stat box) -->
         @if(auth()->user()->hasRole('siswa'))
          <div class="row g-4 row-cols-1 row-cols-md-3 row-cols-lg-6 mb-4">
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
           
           <div class="col">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>{{$tagihan_komite}}</h3>

                <p>Dana Komite </p>
              </div>
              <div class="icon">
                <i class="fas fa-wallet"></i>
              </div>
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>{{$tagihan_lain}}</h3>

                <p>Tagihan Lain </p>
              </div>
              <div class="icon">
                <i class="fas fa-file-invoice-dollar"></i>
              </div>
            </div>
          </div>

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
                <h3>{{$absenguru}} <sup style="font-size: 20px">Hari</sup></h3>

                <p>{{auth()->user()->name}} Tidak Masuk</p>
              </div>
              <div class="icon">
                <i class="fas fa-chalkboard-teacher"></i>
              </div>
              <a href="/ijin" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        @endif

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>{{$ijinpesiar}} <sup style="font-size: 20px">Ijin Pesiar</sup></h3>
                <p>Anak Ijin Hari Ini</p>
              </div>
              <div class="icon">
                <i class="fas fa-hiking"></i>
              </div>
              <a href="/ijinsiswa" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{$ijinbermalamwajib}} <sup style="font-size: 20px">Ijin Bermalam</sup></h3>
                <p>Anak Ijin Hari Ini</p>
              </div>
              <div class="icon">
                <i class="fas fa-bed"></i>
              </div>
              <a href="/ijinsiswa" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>{{$ijinbermalamresmi}} <sup style="font-size: 20px">Ijin Bermalam Resmi</sup></h3>
                <p>Anak Ijin Hari Ini</p>
              </div>
              <div class="icon">
                <i class="fas fa-hotel"></i>
              </div>
              <a href="/ijinsiswa" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-secondary">
              <div class="inner">
                <h3>{{$ijinjalan}} <sup style="font-size: 20px">Ijin Jalan</sup></h3>
                <p>Anak Ijin Hari Ini</p>
              </div>
              <div class="icon">
                <i class="fas fa-walking"></i>
              </div>
              <a href="/ijinsiswa" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>{{$ijinkhusus}} <sup style="font-size: 20px">Ijin Khusus</sup></h3>
                <p>Anak Ijin Hari Ini</p>
              </div>
              <div class="icon">
                <i class="fas fa-star"></i>
              </div>
              <a href="/ijinsiswa" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

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
                  <div class="card border shadow-sm flex-shrink-0" style="min-width: 210px; max-width: 230px; border-radius: 10px; background: #ffffff;">
                    <div class="card-body p-2">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark" style="font-size: 13px;">Kelas {{ $rekap['kelas'] }}</span>
                        @if($rekap['status'] === 'Sudah Verifikasi')
                          <span class="badge bg-success" style="font-size: 10px;"><i class="fas fa-check-circle me-1"></i> Selesai</span>
                        @else
                          <span class="badge bg-danger" style="font-size: 10px;"><i class="fas fa-clock me-1"></i> Belum</span>
                        @endif
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
                        <td class="py-2">{{ $rekap['detail'] }}</td>
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

        <!-- Warning Jurnal Harian -->
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

        @if(auth()->user()->hasRole('walikelas') || auth()->user()->walikelas_kelas)
          @if($waliClassNotFilled)
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 text-white" role="alert" style="border-radius: 12px; background-color: #dc3545;">
              <i class="fas fa-exclamation-circle me-2"></i>
              <strong>Peringatan Jurnal Kelas:</strong> Kelas perwalian Anda (<strong>{{ auth()->user()->walikelas_kelas ?: auth()->user()->name }}</strong>) belum mengisi jurnal harian hari ini! Harap hubungi Ketua Kelas Anda.
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        @endif

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

        @if(auth()->user()->hasRole('guru'))
          @if(count($guruScheduleNotFilled) > 0)
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 12px; background: #fff3cd; color: #856404;">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Peringatan Jurnal Mengajar:</strong> Anda memiliki jadwal mengajar hari ini untuk kelas berikut tetapi belum mengisi jurnal mengajar harian: 
              <strong>{{ implode(', ', $guruScheduleNotFilled) }}</strong>.
              <a href="/jurnalbaru" class="btn btn-warning btn-sm text-dark ms-2 fw-bold"><i class="fas fa-edit me-1"></i> Isi Jurnal</a>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        @endif

        @if(auth()->user()->hasRole('guru') || auth()->user()->hasRole('tendik'))
          <!-- Guru Attendance Calendar Card -->
          <div class="card shadow-sm border-0 mb-4" style="border-radius: 14px;">
            <div class="card-header bg-light py-3">
              <h5 class="m-0 fw-bold text-dark"><i class="fas fa-calendar-alt me-2 text-primary"></i> Kalender Presensi Saya ({{ date('F Y') }})</h5>
            </div>
            <div class="card-body p-4">
              <!-- Calendar Legend -->
              <div class="d-flex flex-wrap gap-3 mb-4 small fw-bold">
                <div class="d-flex align-items-center"><span class="badge bg-success me-2" style="width: 15px; height: 15px; border-radius: 50%; display: inline-block;"></span> Hadir (Tepat Waktu)</div>
                <div class="d-flex align-items-center"><span class="badge bg-danger me-2" style="width: 15px; height: 15px; border-radius: 50%; display: inline-block;"></span> Terlambat</div>
                <div class="d-flex align-items-center"><span class="badge bg-warning me-2" style="width: 15px; height: 15px; border-radius: 50%; display: inline-block;"></span> Izin / Sakit (Disetujui)</div>
                <div class="d-flex align-items-center"><span class="badge bg-secondary me-2" style="width: 15px; height: 15px; border-radius: 50%; display: inline-block;"></span> Alfa / Tanpa Keterangan</div>
                <div class="d-flex align-items-center"><span class="badge bg-light text-dark border me-2" style="width: 15px; height: 15px; border-radius: 50%; display: inline-block;"></span> Akhir Pekan</div>
              </div>

              <div class="table-responsive">
                <table class="table table-bordered text-center align-middle" style="table-layout: fixed;">
                  <thead>
                    <tr class="table-light">
                      <th style="color: red;">Min</th>
                      <th>Sen</th>
                      <th>Sel</th>
                      <th>Rab</th>
                      <th>Kam</th>
                      <th>Jum</th>
                      <th>Sab</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                      $daysInMonth = now()->daysInMonth;
                      $firstDayOfMonth = date('w', strtotime(date('Y-m-01'))); // 0 (Sun) - 6 (Sat)
                      $currentDay = 1;
                    @endphp
                    
                    @for($row = 0; $row < 6; $row++)
                      @if($currentDay > $daysInMonth) @break @endif
                      <tr>
                        @for($col = 0; $col < 7; $col++)
                          @if(($row == 0 && $col < $firstDayOfMonth) || $currentDay > $daysInMonth)
                            <td class="bg-light opacity-50"></td>
                          @else
                            @php
                              $dateStr = sprintf('%04d-%02d-%02d', now()->year, now()->month, $currentDay);
                              $status = $attendanceData[$dateStr] ?? 'libur';
                              
                              $bgClass = 'bg-white';
                              $titleText = 'Tidak ada catatan';
                              $badgeColor = '';

                              if ($status == 'tepat_waktu') {
                                  $badgeColor = '#28a745'; // Green
                                  $titleText = 'Hadir Tepat Waktu';
                              } elseif ($status == 'terlambat') {
                                  $badgeColor = '#dc3545'; // Red
                                  $titleText = 'Hadir Terlambat';
                              } elseif ($status == 'izin') {
                                  $badgeColor = '#ffc107'; // Yellow/Orange
                                  $titleText = 'Izin Resmi';
                               } elseif ($status == 'alpha') {
                                  $badgeColor = '#6c757d'; // Gray
                                  $titleText = 'Alfa / Tanpa Keterangan';
                               } elseif ($status == 'libur') {
                                  $bgClass = 'bg-light';
                                  $titleText = 'Akhir Pekan';
                              }
                            @endphp
                            <td class="{{ $bgClass }}" style="height: 80px; position: relative;" title="{{ $titleText }}">
                              <span class="fw-bold text-secondary" style="position: absolute; top: 5px; left: 5px; font-size: 14px;">{{ $currentDay }}</span>
                              @if($badgeColor)
                                <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $badgeColor }}; margin: 20px auto 0 auto;" class="shadow-sm"></div>
                                <span class="small d-block text-muted mt-1" style="font-size: 10px;">{{ $titleText }}</span>
                              @endif
                            </td>
                            @php $currentDay++; @endphp
                          @endif
                        @endfor
                      </tr>
                    @endfor
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endif

      </div>
    </section>

@endsection