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
									<h3 class="fw-bold m-0" style="color: #004d1a;"><i class="fas fa-file-alt me-2"></i>Data Permohonan Izin Guru & Pegawai</h3>
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

								    <button type="button" class=" btn btn-success btn-sm " data-bs-toggle="modal" data-bs-target="#rk">Rekap Kehadiran</button>

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
												<th>LAMPIRAN</th>
												<th>STATUS</th>
												<th>WAKTU IJIN</th>
												<th>AKSI</th>
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
														<span class="badge bg-warning text-dark">Terlambat</span>
													@elseif($ijin->sia == 'Sakit')
														<span class="badge bg-info">Sakit</span>
													@elseif($ijin->sia == 'Ijin')
														<span class="badge bg-success">Ijin</span>
													@else
														<span class="badge bg-danger">Alpha</span>
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
												<td>
													@if($ijin->attachment)
														<a href="{{ asset($ijin->attachment) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fas fa-file-alt"></i> Bukti</a>
													@else
														<span class="text-muted small">-</span>
													@endif
												</td>
												<td>
													@if($ijin->approval_status == 'pending')
														<span class="badge bg-warning text-dark"><i class="fas fa-spinner fa-spin me-1"></i> Pending</span>
													@elseif($ijin->approval_status == 'approved')
														<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Disetujui</span>
													@else
														<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Ditolak</span>
													@endif
												</td>
												<td>{{$ijin->created_at->format('d M Y - H:i')}}</td>
												<td>
													<div class="d-flex gap-1 flex-wrap">
														@if(auth()->user()->role == 'admin')
															@if($ijin->approval_status == 'pending')
																<form action="{{ route('ijin.approve', $ijin->id) }}" method="POST" class="d-inline">
																	@csrf
																	<button type="submit" class="btn btn-xs btn-success text-white"><i class="fas fa-check"></i></button>
																</form>
																<form action="{{ route('ijin.reject', $ijin->id) }}" method="POST" class="d-inline">
																	@csrf
																	<button type="submit" class="btn btn-xs btn-danger text-white"><i class="fas fa-times"></i></button>
																</form>
															@endif
															<button type="button" class="btn btn-warning btn-xs text-white" 
																data-myid="{{$ijin->id}}"
																data-mytglmasuk="{{$ijin->tglmasuk}}"
																data-myguru="{{$ijin->guru}}"
																data-mymapel="{{$ijin->mapel}}"
																data-mysia="{{$ijin->sia}}"
																data-myjumlah="{{$ijin->jumlah}}"
																data-myjam_terlambat="{{$ijin->jam_terlambat}}"
																data-myket="{{$ijin->ket}}"
																data-bs-toggle="modal" data-bs-target="#edit">Edit</button>
															<a href="/ijin/{{$ijin->id}}/delete" class="btn btn-danger btn-xs text-white" onclick="return confirm('Hapus izin ini?')">Hapus</a>
														@else
															@if($ijin->approval_status == 'pending')
																<a href="/ijin/{{$ijin->id}}/delete" class="btn btn-danger btn-xs text-white" onclick="return confirm('Batalkan izin ini?')">Batal</a>
															@else
																<span class="text-muted small">No Action</span>
															@endif
														@endif
													</div>
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
				    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
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
				        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Download</button>
            </form>
      </div>

    </div>
  </div>
</div>



@endif

@endsection

