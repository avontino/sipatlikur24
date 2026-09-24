@extends('layouts.master')

@section('content')
<section class="content-header pt-3">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div>
        <h4 class="fw-bold mb-1" style="color: #004d1a;">
          <i class="fas fa-file-signature me-2"></i>Formulir Permohonan Izin Guru & Pegawai
        </h4>
        <p class="text-muted small mb-0">
          <i class="fas fa-info-circle me-1 text-primary"></i>Abaikan formulir ini jika Anda hadir di sekolah seperti biasa.
        </p>
      </div>
      <div class="d-flex gap-2">
        <a href="/ijin/live" class="btn btn-sm btn-info text-white shadow-sm fw-bold">
          <i class="fas fa-broadcast-tower me-1"></i> Live Monitoring Hari Ini
        </a>
        <a href="/ijin" class="btn btn-sm btn-outline-secondary shadow-sm">
          <i class="fas fa-list me-1"></i> Daftar Riwayat Izin
        </a>
      </div>
    </div>

    @if(session('sukses'))
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fa fa-check-circle me-2"></i> {{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> Mohon periksa kembali formulir Anda:
        <ul class="mb-0 mt-1">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
      <div class="card-header bg-light py-3 border-bottom">
        <h6 class="fw-bold m-0 text-dark">
          <i class="fas fa-edit me-2 text-success"></i>Isi Data Keterangan Izin
        </h6>
      </div>
      <div class="card-body p-4">
        <form action="/ijin/create" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row g-3">
            <!-- Tanggal Izin -->
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold text-dark">Tanggal Izin <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="far fa-calendar-alt text-muted"></i></span>
                <input name="tglmasuk" type="date" class="form-control" id="tglmasuk" value="{{ old('tglmasuk', date('Y-m-d')) }}" required>
              </div>
            </div>

            <!-- Nama Guru / Pegawai -->
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold text-dark">Nama Guru / Pegawai <span class="text-danger">*</span></label>
              @php
                $canChooseOtherTeacher = in_array(auth()->user()->role, ['admin', 'kurikulum', 'pembina', 'kesiswaan', 'kepala']) 
                  || auth()->user()->hasRole('admin') 
                  || auth()->user()->hasRole('kurikulum');
              @endphp
              @if($canChooseOtherTeacher)
                <!-- Admin/Kurikulum/Piket dapat memilih guru lain jika mewakili input -->
                <select name="guru_id" class="form-control select2" id="selectGuru" onchange="updateGuruHidden(this)">
                  @foreach(\App\Models\User::whereIn('role', ['guru', 'walikelas', 'tendik', 'kurikulum', 'kesiswaan', 'kepala', 'admin'])->orderBy('name', 'asc')->get() as $u)
                    <option value="{{ $u->id }}" data-name="{{ $u->name }}" {{ $u->id == auth()->id() ? 'selected' : '' }}>
                      {{ $u->name }} ({{ strtoupper($u->role) }})
                    </option>
                  @endforeach
                </select>
                <input type="hidden" name="guru" value="{{ auth()->user()->name }}" id="guruNameHidden">
                <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1 text-primary"></i>Sebagai Kurikulum/Admin, Anda dapat memilih guru lain yang mengajukan izin.</small>
              @else
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="far fa-user text-muted"></i></span>
                  <input name="guru" value="{{ auth()->user()->name }}" type="text" class="form-control bg-light" readonly>
                </div>
              @endif
            </div>

            <!-- Kategori Izin (Sesuai Kebutuhan Riil GAS) -->
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold text-dark">Kategori Izin <span class="text-danger">*</span></label>
              <select name="sia" class="form-control form-select" id="selectKategori" onchange="toggleKategoriFields()" required>
                <option value="" disabled selected>-- Pilih Kategori Izin --</option>
                <option value="Tugas Kedinasan" {{ old('sia') == 'Tugas Kedinasan' ? 'selected' : '' }}>
                  Tugas Kedinasan (Dinas Luar / Lomba / MGMP / Diklat)
                </option>
                <option value="Sakit" {{ old('sia') == 'Sakit' ? 'selected' : '' }}>
                  Sakit
                </option>
                <option value="Izin Terlambat" {{ old('sia') == 'Izin Terlambat' ? 'selected' : '' }}>
                  Izin Terlambat (Datang Lebih Lambat)
                </option>
                <option value="Izin Keluar Jam Dinas" {{ old('sia') == 'Izin Keluar Jam Dinas' ? 'selected' : '' }}>
                  Izin Keluar Saat Jam Dinas (Keperluan Penting & Kembali ke Sekolah)
                </option>
                <option value="Izin Pulang Sebelum Waktunya" {{ old('sia') == 'Izin Pulang Sebelum Waktunya' ? 'selected' : '' }}>
                  Izin Pulang Sebelum Waktunya (Pulang Lebih Awal)
                </option>
                <option value="Keperluan Pribadi" {{ old('sia') == 'Keperluan Pribadi' ? 'selected' : '' }}>
                  Keperluan Pribadi / Keluarga
                </option>
                <option value="Cuti" {{ old('sia') == 'Cuti' ? 'selected' : '' }}>
                  Cuti (Tahunan / Melahirkan / Alasan Penting)
                </option>
              </select>
            </div>

            <!-- Mata Pelajaran yang Diampu -->
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold text-dark">Mata Pelajaran (Opsional)</label>
              <select name="mapel" class="form-control form-select">
                <option value="-">- Bukan Guru Mapel / Umum -</option>
                @foreach($ma_pel as $mapel)
                  <option value="{{ $mapel->mapel }}">{{ $mapel->mapel }}</option>
                @endforeach
              </select>
            </div>

            <!-- BIDANG DINAMIS: Izin Keluar Jam Dinas (Jam Keluar & Estimasi Kembali) -->
            <div class="col-md-12 mb-3" id="group_izin_keluar" style="display: none;">
              <div class="p-3 bg-light border border-warning rounded">
                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-clock text-warning me-2"></i>Rentang Waktu Izin Keluar</h6>
                <div class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Jam Keluar Sekolah (HH:mm) <span class="text-danger">*</span></label>
                    <input name="jam_keluar" type="time" class="form-control" id="jam_keluar" value="{{ old('jam_keluar') }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Estimasi Jam Kembali ke Sekolah (HH:mm) <span class="text-danger">*</span></label>
                    <input name="jam_kembali" type="time" class="form-control" id="jam_kembali" value="{{ old('jam_kembali') }}">
                  </div>
                </div>
                <small class="text-muted mt-1 d-block">
                  * Catatan: Guru piket dan pimpinan dapat memantau estimasi jam kedatangan kembali Anda di sekolah.
                </small>
              </div>
            </div>

            <!-- BIDANG DINAMIS: Izin Terlambat -->
            <div class="col-md-6 mb-3" id="group_jam_terlambat" style="display: none;">
              <label class="form-label fw-semibold text-dark">Estimasi Jam Tiba di Sekolah (HH:mm) <span class="text-danger">*</span></label>
              <input name="jam_terlambat" type="time" class="form-control" id="jam_terlambat" value="{{ old('jam_terlambat') }}">
            </div>

            <!-- BIDANG DINAMIS: Izin Pulang Lebih Awal -->
            <div class="col-md-6 mb-3" id="group_jam_pulang_cepat" style="display: none;">
              <label class="form-label fw-semibold text-dark">Jam Kepulangan (HH:mm) <span class="text-danger">*</span></label>
              <input name="jam_pulang_cepat" type="time" class="form-control" id="jam_pulang_cepat" value="{{ old('jam_pulang_cepat') }}" onchange="document.getElementById('jam_keluar').value = this.value">
            </div>

            <!-- Jumlah Hari (Jika Cuti / Sakit / Tugas Dinas Panjang) -->
            <div class="col-md-6 mb-3" id="group_jumlah_hari">
              <label class="form-label fw-semibold text-dark">Jumlah Hari Izin</label>
              <div class="input-group">
                <input name="jumlah" type="number" min="1" max="30" class="form-control" id="jumlah" value="{{ old('jumlah', 1) }}">
                <span class="input-group-text bg-light">Hari</span>
              </div>
            </div>

            <!-- Keterangan Detail -->
            <div class="col-md-12 mb-3">
              <label class="form-label fw-semibold text-dark">Keterangan / Alasan Izin <span class="text-danger">*</span></label>
              <textarea name="ket" class="form-control" rows="3" placeholder="Tuliskan keterangan detail keperluan/kondisi izin Anda..." required>{{ old('ket') }}</textarea>
            </div>

            <!-- Bukti Fisik / Surat Tugas / Surat Dokter -->
            <div class="col-md-12 mb-3">
              <label class="form-label fw-semibold text-dark">
                Lampiran Dokumen / Bukti Foto / Surat Tugas / Surat Dokter <small class="text-muted">(Opsional, PDF/JPG/PNG maks. 2MB)</small>
              </label>
              <input name="attachment" type="file" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
            <a href="/ijin/live" class="btn btn-outline-secondary px-4">Batal</a>
            <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm" style="background-color: #00a884; border-color: #00a884;">
              <i class="fas fa-paper-plane me-1"></i> Kirim Permohonan Izin
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
  function toggleKategoriFields() {
    const kat = document.getElementById('selectKategori').value;
    const groupKeluar = document.getElementById('group_izin_keluar');
    const groupTerlambat = document.getElementById('group_jam_terlambat');
    const groupPulang = document.getElementById('group_jam_pulang_cepat');
    const groupJumlah = document.getElementById('group_jumlah_hari');

    // Sembunyikan semua bidang khusus terlebih dahulu
    groupKeluar.style.display = 'none';
    groupTerlambat.style.display = 'none';
    groupPulang.style.display = 'none';
    groupJumlah.style.display = 'block';

    if (kat === 'Izin Keluar Jam Dinas') {
      groupKeluar.style.display = 'block';
      groupJumlah.style.display = 'none';
    } else if (kat === 'Izin Terlambat') {
      groupTerlambat.style.display = 'block';
      groupJumlah.style.display = 'none';
    } else if (kat === 'Izin Pulang Sebelum Waktunya') {
      groupPulang.style.display = 'block';
      groupJumlah.style.display = 'none';
    }
  }

  function updateGuruHidden(sel) {
    if (!sel) return;
    var opt = sel.options[sel.selectedIndex];
    if (opt) {
      var name = opt.getAttribute('data-name') || opt.text.replace(/\s*\(.*\)$/, '').trim();
      var hidden = document.getElementById('guruNameHidden');
      if (hidden) {
        hidden.value = name;
      }
    }
  }

  // Trigger saat pertama kali load (misal redirect back with error)
  document.addEventListener('DOMContentLoaded', function() {
    toggleKategoriFields();
    var sel = document.getElementById('selectGuru');
    if (sel) {
      updateGuruHidden(sel);
    }
  });
</script>
@endsection