@extends('layouts.master')

@section('content')

<section class="content">
      <div class="container-fluid">
      	<div class="card">
	
			<div class="row">
				<div class="col-md-12">
						<div class="panel">
								<div class="card-header">
									<h3 class="panel-title">Update Ijin</h3>
								</div>
								<div class="card-body">
									
						  <form action="/ijin/{{$ijin->id}}/update" method="POST"> 
				        	{{csrf_field()}}

							<div class="form-group">
						    <label >Hari/Tanggal Ijin</label>
							<input name="tglmasuk" type="date" class="form-control" id="tglmasuk" aria-describedby="emailHelp" value="{{$ijin->tglmasuk}}">
						  	</div>
					
						  	<div class="form-group">
						    <label >Guru</label>
					    	<input name="guru" value="{{auth()->user()->name}}" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" readonly>
							</div>

						  <div class="form-group">
						    <label >Mata Pelajaran</label>
						    <select name="mapel" class="form-control" >
						    @foreach($ma_pel as $mapel)
						    	@if( $mapel->mapel == $ijin->mapel )
					            <option value="{{ $mapel->mapel }}" selected="selected"> {{ $mapel->mapel }}</option>
					        	@else
					            <option value="{{ $mapel->mapel }}"> {{ $mapel->mapel }}</option>
					        	@endif
						    @endforeach						    	
						    </select>

						  	</div>

						  <div class="form-group">
						    <label for="exampleFormControlSelect1">S/I/A/T</label>
						    <select name="sia" class="form-control" id="exampleFormControlSelect1" onchange="toggleEditJamTerlambat()">
						      <option value="Sakit" @if($ijin->sia=='Sakit') selected @endif>Sakit</option>
						      <option value="Ijin" @if($ijin->sia=='Ijin') selected @endif>Ijin</option>
						      <option value="Alpha" @if($ijin->sia=='Alpha') selected @endif>Alpha</option>
						      <option value="Terlambat" @if($ijin->sia=='Terlambat') selected @endif>Terlambat</option>
						    </select>
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Jumlah Hari</label>
						    <input name="jumlah" type="text" class="form-control" id="edit_jumlah" aria-describedby="emailHelp" value="{{$ijin->jumlah}}">
						  </div>

						  <div class="form-group" id="edit_jam_terlambat_group" @if($ijin->sia != 'Terlambat') style="display: none;" @endif>
						    <label for="edit_jam_terlambat">Jam Terlambat (HH:MM)</label>
						    <input name="jam_terlambat" type="time" class="form-control" id="edit_jam_terlambat" value="{{$ijin->jam_terlambat}}">
						  </div>

						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Keterangan</label>
						    <textarea name="ket" class="form-control" id="exampleFormControlTextarea1" rows="3" >{{$ijin->ket}}</textarea>
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