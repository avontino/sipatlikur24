@extends('layouts.master')

@section('content')    

<script type="text/javascript">
  (function() {
    // Refresh otomatis jika pengguna sedang diam/idle selama 1 menit (60 detik)
    var IDLE_TIMEOUT_MS = 60000; 
    var refreshTimer = null;

    function isModalOpen() {
      return document.querySelectorAll('.modal.show, .modal.in').length > 0;
    }

    function doRefresh() {
      // Jangan pernah reload jika ada pop-up / modal info yang sedang dibuka
      if (isModalOpen()) {
        resetTimer(); // Tunda dan periksa kembali nanti
        return;
      }
      location.reload();
    }

    function resetTimer() {
      if (refreshTimer) {
        clearTimeout(refreshTimer);
      }
      refreshTimer = setTimeout(doRefresh, IDLE_TIMEOUT_MS);
    }

    // Reset timer jika user sedang aktif berinteraksi (melihat info, scroll, geser kartu, klik, dll)
    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'].forEach(function(evt) {
      window.addEventListener(evt, resetTimer, { passive: true });
    });

    // Jalankan timer saat halaman pertama kali dimuat
    resetTimer();
  })();
</script>

    <section class="content pt-3">
      <div class="container-fluid">
        
        <!-- 1. Hero Welcome Banner Card -->
        <div class="card shadow-sm border-0 rounded-3 mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #004d1a 0%, #006622 50%, #009638 100%);">
          <div class="card-body p-4 position-relative">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 54px; height: 54px; background: rgba(255,255,255,0.18); backdrop-filter: blur(4px);">
                  <i class="fas fa-school fa-2x text-warning"></i>
                </div>
                <div>
                  @php
                    $hour = (int) \Carbon\Carbon::now()->format('H');
                    $greeting = 'Selamat Malam';
                    if ($hour >= 4 && $hour < 11) {
                      $greeting = 'Selamat Pagi';
                    } elseif ($hour >= 11 && $hour < 15) {
                      $greeting = 'Selamat Siang';
                    } elseif ($hour >= 15 && $hour < 19) {
                      $greeting = 'Selamat Sore';
                    }
                    $userRoleLabel = strtoupper(auth()->user()->role);
                    if (auth()->user()->hasRole('walikelas') || auth()->user()->walikelas_kelas) {
                      $userRoleLabel = 'WALI KELAS ' . (auth()->user()->walikelas_kelas ?: auth()->user()->getManagedClass());
                    } elseif (auth()->user()->hasRole('ketuakelas')) {
                      $userRoleLabel = 'KETUA KELAS ' . auth()->user()->name;
                    } elseif (auth()->user()->hasRole('guru')) {
                      $userRoleLabel = 'GURU';
                    } elseif (auth()->user()->hasRole('kurikulum')) {
                      $userRoleLabel = 'TIM KURIKULUM';
                    } elseif (auth()->user()->hasRole('admin')) {
                      $userRoleLabel = 'ADMINISTRATOR';
                    }
                  @endphp
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h3 class="fw-bold text-white mb-0" style="font-size: 22px;">{{ $greeting }}, {{ auth()->user()->name }}! 👋</h3>
                    <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 11px; letter-spacing: 0.5px;">{{ $userRoleLabel }}</span>
                  </div>
                  <p class="text-white-50 small mb-0 mt-1">
                    <i class="far fa-calendar-alt me-1 text-warning"></i> {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                    @if(session('tahun_ajaran'))
                      <span class="mx-2 opacity-50">|</span>
                      <i class="fas fa-graduation-cap me-1 text-warning"></i> TA {{ session('tahun_ajaran') }} {{ session('semester') ? '(' . session('semester') . ')' : '' }}
                    @endif
                  </p>
                </div>
              </div>
              @if(isset($totalClasses) && $totalClasses > 0)
                @php
                  $compRate = round(($totalVerified / $totalClasses) * 100, 1);
                  $barColor = $compRate >= 85 ? '#22c55e' : ($compRate >= 60 ? '#facc15' : '#ef4444');
                @endphp
                <div class="p-3 rounded-3 flex-shrink-0" style="background: rgba(0, 0, 0, 0.2); min-width: 230px;">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-white-50 fw-semibold text-uppercase" style="font-size: 10.5px;">Verifikasi Pagi Hari Ini</span>
                    <span class="fw-bold text-white" style="font-size: 13px;">{{ $totalVerified }}/{{ $totalClasses }} Kelas</span>
                  </div>
                  <div class="progress" style="height: 6px; background-color: rgba(255,255,255,0.2);">
                    <div class="progress-bar" role="progressbar" style="width: {{ $compRate }}%; background-color: {{ $barColor }};" aria-valuenow="{{ $compRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="small text-white-50" style="font-size: 10.5px;">Capaian Sekolah:</span>
                    <span class="fw-bold text-warning" style="font-size: 11.5px;">{{ $compRate }}% Terverifikasi</span>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>

        <!-- 2. Peringatan Jurnal Mengajar Guru -->
        @if(auth()->user()->hasRole('guru'))
          @if(count($guruScheduleNotFilled) > 0)
            <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden" style="background: #fffbeb; border-left: 5px solid #f59e0b !important;">
              <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fde68a; color: #b45309;">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                  </div>
                  <div>
                    <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 14px;">Peringatan Jurnal Mengajar Hari Ini</h6>
                    <p class="mb-0 small" style="color: #78350f; font-size: 12.5px;">
                      Anda memiliki jadwal mengajar tetapi belum mengisi jurnal: <strong>{{ implode(', ', $guruScheduleNotFilled) }}</strong>.
                    </p>
                  </div>
                </div>
                <a href="/jurnal" class="btn btn-warning btn-sm fw-bold px-3 shadow-sm" style="color: #78350f; border-radius: 8px;">
                  <i class="fas fa-edit me-1"></i> Isi Jurnal Sekarang
                </a>
              </div>
            </div>
          @endif
        @endif

        <!-- Peringatan Jurnal Admin / Kurikulum / Lihat -->
        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('kurikulum') || auth()->user()->hasRole('lihat'))
          @if(count($classesNotFilled) > 0)
            <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden" style="background: #fffbeb; border-left: 5px solid #f59e0b !important;">
              <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fde68a; color: #b45309;">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                  </div>
                  <div>
                    <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 14px;">Peringatan Jurnal Harian Kelas</h6>
                    <p class="mb-0 small" style="color: #78350f; font-size: 12.5px;">
                      Terdapat <strong>{{ count($classesNotFilled) }} kelas</strong> belum mengisi jurnal harian hari ini: 
                      <span class="badge bg-warning text-dark ms-1">{{ implode(', ', $classesNotFilled) }}</span>
                    </p>
                  </div>
                </div>
                <a href="/jurnalh?view=kurikulum" class="btn btn-outline-warning btn-sm fw-bold px-3" style="color: #78350f; border-radius: 8px;">
                  <i class="fas fa-eye me-1"></i> Pantau Jurnal
                </a>
              </div>
            </div>
          @endif
        @endif

        <!-- Peringatan Jurnal Wali Kelas -->
        @if(auth()->user()->hasRole('walikelas') || auth()->user()->walikelas_kelas)
          @if($waliClassNotFilled)
            <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden text-white" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-left: 5px solid #991b1b !important;">
              <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 42px; height: 42px; background: rgba(255,255,255,0.2);">
                    <i class="fas fa-exclamation-triangle fa-lg text-white"></i>
                  </div>
                  <div>
                    <h6 class="fw-bold mb-1 text-white" style="font-size: 14px;">Peringatan Jurnal Kelas Anda</h6>
                    <p class="mb-0 small text-white-50" style="font-size: 12.5px;">
                      Kelas perwalian Anda (<strong>{{ auth()->user()->walikelas_kelas ?: auth()->user()->name }}</strong>) belum mengisi jurnal harian hari ini! Harap koordinasi dengan Ketua Kelas.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          @endif
        @endif

        <!-- Peringatan Jurnal Ketua Kelas -->
        @if(auth()->user()->hasRole('ketuakelas'))
          @if(!$todayJurnalFilled)
            <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden text-white" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-left: 5px solid #991b1b !important;">
              <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 42px; height: 42px; background: rgba(255,255,255,0.2);">
                    <i class="fas fa-exclamation-circle fa-lg text-white"></i>
                  </div>
                  <div>
                    <h6 class="fw-bold mb-1 text-white" style="font-size: 14px;">Peringatan: Jurnal Hari Ini Belum Diisi</h6>
                    <p class="mb-0 small text-white-50" style="font-size: 12.5px;">Kelas Anda belum mengisi jurnal harian untuk jadwal KBM hari ini!</p>
                  </div>
                </div>
                <a href="/jurnalbaru" class="btn btn-light btn-sm fw-bold px-3 shadow-sm text-danger" style="border-radius: 8px;">
                  <i class="fas fa-edit me-1"></i> Isi Jurnal Sekarang
                </a>
              </div>
            </div>
          @endif
        @endif

        <!-- 3. Verification Widget (Wali Kelas and Ketua Kelas) -->
        @if(!empty($managedClass))
          <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
              <h6 class="card-title m-0 fw-bold text-dark" style="font-size: 14px;">
                <i class="fas fa-clipboard-check text-primary me-2"></i> Verifikasi Absensi Pagi - Kelas {{ $managedClass }}
              </h6>
              <span class="badge bg-secondary px-2 py-1" style="font-size: 11px;">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</span>
            </div>
            <div class="card-body p-3">
              @if($todayVerification)
                <div class="p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #22c55e; color: #ffffff;">
                      <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                    <div>
                      <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold text-success mb-0" style="font-size: 15px;">Absensi Pagi Kelas Terverifikasi!</h6>
                        <span class="badge bg-success px-2 py-0" style="font-size: 10px;">{{ $todayVerification->status == 'NIHIL' ? 'NIHIL (Hadir Semua)' : 'ADA ABSEN' }}</span>
                      </div>
                      <p class="mb-0 small text-dark mt-1" style="font-size: 12px;"><strong>Rincian:</strong> {{ $currentDetailStr }}</p>
                      <span class="text-muted small" style="font-size: 11px;"><i class="fas fa-user-check me-1"></i>Diverifikasi oleh: {{ optional(\App\Models\User::find($todayVerification->verified_by))->name ?? 'Sistem' }} pukul {{ \Carbon\Carbon::parse($todayVerification->updated_at)->format('H:i') }} WIB</span>
                    </div>
                  </div>
                  <a href="/jurnalbaru" class="btn btn-outline-success btn-sm fw-semibold px-3" style="border-radius: 6px;">
                    <i class="fas fa-sync me-1"></i> Perbarui Verifikasi
                  </a>
                </div>
              @else
                <div class="p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background-color: #fffbeb; border: 1px solid #fde68a;">
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #f59e0b; color: #ffffff;">
                      <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <div>
                      <h6 class="fw-bold mb-0" style="color: #92400e; font-size: 15px;">Belum Verifikasi Kehadiran Pagi</h6>
                      <p class="mb-0 small mt-1" style="color: #78350f; font-size: 12px;">Kelas Anda belum melakukan verifikasi absensi pagi untuk hari ini.</p>
                      <p class="mb-0 small text-muted" style="font-size: 11.5px;">Data saat ini: {{ $currentDetailStr }}</p>
                      <p class="mb-0 text-muted fst-italic" style="font-size: 11px;">*Jika terdapat siswa yang Sakit, Izin, Terlambat (Dispen), atau Alpha hari ini, harap input absensi terlebih dahulu di menu Absensi Siswa sebelum verifikasi.</p>
                    </div>
                  </div>
                  <a href="/jurnalbaru" class="btn btn-warning btn-sm fw-bold px-3 text-dark shadow-sm" style="border-radius: 6px;">
                    <i class="fas fa-check-circle me-1"></i> Verifikasi Absensi Pagi
                  </a>
                </div>
              @endif
            </div>
          </div>
        @endif

        <!-- 4. Modern Metric Cards -->
        @if(auth()->user()->hasRole('siswa'))
          <div class="row g-3 mb-4">
            <div class="col-lg-3 col-sm-6">
              <div class="card shadow-sm border-0 rounded-3 h-100" style="border-left: 6px solid #1e293b !important; background: #ffffff;">
                <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center">
                  <div>
                    <span class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 13px; letter-spacing: 0.8px;">Status Kehadiran</span>
                    <h2 class="fw-bold text-dark mb-1" style="font-size: 38px; line-height: 1.1;">{{ $status }}</h2>
                    <span class="text-secondary" style="font-size: 13px;"><i class="far fa-calendar-check me-1"></i>Hari Ini</span>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 58px; height: 58px; background-color: #f1f5f9; color: #1e293b;">
                    <i class="fas fa-id-badge fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="card shadow-sm border-0 rounded-3 h-100" style="border-left: 6px solid #ef4444 !important; background: #ffffff;">
                <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center">
                  <div>
                    <span class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 13px; letter-spacing: 0.8px;">Poin Pelanggaran</span>
                    <h2 class="fw-bold text-danger mb-1" style="font-size: 42px; line-height: 1.1;">0</h2>
                    <span class="text-secondary" style="font-size: 13px;"><i class="fas fa-shield-alt me-1"></i>Kedisiplinan</span>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 58px; height: 58px; background-color: #fee2e2; color: #ef4444;">
                    <i class="fas fa-user-shield fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="card shadow-sm border-0 rounded-3 h-100" style="border-left: 6px solid #3b82f6 !important; background: #ffffff;">
                <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center">
                  <div>
                    <span class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 13px; letter-spacing: 0.8px;">Poin Prestasi</span>
                    <h2 class="fw-bold text-primary mb-1" style="font-size: 42px; line-height: 1.1;">0</h2>
                    <span class="text-secondary" style="font-size: 13px;"><i class="fas fa-award me-1"></i>Penghargaan</span>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 58px; height: 58px; background-color: #dbeafe; color: #3b82f6;">
                    <i class="fas fa-trophy fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="card shadow-sm border-0 rounded-3 h-100" style="border-left: 6px solid #10b981 !important; background: #ffffff;">
                <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center">
                  <div>
                    <span class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 13px; letter-spacing: 0.8px;">Total Poin Siswa</span>
                    <h2 class="fw-bold text-success mb-1" style="font-size: 42px; line-height: 1.1;">0</h2>
                    <span class="text-secondary" style="font-size: 13px;"><i class="fas fa-calculator me-1"></i>Akumulasi</span>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 58px; height: 58px; background-color: #d1fae5; color: #10b981;">
                    <i class="fas fa-user-check fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif

        @php
          $colClass = auth()->user()->hasRole('guru') ? 'col-lg-3 col-sm-6' : 'col-lg-4 col-sm-6';
        @endphp

        <div class="row g-3 mb-4">
          <!-- Siswa Sakit -->
          <div class="{{ $colClass }}">
            <div class="card shadow-sm border-0 rounded-3 h-100" style="border-left: 6px solid #0284c7 !important; background: #ffffff; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 24px rgba(0,0,0,0.09)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
              <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center">
                <div>
                  <span class="fw-bold text-uppercase d-block mb-1" style="color: #475569; font-size: 13.5px; letter-spacing: 0.8px;">Siswa Sakit</span>
                  <h2 class="fw-bold mb-1" style="color: #0284c7; font-size: 44px; font-weight: 800; line-height: 1.1;">{{ $sakit }}</h2>
                  <span class="text-secondary" style="font-size: 13px;"><i class="far fa-clock me-1 text-muted"></i>Tercatat Hari Ini</span>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 60px; height: 60px; background-color: #e0f2fe; color: #0284c7;">
                  <i class="fas fa-head-side-cough fa-2x"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Siswa Izin -->
          <div class="{{ $colClass }}">
            <div class="card shadow-sm border-0 rounded-3 h-100" style="border-left: 6px solid #d97706 !important; background: #ffffff; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 24px rgba(0,0,0,0.09)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
              <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center">
                <div>
                  <span class="fw-bold text-uppercase d-block mb-1" style="color: #475569; font-size: 13.5px; letter-spacing: 0.8px;">Siswa Izin</span>
                  <h2 class="fw-bold mb-1" style="color: #d97706; font-size: 44px; font-weight: 800; line-height: 1.1;">{{ $ijin }}</h2>
                  <span class="text-secondary" style="font-size: 13px;"><i class="far fa-clock me-1 text-muted"></i>Tercatat Hari Ini</span>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 60px; height: 60px; background-color: #fef3c7; color: #d97706;">
                  <i class="fas fa-file-signature fa-2x"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Siswa Alpha -->
          <div class="{{ $colClass }}">
            <div class="card shadow-sm border-0 rounded-3 h-100" style="border-left: 6px solid #dc2626 !important; background: #ffffff; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 24px rgba(0,0,0,0.09)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
              <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center">
                <div>
                  <span class="fw-bold text-uppercase d-block mb-1" style="color: #475569; font-size: 13.5px; letter-spacing: 0.8px;">Siswa Alpha</span>
                  <h2 class="fw-bold mb-1" style="color: #dc2626; font-size: 44px; font-weight: 800; line-height: 1.1;">{{ $alpha }}</h2>
                  <span class="text-secondary" style="font-size: 13px;"><i class="fas fa-times-circle me-1 text-danger opacity-75"></i>Tanpa Keterangan</span>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 60px; height: 60px; background-color: #fee2e2; color: #dc2626;">
                  <i class="fas fa-user-times fa-2x"></i>
                </div>
              </div>
            </div>
          </div>

          @if(auth()->user()->hasRole('guru'))
            <!-- Presensi Guru Pribadi -->
            <div class="{{ $colClass }}">
              <div class="card shadow-sm border-0 rounded-3 h-100" style="border-left: 6px solid #7c3aed !important; background: #ffffff; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 24px rgba(0,0,0,0.09)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
                <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center">
                  <div>
                    <span class="fw-bold text-uppercase d-block mb-1" style="color: #475569; font-size: 13.5px; letter-spacing: 0.8px;">Presensi Saya</span>
                    <h2 class="fw-bold mb-1" style="color: #7c3aed; font-size: 44px; font-weight: 800; line-height: 1.1;">{{ is_numeric($absenguru) ? $absenguru : 0 }} <span style="font-size: 18px; font-weight: 600; color: #64748b;">Hari</span></h2>
                    <span class="text-secondary" style="font-size: 13px;"><i class="far fa-calendar-alt me-1 text-muted"></i>Tidak Masuk Bulan Ini</span>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 60px; height: 60px; background-color: #ede9fe; color: #7c3aed;">
                    <i class="fas fa-chalkboard-teacher fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
        <!-- Verification Summary Slider & Table -->
        @if(isset($verifikasiRekap) && count($verifikasiRekap) > 0)
          <div class="card shadow mb-4">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h5 class="card-title m-0 font-weight-bold text-dark" style="font-size: 14px;">
                <i class="fas fa-list-alt me-2 text-primary"></i> Status Verifikasi Absensi Pagi Seluruh Kelas
              </h5>
              <div class="d-flex align-items-center gap-2">
                <a href="/rekap-verifikasi" class="btn btn-sm btn-primary py-0 px-2 fw-bold" style="font-size: 11px; border-radius: 6px;" title="Lihat Rekapitulasi Verifikasi Bulanan">
                  <i class="fas fa-chart-bar me-1"></i> Rekap Bulanan
                </a>
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
                  @php
                    $modalId = 'modalDetailAbsen' . Str::slug($rekap['kelas']);
                    $siswaAbsen = $rekap['siswa_absen'] ?? [];
                  @endphp
                  <div class="card border shadow-sm flex-shrink-0 verifikasi-card" 
                       style="min-width: 220px; max-width: 240px; border-radius: 12px; background: #ffffff; cursor: pointer; transition: all 0.2s ease-in-out;"
                       data-bs-toggle="modal" data-bs-target="#{{ $modalId }}"
                       data-toggle="modal" data-target="#{{ $modalId }}"
                       onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 18px rgba(0,0,0,0.12)';"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';"
                       title="Klik untuk melihat siswa yang tidak masuk">
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
                      <div class="text-center pt-1 mt-1 border-top" style="font-size: 10px;">
                        <span class="text-primary fw-bold"><i class="fas fa-search me-1"></i>Klik: Cek Siswa Tidak Masuk</span>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>

            <!-- Table View (Lengkap Seluruh Kelas Tanpa Slider Vertikal) -->
            <div class="card-body p-0">
              <div class="table-responsive" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch;">
                <table class="table table-bordered table-striped table-hover table-sm m-0" style="font-size: 12px; min-width: 680px;">
                  <thead class="table-light">
                    <tr>
                      <th class="py-2 text-center" style="width: 12%;">Kelas</th>
                      <th class="py-2 text-center" style="width: 22%;">Status Verifikasi</th>
                      <th class="py-2" style="width: 38%;">Keterangan Detail Kehadiran</th>
                      <th class="py-2 text-center" style="width: 18%;">Diverifikasi Oleh</th>
                      <th class="py-2 text-center" style="width: 10%;">Jam</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($verifikasiRekap as $rekap)
                      @php
                        $modalId = 'modalDetailAbsen' . Str::slug($rekap['kelas']);
                        $siswaAbsen = $rekap['siswa_absen'] ?? [];
                      @endphp
                      <tr>
                        <td class="py-2 text-center font-weight-bold text-dark">{{ $rekap['kelas'] }}</td>
                        <td class="py-2 text-center">
                          @if($rekap['status'] === 'Sudah Verifikasi')
                            <span class="badge bg-success" style="font-size: 10px;"><i class="fas fa-check-circle me-1"></i> Sudah Verifikasi</span>
                          @else
                            <span class="badge bg-danger" style="font-size: 10px;"><i class="fas fa-minus-circle me-1"></i> Belum Verifikasi</span>
                          @endif
                        </td>
                        <td class="py-2">
                          <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                            <div>
                              <span class="badge bg-primary me-2 fw-bold" style="font-size: 11px;">{{ $rekap['hadir'] }} / {{ $rekap['total'] }} Hadir</span>
                              <span>{{ $rekap['detail'] }}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 10.5px; border-radius: 4px;"
                                    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}"
                                    data-toggle="modal" data-target="#{{ $modalId }}"
                                    title="Lihat rincian siswa tidak masuk">
                              <i class="fas fa-user-times me-1"></i>Detail
                            </button>
                          </div>
                        </td>
                        <td class="py-2 text-center text-muted">{{ $rekap['verified_by'] }}</td>
                        <td class="py-2 text-center text-muted">{{ $rekap['time'] }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Modals Detail Siswa Tidak Masuk Per Kelas -->
          @foreach($verifikasiRekap as $rekap)
            @php
              $modalId = 'modalDetailAbsen' . Str::slug($rekap['kelas']);
              $siswaAbsen = $rekap['siswa_absen'] ?? [];
            @endphp
            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                  <div class="modal-header {{ $rekap['status'] === 'Sudah Verifikasi' ? 'bg-success text-white' : 'bg-primary text-white' }} py-2 px-3">
                    <h6 class="modal-title font-weight-bold mb-0" id="{{ $modalId }}Label" style="font-size: 15px;">
                      <i class="fas fa-user-times me-2"></i> Daftar Siswa Tidak Masuk - Kelas {{ $rekap['kelas'] }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-3">
                    <!-- Summary Card -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-2 mb-3 rounded bg-light border">
                      <div>
                        <span class="text-muted small">Status Verifikasi:</span>
                        @if($rekap['status'] === 'Sudah Verifikasi')
                          <span class="badge bg-success ms-1"><i class="fas fa-check-circle me-1"></i>Sudah Verifikasi</span>
                        @else
                          <span class="badge bg-danger ms-1"><i class="fas fa-clock me-1"></i>Belum Verifikasi</span>
                        @endif
                        <span class="text-muted small ms-2"><i class="far fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</span>
                      </div>
                      <div>
                        <span class="badge bg-primary px-2 py-1" style="font-size: 12px;">{{ $rekap['hadir'] }} / {{ $rekap['total'] }} Hadir</span>
                        <span class="badge bg-danger px-2 py-1 ms-1" style="font-size: 12px;">{{ count($siswaAbsen) }} Tidak Masuk</span>
                      </div>
                    </div>

                    @if(count($siswaAbsen) > 0)
                      <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-sm align-middle m-0" style="font-size: 12.5px;">
                          <thead class="table-light">
                            <tr class="text-center">
                              <th style="width: 8%;">No</th>
                              <th style="width: 48%;">Nama Siswa</th>
                              <th style="width: 24%;">Keterangan</th>
                              <th style="width: 20%;">Waktu Catat</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($siswaAbsen as $idx => $s)
                              <tr>
                                <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                                <td class="fw-bold text-dark">{{ $s['nama'] }}</td>
                                <td class="text-center">
                                  @php
                                    $ketLower = strtolower($s['ket']);
                                    $badgeStyle = 'background-color: #64748b; color: #ffffff !important;';
                                    if (str_contains($ketLower, 'sakit')) {
                                      $badgeStyle = 'background-color: #d97706; color: #ffffff !important;';
                                    } elseif (str_contains($ketLower, 'izin') || str_contains($ketLower, 'ijin')) {
                                      $badgeStyle = 'background-color: #0284c7; color: #ffffff !important;';
                                    } elseif (str_contains($ketLower, 'alpha')) {
                                      $badgeStyle = 'background-color: #dc2626; color: #ffffff !important;';
                                    } elseif (str_contains($ketLower, 'dispen')) {
                                      $badgeStyle = 'background-color: #7c3aed; color: #ffffff !important;';
                                    }
                                  @endphp
                                  <span class="badge px-2 py-1" style="{{ $badgeStyle }} font-size: 11.5px; font-weight: 600; border-radius: 6px;">
                                    {{ $s['ket'] }}
                                  </span>
                                </td>
                                <td class="text-center text-muted"><i class="far fa-clock me-1"></i>{{ $s['jam'] }}</td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @else
                      <div class="text-center py-4 px-2">
                        <div class="mb-2">
                          <i class="fas fa-check-circle text-success" style="font-size: 40px;"></i>
                        </div>
                        <h6 class="fw-bold text-success mb-1">NIHIL - Seluruh Siswa Hadir</h6>
                        <p class="text-muted small mb-0">Tidak ada catatan siswa yang sakit, izin, alpha, atau dispensasi untuk kelas {{ $rekap['kelas'] }} hari ini.</p>
                      </div>
                    @endif
                  </div>
                  <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
                  </div>
                </div>
              </div>
            </div>
          @endforeach

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