@extends('layouts.master')

@section('content')
<section class="content-header">
      <div class="container-fluid">
	@if(session('sukses'))
	<div class="alert alert-success alert-dismissible" role="alert">
										<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
										<i class="fa fa-check-circle"></i> 

  	{{session('sukses')}}
	</div>
	@endif
        <!-- Filter Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0 fw-bold card-title"><i class="fas fa-filter me-2"></i> Filter Buku Tamu</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="/tamu" class="row align-items-end g-3">
                    <div class="col-md-4 col-sm-6 col-12">
                        <label for="filter" class="form-label small fw-bold text-secondary">Tanggal Kunjungan</label>
                        <input type="date" name="filter" id="filter" class="form-control" value="{{ request('filter') }}">
                    </div>
                    <div class="col-md-4 col-sm-6 col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="/tamu" class="btn btn-secondary"><i class="fas fa-undo me-1"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
			<div class="row">
				<div class="col-md-12">
					<div class="panel">			
								<div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
									<h5 class="m-0 fw-bold text-dark card-title"><i class="fas fa-users me-2"></i> Data Tamu SMAN TARUNA NALA Malang</h5>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambah">
                                            <i class="fas fa-plus me-1"></i> Tambah Tamu
                                        </button>
                                        <a href="/tamu/export?filter={{ request('filter') }}" class="btn btn-sm btn-success text-white">
                                            <i class="fas fa-file-excel me-1"></i> Export Excel
                                        </a>
                                    </div>
								</div>

								<div class="card-body">
									<table id="example3" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>NAMA TAMU</th>
												<th>ALAMAT TAMU</th>
												<th>EMAIL</th>
												<th>INSTANSI</th>
												<th>MAKSUD/TUJUAN</th>
												<th>NO.TELPON</th>
												<th>WAKTU</th>
												<th>AKSI</th>
												
											</tr>
										</thead>
										<tbody>
											@foreach($data_tamu as $tamu)
											<tr>
												<td>{{$tamu->nama}}</td>	
												<td>{{$tamu->alamat}}</td>
												<td>{{$tamu->email}}</td>
												<td>{{$tamu->instansi}}</td>	
												<td>{{$tamu->maksud}}</td>
												<td>{{$tamu->telp}}</td>
												<td>{{$tamu->created_at->format('d M Y - H:i:s')}}</td>
												<td>

													<button type="button" class="btn btn-warning btn-sm" 
													data-myid="{{$tamu->id}}"
													data-mynama="{{$tamu->nama}}"
													data-myalamat="{{$tamu->alamat}}"
													data-myemail="{{$tamu->email}}"
													data-myinstansi="{{$tamu->instansi}}"
													data-mymaksud="{{$tamu->maksud}}"
													data-mytelp="{{$tamu->telp}}"

													data-bs-toggle="modal" data-bs-target="#edittamu">Edit</button>

								  					<a href="/tamu/{{$tamu->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>
 													</form>

												</td>
											</tr>
											@endforeach

										</tbody>
									</table>
				
								</div>
							</div>
				</div>
			</div>
		</div>	
	</div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambah" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <h4 class="modal-title" id="myModalLabel">Tambah tamu</h4>
      </div>
      <div class="modal-body">
        <form action="/tamu/create" method="POST"> 
				        	{{csrf_field()}}
	
						   <div class="form-group">
						    <label for="exampleInputEmail1">NAMA TAMU</label>
						    <input name="nama" type="text" class="form-control" id="nama" aria-describedby="emailHelp" placeholder="Nama Tamu">
						  </div>	

						  <div class="form-group">
						    <label for="exampleInputEmail1">ALAMAT TAMU</label>
						    <textarea name="alamat" class="form-control" id="alamat" rows="2" ></textarea>
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">EMAIL</label>
						    <input name="email" type="text" class="form-control" id="email" aria-describedby="emailHelp" placeholder="Email Tamu">
						  </div>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">INSTANSI</label>
						    <input name="instansi" type="text" class="form-control" id="email" aria-describedby="instansi" placeholder="Instansi Tamu">
						  </div>
						  <div class="form-group">
						    <label for="exampleInputEmail1">MAKSUD/TUJUAN</label>
						    <textarea name="maksud" class="form-control" id="maksud" rows="2" ></textarea>
						  </div>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">NO.TELPON</label>
						    <input name="telp" type="text" class="form-control" id="telp" aria-describedby="emailHelp" placeholder="No. Telp">
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

<!-- Modal Edit -->
<div class="modal fade" id="edittamu" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <h4 class="modal-title" id="myModalLabel">Edit Operator</h4>
      </div>
      <div class="modal-body">
        <form action="/tamu/update" method="post"> 
				        	{{csrf_field()}}
				        	<input type="hidden" name="tamuid" id="tamuid" value="">
						  <div class="form-group">
						    <label for="exampleInputEmail1">NAMA TAMU</label>
						    <input name="nama" type="text" class="form-control" id="nama" aria-describedby="emailHelp" placeholder="Nama Tamu">
						  </div>	

						  <div class="form-group">
						    <label for="exampleInputEmail1">ALAMAT TAMU</label>
						    <textarea name="alamat" class="form-control" id="alamat" rows="2" ></textarea>
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">EMAIL</label>
						    <input name="email" type="text" class="form-control" id="email" aria-describedby="emailHelp" placeholder="Email Tamu">
						  </div>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">INSTANSI</label>
						    <input name="instansi" type="text" class="form-control" id="email" aria-describedby="instansi" placeholder="Instansi Tamu">
						  </div>
						  <div class="form-group">
						    <label for="exampleInputEmail1">MAKSUD/TUJUAN</label>
						    <textarea name="maksud" class="form-control" id="maksud" rows="2" ></textarea>
						  </div>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">NO.TELPON</label>
						    <input name="telp" type="text" class="form-control" id="telp" aria-describedby="emailHelp" placeholder="No. Telp">
						  </div>
						 						  
				      </div>
				      <div class="modal-footer">
				        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				 		<button type="submit" class="btn btn-primary">Update</button>
						</form>
      </div>

    </div>
  </div>


@endsection

