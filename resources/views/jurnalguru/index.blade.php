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
	<div class="card shadow-sm border-0">
			<div class="row">
				<div class="col-md-12">
					<div class="panel">			
								<div class="card-header bg-light py-3">
									<h3 class="fw-bold m-0" style="color: #002366;"><i class="fas fa-edit me-2"></i>Data Jurnal Saya</h3>
								</div>




								<div class="card-body">
                                  								@if($data_jurnal->isEmpty())
											<tr>
												<td colspan="11" class="text-center">Tidak ada data jurnal tersedia</td>
											</tr>
										@else	

								  <form class="form-inline d-flex align-items-center flex-wrap gap-2" method="GET" action="/jurnalguru">
								    <div class="form-group mr-2">
								      <label for="start_date" class="mr-2">Dari Tanggal:</label>
								      <input name="start_date" id="start_date" class="form-control" type="date" value="{{ request('start_date') }}">
								    </div>
								    <div class="form-group mr-2">
								      <label for="end_date" class="mr-2">Sampai Tanggal:</label>
								      <input name="end_date" id="end_date" class="form-control" type="date" value="{{ request('end_date') }}">
								    </div>
								    <button type="submit" class="btn btn-primary mr-2">Filter</button>
								    <a href="/jurnalguru" class="btn btn-secondary mr-2">Reset</a>
								    <a href="/jurnalguru/export?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" class="btn btn-success">Download Rekap (Excel)</a>
								  </form>

								  

								  </br>
									<table id="example3" class="table table-bordered table-striped">
										<thead>
											<tr>
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
												<th>AKSI</th>
												
											</tr>
										</thead>
										<tbody>
											@foreach($data_jurnal as $jurnal)
											<tr style="@if($jurnal->materi == 'Jam Kosong') background-color: #ffcccc; @elseif($jurnal->penugasan == 'Ada') background-color: #ffffcc; @endif">
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

												<td>
													<!-- <a href="/jurnalguru/{{$jurnal->id}}/edit" class="btn btn-warning btn-sm">Edit</a> -->
													<button type="button" class="btn btn-warning btn-sm" 
													data-myid="{{$jurnal->id}}"
													data-mykelas="{{$jurnal->kelas}}"
											data-myket_guru_mapel="{{$jurnal->ket_guru_mapel}}"
													data-mypenugasan="{{$jurnal->penugasan}}"
													data-myjamke="{{$jurnal->jamke}}"
													data-myjumlahjam="{{$jurnal->jumlahjam}}"
													data-mymapel="{{$jurnal->mapel}}"
													data-myguru="{{$jurnal->guru}}"
													
													data-mydispen="{{$jurnal->dispen}}"
													data-mymateri="{{$jurnal->materi}}"
													data-mycatatan="{{$jurnal->catatan}}"

									data-bs-toggle="modal" data-bs-target="#editjurnalguru">Edit</button>
												</td>

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
												<td colspan="11" class="text-center"></td>
											</tr>
										@else	
<!-- Modal Edit -->
<div class="modal fade" id="editjurnalguru" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        
        <h4 class="modal-title" id="exampleModalLabel">Edit Jurnal</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/jurnalguru/update" method="post"> 
				        	{{csrf_field()}}
			<input type="hidden" name="jurnalid" id="jurnalid" value="">
						   
				<div class="form-group">
                <label >Kelas</label>
                <select name="kelas" class="form-control" id="kelas" readonly>
            
                @foreach($ke_las as $kelas)

              @if( $kelas->kelas == $jurnal->kelas )
                      <option value="{{ $kelas->kelas }}" selected="selected" > {{ $kelas->kelas }}</option>
                  @else
                      <option value="{{ $kelas->kelas }}"> {{ $kelas->kelas }}</option>
                  @endif
   
                @endforeach   
                          
                </select>

                </div>
              <div class="form-group">
                <label for="exampleFormControlSelect1">Keterangan Guru Mapel</label>
                <select name="ket_guru_mapel" class="form-control" id="ket_guru_mapel" readonly>
                  <option value="Hadir" @if($jurnal->ket_guru_mapel=='Hadir') selected @endif>Masuk</option>
                  <option value="Tidak Masuk" @if($jurnal->ket_guru_mapel=='Tidak Masuk') selected @endif>Tidak Masuk</option>
                </select>
              </div>          
              <div class="form-group">
                <label for="exampleFormControlSelect1">Penugasan (Diisi Jika Guru Tidak Masuk)</label>
                <select name="penugasan" class="form-control" id="penugasan" readonly>
                  <option value="Tidak Ada" @if($jurnal->penugasan=='Tidak Ada') selected @endif>Tidak Ada</option>
                  <option value="Ada" @if($jurnal->penugasan=='Ada') selected @endif>Ada</option>
                </select>
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jam Ke</label>
                <input name="jamke" type="text" class="form-control" id="jamke" aria-describedby="emailHelp" placeholder="Diisi 1-11" value="{{$jurnal->jamke}}" readonly>
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jumlah Jam</label>
                <input name="jumlahjam" type="text" class="form-control" id="jumlahjam" aria-describedby="emailHelp" placeholder="Jumlah Jam" value="{{$jurnal->jumlahjam}}" readonly>
              </div>
              <div class="form-group">
                <label >Mata Pelajaran</label>
                <select name="mapel" class="form-control" id="mapel" readonly>
                @foreach($ma_pel as $mapel)
                  @if( $mapel->mapel == $jurnal->mapel )
                      <option value="{{ $mapel->mapel }}" selected="selected"> {{ $mapel->mapel }}</option>
                    @else
                      <option value="{{ $mapel->mapel }}"> {{ $mapel->mapel }}</option>
                    @endif	
                @endforeach                 
                </select>

                </div>
             
                <div class="form-group">
                <label >Guru</label>
                <select name="guru" class="form-control" id="guru" readonly>
                @foreach($gu_ru as $guru)
                  @if( $guru->guru == $jurnal->guru )
                      <option value="{{ $guru->guru }}" selected="selected"> {{ $guru->guru }}</option>
                    @else
                      <option value="{{ $guru->guru }}"> {{ $guru->guru }}</option>
                    @endif
                @endforeach                 
                </select>
             
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
				        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				 		<button type="submit" class="btn btn-primary">Update</button>
						</form>
      </div>

    </div>
  </div>
</div>
</div>
        @endif
@endsection


