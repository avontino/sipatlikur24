@extends('layouts.master')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
      <i class="fa fa-check-circle"></i> 
      {{session('sukses')}}
    </div>
    @endif

    @if(session('gagal'))
    <div class="alert alert-danger alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
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

              <div class="form-group">
                <label>Nama Siswa</label>
                <input name="nama" value="{{auth()->user()->name}}" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" readonly>               
              </div>

              <div class="form-group">
                <label>Kelas</label>
                <input name="kelas" value="{{$siswa->kelas}}" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" readonly>
              </div>

              <div class="form-group">
                <label>Jenis Ijin</label>
                <select id="ijinSelect" name="ijin" class="form-control" required>
                <option value="">Pilih Jenis Ijin</option>
                                    <option value="Ijin Pesiar">Ijin Pesiar</option>
                                    <option value="Ijin Bermalam Wajib">Ijin Bermalam Wajib</option>
                                    <option value="Ijin Bermalam Resmi">Ijin Bermalam Resmi</option>
                                    <option value="Ijin Khusus">Ijin Khusus</option>
                                    <option value="Ijin Jalan">Ijin Jalan</option>
                </select>
              </div>

              <div id="sisaIjin" class="alert alert-info mt-3" style="display: none;">
                <i class="fa fa-info-circle"></i> 
                <span id="sisaText"></span>
              </div>

              <div class="form-group" id="fileUploadGroup" style="display: none;">
                <label for="file">Upload File (Gambar JPG/JPEG/PNG)</label>
                <input type="file" name="file" id="fileUploadInput" class="form-control" accept=".jpg, .jpeg, .png">
              </div>

              <button type="submit" class="btn btn-primary">Tambah</button>
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
                    <th>PEMBINA</th>
                    <th>KURIKULUM</th>
                    <th>WALIKELAS</th>
                    <th>TIM KESEHATAN</th>
                    <th>WAKTU IJIN</th>
                    <th>SURAT</th>
                    <th>FILE</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($data_ijinsiswa as $ijinsiswa)
                  <tr>
                    <td>{{$ijinsiswa->nama}}</td> 
                    <td>{{$ijinsiswa->kelas}}</td>
                    <td>{{$ijinsiswa->ketijin}}</td>
                    @if($ijinsiswa->oksis=='belum')
                    <td style="background-color: #ff0000" align="center">
                      <span class="nav-icon fas fa-minus-square"></span></td>
                    @else
                    <td style="background-color: #32CD32" align="center">
                      <span class="nav-icon fas fa-check-square"></span></td>
                    @endif

                    @if($ijinsiswa->okkur=='belum')
                    <td style="background-color: #ff0000" align="center">
                      <span class="nav-icon fas fa-minus-square"></span></td>
                    @else
                    <td style="background-color: #32CD32" align="center">
                      <span class="nav-icon fas fa-check-square"></span></td>
                    @endif

                    @if($ijinsiswa->okbin=='belum')
                    <td style="background-color: #ff0000" align="center">
                      <span class="nav-icon fas fa-minus-square"></span></td>
                    @else
                    <td style="background-color: #32CD32" align="center">
                      <span class="nav-icon fas fa-check-square"></span></td>
                    @endif

                    @if($ijinsiswa->okas=='belum')
                    <td style="background-color: #ff0000" align="center">
                      <span class="nav-icon fas fa-minus-square"></span></td>
                    @else
                    <td style="background-color: #32CD32" align="center">
                      <span class="nav-icon fas fa-check-square"></span></td>
                    @endif
                    <td>{{$ijinsiswa->created_at->format('d M Y - H:i:s')}}</td>
                    <td>
                        @if($ijinsiswa->filex != 'Surat Salah')	
                        {{$ijinsiswa->filex}}
                        @else
 <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#uploadUlangModal{{ $ijinsiswa->id }}">
                            Upload Ulang
                        </button>







                        @endif
                    </td>
                    <td>
										@if($ijinsiswa->file_path !=null)	
                       <!-- Tombol untuk melihat file -->
                      <a href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#lihatFile{{ $ijinsiswa->id }}">Lihat</a>
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


<!-- Modal Upload Ulang -->
@foreach($data_ijinsiswa as $ijinsiswa)
<div class="modal fade" id="uploadUlangModal{{ $ijinsiswa->id }}" tabindex="-1" role="dialog" aria-labelledby="uploadUlangLabel{{ $ijinsiswa->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadUlangLabel{{ $ijinsiswa->id }}">Upload Ulang File Ijin</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
	

    // Dummy data for the example
    var siswaData = {
        'Ijin Pesiar': {{ json_encode($siswa->ip) }},
        'Ijin Bermalam Wajib': {{ json_encode($siswa->ib) }},
        'Ijin Bermalam Resmi': {{ json_encode($siswa->ibr) }},
        'Ijin Jalan': {{ json_encode($siswa->ij) }},
        'Ijin Khusus': {{ json_encode($siswa->ik) }}
    };

    selectElement.addEventListener('change', function () {
        var selectedOption = this.value;
        if (siswaData[selectedOption] !== undefined) {
            sisaText.textContent = 'Sisa ' + selectedOption + ': ' + siswaData[selectedOption];
            sisaIjin.style.display = 'block';
        } else {
            sisaIjin.style.display = 'none';
        }

        // Menampilkan atau menyembunyikan file upload berdasarkan opsi yang dipilih
        if (selectedOption === 'Ijin Bermalam Wajib' || selectedOption === 'Ijin Bermalam Resmi') {
            fileUploadGroup.style.display = 'block';
            document.getElementById('fileUploadInput').required = true; // Menjadikan input file wajib diisi
        } else {
            fileUploadGroup.style.display = 'block';
            document.getElementById('fileUploadInput').required = false; // Input file tidak wajib diisi
        }
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
@endsection
