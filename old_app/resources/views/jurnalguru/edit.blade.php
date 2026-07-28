@extends('layouts.master')

@section('content')

<div class="main">
	<div class="main-content">
		<div class="container-fluid">
	
			<div class="row">
				<div class="col-md-12">
						<div class="panel">
								<div class="panel-heading">
									<h3 class="panel-title">Update Jurnal Guru</h3>
								</div>
								<div class="panel-body">
									
						  <form action="/jurnalguru/{{$jurnal->id}}/update" method="POST"> 
				        	{{csrf_field()}}

				        	<!--
						  <div class="form-group">
						    <label for="exampleInputEmail1">Kelas</label>
						    <input name="kelas" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Kelas">
						  </div> -->
						  <div class="form-group">
						    <label >Kelas</label>
						    <select name="kelas" class="form-control" readonly>
						
						    @foreach($ke_las as $kelas)

							@if( $kelas->kelas == $jurnal->kelas )
					            <option value="{{ $kelas->kelas }}" selected="selected"> {{ $kelas->kelas }}</option>
					        @else
					            <option value="{{ $kelas->kelas }}"> {{ $kelas->kelas }}</option>
					        @endif
   
						    @endforeach		
										    	
						    </select>

						  	</div>
						  <div class="form-group">
						    <label for="exampleFormControlSelect1">Keterangan Guru Mapel</label>
						    <select name="ket_guru_mapel" class="form-control" id="exampleFormControlSelect1" readonly>
						      <option value="Masuk" @if($jurnal->ket_guru_mapel=='Masuk') selected @endif>Masuk</option>
						      <option value="Tidak Masuk" @if($jurnal->ket_guru_mapel=='Tidak Masuk') selected @endif>Tidak Masuk</option>
						    </select>
						  </div>					
						  <div class="form-group">
						    <label for="exampleFormControlSelect1">Penugasan (Diisi Jika Guru Tidak Masuk)</label>
						    <select name="penugasan" class="form-control" id="exampleFormControlSelect1" readonly>
						      <option value="Tidak Ada" @if($jurnal->penugasan=='Tidak Ada') selected @endif>Tidak Ada</option>
						      <option value="Ada" @if($jurnal->penugasan=='Ada') selected @endif>Ada</option>
						    </select>
						  </div>
						  <div class="form-group">
						    <label for="exampleInputEmail1">Jam Ke</label>
						    <input name="jamke" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Diisi 1-11" value="{{$jurnal->jamke}}" readonly>
						  </div>
						  <div class="form-group">
						    <label for="exampleInputEmail1">Jumlah Jam</label>
						    <input name="jumlahjam" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Jumlah Jam" value="{{$jurnal->jumlahjam}}" readonly>
						  </div>
						  <!--
						  <div class="form-group">
						    <label for="exampleInputEmail1">Mata Pelajaran</label>
						    <input name="mapel" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Mapel">
						  </div>
						-->	<div class="form-group">
						    <label >Mata Pelajaran</label>
						    <select name="mapel" class="form-control" readonly>
						    @foreach($ma_pel as $mapel)
						    	@if( $mapel->mapel == $jurnal->mapel )
					            <option value="{{ $mapel->mapel }}" selected="selected"> {{ $mapel->mapel }}</option>
					        	@else
					            <option value="{{ $mapel->mapel }}"> {{ $mapel->mapel }}</option>
					        	@endif
						    @endforeach						    	
						    </select>

						  	</div>
						 <!-- <div class="form-group">
						    <label for="exampleInputEmail1">Guru</label>
						    <input name="guru" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Nama Guru">
						  </div> -->
						  	<div class="form-group">
						    <label >Guru</label>
						    <select name="guru" class="form-control" readonly>
						    @foreach($gu_ru as $guru)
						    	@if( $guru->guru == $jurnal->guru )
					            <option value="{{ $guru->guru }}" selected="selected"> {{ $guru->guru }}</option>
					        	@else
					            <option value="{{ $guru->guru }}"> {{ $guru->guru }}</option>
					        	@endif
						    @endforeach						    	
						    </select>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Absen Siswa Tidak Masuk</label>
						    <textarea name="absen" class="form-control" id="exampleFormControlTextarea1" rows="3" >{{$jurnal->absen}}</textarea>
						  </div>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Materi</label>
						    <textarea name="materi" class="form-control" id="exampleFormControlTextarea1" rows="3" >{{$jurnal->materi}}</textarea>
						  </div>
						  <div class="form-group">
						    <label for="exampleFormControlTextarea1">Catatan</label>
						    <textarea name="catatan" class="form-control" id="exampleFormControlTextarea1" rows="3" >{{$jurnal->catatan}}</textarea>
						  </div>


						  <div class="form-group">
						    <input name="waktu" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Waktu" value="{{$jurnal->waktu}}">
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