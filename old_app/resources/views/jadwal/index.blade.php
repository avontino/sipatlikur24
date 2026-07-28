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

            <div class="card-header">
            <div class="row">
          <div class="col-12">
             <form class="form-inline ml-auto" method="GET" action="/siswa">
                    <h3 class="mr-sm-5 card-title">Jadwal Pelajaran</h3>
                    @if(auth()->user()->role=='admin')
                    <button type="button" class=" mr-sm-4 btn btn-primary btn-sm " data-toggle="modal" data-target="#tambahjadwal">Tambah Jadwal</button>

                    <!-- <a href="/siswa/export" class="mr-sm-3 btn btn-sm btn-primary">Export</a> -->

                    <button type="button" class=" btn btn-default btn-sm " data-toggle="modal" data-target="#exim">Export/Import</button>
                    @endif
            </form>
          </div>
        </div>

           
            </div>


            <!-- /.card-header -->
            <div class="card-body">
              
              <table id="example3" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Kelas</th>
                    <th>Jam Ke</th>
                    <th>Jumlah Jam</th>
                    <th>Mata Pelajaran</th>
                    <th>Guru</th>
                    <th>Hari</th>
                     @if(auth()->user()->role=='admin' OR auth()->user()->role=='kurikulum')
                    <th>Aksi</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                @foreach($data_jadwal as $jadwal)
                  <tr>
                    <td>{{$jadwal->kelas}}</td>
                    <td>{{$jadwal->jamke}}</td>
                    <td>{{$jadwal->jumlahjam}}</td>
                    <td>{{$jadwal->mapel}}</td>
                    <td>{{$jadwal->guru}}</td>
                    <td>{{$jadwal->hari}}</td>
                    @if(auth()->user()->role=='admin' OR auth()->user()->role=='kurikulum')
                    <td>
                          <button type="button" class="btn btn-warning btn-sm" 
                          data-myid="{{$jadwal->id}}"
                          data-mykelas="{{$jadwal->kelas}}"
                          data-myjamke="{{$jadwal->jamke}}"
                          data-myjumlahjam="{{$jadwal->jumlahjam}}"
                          data-mymapel="{{$jadwal->mapel}}"
                          data-myguru="{{$jadwal->guru}}"
                          data-myhari="{{$jadwal->hari}}"
                          data-myj1="{{$jadwal->j1}}"
                          data-myj2="{{$jadwal->j2}}"
                          data-myj3="{{$jadwal->j3}}"
                          data-myj4="{{$jadwal->j4}}"
                          data-myj5="{{$jadwal->j5}}"
                          data-myj6="{{$jadwal->j6}}"
                          data-myj7="{{$jadwal->j7}}"
                          data-myj8="{{$jadwal->j8}}"
                          data-myj9="{{$jadwal->j9}}"
                          data-myj10="{{$jadwal->j10}}"
                          data-myj11="{{$jadwal->j11}}"
                   

                  data-toggle="modal" data-target="#editjadwalpel">Edit</button>
                   
                     <a href="/jadwal/{{$jadwal->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>
                    @endif
                    </td>
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



<!-- Modal Tambah Jadwal -->
<div class="modal fade" id="tambahjadwal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        
        <h4 class="modal-title" id="exampleModalLabel">Tambah Jadwal</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form action="/jadwal/create" method="POST"> 
                  {{csrf_field()}}
  

        <div class="form-group">
               
               <div class="form-group">
                <label >Kelas</label>
                <select name="kelas" class="form-control" >
                @foreach($ke_las as $kelas)
                  <option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
                @endforeach                 
                </select>

                </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Jam Ke</label>
                <input name="jamke" type="text" class="form-control" id="jamke" aria-describedby="emailHelp" placeholder="Diisi 1-11" >
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jumlah Jam</label>
                <input name="jumlahjam" type="text" class="form-control" id="jumlahjam" aria-describedby="emailHelp" placeholder="Jumlah Jam" >
              </div>

              <div class="form-group">
                <label >Mata Pelajaran</label>
                <select name="mapel" class="form-control" >
                @foreach($ma_pel as $mapel)
                  <option value="{{$mapel->mapel}}">{{$mapel->mapel}}</option>
                @endforeach                 
                </select>

                </div>

                <div class="form-group">
                <label >Guru</label>
                <select name="guru" class="form-control" >
                @foreach($gu_ru as $guru)
                  <option value="{{$guru->guru}}">{{$guru->guru}}</option>
                @endforeach                 
                </select>

                <div class="form-group">
                <label >Hari</label>
                <input name="hari" type="text" class="form-control" id="hari" aria-describedby="emailHelp" placeholder="Nama Hari Bahasa Inggris" >
                </div>

                <div class="form-group">
                <label >1</label>
                <input name="j1" type="text" class="form-control" id="j1" aria-describedby="emailHelp" value="0" >
                </div>

                <div class="form-group">
                <label >2</label>
                <input name="j2" type="text" class="form-control" id="j2" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >3</label>
                <input name="j3" type="text" class="form-control" id="j3" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >4</label>
                <input name="j4" type="text" class="form-control" id="j4" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >5</label>
                <input name="j5" type="text" class="form-control" id="j5" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >6</label>
                <input name="j6" type="text" class="form-control" id="j6" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >7</label>
                <input name="j7" type="text" class="form-control" id="j7" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >8</label>
                <input name="j8" type="text" class="form-control" id="j8" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >9</label>
                <input name="j9" type="text" class="form-control" id="j9" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >10</label>
                <input name="j10" type="text" class="form-control" id="j10" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >11</label>
                <input name="j11" type="text" class="form-control" id="j11" aria-describedby="emailHelp" value="0" >
                </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Tambah Jadwal</button>
            </form>
            </div>

    </div>
  </div>
</div>

</div>
</div>
</div>
<!-- Modal Edit -->
<div class="modal fade" id="editjadwalpel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        
        <h4 class="modal-title" id="exampleModalLabel">Edit Jadwal</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form action="/jadwal/update" method="POST"> 
                  {{csrf_field()}}
        <input type="hidden" name="jadwalid" id="jadwalid" value="">

        <div class="form-group">
               
               <div class="form-group">
                <label >Kelas</label>
                <select name="kelas" class="form-control" id="kelas">
            
                @foreach($ke_las as $kelas)

              @if( $kelas->kelas == $jadwal->kelas )
                      <option value="{{ $kelas->kelas }}" selected="selected" > {{ $kelas->kelas }}</option>
                  @else
                      <option value="{{ $kelas->kelas }}"> {{ $kelas->kelas }}</option>
                  @endif
   
                @endforeach   
                          
                </select>

                </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Jam Ke</label>
                <input name="jamke" type="text" class="form-control" id="jamke" aria-describedby="emailHelp" placeholder="Diisi 1-11" value="{{$jadwal->jamke}}" >
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jumlah Jam</label>
                <input name="jumlahjam" type="text" class="form-control" id="jumlahjam" aria-describedby="emailHelp" placeholder="Jumlah Jam" value="{{$jadwal->jumlahjam}}" >
              </div>

              <div class="form-group">
                <label >Mata Pelajaran</label>
                <select name="mapel" class="form-control" id="mapel">
                @foreach($ma_pel as $mapel)
                  @if( $mapel->mapel == $jadwal->mapel )
                      <option value="{{ $mapel->mapel }}" selected="selected"> {{ $mapel->mapel }}</option>
                    @else
                      <option value="{{ $mapel->mapel }}"> {{ $mapel->mapel }}</option>
                    @endif
                @endforeach                 
                </select>

                </div>
             
                <div class="form-group">
                <label >Guru</label>
                <select name="guru" class="form-control" id="guru">
                @foreach($gu_ru as $guru)
                  @if( $guru->guru == $jadwal->guru )
                      <option value="{{ $guru->guru }}" selected="selected"> {{ $guru->guru }}</option>
                    @else
                      <option value="{{ $guru->guru }}"> {{ $guru->guru }}</option>
                    @endif
                @endforeach                 
                </select>
                @if(auth()->user()->role=='admin')
                <div class="form-group">
                <label >Hari</label>
                <input name="hari" type="text" class="form-control" id="hari" aria-describedby="emailHelp" placeholder="hari" value="{{$jadwal->hari}}">

                <div class="form-group">
                <label >1</label>
                <input name="j1" type="text" class="form-control" id="j1" aria-describedby="emailHelp" value="{{$jadwal->j1}}">
                </div>

                                <div class="form-group">
                <label >2</label>
                <input name="j2" type="text" class="form-control" id="j2" aria-describedby="emailHelp" value="{{$jadwal->j2}}">
                </div>

                                <div class="form-group">
                <label >3</label>
                <input name="j3" type="text" class="form-control" id="j3" aria-describedby="emailHelp" value="{{$jadwal->j3}}">
                </div>

                                <div class="form-group">
                <label >4</label>
                <input name="j4" type="text" class="form-control" id="j4" aria-describedby="emailHelp" value="{{$jadwal->j4}}">
                </div>

                                <div class="form-group">
                <label >5</label>
                <input name="j5" type="text" class="form-control" id="j5" aria-describedby="emailHelp" value="{{$jadwal->j5}}">
                </div>

                                <div class="form-group">
                <label >6</label>
                <input name="j6" type="text" class="form-control" id="j6" aria-describedby="emailHelp" value="{{$jadwal->j6}}">
                </div>

                                <div class="form-group">
                <label >7</label>
                <input name="j7" type="text" class="form-control" id="j7" aria-describedby="emailHelp" value="{{$jadwal->j7}}">
                </div>

                                <div class="form-group">
                <label >8</label>
                <input name="j8" type="text" class="form-control" id="j8" aria-describedby="emailHelp" value="{{$jadwal->j8}}">
                </div>

                                <div class="form-group">
                <label >9</label>
                <input name="j9" type="text" class="form-control" id="j9" aria-describedby="emailHelp" value="{{$jadwal->j9}}">
                </div>

                                <div class="form-group">
                <label >10</label>
                <input name="j10" type="text" class="form-control" id="j10" aria-describedby="emailHelp" value="{{$jadwal->j10}}">
                </div>

                <div class="form-group">
                <label >11</label>
                <input name="j11" type="text" class="form-control" id="j11" aria-describedby="emailHelp" value="{{$jadwal->j11}}">
                </div>
             @endif             
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Edit Jadwal</button>
            </form>
            </div>
    </div>
  </div>
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
        <form action="/jadwal/import" method="POST" enctype="multipart/form-data"> 
                  {{csrf_field()}}

              <div class="form-group">
              <label class="mr-sm-3 " for="export">Export</label>                
                <div class="col-md-6">
                  
                  <a href="/jadwal/export" class="btn btn-sm btn-success">Export</a>
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


@endsection
