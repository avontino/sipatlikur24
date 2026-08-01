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
									<h3 class="panel-title">Data Pelaporan Kasus SMP NEGERI 24 Malang {{ date('d-m-Y') }}</h3>

								</div>

								<div class="card-body">


								  <form class="form-inline" method="GET" action="/lihatkasus">
								   <!-- <input name="cari" class="form-control mr-sm-2"  placeholder="Cari" >
								    <button type="submit" class="btn btn-primary">Cari</button> -->
						      		
								  </form>
								  
											 
									<table id="example3" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>NAMA SISWA</th>
												<th>KELAS</th>
												<th>KATEGORI (POIN)</th>
												<th>KEJADIAN KASUS</th>
												<th>TEMPAT</th>
												<th>PELAPOR</th>
												<th>WAKTU</th>
											</tr>
										</thead>
										<tbody>
											@foreach($data_kasus as $kasus)
											<tr>
												<td class="fw-bold text-dark">{{ $kasus->siswa->nama ?? '-' }}</td>
												<td>{{ $kasus->siswa->kelas ?? '-' }}</td>
												<td>
													@if($kasus->kategoriPoin)
														@if($kasus->kategoriPoin->jenis == 'pelanggaran')
															<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> {{ $kasus->kategoriPoin->nama }} ({{ $kasus->poin }} Poin)</span>
														@else
															<span class="badge bg-success"><i class="fas fa-trophy"></i> {{ $kasus->kategoriPoin->nama }} ({{ $kasus->poin }} Poin)</span>
														@endif
													@else
														-
													@endif
												</td>
												<td>{{$kasus->kejadian}}</td>
												<td>{{$kasus->tempat}}</td>
												<td>{{$kasus->pelapor}}</td>
												<td>{{$kasus->created_at->format('H:i:s')}}</td>
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

