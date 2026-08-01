  <nav class="app-header navbar navbar-expand navbar-white navbar-light border-bottom" style="position: relative; z-index: 1070;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item">
        <a href="/logout" class="nav-link text-danger fw-bold" title="Logout dari aplikasi"><i class="fas fa-sign-out-alt me-1"></i> LOGOUT</a>
      </li>
<!--       <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li> -->
    </ul>



    <!-- SEARCH FORM -->
    <!-- <form class="form-inline ms-3">
      <div class="input-group input-group-sm">
        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-navbar" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form> -->

      <!-- Right navbar links -->
    <ul class="navbar-nav ms-auto pe-3">
      @if(auth()->check())
        @php
          $notifs = auth()->user()->unreadNotifications()->take(5)->get();
          $notifCount = auth()->user()->unreadNotifications()->count();
          
          $todayPresensi = null;
          $isGuruOrTendik = false;
          $showPresensiWarning = false;
          $presensiTitle = '';
          $presensiMsg = '';
          $presensiIcon = 'fa-user-check text-warning';
          
          if(false && auth()->check()) {
              $isGuruOrTendik = (auth()->user()->role == 'guru' || auth()->user()->role == 'tendik');
              if ($isGuruOrTendik) {
                  $todayPresensi = \App\Models\PresensiGuru::where('user_id', auth()->id())
                      ->where('tanggal', date('Y-m-d'))
                      ->first();
                  
                  // Ambil config jam masuk/pulang
                  $config = \DB::table('presensi_guru_settings')->first() ?? (object) [
                      'jam_masuk' => '07:00:00',
                      'jam_pulang' => '14:00:00'
                  ];
                  
                  $nowTime = date('H:i:s');
                  
                  if (!$todayPresensi) {
                      // Belum presensi datang
                      $showPresensiWarning = true;
                      $presensiTitle = 'Belum Presensi Datang';
                      $presensiMsg = 'Hari ini Anda belum melakukan presensi datang. Batas jam masuk: ' . substr($config->jam_masuk, 0, 5) . ' WIB.';
                      $presensiIcon = 'fa-exclamation-triangle text-danger';
                  } else {
                      // Sudah datang
                      if (empty($todayPresensi->jam_pulang)) {
                          // Belum pulang
                          // Jika sudah melewati jam pulang
                          if ($nowTime >= $config->jam_pulang) {
                              $showPresensiWarning = true;
                              $presensiTitle = 'Belum Presensi Pulang';
                              $presensiMsg = 'Sudah memasuki jam pulang. Harap segera lakukan presensi pulang.';
                              $presensiIcon = 'fa-walking text-warning';
                          } else {
                              // Sudah absen datang, belum jam pulang
                              $showPresensiWarning = true;
                              $presensiTitle = 'Presensi Datang: ' . ($todayPresensi->status_datang ?? 'OK');
                              $presensiMsg = 'Masuk jam ' . substr($todayPresensi->jam_datang, 0, 5) . ' WIB. Presensi pulang dibuka jam ' . substr($config->jam_pulang, 0, 5) . ' WIB.';
                              $presensiIcon = 'fa-check-circle text-success';
                          }
                      } else {
                          // Sudah presensi pulang (lengkap)
                          $showPresensiWarning = true;
                          $presensiTitle = 'Presensi Hari Ini Lengkap';
                          $presensiMsg = 'Datang: ' . substr($todayPresensi->jam_datang, 0, 5) . ' WIB. Pulang: ' . substr($todayPresensi->jam_pulang, 0, 5) . ' WIB.';
                          $presensiIcon = 'fa-check-double text-success';
                      }
                  }
              }
          }
          
          $isWarningStatus = ($presensiTitle == 'Belum Presensi Datang' || $presensiTitle == 'Belum Presensi Pulang');
          if ($showPresensiWarning && $isWarningStatus) {
              $notifCount += 1;
          }
        @endphp
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
          <a class="nav-link position-relative" data-bs-toggle="dropdown" href="#" aria-expanded="false">
            <i class="far fa-bell" style="font-size: 20px;"></i>
            @if($notifCount > 0)
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px; margin-top: 5px; margin-left: -5px;">
                {{ $notifCount }}
              </span>
            @endif
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0 border-0 shadow" style="min-width: 280px; max-width: 320px; z-index: 1080 !important;">
            <div class="dropdown-header bg-light py-2 border-bottom d-flex justify-content-between align-items-center">
              <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $notifCount }} Notifikasi Baru</span>
              @if($notifCount > 0)
                <form action="{{ route('notifications.markAllRead') }}" method="POST" class="m-0">
                  @csrf
                  <button type="submit" class="btn btn-link text-primary p-0 small text-decoration-none" style="font-size: 11px;">Tandai semua dibaca</button>
                </form>
              @endif
            </div>
            <div class="dropdown-divider m-0"></div>
            
            <div class="notif-scroll-area" style="max-height: 250px; overflow-y: auto;">
              @if($showPresensiWarning)
                <a href="/presensi-guru" class="dropdown-item py-2 d-flex align-items-start border-bottom" style="white-space: normal; background-color: {{ $isWarningStatus ? '#fff5f5' : '' }}">
                  <div class="me-2 mt-1">
                    <span class="d-inline-flex p-2 rounded-circle" style="background-color: rgba(0,0,0,0.05);">
                      <i class="fas {{ $presensiIcon }}" style="width: 14px; text-align: center; font-size: 12px;"></i>
                    </span>
                  </div>
                  <div style="flex: 1;">
                    <div class="small font-weight-bold text-dark" style="font-size: 12px; font-weight: 600;">{{ $presensiTitle }}</div>
                    <div class="text-muted small mt-0" style="font-size: 11px; line-height: 1.3;">{{ $presensiMsg }}</div>
                    <div class="text-muted small mt-1" style="font-size: 9px;"><i class="far fa-clock me-1"></i>Presensi Hari Ini</div>
                  </div>
                </a>
              @endif
              @forelse($notifs as $notif)
                @php
                  $data = is_array($notif->data) ? $notif->data : json_decode($notif->data, true);
                  $icon = 'fa-bell text-secondary';
                  if (($data['category'] ?? '') == 'jurnal') {
                      $icon = 'fa-book text-primary';
                  } elseif (($data['category'] ?? '') == 'absen') {
                      $icon = 'fa-user-check text-success';
                  } elseif (($data['category'] ?? '') == 'ijin') {
                      $icon = 'fa-id-card text-warning';
                  } elseif (($data['category'] ?? '') == 'garjas') {
                      $icon = 'fa-running text-info';
                  }
                @endphp
                <a href="{{ route('notifications.read', $notif->id) }}" class="dropdown-item py-2 d-flex align-items-start border-bottom hover-bg-light" style="white-space: normal;">
                  <div class="me-2 mt-1">
                    <span class="d-inline-flex p-2 rounded-circle" style="background-color: rgba(0,0,0,0.05);">
                      <i class="fas {{ $icon }}" style="width: 14px; text-align: center; font-size: 12px;"></i>
                    </span>
                  </div>
                  <div style="flex: 1;">
                    <div class="small font-weight-bold text-dark" style="font-size: 12px; font-weight: 600;">{{ $data['title'] ?? 'Notifikasi' }}</div>
                    <div class="text-muted small mt-0" style="font-size: 11px; line-height: 1.3;">{{ $data['message'] ?? '' }}</div>
                    <div class="text-muted small mt-1" style="font-size: 9px;"><i class="far fa-clock me-1"></i>{{ $notif->created_at->diffForHumans() }}</div>
                  </div>
                </a>
              @empty
                <div class="dropdown-item py-3 text-center text-muted small">Tidak ada notifikasi baru</div>
              @endforelse
            </div>
            
            <div class="dropdown-divider m-0"></div>
            <a href="/dashboard" class="dropdown-item dropdown-footer text-center small text-primary py-2 text-decoration-none" style="font-size: 12px;">Lihat Semua di Dashboard</a>
          </div>
        </li>
      @endif
    </ul>
  </nav>