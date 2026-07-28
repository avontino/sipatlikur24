      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item">
            <a href="/dashboard" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
                
              </p>
            </a>

          </li>

                    <li class="nav-item">
            <a href="/garjas" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Garjas
                
              </p>
            </a>

          </li>
          
          <li class="nav-item">
            <a href="/jurnalh" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Jurnal Harian
              </p>
            </a>
          </li>

          @if(auth()->user()->role=='admin' OR auth()->user()->role=='guru' OR auth()->user()->role=='kurikulum' OR auth()->user()->role=='tamu')
          <li class="nav-item">
            <a href="/perangkat" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Perangkat Pembelajaran
              </p>
            </a>
          </li>
          @endif

           @if(auth()->user()->role=='ketuakelas' OR auth()->user()->role=='walikelas')
          <li class="nav-item">
            <a href="/jurnalbaru" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Tambah Jurnal
              </p>
            </a>
          </li>

<!--           <li class="nav-item">
            <a href="/tambahjurnal" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Tambah Jurnal
              </p>
            </a>
          </li> -->

           <li class="nav-item">
            <a href="/edits" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Edit Jurnal
              </p>
            </a>
          </li>
          @endif
          @if(auth()->user()->role=='ketuakelas' OR auth()->user()->role=='walikelas' OR auth()->user()->role=='siswa')
              <li class="nav-item">
            <a href="/jurnal" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Jurnal Kelas Lengkap
              </p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="/absen" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Absensi Siswa
              </p>
            </a>
          </li>
          @endif

          @if(auth()->user()->role=='siswa')
          <li class="nav-item">
            <a href="/tambahijinsiswa" class="nav-link">
              <i class="nav-icon fas fa-globe"></i>
              <p>
                Tambah Ijin Siswa
              </p>
            </a>
          </li>
          @endif
    
          
          @if(auth()->user()->role=='lihat' OR auth()->user()->role=='admin' OR auth()->user()->role=='guru')

          <li class="nav-item">
            <a href="/lihatjurnal" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Lihat Jurnal Harian
              </p>
            </a>
          </li>
          @endif
          @if(auth()->user()->role=='admin' OR auth()->user()->role=='guru' OR auth()->user()->role=='lihat')
          <li class="nav-item">
            <a href="/jurnal" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Jurnal Lengkap
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="/jadwal" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Jadwal Pelajaran
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="/absen" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Absensi Siswa
              </p>
            </a>
          </li>
          @endif
          @if(auth()->user()->role=='ketuakelas' OR auth()->user()->role=='admin' OR auth()->user()->role=='guru' OR auth()->user()->role=='lihat')
          <li class="nav-item">
            <a href="/siswa" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Rekap Absensi
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/jrekap" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Rekap Jurnal
              </p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="/susulan" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Jurnal Susulan
              </p>
            </a>
          </li>
          
          @endif
          @if(auth()->user()->role=='admin' OR auth()->user()->role=='guru' OR auth()->user()->role=='kepala' OR auth()->user()->role=='pembina' OR auth()->user()->role=='kurikulum' OR auth()->user()->role=='walikelas' OR auth()->user()->role=='kesehatan' OR auth()->user()->role=='siswa' OR auth()->user()->role=='satpam')
           <li class="nav-item">
            <a href="/ijinsiswa" class="nav-link">
              <i class="nav-icon fas fa-globe"></i>
              <p>
                Ijin Siswa
              </p>
            </a>
          </li>
          @endif
          @if(auth()->user()->role=='pelapor' OR auth()->user()->role=='admin' OR auth()->user()->role=='guru' OR auth()->user()->role=='lihat')
          <li class="nav-item">
            <a href="/tambahkasus" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Tambah Kasus
              </p>
            </a>
          </li>

           <li class="nav-item">
            <a href="/lihatkasus" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Lihat Kasus
              </p>
            </a>
          </li>
          @endif
          @if(auth()->user()->role=='admin')
          <li class="nav-item">
            <a href="/kasus" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Kasus Lengkap
              </p>
            </a>
          </li>
          @endif
          @if(auth()->user()->role=='guru')
          <li class="nav-item">
            <a href="/jurnalguru" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Jurnal Per Guru
              </p>
            </a>
          </li>
          @endif
          @if(auth()->user()->role=='guru' OR auth()->user()->role=='admin' OR auth()->user()->role=='tendik')
          <li class="nav-item">
            <a href="/tambahijin" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Tambah Ijin Guru
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="/ijin" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Ijin Lengkap
              </p>
            </a>
          </li>     
          @endif
          @if(auth()->user()->role=='admin')
          <li class="nav-item">
            <a href="/operator" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Operator Lengkap
              </p>
            </a>
          </li>       
          @endif
          <!-- @if(auth()->user()->role=='surat' OR auth()->user()->role=='admin')
          <li class="nav-item">
            <a href="/surat" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Surat
              </p>
            </a>
          </li>
          @endif
          @if(auth()->user()->role=='tamu' OR auth()->user()->role=='admin')
          <li class="nav-item">
            <a href="/tamu" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Tamu
              </p>
            </a>
          </li>
          @endif -->
          @if(auth()->user()->role=='siswa')
          <li class="nav-item">
            <a href="/absen" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Absensi Siswa
              </p>
            </a>
          </li>
          @endif
          
          @if(auth()->user()->role=='siswa' OR auth()->user()->role=='admin' OR auth()->user()->role=='keuangan')
                    <li class="nav-item">
            <a href="/tagihan" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Tagihan Siswa
              </p>
            </a>
          </li>
          @endif
           <li class="nav-item">
            <a href="logout" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                LOGOUT
              </p>
            </a>
          </li>

          
      </nav>