@extends('layouts.master')		

@section('content')

<script type="text/javascript">
   setTimeout(function(){
       location.reload();
   },20000);
</script>

<section class="content-header">
      <div class="container-fluid">
      	<div class="card">
			<div class="row">
				<div class="col-md-12">
					<div class="panel">			
								<div class="card-header">
									<h3 class="panel-title">Data Jurnal SMP NEGERI 24 Malang {{ date('d-m-Y') }}</h3>

								</div>

								 <div class="card-body">

								 	<form class="form-inline" method="GET" action="/lihatjurnal">
							<select name="cari" class="form-control mr-sm-2" >
						    @foreach($ke_las as $kelas)
						    	<option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
						    @endforeach						    	
						    </select>
								    <button type="submit" class="btn btn-primary mr-sm-2">Cari</button>
						      		
								  </form>
								  *Untuk Mencari Kelas
								  
											 
									 <table id="example3" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>KELAS</th>
												<th>KET GURU</th>
												<th>PENUGASAN</th>
												<th>JAM KE</th>
												<th>JUMLAH JAM</th>
												<th>MATA PELAJARAN</th>
												<th>GURU</th>
												<th>CATATAN</th>
												<th>WAKTU</th>
											</tr>
										</thead>
										<tbody>
											@foreach($data_jurnal as $jurnal)
											<tr>
												<td>{{$jurnal->kelas}}</td>
												<td>
													@if($jurnal->ket_guru_mapel=='Tidak Masuk')
													<font color="#ff0000">{{$jurnal->ket_guru_mapel}}
													@else($jurnal->ket_guru_mapel=='Hadir')
													<font color="#000000">{{$jurnal->ket_guru_mapel}}
													@endif
												</td>
												<td>
													@if($jurnal->penugasan=='Tidak Ada'&&$jurnal->ket_guru_mapel=='Tidak Masuk')
													<font color="#ff0000">{{$jurnal->penugasan}}
													@else($jurnal->penugasan=='Ada')
													<font color="#000000">{{$jurnal->penugasan}}
													@endif
												</td>
												<td>{{$jurnal->jamke}}</td>
												<td>{{$jurnal->jumlahjam}}</td>
												<td>{{$jurnal->mapel}}</td>
												<td>{{$jurnal->guru}}</td>
												<td>
													@if($jurnal->catatan=='Catatan')
													<font color="#000000">{{$jurnal->catatan}}
													@else($jurnal->catatan!='Catatan')
													<font color="#ff0000">{{$jurnal->catatan}}
													@endif
												</td>
												<td>{{$jurnal->created_at->format('H:i:s')}}</td>
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




@endsection

