@extends('layouts.master')

@section('content')  
</br>
<section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row mb-auto">
          <div class="col">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{$sakit}}</h3>

                <p>Total Siswa Sakit Sampai Hari Ini</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>{{$ijin}}</h3>

                <p>Total Siswa Ijin Sampai Hari Ini</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-secondary">
              <div class="inner">
                <h3>{{$alpha}}</h3>

                <p>Total Siswa Alpha Sampai Hari Ini</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
          </div>

      </section>  

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


<!--Tabel Absen SIswa-->
<div class="card">

            <div class="card-header">
            <div class="row">
          <div class="col-sm">
             <form class="form-inline ml-auto" method="GET" action="/absen">
                    <h3 class="mr-sm-5 card-title">Absen Siswa </h3>
          </div>
          </div>
          </br>
          <div class="row">
          <div class="col-sm">
                    <input name="crtgl" type="date" class="form-control-sm "> 
                    <button type="submit" class="btn btn-sm btn-primary " name="action" value="tanggal">Filter Tanggal</button>
          </div>
          
          <div class="col-sm">
                    @if(auth()->user()->role=='admin' OR auth()->user()->role=='kesiswaan' OR auth()->user()->role=='kurikulum' OR auth()->user()->role=='guru')
                    <select name="kelas" class="form-control-sm " >
                    @foreach($ke_las as $kelas)
                    <option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
                    @endforeach                 
                    </select>        
                    <button type="submit" class="btn btn-sm btn-primary " name="action" value="kelas">Filter Kelas</button>
            </div>
            <div class="col-sm">
                    <button type="submit" class="btn btn-sm btn-primary " name="action" value="kelastgl">Filter Kelas & Tanggal</button>
             </div>
                    
            <div class="col-sm">
                    <button type="button" class="btn btn-primary btn-sm " data-toggle="modal" data-target="#tambahabsen">Tambah Absensi</button>

                     <a href="/absen/export" class="btn btn-sm btn-primary ">Rekap Absensi</a>
                     @endif
             </div>
            </form>
          
        
            </div>
           
            </div>


            <!-- /.card-header -->
            <div class="card-body">
              <table id="example3" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Keterangan</th>
                    <th>Waktu</th>
                    @if(auth()->user()->role=='admin')
                    <th>Aksi</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                @foreach($ab_sen as $absen)
                  <tr>
                    <td>{{$absen->nama}}</td>
                    <td>{{$absen->kelas}}</td>
                    <td>{{$absen->ket}}</td>
                    <td>{{$absen->created_at->format ('d-m-Y H:m:s')}}</td>

                    @if(auth()->user()->role=='admin')
                    <td>


                  <a href="/absen/{{$absen->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>

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

<!-- Modal Tambah Absen-->
<div class="modal fade" id="tambahabsen" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Tambah Absensi</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form action="/absen/create" method="POST"> 
                  {{csrf_field()}}
              
              <div class="form-group">
                <label for="exampleInputEmail1">TANGGAL</label>
                <input name="tgl" type="date" class="form-control" id="tgl" aria-describedby="emailHelp" >
              </div>

             <!-- Dropdown Kelas -->
          <div class="form-group">
            <label for="kelasSelectModal">Kelas</label>
            <select name="kelas" id="kelasSelectModal" class="form-control" required>
              <option value="">Pilih Kelas</option>
              @foreach($ke_las as $kelas)
                <option value="{{ $kelas->kelas }}">{{ $kelas->kelas }}</option>
              @endforeach                 
            </select>
          </div>

          <!-- Dropdown Nama Siswa -->
          <div class="form-group">
            <label for="studentSelectModal">Nama Siswa</label>
            <select name="nama" id="studentSelectModal" class="form-control" required disabled>
              <option value="">Pilih Nama Siswa</option>
              @foreach($sis_wa as $siswa)
                <option value="{{ $siswa->nama }}" data-kelas="{{ $siswa->kelas }}" style="display: none;">{{ $siswa->nama }}</option>
              @endforeach                 
            </select>
          </div>
          
              <div class="form-group">
                <label for="exampleFormControlSelect1">Keterangan</label>
                <select name="ket" class="form-control" id="exampleFormControlSelect1">
                  <option value="Sakit">Sakit</option>
                  <option value="Ijin">Ijin</option>
                  <option value="Alpha">Alpha</option>
                  <option value="Dispen">Terlambat</option>
                </select>
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


@endsection
