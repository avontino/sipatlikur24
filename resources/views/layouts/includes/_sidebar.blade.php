<nav class="mt-2">
  <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
    
    <!-- CATEGORY: NAVIGASI UTAMA -->
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-2 mb-1" style="font-size: 11px; letter-spacing: 0.8px;">Navigasi Utama</li>
    
    <li class="nav-item">
      <a href="/dashboard" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-line"></i>
        <p>Dashboard</p>
      </a>
    </li>

    @if(auth()->user()->hasRole('admin'))
    <!-- ========================================================= -->
    <!-- DEDICATED ADMIN SIDEBAR (CLEAN, NON-REDUNDANT, USER-FRIENDLY) -->
    <!-- ========================================================= -->

    <!-- CATEGORY: PENGATURAN AKSES & MASTER DATA -->
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-3 mb-1" style="font-size: 11px; letter-spacing: 0.8px; color: #cbd5e1 !important;">Akses & Master Data</li>
    
    <li class="nav-item">
      <a href="/operator" class="nav-link {{ Request::is('operator*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-cog text-warning"></i>
        <p>Data Guru & Operator</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/tahun-ajaran" class="nav-link {{ Request::is('tahun-ajaran*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-alt text-info"></i>
        <p>Tahun Ajaran</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/kelas" class="nav-link {{ Request::is('kelas*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-school text-success"></i>
        <p>Data Kelas</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/mapel" class="nav-link {{ Request::is('mapel*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-book text-primary"></i>
        <p>Data Mapel</p>
      </a>
    </li>

    <!-- CATEGORY: PENGAWASAN KBM & GURU -->
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-3 mb-1" style="font-size: 11px; letter-spacing: 0.8px; color: #93c5fd !important;">Pengawasan KBM & Guru</li>
    
    <li class="nav-item">
      <a href="/jurnalh?view=kurikulum" class="nav-link {{ Request::is('jurnalh*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-day text-info"></i>
        <p>Jurnal Harian Sekolah</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/jadwal" class="nav-link {{ Request::is('jadwal*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-alt text-warning"></i>
        <p>Jadwal Pelajaran</p>
      </a>
    </li>
    
    <li class="nav-item {{ Request::is('jrekap*', 'jurnal', 'jurnalguru*', 'susulan*', 'perangkat*') ? 'menu-open' : '' }}">
      <a href="#" class="nav-link {{ Request::is('jrekap*', 'jurnal', 'jurnalguru*', 'susulan*', 'perangkat*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-clipboard-list text-primary"></i>
        <p>
          Rekap KBM & Perangkat
          <i class="nav-arrow fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview ps-2">
        <li class="nav-item">
          <a href="/jrekap" class="nav-link {{ Request::is('jrekap*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-list-alt text-info"></i>
            <p>Rekap Jurnal Kelas</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/jurnal" class="nav-link {{ Request::is('jurnal') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book-open text-primary"></i>
            <p>Riwayat Jurnal Lengkap</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/jurnalguru" class="nav-link {{ Request::is('jurnalguru*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-clock text-warning"></i>
            <p>Rekap Jurnal Guru</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/susulan" class="nav-link {{ Request::is('susulan*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-history text-secondary"></i>
            <p>Jurnal Susulan</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/perangkat" class="nav-link {{ Request::is('perangkat*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-briefcase text-success"></i>
            <p>Perangkat Pembelajaran</p>
          </a>
        </li>
      </ul>
    </li>

    {{-- Presensi & Lokasi Guru (Disembunyikan) --}}

    <!-- CATEGORY: PENGAWASAN SISWA & KEDISIPLINAN -->
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-3 mb-1" style="font-size: 11px; letter-spacing: 0.8px; color: #86efac !important;">Siswa & Kedisiplinan</li>
    
    <li class="nav-item">
      <a href="/absen" class="nav-link {{ Request::is('absen*') && !request()->filled('view') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-check text-success"></i>
        <p>Absensi Seluruh Siswa</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/siswa" class="nav-link {{ Request::is('siswa*') && !request()->filled('view') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users text-primary"></i>
        <p>Rekap Absensi Siswa</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/ijinsiswa" class="nav-link {{ Request::is('ijinsiswa*') && !request()->filled('view') ? 'active' : '' }}">
        <i class="nav-icon fas fa-id-card text-info"></i>
        <p>Ijin Siswa (Semua)</p>
      </a>
    </li>
    
    <li class="nav-item {{ Request::is('tambahkasus*', 'lihatkasus*', 'kasus*', 'kategori-poin*', 'history-poin*', 'poin-siswa*') ? 'menu-open' : '' }}">
      <a href="#" class="nav-link {{ Request::is('tambahkasus*', 'lihatkasus*', 'kasus*', 'kategori-poin*', 'history-poin*', 'poin-siswa*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-gavel text-danger"></i>
        <p>
          Pelanggaran & Poin
          <i class="nav-arrow fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview ps-2">
        <li class="nav-item">
          <a href="/tambahkasus" class="nav-link {{ Request::is('tambahkasus*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-exclamation-triangle"></i>
            <p>Tambah Kasus</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/history-poin" class="nav-link {{ Request::is('history-poin*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-search"></i>
            <p>History Poin</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/kategori-poin" class="nav-link {{ Request::is('kategori-poin*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-list-ul"></i>
            <p>Kategori Poin</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/lihatkasus" class="nav-link {{ Request::is('lihatkasus*') || Request::is('kasus') ? 'active' : '' }}">
            <i class="nav-icon fas fa-folder-open"></i>
            <p>Lihat Kasus</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/poin-siswa" class="nav-link {{ Request::is('poin-siswa*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-star text-warning"></i>
            <p>Poin & SP Siswa</p>
          </a>
        </li>
      </ul>
    </li>

    {{-- Garjas & Tagihan Siswa (Disembunyikan) --}}

    <!-- CATEGORY: PEMELIHARAAN SISTEM -->
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-3 mb-1" style="font-size: 11px; letter-spacing: 0.8px; color: #f43f5e !important;">Pemeliharaan Sistem</li>
    
    <li class="nav-item">
      <a href="/admin/logs" class="nav-link {{ Request::is('admin/logs*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-history text-info"></i>
        <p>Log Aktivitas</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/admin/backup" class="nav-link {{ Request::is('admin/backup*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-database text-warning"></i>
        <p>Backup & Restore</p>
      </a>
    </li>

    @else
    <!-- ========================================================= -->
    <!-- STANDARD SIDEBAR FOR OTHER ROLES (GURU, WALIKELAS, KURIKULUM, SISWA, ETC.) -->
    <!-- ========================================================= -->

    @if(auth()->user()->hasPermission('izin_view') && !auth()->user()->hasRole('siswa') && !auth()->user()->hasRole('kurikulum') && !auth()->user()->hasRole('walikelas'))
    <li class="nav-item">
      <a href="/ijinsiswa" class="nav-link {{ Request::is('ijinsiswa*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-id-card text-info"></i>
        <p>Ijin Siswa</p>
      </a>
    </li>
    @endif

    {{-- Presensi Guru (Disembunyikan) --}}
    @if(auth()->user()->role !== 'siswa' && !auth()->user()->hasRole('siswa'))
    <li class="nav-item">
      <a href="/jurnalh" class="nav-link {{ Request::is('jurnalh*') && !request()->filled('view') ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-day text-success"></i>
        <p>Jurnal Harian Saya</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/jurnal" class="nav-link {{ Request::is('jurnal*') && !request()->filled('view') ? 'active' : '' }}">
        <i class="nav-icon fas fa-book-open text-primary"></i>
        <p>Riwayat Jurnal Saya</p>
      </a>
    </li>
    @endif


    <!-- CATEGORY: GURU / STAF -->
    @if(auth()->user()->hasRole('guru'))
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-3 mb-1" style="font-size: 11px; letter-spacing: 0.8px; color: #a5b4fc !important;">Menu Guru & Staf</li>

    <!-- KBM & Jurnal Guru -->
    <li class="nav-item {{ Request::is('jadwal*', 'perangkat*') ? 'menu-open' : '' }}">
      <a href="#" class="nav-link {{ Request::is('jadwal*', 'perangkat*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-book text-info"></i>
        <p>
          KBM & Jurnal Guru
          <i class="nav-arrow fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview ps-2">
        <li class="nav-item">
          <a href="/jadwal" class="nav-link {{ Request::is('jadwal*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Jadwal Pelajaran</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/perangkat" class="nav-link {{ Request::is('perangkat*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-briefcase"></i>
            <p>Perangkat Pembelajaran</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/jurnalguru" class="nav-link {{ Request::is('jurnalguru*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-download text-warning"></i>
            <p>Jurnal Guru (Rekap)</p>
          </a>
        </li>
      </ul>
    </li>

    <!-- Presensi & Izin Guru -->
    <li class="nav-item {{ Request::is('tambahijin*', 'ijin*') && !Request::is('ijinsiswa*') ? 'menu-open' : '' }}">
      <a href="#" class="nav-link {{ Request::is('tambahijin*', 'ijin*') && !Request::is('ijinsiswa*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chalkboard-teacher text-warning"></i>
        <p>
          Presensi & Izin Guru
          <i class="nav-arrow fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview ps-2">
        <li class="nav-item">
          <a href="/tambahijin" class="nav-link {{ Request::is('tambahijin*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-medical"></i>
            <p>Tambah Izin Guru</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/ijin" class="nav-link {{ Request::is('ijin') ? 'active' : '' }}">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>Daftar Izin Guru</p>
          </a>
        </li>
      </ul>
    </li>
    @endif

    <!-- Siswa -->
    @if((auth()->user()->hasPermission('presensi_view') || auth()->user()->hasPermission('presensi_create') || auth()->user()->hasPermission('poin_view')) && !auth()->user()->hasRole('siswa') && !auth()->user()->hasRole('walikelas') && !auth()->user()->hasRole('kurikulum'))
    <li class="nav-item {{ Request::is('absen*', 'siswa*', 'poin-siswa*') && request()->query('view') !== 'walikelas' ? 'menu-open' : '' }}">
      <a href="#" class="nav-link {{ Request::is('absen*', 'siswa*', 'poin-siswa*') && request()->query('view') !== 'walikelas' ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-graduate text-success"></i>
        <p>
          Siswa
          <i class="nav-arrow fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview ps-2">
        @if(auth()->user()->hasPermission('presensi_create'))
        <li class="nav-item">
          <a href="/absen" class="nav-link {{ Request::is('absen*') && request()->query('view') !== 'walikelas' ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-check"></i>
            <p>Absensi Siswa</p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('presensi_view'))
        <li class="nav-item">
          <a href="/siswa" class="nav-link {{ Request::is('siswa*') && request()->query('view') !== 'walikelas' ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>Rekap Absensi Siswa</p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('poin_view'))
        <li class="nav-item">
          <a href="/poin-siswa" class="nav-link {{ Request::is('poin-siswa*') && request()->query('view') !== 'walikelas' ? 'active' : '' }}">
            <i class="nav-icon fas fa-star text-warning"></i>
            <p>Poin & SP Siswa</p>
          </a>
        </li>
        @endif
      </ul>
    </li>
    @endif

    {{-- Garjas (Disembunyikan) --}}


    <!-- CATEGORY: MENU SISWA -->
    @if(auth()->user()->role == 'siswa')
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-3 mb-1" style="font-size: 11px; letter-spacing: 0.8px; color: #fde047 !important;">Menu Siswa</li>
    
    <li class="nav-item">
      <a href="/tambahijinsiswa" class="nav-link {{ Request::is('tambahijinsiswa*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-clock text-warning"></i>
        <p>Tambah Izin Saya</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/ijinsiswa" class="nav-link {{ Request::is('ijinsiswa') ? 'active' : '' }}">
        <i class="nav-icon fas fa-id-card text-info"></i>
        <p>Riwayat Izin Saya</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/history-poin" class="nav-link {{ Request::is('history-poin*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-exclamation-triangle text-danger"></i>
        <p>Poin Pelanggaran Saya</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/absen" class="nav-link {{ Request::is('absen*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-check text-success"></i>
        <p>Absensi Saya</p>
      </a>
    </li>
    @endif


    <!-- CATEGORY: WALI KELAS -->
    @if((auth()->user()->hasRole('walikelas') || auth()->user()->walikelas_kelas) && auth()->user()->role !== 'siswa')
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-3 mb-1" style="font-size: 11px; letter-spacing: 0.8px; color: #86efac !important;">Menu Wali Kelas</li>

    <li class="nav-item">
      <a href="/jurnalh?view=walikelas" class="nav-link {{ Request::is('jurnalh*') && request()->query('view') === 'walikelas' ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-day text-success"></i>
        <p>Jurnal Harian</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/jurnalbaru" class="nav-link {{ Request::is('jurnalbaru*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-plus-circle text-success"></i>
        <p>Tambah Jurnal</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/edits" class="nav-link {{ Request::is('edits*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-edit text-success"></i>
        <p>Edit Jurnal</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/jurnal?view=walikelas" class="nav-link {{ Request::is('jurnal*') && request()->query('view') === 'walikelas' ? 'active' : '' }}">
        <i class="nav-icon fas fa-history text-success"></i>
        <p>Riwayat Jurnal</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/absen?view=walikelas" class="nav-link {{ Request::is('absen*') && request()->query('view') === 'walikelas' ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-check text-success"></i>
        <p>Absensi Siswa</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/siswa?view=walikelas" class="nav-link {{ Request::is('siswa*') && request()->query('view') === 'walikelas' ? 'active' : '' }}">
        <i class="nav-icon fas fa-users text-success"></i>
        <p>Rekap Absensi Siswa</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/ijinsiswa?view=walikelas" class="nav-link {{ Request::is('ijinsiswa*') && request()->query('view') === 'walikelas' ? 'active' : '' }}">
        <i class="nav-icon fas fa-id-card text-success"></i>
        <p>Ijin Siswa</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/poin-siswa?view=walikelas" class="nav-link {{ Request::is('poin-siswa*') && request()->query('view') === 'walikelas' ? 'active' : '' }}">
        <i class="nav-icon fas fa-star text-success"></i>
        <p>Poin Siswa</p>
      </a>
    </li>
    @endif


    <!-- CATEGORY: KURIKULUM -->
    @if(auth()->user()->hasRole('kurikulum'))
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-3 mb-1" style="font-size: 11px; letter-spacing: 0.8px; color: #93c5fd !important;">Menu Kurikulum</li>

    <li class="nav-item">
      <a href="/jurnalh?view=kurikulum" class="nav-link {{ Request::is('jurnalh*') && request()->query('view') === 'kurikulum' ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-day text-info"></i>
        <p>Jurnal Harian</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/ijinsiswa?view=kurikulum" class="nav-link {{ Request::is('ijinsiswa*') && request()->query('view') === 'kurikulum' ? 'active' : '' }}">
        <i class="nav-icon fas fa-id-card text-info"></i>
        <p>Ijin Siswa</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/perangkat" class="nav-link {{ Request::is('perangkat*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-briefcase text-info"></i>
        <p>Perangkat Pembelajaran</p>
      </a>
    </li>

    <!-- Rekap Jurnal & Susulan -->
    <li class="nav-item {{ Request::is('jrekap*', 'susulan*') ? 'menu-open' : '' }}">
      <a href="#" class="nav-link {{ Request::is('jrekap*', 'susulan*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-clipboard-list text-info"></i>
        <p>
          Rekap & Susulan KBM
          <i class="nav-arrow fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview ps-2">
        <li class="nav-item">
          <a href="/jrekap" class="nav-link {{ Request::is('jrekap*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-list-alt"></i>
            <p>Rekap Jurnal Kelas</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/susulan" class="nav-link {{ Request::is('susulan*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-history"></i>
            <p>Jurnal Susulan</p>
          </a>
        </li>
      </ul>
    </li>

    <!-- Data Master -->
    <li class="nav-item {{ Request::is('tahun-ajaran*', 'kelas*', 'mapel*') ? 'menu-open' : '' }}">
      <a href="#" class="nav-link {{ Request::is('tahun-ajaran*', 'kelas*', 'mapel*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-folder text-teal"></i>
        <p>
          Data Master & Lokasi
          <i class="nav-arrow fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview ps-2">
        <li class="nav-item">
          <a href="/tahun-ajaran" class="nav-link {{ Request::is('tahun-ajaran*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Tahun Ajaran</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/kelas" class="nav-link {{ Request::is('kelas*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-school"></i>
            <p>Data Kelas</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/mapel" class="nav-link {{ Request::is('mapel*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book"></i>
            <p>Data Mapel</p>
          </a>
        </li>
      </ul>
    </li>
    @endif

    <!-- CATEGORY: KASUS & LAPORAN -->
    @if(auth()->user()->hasRole('kesiswaan') || auth()->user()->hasRole('pembina'))
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-3 mb-1" style="font-size: 11px; letter-spacing: 0.8px; color: #f87171 !important;">Kasus & Laporan</li>

    <li class="nav-item {{ Request::is('tambahkasus*', 'lihatkasus*', 'kasus*', 'kategori-poin*', 'history-poin*') ? 'menu-open' : '' }}">
      <a href="#" class="nav-link {{ Request::is('tambahkasus*', 'lihatkasus*', 'kasus*', 'kategori-poin*', 'history-poin*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-gavel text-danger"></i>
        <p>
          Pelanggaran & Poin
          <i class="nav-arrow fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview ps-2">
        <li class="nav-item">
          <a href="/tambahkasus" class="nav-link {{ Request::is('tambahkasus*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-exclamation-triangle"></i>
            <p>Tambah Kasus</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/history-poin" class="nav-link {{ Request::is('history-poin*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-search"></i>
            <p>History Poin</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/kategori-poin" class="nav-link {{ Request::is('kategori-poin*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-list-ul"></i>
            <p>Kategori Poin</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/lihatkasus" class="nav-link {{ Request::is('lihatkasus*') || Request::is('kasus') ? 'active' : '' }}">
            <i class="nav-icon fas fa-folder-open"></i>
            <p>Lihat Kasus</p>
          </a>
        </li>
      </ul>
    </li>
    @endif
    @endif


    <!-- CATEGORY: AKUN -->
    <li class="nav-header text-uppercase fs-7 text-white-50 px-3 mt-3 mb-1" style="font-size: 11px; letter-spacing: 0.8px;">Akun</li>
    <li class="nav-item">
      <a href="/ganti-password" class="nav-link {{ Request::is('ganti-password*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-key text-warning"></i>
        <p>Ganti Password</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/logout" class="nav-link text-danger">
        <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
        <p class="fw-bold">LOGOUT</p>
      </a>
    </li>
    
    <!-- Spacer item so LOGOUT is fully visible above mobile bottom navigation bar -->
    <li class="nav-item d-block d-md-none" style="height: 90px; margin-bottom: 90px;"></li>

  </ul>
</nav>