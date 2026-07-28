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

						  <div class="form-group mb-3">
						    <label for="pelapor" class="small fw-bold">Pelapor</label>
						    <input name="pelapor" type="text" class="form-control" id="pelapor" placeholder="Nama Pelapor" required>
						  </div>

						  <div class="form-group mb-3">
						    <label for="kejadian" class="small fw-bold">Kejadian Kasus</label>
						    <textarea name="kejadian" class="form-control" id="kejadian" rows="3" placeholder="Tuliskan keterangan detail kejadian..." required></textarea>
						  </div>

						  <div class="form-group mb-3">
						    <label for="tempat" class="small fw-bold">Tempat</label>
						    <input name="tempat" type="text" class="form-control" id="tempat" placeholder="Tempat Kasus" required>
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