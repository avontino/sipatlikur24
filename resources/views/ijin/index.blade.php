@extends('layouts.master')

@section('content')

<section class="content pt-3">
    <div class="container-fluid">
	@if(session('sukses'))
	<div class="alert alert-success alert-dismissible" role="alert">
										<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
										<i class="fa fa-check-circle"></i> 

  	{{session('sukses')}}
	</div>
	@endif


	<div class="card shadow-sm border-0">
			<div class="row">
				<div class="col-md-12">
					<div class="panel">			
								<div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
									<h4 class="fw-bold m-0" style="color: #004d1a;"><i class="fas fa-file-alt me-2"></i>Data Permohonan Izin Guru & Pegawai</h4>
									<div class="d-flex gap-2">
										<a href="/ijin/live" class="btn btn-sm btn-info text-white fw-bold shadow-sm">
											<i class="fas fa-broadcast-tower me-1"></i> Live Monitoring Hari Ini
										</a>
										<a href="/tambahijin" class="btn btn-sm btn-success text-white fw-bold shadow-sm" style="background-color: #00a884; border-color: #00a884;">
											<i class="fas fa-plus-circle me-1"></i> Tambah Izin
										</a>
									</div>
								</div>

								<div class="card-body">
								  @if(!empty($isStaffOrAdmin))
								  <div class="mb-3 d-flex gap-2 flex-wrap align-items-center justify-content-between p-2 rounded bg-light border">
								    <ul class="nav nav-pills gap-1">
								      <li class="nav-item">
								        <a class="nav-link py-1 px-3 {{ empty($showAll) ? 'active fw-bold' : 'bg-white text-dark border' }}" href="/ijin{{ request('filter') ? '?filter='.request('filter') : '' }}">
								          <i class="fas fa-user me-1 text-primary"></i> Izin Saya Sendiri
								        </a>
								      </li>
								      <li class="nav-item">
								        <a class="nav-link py-1 px-3 {{ !empty($showAll) ? 'active fw-bold' : 'bg-white text-dark border' }}" href="/ijin?view=kurikulum{{ request('filter') ? '&filter='.request('filter') : '' }}">
								          <i class="fas fa-users-cog me-1 text-success"></i> Kelola Izin Semua Guru
								        </a>
								      </li>
								    </ul>
								    <div class="small text-muted">
								      @if(!empty($showAll))
								        <span class="badge bg-success"><i class="fas fa-eye me-1"></i> Mode Pengawasan (Semua Guru)</span>
								      @else
								        <span class="badge bg-primary"><i class="fas fa-user-check me-1"></i> Mode Personal (Izin Pribadi Saya)</span>
								      @endif
								    </div>
								  </div>
								  @endif

								  <form class="form-inline mb-3 d-flex gap-2 flex-wrap align-items-center" method="GET" action="/ijin">
								    @if(!empty($showAll))
								      <input type="hidden" name="view" value="kurikulum">
								    @endif
								    <input name="filter" class="form-control form-control-sm" type="date" value="{{ request('filter') }}" style="width: auto;">
								    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
								    @if(request('filter'))
								    	<a href="/ijin{{ !empty($showAll) ? '?view=kurikulum' : '' }}" class="btn btn-sm btn-outline-secondary">Reset</a>
								    @endif

								    <div class="ms-auto d-flex gap-2">
								    	<a href="/ijin/export" class="btn btn-sm btn-success text-white"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
								    	<button type="button" class="btn btn-sm btn-secondary text-white" data-bs-toggle="modal" data-bs-target="#rk"><i class="fas fa-calendar-alt me-1"></i> Rekap Kehadiran</button>
								    </div>
								  </form>

									 <table id="example3" class="table table-bordered table-striped align-middle" style="font-size: 13px;">
										<thead class="table-light">
											<tr>
												<th>TANGGAL IJIN</th>
												<th>NAMA GURU</th>
												<th>MAPEL</th>
												<th>KATEGORI IZIN</th>
												<th>DURASI</th>
												<th>WAKTU / JAM</th>
												<th>KETERANGAN</th>
												<th>LAMPIRAN</th>
												<th>STATUS</th>
												<th>WAKTU CATAT</th>
												<th>AKSI</th>
											</tr>
										</thead>
										<tbody>
											@foreach($data_ijin as $ijin)
											@php
												$siaLower = strtolower(trim($ijin->sia));
											@endphp
											<tr>
													<td class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($ijin->tglmasuk)->format('d/m/Y') }}</td>
													<td class="fw-bold text-dark">{{$ijin->guru}}</td>
													<td>{{$ijin->mapel ?: '-'}}</td>
													<td>
														@if(str_contains($siaLower, 'tugas') || str_contains($siaLower, 'dinas'))
															<span class="badge bg-info text-white"><i class="fas fa-briefcase me-1"></i>Tugas Kedinasan</span>
														@elseif(str_contains($siaLower, 'sakit'))
															<span class="badge text-white" style="background-color: #ea580c;"><i class="fas fa-medkit me-1"></i>Sakit</span>
														@elseif(str_contains($siaLower, 'keluar'))
															<span class="badge text-white" style="background-color: #d97706;"><i class="fas fa-sign-out-alt me-1"></i>Keluar Jam Dinas</span>
														@elseif(str_contains($siaLower, 'pulang'))
															<span class="badge bg-warning text-dark"><i class="fas fa-door-open me-1"></i>Pulang Cepat</span>
														@elseif(str_contains($siaLower, 'terlambat'))
															<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Terlambat</span>
														@elseif(str_contains($siaLower, 'cuti'))
															<span class="badge text-white" style="background-color: #8b5cf6;"><i class="fas fa-calendar-minus me-1"></i>Cuti</span>
														@elseif(str_contains($siaLower, 'pribadi'))
															<span class="badge bg-secondary text-white"><i class="fas fa-user-clock me-1"></i>Pribadi</span>
														@elseif($siaLower == 'ijin')
															<span class="badge bg-success text-white"><i class="fas fa-check me-1"></i>Ijin</span>
														@else
															<span class="badge bg-danger text-white">{{ $ijin->sia }}</span>
														@endif
													</td>
													<td class="text-center">{{ $ijin->jumlah > 0 ? $ijin->jumlah . ' Hari' : '-' }}</td>
													<td>
														@if($ijin->jam_keluar && $ijin->jam_kembali)
															<span class="badge bg-light text-dark border"><i class="far fa-clock me-1"></i>{{ substr($ijin->jam_keluar, 0, 5) }} - {{ substr($ijin->jam_kembali, 0, 5) }}</span>
														@elseif($ijin->jam_keluar)
															<span class="badge bg-light text-dark border"><i class="far fa-clock me-1"></i>Jam {{ substr($ijin->jam_keluar, 0, 5) }}</span>
														@elseif($ijin->jam_terlambat)
															<span class="badge bg-light text-dark border"><i class="far fa-clock me-1"></i>Pukul {{ substr($ijin->jam_terlambat, 0, 5) }}</span>
														@else
															<span class="text-muted small">-</span>
														@endif
													</td>
													<td>{{$ijin->ket}}</td>
													<td>
														@if($ijin->attachment)
															<a href="{{ asset($ijin->attachment) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fas fa-file-alt me-1"></i> Bukti</a>
														@else
															<span class="text-muted small">-</span>
														@endif
													</td>
													<td>
														@if($ijin->approval_status == 'pending')
															<span class="badge bg-warning text-dark"><i class="fas fa-spinner fa-spin me-1"></i> Pending</span>
														@elseif($ijin->approval_status == 'approved')
															<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Disetujui</span>
														@else
															<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Ditolak</span>
														@endif
													</td>
													<td>
														@if($ijin->created_at)
															{{ is_object($ijin->created_at) ? $ijin->created_at->format('d M Y - H:i') : \Carbon\Carbon::parse($ijin->created_at)->format('d M Y - H:i') }}
														@else
															-
														@endif
													</td>
													<td>
														<div class="d-flex gap-1 flex-wrap">
															@php
																$canManageIjin = in_array(auth()->user()->role, ['admin', 'kurikulum']) 
																	|| auth()->user()->hasRole('admin') 
																	|| auth()->user()->hasRole('kurikulum');
															@endphp
															@if($canManageIjin)
																@if(($ijin->approval_status ?? 'approved') == 'pending')
																	<form action="/ijin/{{ $ijin->id }}/approve" method="POST" class="d-inline">
																		@csrf
																		<button type="submit" class="btn btn-xs btn-success text-white" title="Setujui"><i class="fas fa-check"></i></button>
																	</form>
																	<form action="/ijin/{{ $ijin->id }}/reject" method="POST" class="d-inline">
																		@csrf
																		<button type="submit" class="btn btn-xs btn-danger text-white" title="Tolak"><i class="fas fa-times"></i></button>
																	</form>
																@endif
																<button type="button" class="btn btn-warning btn-xs text-white btn-edit-ijin" 
																	data-myid="{{$ijin->id}}"
																	data-mytglmasuk="{{$ijin->tglmasuk}}"
																	data-myguru="{{$ijin->guru}}"
																	data-mymapel="{{$ijin->mapel}}"
																	data-mysia="{{$ijin->sia}}"
																	data-myjumlah="{{$ijin->jumlah}}"
																	data-myjam_terlambat="{{$ijin->jam_terlambat}}"
																	data-myjamterlambat="{{$ijin->jam_terlambat}}"
																	data-myket="{{$ijin->ket}}"
																	data-bs-toggle="modal" data-bs-target="#editijin"
																	data-toggle="modal" data-target="#editijin">Edit</button>
																<a href="/ijin/{{$ijin->id}}/delete" class="btn btn-danger btn-xs text-white" onclick="return confirm('Hapus izin ini?')">Hapus</a>
															@else
																@if($ijin->approval_status == 'pending')
																	<a href="/ijin/{{$ijin->id}}/delete" class="btn btn-danger btn-xs text-white" onclick="return confirm('Batalkan izin ini?')">Batal</a>
																@else
																	<span class="text-muted small">-</span>
																@endif
															@endif
														</div>
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
</section>

<!-- Modal Edit -->
<div class="modal fade" id="editijin" tabindex="-1" aria-labelledby="editIjinModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editIjinModalLabel"><i class="fas fa-edit me-1 text-warning"></i> Edit Izin Absen</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="/ijin/update" method="POST"> 
				@csrf
				<input type="hidden" name="ijinid" id="ijinid" value="">
				<div class="modal-body">
					<div class="form-group mb-3">
						<label class="form-label fw-bold">Hari/Tanggal Izin</label>
						<input name="tglmasuk" type="date" class="form-control" id="modal_tglmasuk" required>
					</div>
				
					<div class="form-group mb-3">
						<label class="form-label fw-bold">Guru / Pegawai</label>
						<input name="guru" type="text" class="form-control" id="modal_guru" readonly style="background-color: #f8f9fa;">
					</div>

					<div class="form-group mb-3">
						<label class="form-label fw-bold">Mata Pelajaran</label>
						<select name="mapel" class="form-control form-select" id="modal_mapel">
							<option value="-">- Bukan Guru Mapel / Umum -</option>
							@foreach($ma_pel as $mapel)
								<option value="{{ $mapel->mapel }}">{{ $mapel->mapel }}</option>
							@endforeach						    	
						</select>
					</div>

					<div class="form-group mb-3">
						<label class="form-label fw-bold">Kategori Izin</label>
						<select name="sia" class="form-control form-select" id="modal_sia" onchange="toggleModalJamTerlambat()">
							<option value="Sakit">Sakit</option>
							<option value="Ijin">Ijin</option>
							<option value="Tugas Kedinasan">Tugas Kedinasan (Dinas Luar / Lomba / MGMP / Diklat)</option>
							<option value="Izin Terlambat">Izin Terlambat</option>
							<option value="Terlambat">Terlambat</option>
							<option value="Izin Keluar Jam Dinas">Izin Keluar Jam Dinas</option>
							<option value="Izin Pulang Sebelum Waktunya">Izin Pulang Sebelum Waktunya</option>
							<option value="Keperluan Pribadi">Keperluan Pribadi</option>
							<option value="Cuti">Cuti</option>
							<option value="Alpha">Alpha</option>
						</select>
					</div>

					<div class="form-group mb-3">
						<label class="form-label fw-bold">Jumlah Hari</label>
						<input name="jumlah" type="number" min="0" step="1" class="form-control" id="modal_jumlah" required>
						<small class="text-muted d-block mt-1">Ubah angka hari di sini jika guru/pegawai masuk lebih cepat atau menambah durasi izin.</small>
					</div>

					<div class="form-group mb-3" id="modal_jam_terlambat_group" style="display: none;">
						<label class="form-label fw-bold" for="modal_jam_terlambat">Jam Terlambat (HH:MM)</label>
						<input name="jam_terlambat" type="time" class="form-control" id="modal_jam_terlambat">
					</div>

					<div class="form-group mb-3">
						<label class="form-label fw-bold">Keterangan</label>
						<textarea name="ket" class="form-control" id="modal_ket" rows="3"></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODAL EXPORT PERTANGGAL -->
<div class="modal fade" id="rk" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Rekap Kehadiran</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/ijin/rekaphadir" method="GET"> 
                  {{csrf_field()}}


              <div class="form-group mb-3">
                <label for="exampleInputEmail1">DARI TANGGAL</label>
                <input name="tglawal" type="date" class="form-control" id="tgl1" required>
              </div>  

              <div class="form-group mb-3">
                <label for="exampleInputEmail1">SAMPAI TANGGAL</label>
                <input name="tglakhir" type="date" class="form-control" id="tgl2" required>
              </div>   
              

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Download</button>
            </form>
      </div>

    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function openIjinModal(btn) {
        var $btn = $(btn);
        var id = $btn.data('myid');
        var tglmasuk = $btn.data('mytglmasuk');
        var guru = $btn.data('myguru');
        var mapel = $btn.data('mymapel');
        var sia = $btn.data('mysia');
        var jumlah = $btn.data('myjumlah');
        var jamterlambat = $btn.data('myjamterlambat') || $btn.data('myjam_terlambat');
        var ket = $btn.data('myket');

        $('#ijinid').val(id);
        $('#modal_tglmasuk').val(tglmasuk);
        $('#modal_guru').val(guru);
        $('#modal_mapel').val(mapel);
        
        if (sia && $('#modal_sia option[value="' + sia + '"]').length === 0) {
            $('#modal_sia').append(new Option(sia, sia, true, true));
        }
        $('#modal_sia').val(sia);
        
        $('#modal_jumlah').val(jumlah);
        $('#modal_jam_terlambat').val(jamterlambat);
        $('#modal_ket').val(ket);

        if (typeof toggleModalJamTerlambat === 'function') {
            toggleModalJamTerlambat();
        }

        var modalEl = document.getElementById('editijin');
        if (modalEl) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalObj.show();
            } else if ($.fn.modal) {
                $('#editijin').modal('show');
            }
        }
    }

    $(document).on('click', '.btn-edit-ijin', function(e) {
        e.preventDefault();
        openIjinModal(this);
    });
});
</script>
@endpush

