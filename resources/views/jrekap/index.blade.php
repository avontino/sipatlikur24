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
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
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
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
          </div>
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
             <form class="form-inline ms-auto" action="/jrekap" method="get" >

                    <h3 class="mr-sm-5 card-title">Rekap Jurnal</h3>

<!--                     <button type="button" class=" mr-sm-4 btn btn-primary btn-sm " data-bs-toggle="modal" data-bs-target="#tambahjadwal">Tambah Jadwal</button> -->

                    <!-- <a href="/siswa/export" class="mr-sm-3 btn btn-sm btn-primary">Export</a> -->
                    @if(auth()->user()->role=='admin' OR auth()->user()->role=='guru' OR auth()->user()->role=='lihat')
                     <a href="/jrekap/export" class="btn btn-sm btn-primary mr-sm-5">Export</a>

                    <input name="tgl" type="date" class="form-control-sm mr-sm-2"> 
                    <!-- <a href="/jrekap/cek" method="get" class="btn btn-sm btn-primary mr-sm-2">Sinkron</a>        -->
                    <button type="submit" class="btn btn-sm btn-primary mr-sm-5" name="action" value="sinkron">Sinkron</button>

                    
                    <select name="kelas" class="form-control-sm mr-sm-2" >
                    @foreach($ke_las as $kelas)
                    <option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
                    @endforeach                 
                    </select>        
                    <button type="submit" class="btn btn-sm btn-primary mr-sm-5" name="action" value="kelas">Filter Kelas</button>

                    <input name="crtgl" type="date" class="form-control-sm mr-sm-2"> 
                    <button type="submit" class="btn btn-sm btn-primary mr-sm-4" name="action" value="tanggal">Filter Tanggal</button>
<!--                     <input name="tgl" type="date" class="form-control mr-sm-2"> 
                    <button type="submit" class="btn btn-primary mr-sm-2">Cari Kelas</button> -->
                    
                    <button type="button" class=" btn btn-success btn-sm " data-bs-toggle="modal" data-bs-target="#rl">Rekap Laporan</button>

                    @endif
                    @if(auth()->user()->role=='ketuakelas')
                    <a href="/jrekap/exportkelas" class="btn btn-sm btn-primary mr-sm-2">Download</a>
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
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5</th>
                    <th>6</th>
                    <th>7</th>
                    <th>8</th>
                    <th>9</th>
                    <th>10</th>
                    <th>11</th>
                    <th>Tanggal</th>

                  </tr>
                </thead>
                <tbody>
                @foreach($data_jrekap as $jrekap)
                  <tr>
                    <td>{{$jrekap->kelas}}</td>
                    <!-- warna tabel -->
                    @if($jrekap->j1=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j1}} </td>                    
                    @else($jrekap->j1!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j1}} </td>
                    @endif

                    @if($jrekap->j2=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j2}} </td>                    
                    @else($jrekap->j2!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j2}} </td>
                    @endif

                    @if($jrekap->j3=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j3}} </td>                    
                    @else($jrekap->j3!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j3}} </td>
                    @endif

                    @if($jrekap->j4=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j4}} </td>                    
                    @else($jrekap->j4!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j4}} </td>
                    @endif   

                    @if($jrekap->j5=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j5}} </td>                    
                    @else($jrekap->j5!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j5}} </td>
                    @endif

                                        @if($jrekap->j6=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j6}} </td>                    
                    @else($jrekap->j6!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j6}} </td>
                    @endif  

                                        @if($jrekap->j7=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j7}} </td>                    
                    @else($jrekap->j7!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j7}} </td>
                    @endif              

                                        @if($jrekap->j8=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j8}} </td>                    
                    @else($jrekap->j8!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j8}} </td>
                    @endif    

                                        @if($jrekap->j9=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j9}} </td>                    
                    @else($jrekap->j9!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j9}} </td>
                    @endif            

                                        @if($jrekap->j10=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j10}} </td>                    
                    @else($jrekap->j10!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j10}} </td>
                    @endif 

                                        @if($jrekap->j11=='0')
                    <td style="background-color: #ff0000">{{$jrekap->j11}} </td>                    
                    @else($jrekap->j11!='0')      
                    <td style="background-color: #32CD32">{{$jrekap->j11}} </td>
                    @endif
                    <td>{{$jrekap->created_at->format('d-m-Y')}}</td>

                    
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

<!-- MODAL EXPORT PERTANGGAL -->
<div class="modal fade" id="rl" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Export Laporan</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/jrekap/exportlaporan" method="GET"> 
                  {{csrf_field()}}


              <div class="form-group">
                <label for="exampleInputEmail1">DARI TANGGAL</label>
                <input name="tglawal" type="date" class="form-control" id="tgl" aria-describedby="emailHelp" required>
              </div>  

              <div class="form-group">
                <label for="exampleInputEmail1">SAMPAI TANGGAL</label>
                <input name="tglakhir" type="date" class="form-control" id="tgl" aria-describedby="emailHelp" required>
              </div>   
              

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Download</button>
            </form>
      </div>

    </div>
  </div>
</div>





@endsection
