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
									<h3 class="fw-bold m-0" style="color: #002366;"><i class="fas fa-exclamation-triangle me-2"></i>Data Pelaporan Kasus SMAN TARUNA NALA Malang</h3>
								</div>

								<div class="card-body">
								@if($data_kasus->isEmpty())
											<tr>
												<td colspan="11" class="text-center">Tidak ada data kasus tersedia</td>
											</tr>
										@else	

								  <form class="form-inline" method="GET" action="/kasus">
								  	<button type="button" class="btn btn-primary float-end mr-sm-2" data-bs-toggle="modal" data-bs-target="#exampleModal">Tambah Pelaporan</button>

								    <input name="filter" class="form-control mr-sm-2" type="date" >
								    <button type="submit" class="btn btn-primary mr-sm-2">Filter</button>


								    <a href="/kasus/export" class="btn btn-sm btn-primary mr-sm-2">Export</a>					  
								   </form>

									<table id="example3" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>KEJADIAN KASUS</th>
												<th>TEMPAT</th>
												<th>PELAPOR</th>
												<th>WAKTU</th>
												<th>AKSI</th>
											</tr>
										</thead>
										<tbody>
											@foreach($data_kasus as $kasus)
											<tr>
												<td>{{$kasus->kejadian}}</td>
												<td>{{$kasus->tempat}}</td>
												<td>{{$kasus->pelapor}}</td>
												<td>{{$kasus->created_at->format('d M Y')}}</td>
												<td>
													<a href="/kasus/{{$kasus->id}}/delete" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus laporan ini?')">Hapus</a>
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

		@if($data_kasus->isEmpty())
											<tr>
												<!-- <td colspan="11" class="text-center">Tidak ada data jurnal tersedia</td> -->
											</tr>
										@else
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Tambah Pelaporan Kasus</h5>
				    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        <form action="/kasus/create" method="POST"> 
				        	{{csrf_field()}}
						   <div class="form-group">
						    <label for="exampleInputEmail1">Pelapor</label>
						    <input name="pelapor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Nama Pelapor">
						  </div>

						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Kasus</label>
						    <textarea name="kejadian" class="form-control" id="exampleFormControlTextarea1" rows="3" ></textarea>
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Tempat</label>
						    <input name="tempat" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Tempat Kasus">
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

<!-- Modal Edit -->
<div class="modal fade" id="editkasus" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Tambah Pelaporan Kasus</h5>
				    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        <form action="/kasus/update" method="POST"> 
				        	{{csrf_field()}}
				        	<input type="hidden" name="kasusid" id="kasusid" value="">
						   <div class="form-group">
						    <label for="exampleInputEmail1">Pelapor</label>
						    <input name="pelapor" type="text" class="form-control" id="pelapor" aria-describedby="emailHelp" value="{{$kasus->pelapor}}" placeholder="Nama Pelapor">
						  </div>

						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Kejadian Kasus</label>
						    <textarea name="kejadian" class="form-control" id="kejadian" rows="3" >{{$kasus->kejadian}}</textarea>
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Tempat</label>
						    <input name="tempat" type="text" class="form-control" id="tempat" aria-describedby="emailHelp" value="{{$kasus->tempat}}" placeholder="Tempat Kasus">
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
	@endif

@endsection


