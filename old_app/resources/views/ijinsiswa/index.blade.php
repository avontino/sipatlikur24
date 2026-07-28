@extends('layouts.master')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <i class="fa fa-check-circle"></i> 
            {{ session('sukses') }}
        </div>
        @endif
        <div class="card">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="card-header">
                            <h3 class="panel-title">Data Ijin Siswa SMAN TARUNA NALA</h3>
                        </div>
                        <div class="card-body">
                 
    
                            @if(auth()->user()->role == 'admin')
                            <form class="form-inline" method="GET" action="/ijinsiswa">
                                <button type="button" class="btn btn-primary float-right mr-sm-2" data-toggle="modal" data-target="#tambah">Tambah Ijin</button>
                                <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#exim">Rekap Ijin Siswa</button>
                            </form>                                  
                            @endif
                            </br>

							@if(auth()->user()->role == 'satpam')
                            <!-- Frame Webcam -->
                            <video id="video" width="280" height="280" autoplay></video>
							<canvas id="canvas" width="280" height="280" style="display:none;"></canvas>
							<div id="result"></div>
							@endif

                            <table id="qrcodecam" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                    @if(auth()->user()->role != 'siswa')
                                        <th>NAMA</th>
                                        <th>KELAS</th>
                                    @endif 
                                        <th>KET IJIN</th>
									@if(auth()->user()->role == 'kepala')
                                        <th>KEPALA SEKOLAH</th>
                                    @endif 
                                        <th>PEMBINA</th>
                                        @if(auth()->user()->role == 'pembina')
                                        <th>STATUS</th>
                                        @endif
                                        <th>KURIKULUM</th>
                                        @if(auth()->user()->role == 'kurikulum')
                                        <th>STATUS</th>
                                        @endif
                                        <th>WALI KELAS</th>
                                        @if(auth()->user()->role == 'walikelas')
                                        <th>STATUS</th>
                                        @endif
                                        <th>TIM KESEHATAN</th>
                                        @if(auth()->user()->role == 'kesehatan')
                                        <th>STATUS</th>
                                        @endif
                                        <th>WAKTU IJIN</th>
                                    
                                        <th>LIHAT SURAT</th>
                                   
                                    <th>BERANGKAT</th>
									<th>DURASI</th>
									<th>KEMBALI</th>
									<th>BUKTI</th>
									<th>KETERANGAN</th>
									<th>OVERTIME</th>
									<th>SURAT</th>
                                      
                                      <th>AKSI</th>
									  
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data_ijinsiswa as $ijinsiswa)
                                    <tr>
                                    @if(auth()->user()->role != 'siswa')
                                        <td>{{ $ijinsiswa->nama }}</td>  
                                        <td>{{ $ijinsiswa->kelas }}</td>
                                    @endif 
                                        <td>{{ $ijinsiswa->ketijin }}</td>
									@if(auth()->user()->role == 'kepala')
									<td>
										@if($ijinsiswa->filex != 'Surat Salah')	
										<a href="/ijinsiswa/{{ $ijinsiswa->id }}/verifikasi" class="btn btn-success btn-sm">Ijinkan</a>
										@endif
										<p> </p><a href="/ijinsiswa/{{ $ijinsiswa->id }}/suratsalah" class="btn btn-danger btn-sm">Surat Salah</a>
									</td>
                                    @endif 
                                        @if($ijinsiswa->oksis == 'belum')
                                        <td style="background-color: #ff0000" align="center">
                                            <span class="nav-icon fas fa-minus-square"></span>
                                        </td>                 
                                        @else      
                                        <td style="background-color: #32CD32" align="center">
                                            <span class="nav-icon fas fa-check-square"></span>
                                        </td>  
                                        @endif
                                        @if(auth()->user()->role == 'pembina')
                                        <td>
										@if($ijinsiswa->filex != 'Surat Salah')	
										<a href="/ijinsiswa/{{ $ijinsiswa->id }}/verifikasi" class="btn btn-success btn-sm">Ijinkan</a>
										@endif
										<p> </p><a href="/ijinsiswa/{{ $ijinsiswa->id }}/suratsalah" class="btn btn-danger btn-sm">Surat Salah</a>
										</td>
                                        @endif
                                        @if($ijinsiswa->okkur == 'belum')
                                        <td style="background-color: #ff0000" align="center">
                                            <span class="nav-icon fas fa-minus-square"></span>
                                        </td>                 
                                        @else      
                                        <td style="background-color: #32CD32" align="center">
                                            <span class="nav-icon fas fa-check-square"></span>
                                        </td>  
                                        @endif
                                        @if(auth()->user()->role == 'kurikulum')
                                        <td>
										@if($ijinsiswa->filex != 'Surat Salah')	
										<a href="/ijinsiswa/{{ $ijinsiswa->id }}/verifikasi" class="btn btn-success btn-sm">Ijinkan</a>
										@endif
										<p> </p><a href="/ijinsiswa/{{ $ijinsiswa->id }}/suratsalah" class="btn btn-danger btn-sm">Surat Salah</a>
										</td>
                                        @endif
                                        @if($ijinsiswa->okbin == 'belum')
                                        <td style="background-color: #ff0000" align="center">
                                            <span class="nav-icon fas fa-minus-square"></span>
                                        </td>                 
                                        @else      
                                        <td style="background-color: #32CD32" align="center">
                                            <span class="nav-icon fas fa-check-square"></span>
                                        </td>  
                                        @endif
                                        @if(auth()->user()->role == 'walikelas')
                                        <td>
										@if($ijinsiswa->filex != 'Surat Salah')	
										<a href="/ijinsiswa/{{ $ijinsiswa->id }}/verifikasi" class="btn btn-success btn-sm">Ijinkan</a>
										@endif
										<p> </p><a href="/ijinsiswa/{{ $ijinsiswa->id }}/suratsalah" class="btn btn-danger btn-sm">Surat Salah</a>
										</td>
                                        @endif
                                        @if($ijinsiswa->okas == 'belum')
                                        <td style="background-color: #ff0000" align="center">
                                            <span class="nav-icon fas fa-minus-square"></span>
                                        </td>                 
                                        @else      
                                        <td style="background-color: #32CD32" align="center">
                                            <span class="nav-icon fas fa-check-square"></span>
                                        </td>  
                                        @endif
                                        @if(auth()->user()->role == 'kesehatan')
                                        <td>
										@if($ijinsiswa->filex != 'Surat Salah')	
										<a href="/ijinsiswa/{{ $ijinsiswa->id }}/verifikasi" class="btn btn-success btn-sm">Ijinkan</a>
										@endif
										<p> </p><a href="/ijinsiswa/{{ $ijinsiswa->id }}/suratsalah" class="btn btn-danger btn-sm">Surat Salah</a>
										</td>
                                        @endif
                                        <td>{{ $ijinsiswa->created_at->format('d M Y - H:i:s') }}</td>

										
                                        <td>
										@if($ijinsiswa->file_path !=null)	
                                           <!-- Tombol untuk melihat file -->
                                        <a href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#lihatFile{{ $ijinsiswa->id }}">Lihat</a>
                                        @else
										<span class="text">Tidak Ada</span>
										@endif
										</td>
										

                                    
                                    <!-- Check Out Column -->
									<td>
										@if($ijinsiswa->oksis == 'ok' && $ijinsiswa->okkur == 'ok' && $ijinsiswa->okbin == 'ok' && $ijinsiswa->okas == 'ok')
											@if(auth()->user()->role === 'satpam')
												@if(!$ijinsiswa->cekout)
													<a href="/ijinsiswa/{{ $ijinsiswa->id }}/cekout" class="btn btn-primary btn-sm">Berangkat</a>
												@else
													{{ \Carbon\Carbon::parse($ijinsiswa->cekout)->format('d M Y - H:i:s') }}
												@endif
											@else
												@if($ijinsiswa->cekout)
													{{ \Carbon\Carbon::parse($ijinsiswa->cekout)->format('d M Y - H:i:s') }}
												@else
													<span class="text-danger">Belum Berangkat</span>
												@endif
											@endif
										@else
											<span class="text-danger">Belum Semua Disetujui</span>
										@endif
									</td>


                                    <td>{{ $ijinsiswa->durasi}}</td> 
<!-- Tombol Cek In di tabel -->
<td>
    @if($ijinsiswa->cekout)
        @if(!$ijinsiswa->cekin)
            @if(auth()->user()->role === 'satpam')
                <button type="button" class="btn btn-warning btn-sm" 
                    data-myid="{{$ijinsiswa->id}}"								
                    data-toggle="modal" data-target="#modalCekIn">Kembali</button>
            @else
                <span class="text-danger">Belum Kembali</span>
            @endif
        @else
            {{ \Carbon\Carbon::parse($ijinsiswa->cekin)->format('d M Y - H:i:s') }}
        @endif
    @endif
</td>



<td>
    @if($ijinsiswa->file_bukti)
        <img src="{{ $ijinsiswa->file_bukti }}" width="100" height="100" />
    @else
        Tidak ada bukti
    @endif
</td>



                                    <td>{{ $ijinsiswa->ket}}</td> 
                                    <td>{{ $ijinsiswa->wkt}}</td>
									<td>{{ $ijinsiswa->filex}}</td> 
                                     <td> 
                                       @if($ijinsiswa->okkur == 'belum' AND $ijinsiswa->okbin == 'belum' AND $ijinsiswa->okas == 'belum' AND $ijinsiswa->oksis == 'belum' OR auth()->user()->role=='admin')
                                      <a href="/ijinsiswa/{{$ijinsiswa->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>
											
                                        
                                      @else
										<p>
                                          Tidak bisa hapus
                                      </p>
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

    <!-- Modal Tambah -->
    <div class="modal fade" id="tambah" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Tambah Data Ijin</h4>
                </div>
                <div class="modal-body">
                    <form action="/ijinsiswa" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nama</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="kesiswaan">Kesiswaan</option>
                                <option value="kurikulum">Kurikulum</option>
                                <option value="humas">Humas</option>
                                <option value="sarpras">Sarpras</option>
                                <option value="siswa">Siswa</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Lihat File -->
	@foreach($data_ijinsiswa as $ijinsiswa)
<div class="modal fade" id="lihatFile{{ $ijinsiswa->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
			<h4 class="modal-title" id="myModalLabel">File Ijin {{ $ijinsiswa->nama }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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





<!-- Modal Cek In -->
<div class="modal fade" id="modalCekIn" tabindex="-1" role="dialog" aria-labelledby="modalCekInLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCekInLabel">Cek In</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">


                <!-- Form untuk Export Data dengan Filter Tanggal dan Kelas -->
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
                    <div class="form-group">
                        <label for="kelas">Pilih Kelas</label>
                        <select class="form-control" id="kelas" name="kelas">
                            <option value="all">Semua Kelas</option>
                            @foreach($data_ijinsiswa as $ijinsiswa)
                                <option value="{{ $ijinsiswa->kelas }}">{{ $ijinsiswa->kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success mt-2">Export</button>
                </form>
            </div>
        </div>
    </div>
</div>
</section>

<script src="/js/jsQR.js"></script>

 <script>
       let qrCodeResult = '';

document.addEventListener('DOMContentLoaded', () => {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    const resultDiv = document.getElementById('result');

    // Inisialisasi DataTables
    const table = $('#qrcodecam').DataTable({
        "scrollX": true,
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": false,
        "info": true,
        "autoWidth": true,
    });

    // Minta akses ke kamera
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(stream => {
            video.srcObject = stream;
            video.setAttribute("playsinline", true); // Diperlukan untuk iOS
            video.play();

            video.addEventListener('loadedmetadata', () => {
                console.log('Metadata video dimuat');
                requestAnimationFrame(scanQRCode);
            });
        })
        .catch(error => {
            console.error("Kesalahan mengakses kamera: ", error);
        });

    function scanQRCode() {
        if (video.videoWidth > 0 && video.videoHeight > 0) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);

            // Decode QR code
            const code = jsQR(imageData.data, canvas.width, canvas.height);

            if (code) {
                qrCodeResult = code.data.trim(); // Trim untuk menghapus spasi ekstra
                resultDiv.innerHTML = `Isi QR Code: ${qrCodeResult}`;
                filterTableRows();
            } else {
                resultDiv.innerHTML = 'QR code tidak ditemukan. Silakan coba lagi.';
            }
        } else {
            resultDiv.innerHTML = 'Dimensi video tidak valid. Menunggu video untuk diinisialisasi...';
        }

        // Pindai terus-menerus
        requestAnimationFrame(scanQRCode);
    }

    function filterTableRows() {
        // Gunakan metode search DataTables untuk memfilter baris berdasarkan QR Code
        table.search(qrCodeResult).draw();
    }
});

    </script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const videoCekIn = document.getElementById('videoCekIn');
    const canvasCekIn = document.getElementById('canvasCekIn');
    const contextCekIn = canvasCekIn.getContext('2d');
    const snapCekIn = document.getElementById('snapCekIn');
    const photoCekInResult = document.getElementById('photoCekInResult');
    const modalCekIn = $('#modalCekIn');
    const uploadPhotoForm = document.getElementById('uploadPhotoForm');

    // Fungsi untuk menampilkan modal dan mengaktifkan kamera
    modalCekIn.on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        
		var jurnalid = button.data('myid'); // Extract info from data-* attributes
		var modal = $(this)
	    modal.find('.modal-body #jurnalid').val(jurnalid)
        
        // Update form action with the correct ID
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

    // Fungsi untuk mengambil foto
    snapCekIn.addEventListener('click', function() {
        canvasCekIn.width = videoCekIn.videoWidth;
        canvasCekIn.height = videoCekIn.videoHeight;
        contextCekIn.drawImage(videoCekIn, 0, 0, canvasCekIn.width, canvasCekIn.height);
        const imageData = canvasCekIn.toDataURL('image/png');
        photoCekInResult.innerHTML = `<img src="${imageData}" width="50%" height="50%"/>`;
        document.getElementById('file_bukti').value = imageData; // Set image data to hidden input
    });

    // Fungsi untuk menghentikan stream video ketika modal ditutup
    modalCekIn.on('hidden.bs.modal', function () {
        let stream = videoCekIn.srcObject;
        let tracks = stream.getTracks();

        tracks.forEach(track => {
            track.stop();
        });

        videoCekIn.srcObject = null;
    });
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

@if(auth()->user()->role !== 'satpam')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const table = $('#qrcodecam').DataTable({
            "scrollX": true,
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": false,
            "info": true,
            "autoWidth": true,
        });
    });
    </script>
@endif


@endsection
