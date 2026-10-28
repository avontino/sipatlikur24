@extends('layouts.master')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark fw-bold">
          <i class="fas fa-graduation-cap text-warning me-2"></i>Nilai Tes Kemampuan Akademik (TKA)
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Nilai TKA</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('gagal'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-1"></i> {{ session('gagal') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(!$isKelas9)
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <div class="mb-3">
          <i class="fas fa-info-circle fa-4x text-info"></i>
        </div>
        <h4 class="fw-bold">Menu Khusus Siswa Kelas 9</h4>
        <p class="text-muted">
          Mohon maaf, menu Tes Kemampuan Akademik (TKA) saat ini dikhususkan bagi siswa Kelas 9 (IX) sebagai persiapan asesmen dan ujian kelulusan.
        </p>
        <a href="/dashboard" class="btn btn-primary mt-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
      </div>
    </div>
    @elseif($tkaList->isEmpty())
    <div class="card border-0 shadow-sm">
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
    <div class="card shadow-sm border-0 mb-4 bg-gradient-navy text-white" style="background: linear-gradient(135deg, #1e3a8a, #0f172a);">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-md-8">
            <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold text-uppercase">
              <i class="fas fa-award me-1"></i> Siswa Kelas 9 • SMP Negeri 24 Malang
            </span>
            <h2 class="fw-bold mb-1 text-white">{{ $info->nama }}</h2>
            <div class="d-flex flex-wrap gap-3 mt-2 text-white-50 fs-6">
              <div><i class="fas fa-id-card me-1 text-warning"></i> NIS: <strong class="text-white">{{ $info->nis }}</strong></div>
              @if($info->nisn)
              <div><i class="fas fa-fingerprint me-1 text-warning"></i> NISN: <strong class="text-white">{{ $info->nisn }}</strong></div>
              @endif
              <div><i class="fas fa-chalkboard-teacher me-1 text-warning"></i> Kelas: <strong class="text-white">{{ $info->kelas }}</strong></div>
              @if($info->wali_kelas)
              <div><i class="fas fa-user-tie me-1 text-warning"></i> Wali Kelas: <strong class="text-white">{{ $info->wali_kelas }}</strong></div>
              @endif
            </div>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            @if($diagnostik && $to1)
            <a href="/tka/rekap/{{ $info->nis }}/pdf" class="btn btn-warning btn-lg fw-bold shadow">
              <i class="fas fa-file-pdf me-1"></i> Download Rekap Lengkap PDF
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
        <div class="card h-100 shadow-sm border-0">
          <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <h5 class="fw-bold mb-0 text-primary">
              <i class="fas fa-clipboard-check me-2"></i>1. Tes Diagnostik TKA
            </h5>
            @if($diagnostik)
            <a href="/tka/{{ $diagnostik->id }}/pdf" class="btn btn-sm btn-outline-danger fw-bold">
              <i class="fas fa-file-pdf me-1"></i> Unduh PDF
            </a>
            @endif
          </div>
          <div class="card-body">
            @if($diagnostik)
            <p class="text-muted small mb-3">
              Hasil asesmen diagnostik awal kemampuan literasi (Bahasa Indonesia) dan numerasi (Matematika).
            </p>

            <div class="row g-3 mb-3">
              {{-- Bahasa Indonesia --}}
              <div class="col-sm-6">
                <div class="p-3 rounded border text-center bg-light">
                  <div class="text-muted small fw-bold text-uppercase mb-1">Bahasa Indonesia</div>
                  <div class="display-6 fw-bold text-dark">{{ $diagnostik->nilai_bindo }}</div>
                  <div class="mt-2">
                    <span class="badge {{ $diagnostik->badge_class_bindo }} px-3 py-1 fs-7">
                      {{ $diagnostik->ket_bindo ?: 'Belum Ada' }}
                    </span>
                  </div>
                </div>
              </div>

              {{-- Matematika --}}
              <div class="col-sm-6">
                <div class="p-3 rounded border text-center bg-light">
                  <div class="text-muted small fw-bold text-uppercase mb-1">Matematika</div>
                  <div class="display-6 fw-bold text-dark">{{ $diagnostik->nilai_mtk }}</div>
                  <div class="mt-2">
                    <span class="badge {{ $diagnostik->badge_class_mtk }} px-3 py-1 fs-7">
                      {{ $diagnostik->ket_mtk ?: 'Belum Ada' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between bg-primary bg-opacity-10 p-3 rounded">
              <div>
                <span class="text-muted small">Rata-rata Nilai:</span>
                <div class="fw-bold fs-5 text-primary">{{ $diagnostik->nilai_rata_rata }}</div>
              </div>
              <div class="text-end">
                <span class="text-muted small">Total Skor:</span>
                <div class="fw-bold fs-5 text-dark">{{ $diagnostik->nilai_total }}</div>
              </div>
            </div>
            @else
            <div class="text-center py-4 text-muted">
              <i class="fas fa-times-circle fa-2x mb-2 text-secondary"></i>
              <p>Belum ada data nilai Tes Diagnostik.</p>
            </div>
            @endif
          </div>
        </div>
      </div>

      {{-- 2. TRY OUT 1 (TO-1) --}}
      <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <h5 class="fw-bold mb-0 text-success">
              <i class="fas fa-pencil-alt me-2"></i>2. Try Out 1 (TO-1) TKA
            </h5>
            @if($to1)
            <a href="/tka/{{ $to1->id }}/pdf" class="btn btn-sm btn-outline-danger fw-bold">
              <i class="fas fa-file-pdf me-1"></i> Unduh PDF
            </a>
            @endif
          </div>
          <div class="card-body">
            @if($to1)
            <p class="text-muted small mb-3">
              Hasil simulasi ujian Try Out 1 sebagai tolak ukur evaluasi setelah pendalaman materi.
            </p>

            <div class="row g-3 mb-3">
              {{-- Bahasa Indonesia --}}
              <div class="col-sm-6">
                <div class="p-3 rounded border text-center bg-light">
                  <div class="text-muted small fw-bold text-uppercase mb-1">Bahasa Indonesia</div>
                  <div class="display-6 fw-bold text-dark">{{ $to1->nilai_bindo }}</div>
                  <div class="mt-2">
                    <span class="badge {{ $to1->badge_class_bindo }} px-3 py-1 fs-7">
                      {{ $to1->ket_bindo ?: 'Belum Ada' }}
                    </span>
                  </div>
                </div>
              </div>

              {{-- Matematika --}}
              <div class="col-sm-6">
                <div class="p-3 rounded border text-center bg-light">
                  <div class="text-muted small fw-bold text-uppercase mb-1">Matematika</div>
                  <div class="display-6 fw-bold text-dark">{{ $to1->nilai_mtk }}</div>
                  <div class="mt-2">
                    <span class="badge {{ $to1->badge_class_mtk }} px-3 py-1 fs-7">
                      {{ $to1->ket_mtk ?: 'Belum Ada' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between bg-success bg-opacity-10 p-3 rounded">
              <div>
                <span class="text-muted small">Rata-rata Nilai:</span>
                <div class="fw-bold fs-5 text-success">{{ $to1->nilai_rata_rata }}</div>
              </div>
              <div class="text-end">
                <span class="text-muted small">Total Skor:</span>
                <div class="fw-bold fs-5 text-dark">{{ $to1->nilai_total }}</div>
              </div>
            </div>
            @else
            <div class="text-center py-4 text-muted">
              <i class="fas fa-times-circle fa-2x mb-2 text-secondary"></i>
              <p>Belum ada data nilai Try Out 1.</p>
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
