
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
      <div class="row">
        <div class="col-md-12">
          <div class="panel">     
                <div class="card-header">
                  <h3 class="panel-title">Edit Jurnal SMAN TARUNA NALA Malang</h3>

                </div>

                <div class="card-body"> 
                @if($data_jurnal->isEmpty())
											<tr>
												<td colspan="11" class="text-center">Tidak ada data jurnal tersedia</td>
											</tr>
										@else
                   <!-- <form class="form-inline" method="GET" action="/edits"> -->
                    <!-- <button type="button" class="btn btn-primary float-right mr-sm-2" data-toggle="modal" data-target="#exampleModal">Tambah Jurnal</button> -->

<!--                    <input name="filter" class="form-control mr-sm-2" type="date" >
                    <button type="submit" class="btn btn-primary mr-sm-2">Filter</button> -->

                    <!--  <input name="cari" class="form-control mr-sm-2"  placeholder="Cari" >
                    <button type="submit" class="btn btn-primary mr-sm-2">Cari</button> -->

      <!--                <select name="cari" class="form-control mr-sm-2" >
                @foreach($ke_las as $kelas)
                  <option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
                @endforeach                 
                </select>
                    <button type="submit" class="btn btn-primary mr-sm-2">Cari</button> -->

 

                  </form>
                </br>
                       
                   <table id="example3" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>AKSI</th>
                        <th>KELAS</th>
                        <th>KET GURU</th>
                        <th>PENUGASAN</th>
                        <th>JAM KE</th>
                        <th>JUMLAH JAM</th>
                        <th>MATA PELAJARAN</th>
                        <th>GURU</th>
                        <th>MATERI</th>
                        <th>CATATAN</th>
                        <th>WAKTU</th>
                        
                        
                        
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($data_jurnal as $jurnal)
                      <tr style="@if($jurnal->materi == 'Jam Kosong') background-color: #ffcccc; @elseif($jurnal->penugasan == 'Ada') background-color: #ffffcc; @endif">
                        
                        <td>
                          <!-- <a href="/jurnal/{{$jurnal->id}}/edit" class="btn btn-warning btn-sm">Edit</a> -->
                          <button type="button" class="btn btn-warning btn-sm" 
                          data-myid="{{$jurnal->id}}"
                          data-mykelas="{{$jurnal->kelas}}"
                      data-myket_guru_mapel="{{$jurnal->ket_guru_mapel}}"
                          data-mypenugasan="{{$jurnal->penugasan}}"
                          data-myjamke="{{$jurnal->jamke}}"
                          data-myjumlahjam="{{$jurnal->jumlahjam}}"
                          data-mymapel="{{$jurnal->mapel}}"
                          data-myguru="{{$jurnal->guru}}"
                     
                          data-mymateri="{{$jurnal->materi}}"
                          data-mycatatan="{{$jurnal->catatan}}"
                          

                  data-toggle="modal" data-target="#editjurnal">Edit</button>

                          
                        </td>
                        
                        <td>{{$jurnal->kelas}}</td>
                        <td>
                          @if($jurnal->ket_guru_mapel=='Tidak Masuk')
                          <font color="#ff0000">{{$jurnal->ket_guru_mapel}}
                          @else($jurnal->ket_guru_mapel=='Hadir')
                          <font color="#000000">{{$jurnal->ket_guru_mapel}}
                          @endif
                        </td>
                        <td>
                          @if($jurnal->penugasan=='Tidak Ada'&&$jurnal->ket_guru_mapel=='Tidak Masuk')
                          <font color="#ff0000">{{$jurnal->penugasan}}
                          @else($jurnal->penugasan=='Ada')
                          <font color="#000000">{{$jurnal->penugasan}}
                          @endif
                        </td>
                        <td>{{$jurnal->jamke}}</td>
                        <td>{{$jurnal->jumlahjam}}</td>
                        <td>{{$jurnal->mapel}}</td>
                        <td>{{$jurnal->guru}}</td>

                        <td>{{$jurnal->materi}}</td>
                        
                        <td>
                          @if($jurnal->catatan=='Catatan')
                          <font color="#000000">{{$jurnal->catatan}}
                          @else($jurnal->catatan!='Catatan')
                          <font color="#ff0000">{{$jurnal->catatan}}
                          @endif
                        </td>
                        <td>{{$jurnal->created_at->format('d M Y - H:i:s')}}</td>
                        
                      </tr>
                      @endforeach


                    </tbody>
                  </table>
                  @endif
                </div>
              </div>
        </div>
      </div>
    </div>
  </div>


  @if($data_jurnal->isEmpty())
											<tr>
												<!-- <td colspan="11" class="text-center">Tidak ada data jurnal tersedia</td> -->
											</tr>
										@else
<!-- Modal Edit -->
<div class="modal fade" id="editjurnal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        
        <h4 class="modal-title" id="exampleModalLabel">Edit Jurnal</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form action="/jurnal/updates" method="post"> 
                  {{csrf_field()}}
      <input type="hidden" name="jurnalid" id="jurnalid" value="">
               
        <div class="form-group">
                <label >Kelas</label>
                <input name="kelas" class="form-control" id="kelas" readonly>
            

                </div>
              <div class="form-group">
                <label for="exampleFormControlSelect1">Keterangan Guru Mapel</label>
                <select name="ket_guru_mapel" class="form-control" id="ket_guru_mapel">
                  <option value="Hadir" @if($jurnal->ket_guru_mapel=='Hadir') selected @endif>Hadir</option>
                  <option value="Tidak Masuk" @if($jurnal->ket_guru_mapel=='Tidak Masuk') selected @endif>Tidak Masuk</option>
                </select>
              </div>          
              <div class="form-group">
                <label for="exampleFormControlSelect1">Penugasan (Diisi Jika Guru Tidak Masuk)</label>
                <select name="penugasan" class="form-control" id="penugasan">
                  <option value="Tidak Ada" @if($jurnal->penugasan=='Tidak Ada') selected @endif>Tidak Ada</option>
                  <option value="Ada" @if($jurnal->penugasan=='Ada') selected @endif>Ada</option>
                </select>
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jam Ke</label>
                <input name="jamke" type="text" class="form-control" id="jamke" aria-describedby="emailHelp" placeholder="Diisi 1-11" value="{{$jurnal->jamke}}">
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jumlah Jam</label>
                <input name="jumlahjam" type="text" class="form-control" id="jumlahjam" aria-describedby="emailHelp" placeholder="Jumlah Jam" value="{{$jurnal->jumlahjam}}">
              </div>
              <div class="form-group">
                <label >Mata Pelajaran</label>
                <input name="mapel" class="form-control" id="mapel" readonly>

                </div>
             
                <div class="form-group">
                <label >Guru</label>
                <input name="guru" class="form-control" id="guru" readonly>
              
              <div class="form-group">
                <label for="exampleFormControlTextarea1">Materi</label>
                <textarea name="materi" class="form-control" id="materi" rows="3" >{{$jurnal->materi}}</textarea>
              </div>
              <div class="form-group">
                <label for="exampleFormControlTextarea1">Catatan</label>
                <textarea name="catatan" class="form-control" id="catatan" rows="3" >{{$jurnal->catatan}}</textarea>
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
@endif



@endsection


