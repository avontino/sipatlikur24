@extends('layouts.master')

@section('content')
<section class="content pt-3">
    <div class="container-fluid">
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <i class="fa fa-check-circle"></i> 
            {{ session('sukses') }}
        </div>
        @endif
        <div class="card shadow-sm border-0">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="card-header bg-light py-3">
                            <h3 class="fw-bold m-0" style="color: #004d1a;"><i class="fas fa-clipboard-check me-2"></i>Data Izin Siswa SMP NEGERI 24 Malang</h3>
                        </div>
                        <div class="card-body">
             
    
                            @php
                                $canAddIjin = auth()->user()->role !== 'siswa' && auth()->user()->role !== 'satpam';
                            @endphp
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
                                <div>
                                    @if($canAddIjin)
                                    <button type="button" class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPiket">
                                        <i class="fas fa-plus-circle me-1"></i> Tambah Izin Siswa (Guru Piket)
                                    </button>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    @if(auth()->user()->role == 'admin' || auth()->user()->hasRole('kurikulum'))
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#exim">
                                        <i class="fas fa-file-excel me-1"></i> Rekap Ijin Siswa
                                    </button>
                                    @endif
                                </div>
                            </div>

							@if(auth()->user()->role == 'satpam')
                            <!-- Frame Webcam -->
                            <video id="video" width="280" height="280" autoplay></video>
							<canvas id="canvas" width="280" height="280" style="display:none;"></canvas>
							<div id="result"></div>
							@endif

                            <div class="table-responsive">
                            <table id="qrcodecam" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                    @if(auth()->user()->role != 'siswa')
                                        <th>NAMA</th>
                                        <th>KELAS</th>
                                    @endif
                                        <th>KET IJIN</th>
                                        @if((auth()->user()->hasRole('kepala') || auth()->user()->hasRole('admin')) && auth()->user()->role !== 'siswa')
                                        <th>KEPALA SEKOLAH</th>
                                        @endif
                                        <th>GURU PIKET</th>
                                        @if(auth()->user()->role !== 'siswa' && !in_array(auth()->user()->role, ['satpam']))
                                        <th>STATUS GURU PIKET</th>
                                        @endif
                                        <th>VERIFIKATOR PIKET</th>
                                        <th>WALI KELAS</th>
                                        @if((auth()->user()->walikelas_kelas || auth()->user()->hasRole('admin')) && auth()->user()->role !== 'siswa')
                                        <th>STATUS WALI KELAS</th>
                                        @endif
                                        <th>VERIFIKATOR WALIKELAS</th>
                                        <th>WAKTU IJIN</th>
                                        <th>LIHAT SURAT</th>
                                        <th>STATUS SURAT</th>
                                        @if(auth()->user()->role !== 'siswa')
                                        <th>AKSI</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @php
        $user = auth()->user();
        $isSiswa = $user->role == 'siswa';
        $isGuruOrStaff = !$isSiswa && $user->role !== 'satpam';
        $isWaliKelas = $user->walikelas_kelas || $user->hasRole('walikelas');
        $isAdmin = $user->hasRole('admin') || $user->role == 'admin';
        $isKepala = $user->hasRole('kepala') || $user->role == 'kepala';

        $cols = [];
        if (!$isSiswa) { $cols[] = 'nama'; $cols[] = 'kelas'; }
        $cols[] = 'ketijin';
        // Kepala Sekolah action
        if (($isKepala || $isAdmin) && !$isSiswa) { $cols[] = 'kepala_aksi'; }
        // Guru Piket status (all non-siswa see status; all guru/staff get action button)
        $cols[] = 'ok_pembina_status';
        if ($isGuruOrStaff) { $cols[] = 'pembina_aksi'; }
        $cols[] = 'verifikator_piket_name';
        // Wali Kelas status
        $cols[] = 'ok_walikelas_status';
        if (($isWaliKelas || $isAdmin) && !$isSiswa) { $cols[] = 'walikelas_aksi'; }
        $cols[] = 'verifikator_walikelas_name';
        // Time & document
        $cols[] = 'created_at';
        $cols[] = 'lihat_surat';
        $cols[] = 'filex';
        if (!$isSiswa) { $cols[] = 'aksi'; }
    @endphp

    if ($.fn.DataTable.isDataTable('#qrcodecam')) {
        $('#qrcodecam').DataTable().destroy();
    }

    var ijinTable = $('#qrcodecam').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: "{{ url('/ijinsiswa') }}",
            data: function(d) {
                d.view = "{{ request('view') }}";
            }
        },
        columns: [
            @foreach($cols as $col)
            { data: '{{ $col }}', name: '{{ $col }}', orderable: {{ in_array($col, ['nama', 'kelas', 'ketijin', 'created_at', 'filex']) ? 'true' : 'false' }}, searchable: true },
            @endforeach
        ],
        order: [],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat data...</span></div>',
            search: "Cari Ijin Siswa:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data ijin",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Data ijin tidak ditemukan"
        },
        columnDefs: [
            { 
                targets: '_all', 
                defaultContent: '-', 
                render: function(data, type, row) { 
                    if (type === 'display') return data || '-';
                    return data || '-'; 
                } 
            }
        ]
    });

    @if(auth()->user()->role == 'satpam')
    // QR Scanner integration with server-side DataTables
    let qrCodeResult = '';
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    if (video && canvas) {
        const context = canvas.getContext('2d');
        const resultDiv = document.getElementById('result');

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(stream => {
                video.srcObject = stream;
                video.setAttribute("playsinline", true);
                video.play();
                video.addEventListener('loadedmetadata', () => {
                    requestAnimationFrame(scanQRCode);
                });
            })
            .catch(error => { console.error("Kamera error: ", error); });

        function scanQRCode() {
            if (video.videoWidth > 0 && video.videoHeight > 0) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, canvas.width, canvas.height);
                if (code) {
                    qrCodeResult = code.data.trim();
                    resultDiv.innerHTML = `Isi QR Code: ${qrCodeResult}`;
                    ijinTable.search(qrCodeResult).draw();
                } else {
                    resultDiv.innerHTML = 'QR code tidak ditemukan.';
                }
            }
            requestAnimationFrame(scanQRCode);
        }
    }
    @endif
});
</script>

    <!-- Modal Tambah Izin Siswa oleh Guru Piket -->
    <div class="modal fade" id="modalTambahPiket" tabindex="-1" aria-labelledby="modalTambahPiketLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="modalTambahPiketLabel">
                        <i class="fas fa-user-clock me-2"></i> Tambah Izin Siswa (Guru Piket)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/tambahijinsiswa/create" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body text-start px-4 py-3" style="text-align: left !important;">
                        <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3">
                            <i class="fas fa-info-circle fa-lg me-2"></i>
                            <div>
                                Guru Piket dapat menambahkan izin bagi siswa yang meminta izin pulang saat jam sekolah berlangsung. Izin yang diinput akan <strong>langsung diverifikasi & disetujui</strong> oleh Guru Piket.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">1. Pilih Kelas <span class="text-danger">*</span></label>
                                <select id="piketSelectKelas" class="form-select" required>
                                    <option value="">-- Pilih Kelas Siswa --</option>
                                    @if(isset($kelasList))
                                        @foreach($kelasList as $k)
                                            <option value="{{ $k }}">{{ $k }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <input type="hidden" name="kelas" id="piketInputKelas" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">2. Pilih Siswa <span class="text-danger">*</span></label>
                                <select id="piketSelectSiswa" class="form-select" disabled required>
                                    <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                                </select>
                                <input type="hidden" name="nama" id="piketInputNama" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">3. Jenis Izin <span class="text-danger">*</span></label>
                            <select name="ijin" class="form-select" required>
                                <option value="Izin Pulang Saat Jam Sekolah (Sakit)" selected>Izin Pulang Saat Jam Sekolah (Sakit)</option>
                                <option value="Izin Pulang Saat Jam Sekolah (Keperluan Keluarga / Mendesak)">Izin Pulang Saat Jam Sekolah (Keperluan Keluarga / Mendesak)</option>
                                <option value="Izin Meninggalkan Sekolah Sementara (Tugas / Lomba / Dispensasi)">Izin Meninggalkan Sekolah Sementara (Tugas / Lomba / Dispensasi)</option>
                                <option value="Izin Tidak Masuk Sekolah (Sakit / Izin Harian)">Izin Tidak Masuk Sekolah (Sakit / Izin Harian)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">4. Keterangan / Alasan Izin</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Mengalami sakit pusing / demam, telah istirahat di UKS dan dijemput orang tua."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">5. Foto / Surat Bukti <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="file" name="file" class="form-control" accept=".jpg, .jpeg, .png, .webp">
                            <div class="form-text text-muted">Bisa melampirkan foto siswa / surat orang tua / surat dokter jika ada. Boleh dikosongkan karena izin telah diverifikasi langsung oleh Guru Piket di sekolah.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success fw-bold">
                            <i class="fas fa-check-circle me-1"></i> Simpan & Setujui Izin Pulang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const piketSelectKelas = document.getElementById('piketSelectKelas');
        const piketSelectSiswa = document.getElementById('piketSelectSiswa');
        const piketInputKelas  = document.getElementById('piketInputKelas');
        const piketInputNama   = document.getElementById('piketInputNama');

        if (piketSelectKelas && piketSelectSiswa) {
            piketSelectKelas.addEventListener('change', function() {
                const kelas = this.value;
                piketInputKelas.value = kelas;
                piketSelectSiswa.innerHTML = '<option value="">Memuat daftar siswa...</option>';
                piketSelectSiswa.disabled = true;
                piketInputNama.value = '';

                if (!kelas) {
                    piketSelectSiswa.innerHTML = '<option value="">-- Pilih Kelas Terlebih Dahulu --</option>';
                    return;
                }

                fetch('/ijinsiswa/get-siswa-by-kelas/' + encodeURIComponent(kelas))
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            let options = '<option value="">-- Pilih Nama Siswa --</option>';
                            data.forEach(s => {
                                const nisText = s.nis ? ' (' + s.nis + ')' : '';
                                options += `<option value="${s.nama}">${s.nama}${nisText}</option>`;
                            });
                            piketSelectSiswa.innerHTML = options;
                            piketSelectSiswa.disabled = false;
                        } else {
                            piketSelectSiswa.innerHTML = '<option value="">(Tidak ada data siswa di kelas ini)</option>';
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching students:', err);
                        piketSelectSiswa.innerHTML = '<option value="">Gagal memuat siswa</option>';
                    });
            });

            piketSelectSiswa.addEventListener('change', function() {
                piketInputNama.value = this.value;
            });
        }
    });
    </script>

    <!-- Modal Lihat File Single Dynamic -->
    <div class="modal fade" id="lihatFileSingleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="lihatFileModalTitle">File Ijin</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="lihatFileModalImg" src="" class="file-image img-fluid" alt="File Bukti">
                </div>
            </div>
        </div>
    </div>

    <script>
    function showLihatFileModal(filePath, nama) {
        document.getElementById('lihatFileModalTitle').innerText = 'File Ijin ' + nama;
        document.getElementById('lihatFileModalImg').src = filePath;
        var myModal = new bootstrap.Modal(document.getElementById('lihatFileSingleModal'));
        myModal.show();
    }
    </script>

<!-- Modal Cek In -->
<div class="modal fade" id="modalCekIn" tabindex="-1" role="dialog" aria-labelledby="modalCekInLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCekInLabel">Cek In</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <video id="videoCekIn" width="50%" height="50%" autoplay></video>
				
                <canvas id="canvasCekIn" style="display: none;"></canvas>
                <div id="photoCekInResult"></div>
            </div>
            <div class="modal-footer">
			<input type="hidden" name="jurnalid" id="jurnalid" value="">
                <button id="snapCekIn" class="btn btn-primary">Ambil Foto</button>
                <form id="uploadPhotoForm" action="" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="file_bukti" id="file_bukti">
                    <button type="submit" class="btn btn-success">Selesai Cek In</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Export/Import -->
    <div class="modal fade" id="exim" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Export Data</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="/ijinsiswa/export" method="GET">
                    @csrf
                    <div class="form-group">
                        <label for="start_date">Dari Tanggal</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                    <button type="submit" class="btn btn-success mt-2">Export</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="/js/jsQR.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const videoCekIn = document.getElementById('videoCekIn');
    const canvasCekIn = document.getElementById('canvasCekIn');
    if (!videoCekIn || !canvasCekIn) return;
    const contextCekIn = canvasCekIn.getContext('2d');
    const snapCekIn = document.getElementById('snapCekIn');
    const photoCekInResult = document.getElementById('photoCekInResult');
    const modalCekIn = $('#modalCekIn');
    const uploadPhotoForm = document.getElementById('uploadPhotoForm');

    modalCekIn.on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget);
		var jurnalid = button.data('myid');
		var modal = $(this)
	    modal.find('.modal-body #jurnalid').val(jurnalid)
        uploadPhotoForm.action = `/ijinsiswa/${jurnalid}/cekin`;

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(stream => {
                videoCekIn.srcObject = stream;
                videoCekIn.play();
            })
            .catch(error => {
                console.error("Kesalahan mengakses kamera: ", error);
            });
    });

    snapCekIn.addEventListener('click', function() {
        canvasCekIn.width = videoCekIn.videoWidth;
        canvasCekIn.height = videoCekIn.videoHeight;
        contextCekIn.drawImage(videoCekIn, 0, 0, canvasCekIn.width, canvasCekIn.height);
        const imageData = canvasCekIn.toDataURL('image/png');
        photoCekInResult.innerHTML = `<img src="${imageData}" width="50%" height="50%"/>`;
        document.getElementById('file_bukti').value = imageData;
    });

    modalCekIn.on('hidden.bs.modal', function () {
        let stream = videoCekIn.srcObject;
        if (stream) {
            let tracks = stream.getTracks();
            tracks.forEach(track => { track.stop(); });
            videoCekIn.srcObject = null;
        }
    });
});
</script>

<style>
    #modalCekIn .modal-dialog,
    #lihatFileSingleModal .modal-dialog {
        max-width: 600px;
    }

    #modalCekIn .modal-body,
    #lihatFileSingleModal .modal-body {
        text-align: center;
    }

    .file-image {
        max-width: 100%;
        max-height: 70vh;
        width: auto;
        height: auto;
    }
</style>

@endsection
