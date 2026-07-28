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
									<h3 class="panel-title">Data Ijin SMAN TARUNA NALA Malang</h3>

								</div>

								<div class="card-body">
								@if($data_ijin->isEmpty())
											<tr>
												<td colspan="12" class="text-center">Tidak ada data ijin tersedia</td>
											</tr>
										@else	

								  <form class="form-inline" method="GET" action="/ijin">

								    <input name="filter" class="form-control mr-sm-2" type="date" >
								    <button type="submit" class="btn btn-primary mr-sm-2">Filter</button>

								    <a href="/ijin/export" class="btn btn-sm btn-primary mr-sm-2">Export</a>

								    <button type="button" class=" btn btn-success btn-sm " data-toggle="modal" data-target="#rk">Rekap Kehadiran</button>

								  </form>

								  </br>
									 <table id="example3" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>TANGGAL IJIN</th>
												<th>NAMA GURU</th>
												<th>MATA PELAJARAN</th>
												<th>S/I/A/T</th>
												<th>JUMLAH HARI</th>
												<th>JAM TERLAMBAT</th>
												<th>KETERANGAN</th>
												<th>WAKTU IJIN</th>
												@if(auth()->user()->role=='admin')
												<th>AKSI</th>
												@endif
												
											</tr>
										</thead>
										<tbody>
											@foreach($data_ijin as $ijin)
											<tr>
												<td>{{$ijin->tglmasuk}}</td>
												<td>{{$ijin->guru}}</td>
												<td>{{$ijin->mapel}}</td>
												<td>
													@if($ijin->sia == 'Terlambat')
														<span class="badge badge-warning">Terlambat</span>
													@elseif($ijin->sia == 'Sakit')
														<span class="badge badge-info">Sakit</span>
													@elseif($ijin->sia == 'Ijin')
														<span class="badge badge-success">Ijin</span>
													@else
														<span class="badge badge-danger">Alpha</span>
													@endif
												</td>
												<td>{{$ijin->jumlah}}</td>
												<td>
													@if($ijin->jam_terlambat)
														{{date('H:i', strtotime($ijin->jam_terlambat))}}
													@else
														-
													@endif
												</td>
												<td>{{$ijin->ket}}</td>
												<td>{{$ijin->created_at->format('d M Y - H:i:s')}}</td>
												@if(auth()->user()->role=='admin')
												<td>
													<button type="button" class="btn btn-warning btn-sm" 
													data-myid="{{$ijin->id}}"
													data-mytglmasuk="{{$ijin->tglmasuk}}"
													data-myguru="{{$ijin->guru}}"
													data-mymapel="{{$ijin->mapel}}"
													data-mysia="{{$ijin->sia}}"
													data-myjumlah="{{$ijin->jumlah}}"
													data-myjamterlambat="{{$ijin->jam_terlambat}}"
													data-myket="{{$ijin->ket}}"
													data-toggle="modal" data-target="#editijin">Edit</button>

													<a href="/ijin/{{$ijin->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>
												</td>
												@endif
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


@if($data_ijin->isEmpty())
											<tr>
												<!-- <td colspan="12" class="text-center">Tidak ada data jadwal tersedia</td> -->
											</tr>
										@else
<!-- Modal Edit -->
<div class="modal fade" id="editijin" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Edit Ijin Absen</h5>
				    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        <form action="/ijin/update" method="POST"> 
				        	{{csrf_field()}}
				        	<input type="hidden" name="ijinid" id="ijinid" value="">
						   
					<div class="form-group">
						    <label >Hari/Tanggal Ijin</label>
							<input name="tglmasuk" type="date" class="form-control" id="modal_tglmasuk" aria-describedby="emailHelp">
						  	</div>
					
						  	<div class="form-group">
						    <label >Guru</label>
					    	<input name="guru" type="text" class="form-control" id="modal_guru" aria-describedby="emailHelp" readonly>
							</div>

						  <div class="form-group">
						    <label >Mata Pelajaran</label>
						    <select name="mapel" class="form-control" id="modal_mapel">
						    @foreach($ma_pel as $mapel)
						    	<option value="{{ $mapel->mapel }}"> {{ $mapel->mapel }}</option>
						    @endforeach						    	
						    </select>

						  	</div>

						  <div class="form-group">
						    <label for="exampleFormControlSelect1">S/I/A/T</label>
						    <select name="sia" class="form-control" id="modal_sia" onchange="toggleModalJamTerlambat()">
						      <option value="Sakit">Sakit</option>
						      <option value="Ijin">Ijin</option>
						      <option value="Alpha">Alpha</option>
						      <option value="Terlambat">Terlambat</option>
						    </select>
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Jumlah Hari</label>
						    <input name="jumlah" type="text" class="form-control" id="modal_jumlah" aria-describedby="emailHelp">
						  </div>

						  <div class="form-group" id="modal_jam_terlambat_group" style="display: none;">
						    <label for="modal_jam_terlambat">Jam Terlambat (HH:MM)</label>
						    <input name="jam_terlambat" type="time" class="form-control" id="modal_jam_terlambat">
						  </div>

						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Keterangan</label>
						    <textarea name="ket" class="form-control" id="modal_ket" rows="3"></textarea>
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

<!-- MODAL EXPORT PERTANGGAL -->
<div class="modal fade" id="rk" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Rekap Kehadiran</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form action="/ijin/rekaphadir" method="GET"> 
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Download</button>
            </form>
      </div>

    </div>
  </div>
</div>



@endif

@endsection

