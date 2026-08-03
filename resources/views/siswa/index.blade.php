@extends('layouts.master')

@section('content')    

<section class="content pt-3">
    <div class="container-fluid">
  @if(session('sukses'))
  <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <i class="fa fa-check-circle"></i> 

    {{session('sukses')}}
  </div>
  @endif

  @if(session('sukses_reset'))
  <div class="alert alert-warning alert-dismissible border-0 shadow-sm fade show" role="alert">
      <div class="d-flex align-items-center">
          <i class="fas fa-key me-2 fs-5 text-dark"></i>
          <div>
              <strong>{{ session('sukses_reset') }}</strong>
              <div class="small mt-1 text-muted">Harap catat password ini dan berikan kepada siswa karena password ini tidak akan ditampilkan lagi demi keamanan.</div>
          </div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

<div class="card shadow-sm border-0">

            <div class="card-header bg-light py-3">
            <div class="row">
          <div class="col-12">
             <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h3 class="fw-bold m-0 me-auto" style="color: #004d1a;"><i class="fas fa-user-graduate me-2"></i>Data Master Siswa</h3>
                    @if(auth()->user()->hasRole('walikelas') || auth()->user()->walikelas_kelas)
                        <a href="/siswa/export?view=walikelas" class="btn btn-sm btn-success text-white">
                            <i class="fas fa-file-excel"></i> Export Kelas Saya
                        </a>
                    @endif
                    @if(auth()->user()->role=='admin')
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambah">Tambah Siswa</button>
                        <button type="button" class="btn btn-default btn-sm" data-bs-toggle="modal" data-bs-target="#exim">Export/Import</button>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#updateIjinModal">Reset Ijin Siswa</button>
                    @endif
            </div>
          </div>
        </div>
            </div>


            <!-- /.card-header -->
            <div class="card-body">
              <table id="example5" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Sakit</th>
                    <th>Ijin</th>
                    <th>Alpha</th>
                    <th>Dispen</th>
                    @if(auth()->user()->role=='admin')
                    <th>Password</th>
                    <th>Aksi</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                @foreach($data_siswa as $siswa)
                  <tr>
                    <td>{{$siswa->nis}}</td>
                    <td>{{$siswa->nama}}</td>
                    <td>{{$siswa->kelas}}</td>
                    <td class="text-center fw-bold text-warning">{{$siswa->sakit ?? 0}}</td>
                    <td class="text-center fw-bold text-info">{{$siswa->ijin ?? 0}}</td>
                    <td class="text-center fw-bold text-danger">{{$siswa->alpha ?? 0}}</td>
                    <td class="text-center fw-bold text-primary">{{$siswa->dispen ?? 0}}</td>
                    @if(auth()->user()->role=='admin')
                    <td>
                      <form action="/siswa/{{$siswa->id}}/reset-password" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset password siswa ini?');" style="display:inline;">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-sync-alt"></i> Reset</button>
                      </form>
                    </td>
                    <td>
                   
                      <button type="button" class="btn btn-warning btn-sm" 
                          data-myid="{{$siswa->id}}"
                          data-mynis="{{$siswa->nis}}"
                          data-mynama="{{$siswa->nama}}"
                          data-mykelas="{{$siswa->kelas}}"
                          data-mysakit="{{$siswa->sakit}}"
                          data-myijin="{{$siswa->ijin}}"
                          data-myalpha="{{$siswa->alpha}}"
                          data-mydispen="{{$siswa->dispen}}"

                          data-bs-toggle="modal" data-bs-target="#editsiswa">Edit</button>

                            <a href="/siswa/{{$siswa->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>
                      

                          </form>
                    </td>
                    @endif
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>

                </tfoot>
              </table>

            </div>
            <!-- /.card-body -->
          </div>
        </div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambah" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Tambah Siswa</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/siswa/create" method="POST"> 
                  {{csrf_field()}}

              <div class="form-group">
                <label for="exampleInputEmail1">NIS</label>
                <input name="nis" type="text" class="form-control" id="nis" aria-describedby="emailHelp" placeholder="Nomor Induk Siswa">
              </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Nama Siswa</label>
                <input name="nama" type="text" class="form-control" id="nama" aria-describedby="emailHelp" placeholder="Nama Siswa">
              </div>
              <div class="form-group">
                <label for="exampleFormControlSelect1">Kelas</label>
                <select name="kelas" class="form-control" id="kelas">
                @foreach($ke_las as $kelas)
                  <option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
                @endforeach 
                </select>
              </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Sakit</label>
                <input name="sakit" type="text" class="form-control" id="sakit" aria-describedby="emailHelp" placeholder="Jumlah Sakit" value="0">
              </div>
              
              <div class="form-group">
                <label for="exampleInputEmail1">Ijin</label>
                <input name="ijin" type="text" class="form-control" id="ijin" aria-describedby="emailHelp" placeholder="Jumlah Ijin" value="0">
              </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Alpha</label>
                <input name="alpha" type="text" class="form-control" id="alpha" aria-describedby="emailHelp" placeholder="Jumlah Alpha" value="0">
              </div>
              
              <div class="form-group">
                <label for="exampleInputEmail1">Dispen</label>
                <input name="dispen" type="text" class="form-control" id="dispen" aria-describedby="emailHelp" placeholder="Jumlah Dispen" value="0">
              </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Tambah</button>
            </form>
      </div>

    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editsiswa" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Edit Siswa</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        
      </div>
      <div class="modal-body">
        <form action="/siswa/update" method="post"> 
                  {{csrf_field()}}
                  <input type="hidden" name="siswaid" id="siswaid" value="">
              <div class="form-group">
                <label for="exampleInputEmail1">NIS</label>
                <input name="nis" type="text" class="form-control" id="nis" aria-describedby="emailHelp" placeholder="Nomor Induk Siswa">
              </div>  

              <div class="form-group">
                <label for="exampleInputEmail1">Nama Siswa</label>
                <input name="nama" type="text" class="form-control" id="nama" aria-describedby="emailHelp" placeholder="Nama Siswa">
              </div>
              <div class="form-group">
                <label for="exampleFormControlTextarea1">Kelas</label>
                <select name="kelas" class="form-control" id="kelas">
                @foreach($ke_las as $kelas)
                  <option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
                @endforeach 
                </select>
              </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Sakit</label>
                <input name="sakit" type="text" class="form-control" id="sakit" aria-describedby="emailHelp" placeholder="Jumlah Sakit">
              </div>
              
              <div class="form-group">
                <label for="exampleInputEmail1">Ijin</label>
                <input name="ijin" type="text" class="form-control" id="ijin" aria-describedby="emailHelp" placeholder="Jumlah Ijin">
              </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Alpha</label>
                <input name="alpha" type="text" class="form-control" id="alpha" aria-describedby="emailHelp" placeholder="Jumlah Alpha">
              </div>
              
              <div class="form-group">
                <label for="exampleInputEmail1">Dispen</label>
                <input name="dispen" type="text" class="form-control" id="dispen" aria-describedby="emailHelp" placeholder="Jumlah Dispen">
              </div>

                          
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Update</button>
            </form>
      </div>

    </div>
  </div>
</div>

<!-- Modal Export/Import -->
<div class="modal fade" id="exim" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel"><i class="fas fa-exchange-alt me-2"></i> Export / Import Data Siswa</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Section: Export -->
        <div class="mb-4 pb-3 border-bottom">
          <h5 class="fw-bold text-primary mb-2"><i class="fas fa-file-export me-1"></i> Ekspor Data Siswa</h5>
          <p class="text-muted small">Unduh seluruh data siswa untuk Tahun Ajaran aktif saat ini (<strong>{{ session('tahun_ajaran') }}</strong>) dalam bentuk file Excel.</p>
          <a href="/siswa/export" class="btn btn-sm btn-success text-white"><i class="fas fa-file-excel me-1"></i> Unduh Data Siswa (Excel)</a>
        </div>

        <!-- Section: Import -->
        <form action="/siswa/import" method="POST" enctype="multipart/form-data"> 
          {{csrf_field()}}
          <h5 class="fw-bold text-primary mb-2"><i class="fas fa-file-import me-1"></i> Impor Data Siswa</h5>
          <p class="text-muted small mb-3">Impor data siswa baru untuk Tahun Ajaran aktif (<strong>{{ session('tahun_ajaran') }}</strong>). Harap gunakan template resmi di bawah ini agar format data sesuai.</p>
          
          <div class="mb-3">
            <a href="/siswa/template" class="btn btn-sm btn-info text-white"><i class="fas fa-file-download me-1"></i> Unduh Template Excel</a>
          </div>

          <div class="card bg-light p-3 mb-3 border-0 rounded">
            <h6 class="fw-bold text-secondary mb-2" style="font-size: 13px;"><i class="fas fa-info-circle me-1"></i> Petunjuk Penting:</h6>
            <ul class="mb-0 text-muted small ps-3">
              <li>File harus berformat <strong>.xlsx</strong>.</li>
              <li>Kolom Excel harus berurutan: <strong>Kolom A = NIS</strong>, <strong>Kolom B = Nama Siswa</strong>, <strong>Kolom C = Kelas</strong>.</li>
              <li>Baris pertama (header) akan diabaikan secara otomatis oleh sistem.</li>
              <li>Akun login siswa (Role: Siswa, Password default: <code>123456</code>) akan dibuat secara otomatis berdasarkan NIS.</li>
            </ul>
          </div>

          <div class="form-group mb-0">
            <label for="file" class="form-label small fw-bold">Pilih File Excel (.xlsx)</label>
            <input name="file" type="file" class="form-control" id="file" accept=".xlsx" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Mulai Impor</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="updateIjinModal" tabindex="-1" role="dialog" aria-labelledby="updateIjinModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form action="/siswa/update-ijin" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="updateIjinModalLabel">Reset Ijin Siswa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="jenis_ijin">Jenis Ijin</label>
            <select class="form-control" id="jenis_ijin" name="jenis_ijin" required>
              <option value="Izin Keluar / Pulang Karena Sakit">Izin Keluar / Pulang Karena Sakit</option>
              <option value="Izin Keluar / Pulang Keperluan Keluarga">Izin Keluar / Pulang Keperluan Keluarga</option>
              <option value="Izin Meninggalkan Sekolah Sementara">Izin Meninggalkan Sekolah Sementara</option>
              <option value="Izin Tidak Masuk Sekolah (Sakit / Izin Harian)">Izin Tidak Masuk Sekolah (Sakit / Izin Harian)</option>
            </select>
          </div>
          <div class="form-group">
            <label for="jumlah">Jumlah</label>
            <input type="number" class="form-control" id="jumlah" name="jumlah" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>


@endsection