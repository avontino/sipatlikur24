@extends('layouts.master')

@section('content')

<section class="content-header">
      <div class="container-fluid">
      	<div class="card">
	
			<div class="row">
				<div class="col-md-12">
						<div class="panel">
								<div class="card-header">
									<h3 class="panel-title">Update Pelaporan Kasus</h3>
								</div>
								<div class="card-body">
									
						  <form action="/kasus/{{$kasus->id}}/update" method="POST"> 
				        	{{csrf_field()}}


						  <div class="form-group">
						    <label for="exampleInputEmail1">Pelapor</label>
						    <input name="pelapor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="{{$kasus->pelapor}}" placeholder="Nama Pelapor">
						  </div>

						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Kejadian Kasus</label>
						    <textarea name="kejadian" class="form-control" id="exampleFormControlTextarea1" rows="3" >{{$kasus->kejadian}}</textarea>
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Tempat</label>
						    <input name="tempat" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="{{$kasus->tempat}}" placeholder="Tempat Kasus">
						  </div>

						  <button type="submit" class="btn btn-warning">Update</button>


								</div>
							</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection