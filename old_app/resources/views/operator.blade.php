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
									<h3 class="panel-title">Data Operator SMAN TARUNA NALA MALANG</h3>


								</div>

								<div class="card-body">		

								  <form class="form-inline" method="GET" action="/operator">
								  	<button type="button" class="btn btn-primary float-right mr-sm-2" data-toggle="modal" data-target="#tambah">Tambah Operator</button>

<!-- 								     <input name="cari" class="form-control mr-sm-2"  placeholder="Cari" >
								    <button type="submit" class="btn btn-primary mr-sm-2">Cari</button> -->

								    <!--<a href="/operator/export" class="btn btn-sm btn-primary">Export</a>-->
								    <button type="button" class=" btn btn-default btn-sm " data-toggle="modal" data-target="#exim">Export/Import</button>

								  </form>								  

								  </br>
									<table id="example3" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>ROLE</th>
												<th>NAMA</th>
												<th>USERNAME</th>
												<th>AKSI</th>
												
											</tr>
										</thead>
										<tbody>
											@foreach($data_operator as $operator)
											<tr>
												<td>{{$operator->role}}</td>	
												<td>{{$operator->name}}</td>
												<td>{{$operator->username}}</td>
												<td>
													<!-- <a href="/operator/{{$operator->id}}/edit" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit">Edit</a> -->
													<button type="button" class="btn btn-warning btn-sm" 
													data-myid="{{$operator->id}}"
													data-myrole="{{$operator->role}}"
													data-myname="{{$operator->name}}"
													data-myusername="{{$operator->username}}"

													data-toggle="modal" data-target="#edit">Edit</button>

								  					<a href="/operator/{{$operator->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>
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


<!-- Modal Tambah -->
<div class="modal fade" id="tambah" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Tambah Operator</h4>
      </div>
      <div class="modal-body">
        <form action="/operator/create" method="POST"> 
				        	{{csrf_field()}}

						   <div class="form-group">
						    <label for="exampleInputEmail1">Role</label>
						    <input name="role" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Role">
						  </div>	

						  <div class="form-group">
						    <label for="exampleInputEmail1">Nama</label>
						    <input name="name" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Nama User">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Username</label>
						    <input name="username" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Username">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Password</label>
						    <input name="password" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Password">
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
<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Edit Operator</h4>
      </div>
      <div class="modal-body">
        <form action="/operator/update" method="post"> 
				        	{{csrf_field()}}
				        	<input type="hidden" name="opid" id="opid" value="">
						   <div class="form-group">
						    <label for="exampleInputEmail1">Role</label>
						    <input name="role" type="text" class="form-control" id="role" aria-describedby="emailHelp" placeholder="Role">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Nama</label>
						    <input name="name" type="text" class="form-control" id="name" aria-describedby="emailHelp" placeholder="Nama User">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Username</label>
						    <input name="username" type="text" class="form-control" id="username" aria-describedby="emailHelp" placeholder="Username">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Password</label>
						    <input name="password" type="text" class="form-control" aria-describedby="emailHelp" value="123456" placeholder="Password">
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

<!-- Modal Export/Import -->
<div class="modal fade" id="exim" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Export / Import</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form action="/operator/import" method="POST" enctype="multipart/form-data"> 
                  {{csrf_field()}}

              <div class="form-group">
              <label class="mr-sm-3 " for="export">Export</label>                
                <div class="col-md-6">
                  
                  <a href="/operator/export" class="btn btn-sm btn-success">Export</a>
              </div>
              </div>

              <div class="form-group">
                <div class="col-md-6">
                <label for="file">Import</label>
                <input name="file" type="file" class="form-control" id="file" accept=".xlsx">
              </div>
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


@endsection

