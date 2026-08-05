@extends('layouts.master')

@section('content')    

</br>
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>{{$totalkosong}}</h3>

                <p>Jurnal Kosong</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>{{$totalok}}</h3>

                <p>Jurnal Terisi</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
         
      </section>


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

            <div class="card-header">
            <div class="row">
          <div class="col-12">
             <form class="form-inline" method="GET" action="/susulan">
                    <h3 class="mr-sm-5 card-title">Jadwal Pelajaran </h3>

                    <button type="button" class=" mr-sm-4 btn btn-primary btn-sm " data-bs-toggle="modal" data-bs-target="#tambahabsen">Tambah Absensi Susulan</button>

            </form>
          </div>
        </div>

           
            </div>


            <!-- /.card-header -->
            <div class="card-body">
            @if($data_jadwal->isEmpty())
											<tr>
												<td colspan="11" class="text-center">Tidak ada data jadwal tersedia</td>
											</tr>
										@else
              <table id="example3" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Hari</th>
                    <th>Jam Ke</th>
                    <th>Jumlah Jam</th>
                    <th>Mata Pelajaran</th>
                    <th>Guru</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                @foreach($data_jadwal as $jadwal)
                  <tr>
                    <td>{{$jadwal->hari}}</td>
                    <td>{{$jadwal->jamke}}</td>
                    <td>{{$jadwal->jumlahjam}}</td>
                    <td>{{$jadwal->mapel}}</td>
                    <td>{{$jadwal->guru}}</td>
                    
                    <td>
                      @if(!in_array(auth()->user()->role, ['siswa', 'ketuakelas']) && !auth()->user()->hasRole('siswa') && !auth()->user()->hasRole('ketuakelas'))
                          <button type="button" class="btn btn-success btn-sm" 
                          data-myid="{{$jadwal->id}}"
                          data-mykelas="{{$jadwal->kelas}}"

                          data-myjamke="{{$jadwal->jamke}}"
                          data-myjumlahjam="{{$jadwal->jumlahjam}}"
                          data-mymapel="{{$jadwal->mapel}}"
                          data-myguru="{{$jadwal->guru}}"
                          data-mymateri="{{$jadwal->materi}}"
                          data-mycatatan="{{$jadwal->catatan}}"
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

                  data-bs-toggle="modal" data-bs-target="#editjadwal">Tambah Jurnal</button>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>

                </tfoot>
              </table>
            @endif
            </div>
            <!-- /.card-body -->


          </div>

        </div>


        @if($data_jadwal->isEmpty())
											<tr>
												<!-- <td colspan="11" class="text-center">Tidak ada data jurnal tersedia</td> -->
											</tr>
										@else
<!-- Modal Tambah Jurnal -->
<div class="modal fade" id="editjadwal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        
        <h4 class="modal-title" id="exampleModalLabel">Tambah Jurnal</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/susulan/updatesusulan" method="POST"> 
                  {{csrf_field()}}

                  <div class="form-group">
                <label for="exampleInputEmail1">TANGGAL</label>
                <input name="tgl" type="date" class="form-control" id="tgl" aria-describedby="emailHelp" required>
              </div>
               
        <div class="form-group">
                <label >Kelas</label>

                <input name="kelas" type="text" class="form-control" id="kelas" aria-describedby="emailHelp" placeholder="Kelas" value="{{$jadwal->kelas}}" readonly>
                          


                </div>
              <div class="form-group">
                <label for="exampleFormControlSelect1">Keterangan Guru Mapel</label>
                <select name="ket_guru_mapel" class="form-control" id="exampleFormControlSelect1">
                  <option value="Hadir">Hadir</option>
                  <option value="Tidak Masuk">Tidak Masuk</option>
                </select>
              </div>          
              <div class="form-group">
                <label for="exampleFormControlSelect1">Penugasan (Diisi Jika Guru Tidak Masuk)</label>
                <select name="penugasan" class="form-control" id="exampleFormControlSelect1">
                  <option value="Tidak Ada">Tidak Ada</option>
                  <option value="Ada">Ada</option>
                </select>
              </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Jam Ke</label>
                <input name="jamke" type="text" class="form-control" id="jamke" aria-describedby="emailHelp" placeholder="Diisi 1-11" value="{{$jadwal->jamke}}" readonly>
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jumlah Jam</label>
                <input name="jumlahjam" type="text" class="form-control" id="jumlahjam" aria-describedby="emailHelp" placeholder="Jumlah Jam" value="{{$jadwal->jumlahjam}}" readonly>
              </div>
              <div class="form-group">
                <label >Mata Pelajaran</label>
                <input name="mapel" type="text" class="form-control" id="mapel" aria-describedby="emailHelp" placeholder="mapel" value="{{$jadwal->mapel}}" readonly>

                </div>
             
                <div class="form-group">
                <label >Guru</label>
                <input name="guru" type="text" class="form-control" id="guru" aria-describedby="emailHelp" placeholder="guru" value="{{$jadwal->guru}}" readonly>

              <div class="form-group">
                <label for="exampleFormControlTextarea1">Materi</label>
                <textarea name="materi" class="form-control" id="materi" rows="3" >{{$jadwal->materi}}</textarea>
              </div>
              <div class="form-group">
                <label for="exampleFormControlTextarea1">Catatan</label>
                <textarea name="catatan" class="form-control" id="catatan" rows="3" >{{$jadwal->catatan}}</textarea>
              </div>

               <div class="form-group">

                <input name="j1" type="hidden" class="form-control" id="j1" aria-describedby="emailHelp" value="{{$jadwal->j1}}" >
                </div>

                <div class="form-group">
                <input name="j2" type="hidden" class="form-control" id="j2" aria-describedby="emailHelp" value="{{$jadwal->j2}}" >
                </div>

                <div class="form-group">  
                <input name="j3" type="hidden" class="form-control" id="j3" aria-describedby="emailHelp" value="{{$jadwal->j3}}" >
                </div>

                <div class="form-group">
                <input name="j4" type="hidden" class="form-control" id="j4" aria-describedby="emailHelp" value="{{$jadwal->j4}}" >
                </div>

                <div class="form-group">
                <input name="j5" type="hidden" class="form-control" id="j5" aria-describedby="emailHelp" value="{{$jadwal->j5}}" >
                </div>

                <div class="form-group">
                <input name="j6" type="hidden" class="form-control" id="j6" aria-describedby="emailHelp" value="{{$jadwal->j6}}" >
                </div>

                <div class="form-group">
                 <input name="j7" type="hidden" class="form-control" id="j7" aria-describedby="emailHelp" value="{{$jadwal->j7}}" >
                </div>

                <div class="form-group">
                <input name="j8" type="hidden" class="form-control" id="j8" aria-describedby="emailHelp" value="{{$jadwal->j8}}" >
                </div>

                <div class="form-group">
                <input name="j9" type="hidden" class="form-control" id="j9" aria-describedby="emailHelp" value="{{$jadwal->j9}}" >
                </div>

                <div class="form-group">
                <input name="j10" type="hidden" class="form-control" id="j10" aria-describedby="emailHelp" value="{{$jadwal->j10}}" >
                </div>

                <div class="form-group">
                <input name="j11" type="hidden" class="form-control" id="j11" aria-describedby="emailHelp" value="{{$jadwal->j11}}" >
                </div>

              <div class="form-group">
                <input name="waktu" type="hidden" class="form-control" id="waktu" aria-describedby="emailHelp" placeholder="Waktu" value="{{$jadwal->waktu}}">
              </div>

                          
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Tambah Jurnal</button>
            </form>
      </div>

    </div>
  </div>
</div>
@endif

<!-- Modal Tambah Absen Susulan -->
<div class="modal fade" id="tambahabsen" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Tambah Absensi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form action="/susulan/absensusulan" method="POST"> 
                  {{csrf_field()}}
              
              <div class="form-group">
                <label for="exampleInputEmail1">TANGGAL</label>
                <input name="tgl" type="date" class="form-control" id="tgl" aria-describedby="emailHelp" required>
              </div>

              <div class="form-group">
                <label >Nama Siswa</label>
                <select name="nama" class="form-control" >
                @foreach($sis_wa as $siswa)
                  <option value="{{$siswa->nama}}">{{$siswa->nama}}</option>
                @endforeach                 
                </select>

              <div class="form-group">
                <label for="exampleInputEmail1">Kelas</label>
                <input name="kelas" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="{{ $myClass }}" readonly>
              </div>

                </div>
              <div class="form-group">
                <label for="exampleFormControlSelect1">Keterangan</label>
                <select name="ket" class="form-control" id="exampleFormControlSelect1">
                  <option value="Sakit">Sakit</option>
                  <option value="Ijin">Ijin</option>
                  <option value="Alpha">Alpha</option>
                  <option value="Dispen">Dispen</option>
                </select>
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



@endsection
