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
                <h5 class="m-0 fw-bold card-title"><i class="fas fa-filter me-2"></i> Filter Surat Masuk</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="/surat" class="row align-items-end g-3">
                    <div class="col-md-4 col-sm-6 col-12">
                        <label for="filter" class="form-label small fw-bold text-secondary">Tanggal Masuk</label>
                        <input type="date" name="filter" id="filter" class="form-control" value="{{ request('filter') }}">
                    </div>
                    <div class="col-md-4 col-sm-6 col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="/surat" class="btn btn-secondary"><i class="fas fa-undo me-1"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
			<div class="row">
				<div class="col-md-12">
					<div class="panel">			
								<div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
									<h5 class="m-0 fw-bold text-dark card-title"><i class="fas fa-envelope me-2"></i> Data Surat SMP NEGERI 24 Malang</h5>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambah">
                                            <i class="fas fa-plus me-1"></i> Tambah Surat
                                        </button>
                                        <a href="/surat/exportexcel?filter={{ request('filter') }}" class="btn btn-sm btn-success text-white">
                                            <i class="fas fa-file-excel me-1"></i> Export Excel
                                        </a>
                                    </div>
								</div>

								<div class="card-body">
									<table id="example3" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>TANGGAL SURAT</th>
												<th>NO SURAT</th>
												<th>INSTITUSI</th>
												<th>PERIHAL</th>
												<th>KODE SURAT</th>
												<th>KETERANGAN</th>
												<th>AKSI</th>
												
											</tr>
										</thead>
										<tbody>
											@foreach($data_surat as $surat)
											<tr>
												<td>{{$surat->tglmasuk->format('d M Y')}}</td>	
												<td>{{$surat->nosurat}}</td>
												<td>{{$surat->institusi}}</td>
												<td>{{$surat->perihal}}</td>	
												<td>{{$surat->kodesurat}}</td>
												<td>{{$surat->ket}}</td>
												<td>

													<button type="button" class="btn btn-warning btn-sm" 
													data-myid="{{$surat->id}}"
													data-mytglmasuk="{{$surat->tglmasuk}}"
													data-mynosurat="{{$surat->nosurat}}"
													data-myinstitusi="{{$surat->institusi}}"
													data-myperihal="{{$surat->perihal}}"
													data-mykodesurat="{{$surat->kodesurat}}"
													data-myket="{{$surat->ket}}"

													data-bs-toggle="modal" data-bs-target="#editsurat">Edit</button>

								  					<a href="/surat/{{$surat->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>

								  					<a href="/surat/{{$surat->id}}/exportpdf" class="btn btn-success btn-sm">Cetak</a>
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
        <h4 class="modal-title" id="myModalLabel">Tambah Surat</h4>
      </div>
      <div class="modal-body">
        <form action="/surat/create" method="POST"> 
				        	{{csrf_field()}}
	
						   <div class="form-group">
						    <label for="exampleInputEmail1">TANGGAL SURAT</label>
						    <input name="tglmasuk" type="date" class="form-control" id="tglmasuk" aria-describedby="emailHelp" >
						  </div>	

						  <div class="form-group">
						    <label for="exampleInputEmail1">NO SURAT</label>
						    <input name="nosurat" type="text" class="form-control" id="nosurat" aria-describedby="emailHelp" placeholder="Nomor Surat">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">INSTITUSI</label>
						    <input name="institusi" type="text" class="form-control" id="institusi" aria-describedby="emailHelp" placeholder="Institusi Asal Surat">
						  </div>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">PERIHAL</label>
						    <textarea name="perihal" class="form-control" id="perihal" rows="2" ></textarea>
						  </div>
						  <div class="form-group">
						    <label for="exampleInputEmail1">KODE SURAT</label>
						    <input name="kodesurat" type="text" class="form-control" id="kodesurat" aria-describedby="emailHelp" placeholder="Kode Surat">
						  </div>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">KETERANGAN</label>
						    <textarea name="ket" class="form-control" id="ket" rows="2" ></textarea>
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
<div class="modal fade" id="editsurat" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <h4 class="modal-title" id="myModalLabel">Edit Operator</h4>
      </div>
      <div class="modal-body">
        <form action="/surat/update" method="post"> 
				        	{{csrf_field()}}
				        	<input type="hidden" name="srtid" id="srtid" value="">
						   <div class="form-group">
						    <label for="exampleInputEmail1">TANGGAL SURAT</label>
						    <input name="tglmasuk" type="date" class="form-control" id="tglmasuk" aria-describedby="emailHelp" >
						  </div>	

						  <div class="form-group">
						    <label for="exampleInputEmail1">NO SURAT</label>
						    <input name="nosurat" type="text" class="form-control" id="nosurat" aria-describedby="emailHelp" placeholder="Nomor Surat">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">INSTITUSI</label>
						    <input name="institusi" type="text" class="form-control" id="institusi" aria-describedby="emailHelp" placeholder="Institusi Asal Surat">
						  </div>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">PERIHAL</label>
						    <textarea name="perihal" class="form-control" id="perihal" rows="2" ></textarea>
						  </div>
						  <div class="form-group">
						    <label for="exampleInputEmail1">KODE SURAT</label>
						    <input name="kodesurat" type="text" class="form-control" id="kodesurat" aria-describedby="emailHelp" placeholder="Kode Surat">
						  </div>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">KETERANGAN</label>
						    <textarea name="ket" class="form-control" id="ket" rows="2" ></textarea>
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

