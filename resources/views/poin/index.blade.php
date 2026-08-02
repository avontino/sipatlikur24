@extends('layouts.master')		

@section('content')

<section class="content pt-3">
      <div class="container-fluid">
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <i class="fa fa-check-circle"></i> {{session('sukses')}}
        </div>
        @endif
      	<div class="card shadow-sm border-0">
			<div class="row">
				<div class="col-md-12">
					<div class="panel">			
								<div class="card-header bg-light py-3">
									<h3 class="fw-bold m-0" style="color: #004d1a;"><i class="fas fa-star me-2"></i>History Poin Pelanggaran & Prestasi Siswa</h3>
								</div>

								<div class="card-body">
									<table id="example3" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>NAMA SISWA</th>
												<th>KELAS</th>
												<th>KATEGORI (POIN)</th>
												<th>DETAIL KEJADIAN</th>
												<th>TEMPAT</th>
												<th>PELAPOR</th>
												<th>WAKTU</th>
												@if(auth()->user()->role != 'siswa')
												<th>AKSI</th>
												@endif
											</tr>
										</thead>
										<tbody>
											@foreach($data_poin as $poin)
											<tr>
												<td class="fw-bold text-dark">{{ $poin->siswa->nama ?? '-' }}</td>
												<td>{{ $poin->siswa->kelas ?? '-' }}</td>
												<td>
													@if($poin->kategoriPoin)
														@if($poin->kategoriPoin->jenis == 'pelanggaran')
															<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> {{ $poin->kategoriPoin->nama_kategori }} (-{{ $poin->poin }} Poin)</span>
														@else
															<span class="badge bg-success"><i class="fas fa-trophy"></i> {{ $poin->kategoriPoin->nama_kategori }} (+{{ $poin->poin }} Poin)</span>
														@endif
													@else
														-
													@endif
												</td>
												<td>{{$poin->kejadian}}</td>
												<td>{{$poin->tempat}}</td>
												<td>{{$poin->pelapor}}</td>
												<td>{{$poin->created_at->format('d M Y H:i')}}</td>
												@if(auth()->user()->role != 'siswa')
												<td>
													<a href="/history-poin/{{$poin->id}}/delete" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus history poin ini?')">Hapus</a>
												</td>
												@endif
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
</section>

@endsection
