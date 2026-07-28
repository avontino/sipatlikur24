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
                  <h3 class="panel-title">Tambah Jurnal</h3>
                </div>
              </div>
                 <div class="card-body">
                  
              <form action="/jurnal/create" method="POST"> 
                  {{csrf_field()}}

                 
              <div class="form-group">
                <label >Kelas</label>

                <input name="kelas" value="{{auth()->user()->name}}" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" readonly>
                

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
                <input value="0" name="jamke" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Diisi 1-11">
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jumlah Jam</label>
                <input value="0" name="jumlahjam" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Jumlah Jam">
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
                <label for="exampleFormControlTextarea1">Absen Siswa Tidak Masuk</label>
                <textarea name="absen" class="form-control" id="exampleFormControlTextarea1" rows="3" >NIHIL</textarea>
              </div>

              <div class="form-group">
                <label for="exampleFormControlTextarea1">Dispensasi</label>
                <textarea name="dispen" class="form-control" id="exampleFormControlTextarea1" rows="3" >NIHIL</textarea>
              </div>

              <div class="form-group">
                <label for="exampleFormControlTextarea1">Materi</label>
                <textarea name="materi" class="form-control" id="exampleFormControlTextarea1" rows="3" >Materi</textarea>
              </div>

              <div class="form-group">
                <label for="exampleFormControlTextarea1">Catatan</label>
                <textarea name="catatan" class="form-control" id="exampleFormControlTextarea1" rows="3" >Catatan</textarea>
              </div>

              <div class="form-group">
                <input name="waktu" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Waktu" value="{{ date('H:i:s') }}">
              </div>

              <div class="form-group">
                
                <input name="guru_id" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" >
                
              </div>


              

              <button type="submit" class="btn btn-primary">Tambah</button>


                </div>
              </div>
        </div>
      </div> 
     <!--  batas row -->
</form>
</div>
</div>


@endsection