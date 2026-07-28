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

<div class="card">

            <div class="card-header">
            <div class="row">
          <div class="col-12">
             <form class="form-inline ml-auto" method="GET" action="/siswa">
                    <h3 class="mr-sm-5 card-title">Data Master Siswa</h3>
@if(auth()->user()->role=='admin')
                    <button type="button" class=" mr-sm-4 btn btn-primary btn-sm " data-toggle="modal" data-target="#tambah">Tambah Siswa</button>

                    <!-- <a href="/siswa/export" class="mr-sm-3 btn btn-sm btn-primary">Export</a> -->

                    <button type="button" class=" btn btn-default btn-sm " data-toggle="modal" data-target="#exim">Export/Import</button>
                    <button type="button" class="btn btn-danger btn-sm ml-3" data-toggle="modal" data-target="#updateIjinModal">Reset Ijin Siswa</button>

@endif
            </form>
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
                    <!-- <th>Sakit</th>
                    <th>Ijin</th>
                    <th>Alpha</th>
                    <th>Dispen</th> -->
                    <th>Ijin Pesiar</th>
                    <th>Ijin Bermalam Wajib</th>
                    <th>Ijin Bermalam Resmi</th>
                    <th>Ijin Jalan</th>
                    <th>Ijin Khusus</th>
                    @if(auth()->user()->role=='admin')
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
                    <!-- <td>{{$siswa->sakit}}</td>
                    <td>{{$siswa->ijin}}</td>
                    <td>{{$siswa->alpha}}</td>
                    <td>{{$siswa->dispen}}</td> -->
                    <td>{{$siswa->ip}}</td>
                    <td>{{$siswa->ib}}</td>
                    <td>{{$siswa->ibr}}</td>
                    <td>{{$siswa->ij}}</td>
                    <td>{{$siswa->ik}}</td>
                    @if(auth()->user()->role=='admin')
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

                          data-toggle="modal" data-target="#editsiswa">Edit</button>

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
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Tambah Siswa</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Tambah</button>
            </form>
      </div>

    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editsiswa" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Edit Siswa</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Update</button>
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
        <h4 class="modal-title" id="myModalLabel">Export / Import</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form action="/siswa/import" method="POST" enctype="multipart/form-data"> 
                  {{csrf_field()}}

              <div class="form-group">
              <label class="mr-sm-3 " for="export">Export</label>                
                <div class="col-md-6">
                  
                  <a href="/siswa/export" class="btn btn-sm btn-success">Export</a>
              </div>
              </div>

              <div class="form-group">
                <div class="col-md-6">
                <label for="file">Import</label>
                <input name="file" type="file" class="form-control" id="file" accept=".xlsx">
              </div>
              </div>
              
                          
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Submit</button>
            </form>
      </div>

    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="updateIjinModal" tabindex="-1" role="dialog" aria-labelledby="updateIjinModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="/siswa/update-ijin" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="updateIjinModalLabel">Reset Ijin Siswa</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="jenis_ijin">Jenis Ijin</label>
            <select class="form-control" id="jenis_ijin" name="jenis_ijin" required>
              <option value="Ijin Pesiar">Ijin Pesiar</option>
              <option value="Ijin Bermalam Wajib">Ijin Bermalam Wajib</option>
              <option value="Ijin Bermalam Resmi">Ijin Bermalam Resmi</option>
              <option value="Ijin Jalan">Ijin Jalan</option>
              <option value="Ijin Khusus">Ijin Khusus</option>
            </select>
          </div>
          <div class="form-group">
            <label for="jumlah">Jumlah</label>
            <input type="number" class="form-control" id="jumlah" name="jumlah" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>


@endsection