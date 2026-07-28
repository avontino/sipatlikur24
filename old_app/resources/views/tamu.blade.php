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
									<h3 class="panel-title">Data Tamu SMAN TARUNA NALA Malang</h3>


								</div>

								<div class="card-body">		

								  <form class="form-inline" method="GET" action="/tamu">
								  	<button type="button" class="btn btn-primary float-right mr-sm-2" data-toggle="modal" data-target="#tambah">Tambah Tamu</button>

									<input name="filter" class="form-control mr-sm-2" type="date" >
								    <button type="submit" class="btn btn-primary mr-sm-2">Filter</button>

								    <!--  <input name="cari" class="form-control mr-sm-2"  placeholder="Cari" >
								    <button type="submit" class="btn btn-primary">Cari</button> -->

								    <a href="/tamu/export" class="btn btn-sm btn-primary mr-sm-2">Export</a>

								  </form>								  

								  </br>
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

													data-toggle="modal" data-target="#edittamu">Edit</button>

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
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
				        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
				        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				 		<button type="submit" class="btn btn-primary">Update</button>
						</form>
      </div>

    </div>
  </div>


@endsection

