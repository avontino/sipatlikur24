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

    <div class="card">
      <div class="row">
        <div class="col-md-12">
          <div class="panel">
            <div class="card-header">
              <h3 class="panel-title">Tambah Ijin Siswa</h3>
            </div>
          </div>
          <div class="card-body">
            <form action="/tambahijinsiswa/create" method="POST" enctype="multipart/form-data"> 
              {{csrf_field()}}

              @if(auth()->user()->role === 'siswa')
              <div class="form-group mb-3">
                <label class="form-label fw-bold">Nama Siswa</label>
                <input name="nama" value="{{auth()->user()->name}}" type="text" class="form-control" readonly>               
              </div>

              <div class="form-group mb-3">
                <label class="form-label fw-bold">Kelas</label>
                <input name="kelas" value="{{$siswa->kelas}}" type="text" class="form-control" readonly>
              </div>
              @else
              {{-- Guru Piket / Staff View --}}
              <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3">
                <i class="fas fa-info-circle fa-lg me-2"></i>
                <div>
                  <strong>Mode Guru Piket:</strong> Pilih kelas dan siswa yang meminta izin pulang saat jam sekolah. Izin akan otomatis diverifikasi & disetujui.
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Pilih Kelas <span class="text-danger">*</span></label>
                  <select id="selectKelasPage" class="form-select" required>
                    <option value="">-- Pilih Kelas Siswa --</option>
                    @if(isset($kelasList))
                      @foreach($kelasList as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                      @endforeach
                    @endif
                  </select>
                  <input type="hidden" name="kelas" id="inputKelasPage" required>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Pilih Siswa <span class="text-danger">*</span></label>
                  <select id="selectSiswaPage" class="form-select" disabled required>
                    <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                  </select>
                  <input type="hidden" name="nama" id="inputNamaPage" required>
                </div>
              </div>
              @endif

              <div class="form-group mb-3">
                <label class="form-label fw-bold">Jenis Izin <span class="text-danger">*</span></label>
                <select id="ijinSelect" name="ijin" class="form-select" required>
                    <option value="">-- Pilih Jenis Izin --</option>
                    <option value="Izin Pulang Saat Jam Sekolah (Sakit)" {{ auth()->user()->role !== 'siswa' ? 'selected' : '' }}>Izin Pulang Saat Jam Sekolah (Sakit)</option>
                    <option value="Izin Pulang Saat Jam Sekolah (Keperluan Keluarga / Mendesak)">Izin Pulang Saat Jam Sekolah (Keperluan Keluarga / Mendesak)</option>
                    <option value="Izin Meninggalkan Sekolah Sementara">Izin Meninggalkan Sekolah Sementara (Tugas/Dinas/Lomba)</option>
                    <option value="Izin Tidak Masuk Sekolah (Sakit / Izin Harian)">Izin Tidak Masuk Sekolah (Sakit / Izin Harian)</option>
                </select>
              </div>

              <div class="form-group mb-3">
                <label class="form-label fw-bold">Keterangan / Alasan Izin</label>
                <textarea name="keterangan" class="form-control" rows="2" placeholder="Tuliskan alasan izin / keterangan penjemput jika ada..."></textarea>
              </div>

              <div id="sisaIjin" class="alert alert-info mt-3" style="display: none;">
                <i class="fa fa-info-circle"></i> 
                <span id="sisaText"></span>
              </div>

              @if(auth()->user()->role === 'siswa')
              <div class="form-group mb-3" id="fileUploadGroup" style="display: none;">
                <label for="file" class="form-label fw-bold">Upload Foto / Surat Bukti Izin <span class="text-danger">* (Wajib Foto)</span></label>
                <input type="file" name="file" id="fileUploadInput" class="form-control" accept=".jpg, .jpeg, .png, .gif, .webp" required>
                <small class="text-muted">Harap lampirkan foto surat dokter / foto bukti surat izin (JPG, PNG).</small>
              </div>
              @else
              <div class="form-group mb-3">
                <label for="file" class="form-label fw-bold">Upload Foto / Surat Bukti Izin <span class="text-muted fw-normal">(Opsional untuk Guru Piket)</span></label>
                <input type="file" name="file" class="form-control" accept=".jpg, .jpeg, .png, .gif, .webp">
                <small class="text-muted">Boleh dikosongkan karena izin telah diverifikasi langsung oleh Guru Piket di sekolah.</small>
              </div>
              @endif

              <button type="submit" class="btn btn-primary fw-bold">
                <i class="fas fa-check-circle me-1"></i> {{ auth()->user()->role === 'siswa' ? 'Tambah Izin' : 'Simpan & Setujui Izin (Guru Piket)' }}
              </button>
            </form>
          </div>
        </div>
      </div> 
    </div>

    <div class="card">
      <div class="row">
        <div class="col-md-12">
          <div class="panel">
            <div class="card-header">
              <h3 class="panel-title">Data Ijin Siswa</h3>
            </div>
            <div class="card-body">
              </br>
              <table id="example3" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>NAMA</th>
                    <th>KELAS</th>
                    <th>JENIS IJIN</th>
                    <th>WALI KELAS</th>
                    <th>GURU PIKET</th>
                    <th>VERIFIKATOR</th>
                    <th>WAKTU IJIN</th>
                    <th>STATUS SURAT</th>
                    <th>FILE BUKTI</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($data_ijinsiswa as $ijinsiswa)
                  <tr>
                    <td>{{$ijinsiswa->nama}}</td> 
                    <td>{{$ijinsiswa->kelas}}</td>
                    <td>{{$ijinsiswa->ketijin}}</td>
                    
                    {{-- Status Wali Kelas --}}
                    @if(($ijinsiswa->ok_walikelas ?? $ijinsiswa->okbin ?? 'belum') == 'ok')
                    <td style="background-color: #32CD32" align="center" title="Disetujui Wali Kelas">
                      <span class="nav-icon fas fa-check-square text-white"></span></td>
                    @else
                    <td style="background-color: #ff0000" align="center" title="Belum Disetujui Wali Kelas">
                      <span class="nav-icon fas fa-minus-square text-white"></span></td>
                    @endif

                    {{-- Status Guru Piket --}}
                    @if(($ijinsiswa->ok_pembina ?? $ijinsiswa->oksis ?? 'belum') == 'ok')
                    <td style="background-color: #32CD32" align="center" title="Disetujui Guru Piket">
                      <span class="nav-icon fas fa-check-square text-white"></span></td>
                    @else
                    <td style="background-color: #ff0000" align="center" title="Belum Disetujui Guru Piket">
                      <span class="nav-icon fas fa-minus-square text-white"></span></td>
                    @endif

                    <td>{{$ijinsiswa->created_at ? $ijinsiswa->created_at->format('d M Y - H:i:s') : '-'}}</td>
                    @php
                      $verif = [];
                      if ($ijinsiswa->verifikator_piket) $verif[] = $ijinsiswa->verifikator_piket . ' (Guru Piket)';
                      if ($ijinsiswa->verifikator_walikelas) $verif[] = $ijinsiswa->verifikator_walikelas . ' (Wali Kelas)';
                    @endphp
                    <td>{{ count($verif) ? implode(', ', $verif) : '-' }}</td>
                    <td>
                      @php
                        // Check approval using new column names with fallback to old names
                        $piketOk     = ($ijinsiswa->ok_pembina ?? $ijinsiswa->oksis ?? 'belum') == 'ok';
                        $walikelasOk = ($ijinsiswa->ok_walikelas ?? $ijinsiswa->okbin ?? 'belum') == 'ok';

                        $displayStatus = 'Menunggu Verifikasi';
                        if ($ijinsiswa->filex == 'Surat Salah') {
                            $displayStatus = 'Surat Salah';
                        } elseif ($piketOk || $walikelasOk) {
                            // Either Guru Piket OR Wali Kelas approval is sufficient
                            $displayStatus = 'Surat Disetujui';
                        }
                      @endphp
                      @if($displayStatus == 'Surat Salah')
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#uploadUlangModal{{ $ijinsiswa->id }}">
                          Upload Ulang
                        </button>
                      @else
                        <span class="badge {{ $displayStatus == 'Surat Disetujui' ? 'bg-success' : 'bg-secondary' }}">
                          {{ $displayStatus }}
                        </span>
                      @endif
                    </td>
                    <td>
										@if($ijinsiswa->file_path !=null)	
                       <!-- Tombol untuk melihat file -->
                      <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#lihatFile{{ $ijinsiswa->id }}">Lihat</a>
                      @else
										<span class="text">Tidak Ada</span>
										@endif
										</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

    <!-- Modal Lihat File -->
    @foreach($data_ijinsiswa as $ijinsiswa)
<div class="modal fade" id="lihatFile{{ $ijinsiswa->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
			<h4 class="modal-title" id="myModalLabel">File Ijin {{ $ijinsiswa->nama }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                
            </div>
            <div class="modal-body">
                <img src="{{ $ijinsiswa->file_path }}" class="file-image" alt="File Bukti">
            </div>
        </div>
    </div>
</div>
@endforeach


<!-- Modal Upload Ulang -->
@foreach($data_ijinsiswa as $ijinsiswa)
<div class="modal fade" id="uploadUlangModal{{ $ijinsiswa->id }}" tabindex="-1" role="dialog" aria-labelledby="uploadUlangLabel{{ $ijinsiswa->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadUlangLabel{{ $ijinsiswa->id }}">Upload Ulang File Ijin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                
                <form action="/tambahijinsiswa/uploadulang/{{ $ijinsiswa->id }}" method="POST" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="form-group">
                      
                        <label>Nama Siswa</label>
                        <input name="nama" value="{{ $ijinsiswa->nama }}" type="text" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <input name="kelas" value="{{ $ijinsiswa->kelas }}" type="text" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Jenis Ijin</label>
                        <input name="ijin" value="{{ $ijinsiswa->ketijin }}" type="text" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="file">Upload File (Gambar JPG/JPEG/PNG)</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach









<script>
  document.addEventListener('DOMContentLoaded', function () {
    var selectElement = document.getElementById('ijinSelect');
    var sisaText = document.getElementById('sisaText');
    var sisaIjin = document.getElementById('sisaIjin');
    var fileUploadGroup = document.getElementById('fileUploadGroup');
	
    if (selectElement && fileUploadGroup) {
        selectElement.addEventListener('change', function () {
            var selectedOption = this.value;
            if (sisaIjin) sisaIjin.style.display = 'none';

            if (selectedOption !== '') {
                fileUploadGroup.style.display = 'block';
            } else {
                fileUploadGroup.style.display = 'none';
            }
        });
    }

    // Dynamic student loading for Guru Piket
    var selectKelasPage = document.getElementById('selectKelasPage');
    var selectSiswaPage = document.getElementById('selectSiswaPage');
    var inputKelasPage  = document.getElementById('inputKelasPage');
    var inputNamaPage   = document.getElementById('inputNamaPage');

    if (selectKelasPage && selectSiswaPage) {
        selectKelasPage.addEventListener('change', function() {
            var kelas = this.value;
            inputKelasPage.value = kelas;
            selectSiswaPage.innerHTML = '<option value="">Memuat daftar siswa...</option>';
            selectSiswaPage.disabled = true;
            inputNamaPage.value = '';

            if (!kelas) {
                selectSiswaPage.innerHTML = '<option value="">-- Pilih Kelas Terlebih Dahulu --</option>';
                return;
            }

            fetch('/ijinsiswa/get-siswa-by-kelas/' + encodeURIComponent(kelas))
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        var options = '<option value="">-- Pilih Nama Siswa --</option>';
                        data.forEach(function(s) {
                            var nisText = s.nis ? ' (' + s.nis + ')' : '';
                            options += '<option value="' + s.nama + '">' + s.nama + nisText + '</option>';
                        });
                        selectSiswaPage.innerHTML = options;
                        selectSiswaPage.disabled = false;
                    } else {
                        selectSiswaPage.innerHTML = '<option value="">(Tidak ada data siswa di kelas ini)</option>';
                    }
                })
                .catch(function(err) {
                    console.error('Error fetching students:', err);
                    selectSiswaPage.innerHTML = '<option value="">Gagal memuat siswa</option>';
                });
        });

        selectSiswaPage.addEventListener('change', function() {
            inputNamaPage.value = this.value;
        });
    }
});
</script>












<style>
    .modal-dialog {
        width: 50%; /* Atur lebar modal menjadi 90% dari lebar layar */
        max-width: 100%; /* Pastikan modal tidak melebihi lebar layar */
    }

.modal-content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.modal-body {
    flex: 1;
    overflow: auto;
    text-align: center;
}

.file-image {
        max-width: 70%; /* Gambar menyesuaikan dengan lebar modal */
        max-height: 70vh; /* Gambar menyesuaikan dengan tinggi viewport */
        width: auto;
        height: auto;
    }
</style>
@endsection
