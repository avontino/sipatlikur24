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
									<h3 class="panel-title">Pelaporan Kasus</h3>
								</div>
								<div class="card-body">
									
						  <form action="/kasus/create" method="POST"> 
				        	{{csrf_field()}}

						  <div class="form-group">
						    <label for="exampleInputEmail1">Pelapor</label>
						    <input name="pelapor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Nama Pelapor">
						  </div>

						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Kejadian Kasus</label>
						    <textarea name="kejadian" class="form-control" id="exampleFormControlTextarea1" rows="3" ></textarea>
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Tempat</label>
						    <input name="tempat" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Tempat Kasus">
						  </div>

						  <button type="submit" class="btn btn-primary">Laporkan</button>


                </div>
              </div>
        </div>
      </div> 
     <!--  batas row -->
</form>
</div>
</div>
@endsection