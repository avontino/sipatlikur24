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
									<h3 class="panel-title">Tambah Ijin Absen</h3>
								</div>
								<div class="card-body">
									
						  <form action="/ijin/create" method="POST" enctype="multipart/form-data"> 
				        	{{csrf_field()}}

						  <div class="form-group">
						    <label >Hari/Tanggal Ijin</label>
							<input name="tglmasuk" type="date" class="form-control" id="tglmasuk" aria-describedby="emailHelp" required>
						  	</div>
					
						  	<div class="form-group">
						    <label >Guru</label>
					    	<input name="guru" value="{{auth()->user()->name}}" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" readonly>
							</div>

						  <div class="form-group">
						    <label >Mata Pelajaran</label>
						    <select name="mapel" class="form-control" >
						    @foreach($ma_pel as $mapel)
						    	<option value="{{$mapel->mapel}}">{{$mapel->mapel}}</option>
						    @endforeach						    	
						    </select>

						  	</div>

						  <div class="form-group">
						    <label for="exampleFormControlSelect1">S/I/A/T</label>
						    <select name="sia" class="form-control" id="exampleFormControlSelect1" onchange="toggleJamTerlambat()">
						      <option value="Sakit">Sakit</option>
						      <option value="Ijin">Ijin</option>
						      <option value="Alpha">Alpha</option>
						      <option value="Terlambat">Terlambat</option>
						    </select>
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Jumlah Hari</label>
						    <input name="jumlah" type="text" class="form-control" id="jumlah" aria-describedby="emailHelp" value='1'>
						  </div>

						  <div class="form-group" id="jam_terlambat_group" style="display: none;">
						    <label for="jam_terlambat">Jam Terlambat (HH:MM)</label>
						    <input name="jam_terlambat" type="time" class="form-control" id="jam_terlambat" aria-describedby="emailHelp">
						  </div>

						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Keterangan</label>
						    <textarea name="ket" class="form-control" id="exampleFormControlTextarea1" rows="3" placeholder="Tuliskan keterangan detail..."></textarea>
						  </div>

						  <div class="form-group mb-3">
						    <label for="attachment" class="small fw-bold">Bukti Fisik / Surat Tugas / Surat Dokter (PDF/Gambar, Maks. 2MB)</label>
						    <input name="attachment" type="file" class="form-control" id="attachment" accept=".pdf,.png,.jpg,.jpeg">
						  </div>

						  <button type="submit" class="btn btn-primary">Tambah</button>

								</div>
							</div>
				</div>
			</div>
		</div>
	</div>



@endsection