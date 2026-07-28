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
									<h3 class="panel-title">Data Surat SMAN TARUNA NALA Malang</h3>


								</div>

								<div class="card-body">		

								  <form class="form-inline" method="GET" action="/surat">
								  	<button type="button" class="btn btn-primary float-right mr-sm-2" data-toggle="modal" data-target="#tambah">Tambah Surat</button>

									<input name="filter" class="form-control mr-sm-2" type="date" >
								    <button type="submit" class="btn btn-primary mr-sm-2"">Filter</button>

								<!--      <input name="cari" class="form-control mr-sm-2"  placeholder="Cari" >
								    <button type="submit" class="btn btn-primary mr-sm-2">Cari</button> -->

								    <a href="/surat/exportexcel" class="btn btn-sm btn-primary mr-sm-2">Export Excel</a>

								    <!-- <a href="/surat/exportpdf" class="btn btn-sm btn-primary mr-sm-2">Export PDF</a> -->

								  </form>								  

								  </br>
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

													data-toggle="modal" data-target="#editsurat">Edit</button>

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
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
				        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
				        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				 		<button type="submit" class="btn btn-primary">Update</button>
						</form>
      </div>

    </div>
  </div>


@endsection

