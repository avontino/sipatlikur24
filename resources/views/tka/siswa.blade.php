@extends('layouts.master')

@section('content')
<div class="content-header py-4 px-3 mb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-7">
        <div class="d-flex align-items-center">
          <div class="me-3 p-3 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #009638, #004d1a); color: #fff;">
            <i class="fas fa-graduation-cap fa-2x"></i>
          </div>
          <div>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 mb-1 fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">
              <i class="fas fa-star me-1 text-warning"></i> ASESMEN KELAS 9
            </span>
            <h2 class="m-0 text-dark fw-bolder" style="letter-spacing: -0.5px; font-size: 1.65rem;">
              Nilai Tes Kemampuan Akademik (TKA)
            </h2>
            <p class="text-muted small mb-0 mt-1">Laporan pencapaian literasi Bahasa Indonesia & numerasi Matematika</p>
          </div>
        </div>
      </div>
      <div class="col-sm-5 mt-3 mt-sm-0 text-sm-end">
        <ol class="breadcrumb float-sm-end bg-white px-3 py-2 rounded-pill shadow-sm border mb-0">
          <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none text-secondary"><i class="fas fa-home me-1"></i> Dashboard</a></li>
          <li class="breadcrumb-item active text-success fw-bold">Nilai TKA</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
      <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('gagal'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
      <i class="fas fa-exclamation-circle me-1"></i> {{ session('gagal') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(!$isKelas9)
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body text-center py-5">
        <div class="mb-3">
          <i class="fas fa-info-circle fa-4x text-info"></i>
        </div>
        <h4 class="fw-bold">Menu Khusus Siswa Kelas 9</h4>
        <p class="text-muted">
          Mohon maaf, menu Tes Kemampuan Akademik (TKA) saat ini dikhususkan bagi siswa Kelas 9 (IX) sebagai persiapan asesmen dan ujian kelulusan.
        </p>
        <a href="/dashboard" class="btn btn-success rounded-pill px-4 mt-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
      </div>
    </div>
    @elseif($tkaList->isEmpty())
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body text-center py-5">
        <div class="mb-3">
          <i class="fas fa-clipboard-list fa-4x text-warning"></i>
        </div>
        <h4 class="fw-bold">Data Nilai Belum Tersedia</h4>
        <p class="text-muted">
          Nilai Tes Kemampuan Akademik (TKA) untuk akun Anda (<strong>{{ $user->name }}</strong> - NIS: {{ $user->username }}) belum diinput ke sistem oleh Kurikulum.
        </p>
        <p class="small text-muted">Silakan hubungi Wali Kelas atau bagian Kurikulum jika Anda sudah mengikuti tes.</p>
      </div>
    </div>
    @else

    {{-- KARTU IDENTITAS SISWA --}}
    @php
      $info = $tkaList->first();
    @endphp
    <div class="card shadow border-0 mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #004d1a 100%); border-radius: 18px; position: relative;">
      <div style="position: absolute; right: -20px; top: -30px; font-size: 14rem; color: rgba(255,255,255,0.03); pointer-events: none;">
        <i class="fas fa-graduation-cap"></i>
      </div>
      <div class="card-body p-4 position-relative">
        <div class="row align-items-center">
          <div class="col-md-8">
            <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold text-uppercase rounded-pill shadow-sm" style="letter-spacing: 0.5px;">
              <i class="fas fa-award me-1"></i> Siswa Kelas 9 • SMP Negeri 24 Malang
            </span>
            <h2 class="fw-bolder mb-2 text-white" style="letter-spacing: -0.5px;">{{ $info->nama }}</h2>
            <div class="d-flex flex-wrap gap-2 gap-md-3 mt-2 text-white-50 fs-6">
              <div class="bg-white bg-opacity-10 px-3 py-1 rounded-pill"><i class="fas fa-id-card me-1 text-warning"></i> NIS: <strong class="text-white">{{ $info->nis }}</strong></div>
              @if($info->nisn)
              <div class="bg-white bg-opacity-10 px-3 py-1 rounded-pill"><i class="fas fa-fingerprint me-1 text-warning"></i> NISN: <strong class="text-white">{{ $info->nisn }}</strong></div>
              @endif
              <div class="bg-white bg-opacity-10 px-3 py-1 rounded-pill"><i class="fas fa-chalkboard-teacher me-1 text-warning"></i> Kelas: <strong class="text-white">{{ $info->kelas }}</strong></div>
              @if($info->wali_kelas)
              <div class="bg-white bg-opacity-10 px-3 py-1 rounded-pill"><i class="fas fa-user-tie me-1 text-warning"></i> Wali Kelas: <strong class="text-white">{{ $info->wali_kelas }}</strong></div>
              @endif
            </div>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            @if($diagnostik && $to1)
            <a href="/tka/rekap/{{ $info->nis }}/pdf" class="btn btn-warning btn-lg fw-bold shadow-lg rounded-pill px-4">
              <i class="fas fa-file-pdf me-1"></i> Unduh Rekap Lengkap (PDF)
            </a>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- TABEL / KARTU ASESMEN --}}
    <div class="row">

      {{-- 1. TES DIAGNOSTIK --}}
      <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
          <div class="card-header bg-white border-bottom border-light-subtle d-flex justify-content-between align-items-center py-3 px-4">
            <div class="d-flex align-items-center">
              <div class="me-2 rounded-circle p-2 bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-clipboard-check"></i>
              </div>
              <h5 class="fw-bold mb-0 text-dark">1. Tes Diagnostik TKA</h5>
            </div>
            @if($diagnostik)
            <a href="/tka/{{ $diagnostik->id }}/pdf" class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3">
              <i class="fas fa-file-pdf me-1"></i> Rapor PDF
            </a>
            @endif
          </div>
          <div class="card-body p-4 d-flex flex-column justify-content-between">
            @if($diagnostik)
            <div>
              <p class="text-muted small mb-3">
                Hasil asesmen diagnostik awal kemampuan literasi (Bahasa Indonesia) dan numerasi (Matematika).
              </p>

              <div class="row g-3 mb-3">
                {{-- Bahasa Indonesia --}}
                <div class="col-sm-6">
                  <div class="p-3 rounded-4 border text-center bg-light">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Bahasa Indonesia</div>
                    <div class="display-6 fw-bold text-dark">{{ $diagnostik->nilai_bindo }}</div>
                    <div class="mt-2">
                      <span class="badge {{ $diagnostik->badge_class_bindo }} px-3 py-1 fs-7 rounded-pill">
                        {{ $diagnostik->ket_bindo ?: 'Belum Ada' }}
                      </span>
                    </div>
                  </div>
                </div>

                {{-- Matematika --}}
                <div class="col-sm-6">
                  <div class="p-3 rounded-4 border text-center bg-light">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Matematika</div>
                    <div class="display-6 fw-bold text-dark">{{ $diagnostik->nilai_mtk }}</div>
                    <div class="mt-2">
                      <span class="badge {{ $diagnostik->badge_class_mtk }} px-3 py-1 fs-7 rounded-pill">
                        {{ $diagnostik->ket_mtk ?: 'Belum Ada' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Rekap Skor Diagnostik - Warna Putih Jelas & Elegan --}}
            <div class="d-flex justify-content-between align-items-center p-3 rounded-4 shadow-sm text-white" style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);">
              <div>
                <span class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Rata-rata Nilai:</span>
                <div class="fw-bolder fs-3 text-white lh-1 mt-1">{{ $diagnostik->nilai_rata_rata }}</div>
              </div>
              <div class="text-end">
                <span class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Total Skor:</span>
                <div class="fw-bolder fs-3 text-white lh-1 mt-1">{{ $diagnostik->nilai_total }}</div>
              </div>
            </div>
            @else
            <div class="text-center py-5 text-muted">
              <i class="fas fa-times-circle fa-3x mb-2 text-secondary opacity-50"></i>
              <p class="mb-0">Belum ada data nilai Tes Diagnostik.</p>
            </div>
            @endif
          </div>
        </div>
      </div>

      {{-- 2. TRY OUT 1 (TO-1) --}}
      <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
          <div class="card-header bg-white border-bottom border-light-subtle d-flex justify-content-between align-items-center py-3 px-4">
            <div class="d-flex align-items-center">
              <div class="me-2 rounded-circle p-2 bg-success bg-opacity-10 text-success">
                <i class="fas fa-pencil-alt"></i>
              </div>
              <h5 class="fw-bold mb-0 text-dark">2. Try Out 1 (TO-1) TKA</h5>
            </div>
            @if($to1)
            <a href="/tka/{{ $to1->id }}/pdf" class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3">
              <i class="fas fa-file-pdf me-1"></i> Rapor PDF
            </a>
            @endif
          </div>
          <div class="card-body p-4 d-flex flex-column justify-content-between">
            @if($to1)
            <div>
              <p class="text-muted small mb-3">
                Hasil simulasi ujian Try Out 1 sebagai tolak ukur evaluasi setelah pendalaman materi.
              </p>

              <div class="row g-3 mb-3">
                {{-- Bahasa Indonesia --}}
                <div class="col-sm-6">
                  <div class="p-3 rounded-4 border text-center bg-light">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Bahasa Indonesia</div>
                    <div class="display-6 fw-bold text-dark">{{ $to1->nilai_bindo }}</div>
                    <div class="mt-2">
                      <span class="badge {{ $to1->badge_class_bindo }} px-3 py-1 fs-7 rounded-pill">
                        {{ $to1->ket_bindo ?: 'Belum Ada' }}
                      </span>
                    </div>
                  </div>
                </div>

                {{-- Matematika --}}
                <div class="col-sm-6">
                  <div class="p-3 rounded-4 border text-center bg-light">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Matematika</div>
                    <div class="display-6 fw-bold text-dark">{{ $to1->nilai_mtk }}</div>
                    <div class="mt-2">
                      <span class="badge {{ $to1->badge_class_mtk }} px-3 py-1 fs-7 rounded-pill">
                        {{ $to1->ket_mtk ?: 'Belum Ada' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Rekap Skor TO-1 - Warna Putih Jelas & Elegan --}}
            <div class="d-flex justify-content-between align-items-center p-3 rounded-4 shadow-sm text-white" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
              <div>
                <span class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Rata-rata Nilai:</span>
                <div class="fw-bolder fs-3 text-white lh-1 mt-1">{{ $to1->nilai_rata_rata }}</div>
              </div>
              <div class="text-end">
                <span class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Total Skor:</span>
                <div class="fw-bolder fs-3 text-white lh-1 mt-1">{{ $to1->nilai_total }}</div>
              </div>
            </div>
            @else
            <div class="text-center py-5 text-muted">
              <i class="fas fa-times-circle fa-3x mb-2 text-secondary opacity-50"></i>
              <p class="mb-0">Belum ada data nilai Try Out 1.</p>
            </div>
            @endif
          </div>
        </div>
      </div>

    </div>

    {{-- KETERANGAN SKALA PREDIKAT --}}
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0 text-dark">
          <i class="fas fa-info-circle text-info me-2"></i>Keterangan Skala Predikat Tes Kemampuan Akademik (TKA)
        </h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered mb-0 text-center">
            <thead class="table-light">
              <tr>
                <th>Rentang Nilai</th>
                <th>Predikat Kemampuan</th>
                <th>Deskripsi Capaian</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="fw-bold">95.00 – 100.00</td>
                <td><span class="badge bg-success px-3 py-1">ISTIMEWA</span></td>
                <td class="text-start">Penguasaan materi sangat mendalam, penalaran akademik sangat tinggi dan konsisten.</td>
              </tr>
              <tr>
                <td class="fw-bold">76.00 – 94.99</td>
                <td><span class="badge bg-primary px-3 py-1">BAIK</span></td>
                <td class="text-start">Memahami konsep dengan baik, mampu menyelesaikan persoalan akademik secara tepat.</td>
              </tr>
              <tr>
                <td class="fw-bold">50.00 – 75.99</td>
                <td><span class="badge bg-warning text-dark px-3 py-1">MEMADAI</span></td>
                <td class="text-start">Memahami konsep dasar, perlu bimbingan dan latihan lebih intensif pada soal penalaran.</td>
              </tr>
              <tr>
                <td class="fw-bold">&lt; 50.00</td>
                <td><span class="badge bg-danger px-3 py-1">KURANG</span></td>
                <td class="text-start">Membutuhkan remedial materi fundamental dan pendampingan khusus secara berkala.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    @endif
  </div>
</section>
@endsection
