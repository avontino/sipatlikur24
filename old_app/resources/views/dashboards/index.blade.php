@extends('layouts.master')

@section('content')    

<script type="text/javascript">
   setTimeout(function(){
       location.reload();
   },20000);
</script>

    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-auto">
          <div class="col-sm-6">
            <!-- <h1>Dashboard</h1> -->
          </div>
         <!--  <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Blank Page</li>
            </ol>
          </div> -->
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
         <div class="row">
         @if(auth()->user()->role=='siswa')
                    <div class="col">
            <!-- small box -->
            <div class="small-box bg-dark">
              <div class="inner">
                <h3>{{$status}}</h3>

                <p>Hari Ini </p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
            
          </div>
           
           <div class="col">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>{{$tagihan_komite}}</h3>

                <p>Dana Komite </p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
            
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>{{$tagihan_lain}}</h3>

                <p>Tagihan Lain </p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
            
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>0</h3>

                <p>Total Poin Pelanggaran</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3>0</h3>

                <p>Total Poin Prestasi</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>0</h3>

                <p>Total Poin Siswa</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
          </div>
          @endif
          <div class="col">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{$sakit}}</h3>

                <p>Total Siswa Sakit </p>
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

                <p>Total Siswa Ijin </p>
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

                <p>Total Siswa Alpha</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!--<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>-->
            </div>
          </div>

          
          
            </div> 

         
     

         
        <div class="row">
            
        @if(auth()->user()->role=='guru')
          <div class="col">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3>{{$absenguru}} <sup style="font-size: 20px">Hari</sup></h3>

                <p>{{auth()->user()->name}} Tidak Masuk</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="/ijin" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
@endif

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
               <h3>{{$ijinpesiar}} <sup style="font-size: 20px">Ijin Pesiar</sup></h3>

                <p>Anak Ijin Hari Ini</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="/ijinsiswa" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          
          <div class="col">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{$ijinbermalamwajib}} <sup style="font-size: 20px">Ijin Bermalam Wajib</sup></h3>

                <p>Anak Ijin Hari Ini</p>

              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="/ijinsiswa" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>{{$ijinbermalamresmi}} <sup style="font-size: 20px">Ijin Bermalam Resmi</sup></h3>

                <p>Anak Ijin Hari Ini</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="/ijinsiswa" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col">
            <!-- small box -->
            <div class="small-box bg-secondary">
              <div class="inner">
                <h3>{{$ijinjalan}} <sup style="font-size: 20px">Ijin Jalan</sup></h3>

                <p>Anak Ijin Hari Ini</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="/ijinsiswa" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
               <h3>{{$ijinkhusus}} <sup style="font-size: 20px">Ijin Khusus</sup></h3>

                <p>Anak Ijin Hari Ini</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="/ijinsiswa" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
         

        </div>
      </div>
      </section>

    <section class="content">

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Dashboard</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
<!--               <i class="fas fa-minus"></i></button>
            <button type="button" class="btn btn-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fas fa-times"></i></button> -->
          </div>
        </div>
        <div class="card-body">
          Aplikasi Sistem Informasi SMAN TARUNA NALA

        </div>
        <!-- /.card-body -->
        <div class="card-footer">
          Footer
        </div>
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->

    </section>

@endsection