
@extends('layouts.master')

@section('content')

<section class="content pt-3">
    <div class="container-fluid">
        <!-- Flash Messages -->
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{session('sukses')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        @if(session('gagal'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-1"></i> {{session('gagal')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light py-3">
                        <h3 class="fw-bold m-0" style="color: #004d1a;"><i class="fas fa-book-open me-2"></i>Data Jurnal Mengajar SMP NEGERI 24 Malang</h3>
                    </div>
                    <div class="card-body">	
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <form class="d-flex flex-wrap gap-2 gap-md-3 align-items-center" method="GET" action="/jurnal">
                                    @if(auth()->user()->hasPermission('jurnal_create') && !in_array(auth()->user()->role, ['siswa', 'ketuakelas']) && !auth()->user()->hasRole('ketuakelas') && !auth()->user()->hasRole('siswa') && auth()->user()->role != 'guru')
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                            <i class="fas fa-plus"></i> Tambah Jurnal
                                        </button>
                                    @endif

                                    <div class="input-group input-group-sm" style="width: auto;">
                                        <input name="crtgl" type="date" class="form-control">
                                        <button type="submit" class="btn btn-primary" name="action" value="tanggal">Filter Tanggal</button>
                                    </div>

                                    @if(auth()->user()->hasPermission('jurnal_view') && (auth()->user()->role=='admin' || auth()->user()->role=='lihat' || auth()->user()->role=='kurikulum'))
                                        <div class="input-group input-group-sm" style="width: auto;">
                                            <select name="kelas" class="form-select">
                                                @foreach($ke_las as $kelas)
                                                <option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
                                                @endforeach                 
                                            </select>        
                                            <button type="submit" class="btn btn-primary" name="action" value="kelas">Filter Kelas</button>
                                        </div>
                                    @endif

                                    @if(auth()->user()->hasPermission('jurnal_export'))
                                        <div class="input-group input-group-sm" style="width: auto;">
                                            <input name="filterexport" class="form-control" type="date">
                                            <a href="/jurnal/exporttanggal" class="btn btn-success text-white">Excel Per Tanggal</a>
                                        </div>
                                        <a href="/jurnal/export" class="btn btn-sm btn-success text-white">Excel Semua</a>
                                        <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#rekapModal">Rekap Jurnal</button>
                                    @endif

                                    @if(auth()->user()->hasPermission('jurnal_export') && auth()->user()->role=='ketuakelas')
                                        <a href="/jurnal/editsexport" class="btn btn-sm btn-success">Download Rekap (Excel)</a>
                                        <div class="w-100">
                                            <small class="text-muted">* Menu Download ini dapat digunakan dengan membuka Browser, buka Website sidasa.sman10malang.sch.id kemudian login di website tersebut</small>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        </div>
											 
                        <div class="table-responsive">
                            <table id="example3" class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
											<tr>
										    @if(auth()->user()->hasPermission('jurnal_edit') || auth()->user()->hasPermission('jurnal_delete'))
											<th>AKSI</th>
											@endif
												<th>KELAS</th>
												<th>KET GURU</th>
												<th>PENUGASAN</th>
												<th>JAM KE</th>
												<th>JUMLAH JAM</th>
												<th>MATA PELAJARAN</th>
												<th>GURU</th>
												<th>MATERI</th>
												<th>CATATAN</th>
												<th>WAKTU</th>
												
												
											</tr>
										</thead>
										<tbody>

											@foreach($data_jurnal as $jurnal)
											<tr class="@if($jurnal->materi == 'Jam Kosong') table-danger @elseif($jurnal->penugasan == 'Ada') table-warning @else table-success @endif">
												@if(auth()->user()->hasPermission('jurnal_edit') || auth()->user()->hasPermission('jurnal_delete'))
												<td>
													<!-- <a href="/jurnal/{{$jurnal->id}}/edit" class="btn btn-warning btn-sm">Edit</a> -->
													@if(auth()->user()->hasPermission('jurnal_edit'))
														<button type="button" class="btn btn-warning btn-sm btn-edit-jurnal" 
														data-myid="{{$jurnal->id}}"
														data-mykelas="{{$jurnal->kelas}}"
														data-myket_guru_mapel="{{$jurnal->ket_guru_mapel}}"
														data-mypenugasan="{{$jurnal->penugasan}}"
														data-myjamke="{{$jurnal->jamke}}"
														data-myjumlahjam="{{$jurnal->jumlahjam}}"
														data-mymapel="{{$jurnal->mapel}}"
														data-myguru="{{$jurnal->guru}}"
														data-mymateri="{{$jurnal->materi}}"
														data-mycatatan="{{$jurnal->catatan}}">Edit</button>
													@endif

													@if(auth()->user()->hasPermission('jurnal_delete'))
														<a href="/jurnal/{{$jurnal->id}}/delete" class="btn btn-danger btn-sm">Hapus</a>
													@endif
												</td>
												@endif
												<td>{{$jurnal->kelas}}</td>
												<td>
													@if($jurnal->ket_guru_mapel=='Tidak Masuk')
													<font color="#ff0000">{{$jurnal->ket_guru_mapel}}
													@elseif($jurnal->ket_guru_mapel=='Hadir')
													<font color="#000000">{{$jurnal->ket_guru_mapel}}
													@endif
												</td>
												<td>
													@if($jurnal->penugasan=='Tidak Ada'&&$jurnal->ket_guru_mapel=='Tidak Masuk')
													<font color="#ff0000">{{$jurnal->penugasan}}
													@elseif($jurnal->penugasan=='Ada')
													<font color="#000000">{{$jurnal->penugasan}}
													@endif
												</td>
												<td>{{$jurnal->jamke}}</td>
												<td>{{$jurnal->jumlahjam}}</td>
												<td>{{$jurnal->mapel}}</td>
												<td>{{$jurnal->guru}}</td>
												
<!-- Menampilkan Materi dengan Link Biru jika Ada URL -->
    <td>
        @if($jurnal->materi_url)
            <a href="{{ $jurnal->materi_url }}" class="text-primary fw-semibold" target="_blank" style="text-decoration: underline;">
                <i class="fas fa-link me-1"></i>{{ $jurnal->materi_text }}
            </a>
        @else
            <!-- Jika tidak ada URL, tampilkan teks biasa -->
            {{ $jurnal->materi_text }}
        @endif
    </td>

												<td>
													@if($jurnal->catatan=='Catatan')
													<font color="#000000">{{$jurnal->catatan}}
													@elseif($jurnal->catatan!='Catatan')
													<font color="#ff0000">{{$jurnal->catatan}}
													@endif
												</td>
												<td>{{$jurnal->created_at->format('d M Y - H:i:s')}}</td>
												
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





<!-- Modal untuk Rekap Jurnal Guru -->
<div class="modal fade" id="rekapModal" tabindex="-1" role="dialog" aria-labelledby="rekapModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rekapModalLabel">Rekap Jurnal Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form untuk memilih rentang tanggal -->
                <form id="rekapForm" method="GET" action="/jurnal">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Lihat Rekap</button>
                </form>

                <!-- Tombol Export ke Excel dan PDF dalam Modal -->
                <div class="mt-3">
                    <!-- Tombol Excel -->
                    <a href="#" id="exportExcelBtn" class="btn btn-success">Export ke Excel</a>
                    <!-- Tombol PDF -->
                    <a href="#" id="exportPdfBtn" class="btn btn-danger">Export ke PDF</a>
                </div>
            </div>
        </div>
    </div>
</div>




										<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form action="/jurnal/createsusul" method="POST"> 
			{{csrf_field()}}
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Tambah Jurnal</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="exampleInputEmail1">TANGGAL</label>
						<input name="tgl" type="date" class="form-control" id="tgl" aria-describedby="emailHelp" required>
					</div>
					<div class="form-group">
						<label>Kelas</label>
						<select name="kelas" class="form-control" >
							@foreach($ke_las as $kelas)
								<option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
							@endforeach						    	
						</select>
					</div>
					<div class="form-group">
						<label for="exampleFormControlSelect1">Keterangan Guru Mapel</label>
						<select name="ket_guru_mapel" class="form-control" id="exampleFormControlSelect1">
							<option value="Hadir">Hadir</option>
							<option value="Tidak Masuk">Tidak Masuk</option>
						</select>
					</div>					
					<div class="form-group">
						<label for="exampleFormControlSelect1">Penugasan (Diisi Jika Guru Tidak Masuk)</label>
						<select name="penugasan" class="form-control" id="exampleFormControlSelect1">
							<option value="Tidak Ada">Tidak Ada</option>
							<option value="Ada">Ada</option>
						</select>
					</div>
					<div class="form-group">
						<label>Mata Pelajaran</label>
						<select name="mapel" class="form-control" >
							@foreach($ma_pel as $mapel)
								<option value="{{$mapel->mapel}}">{{$mapel->mapel}}</option>
							@endforeach						    	
						</select>
					</div>
					<div class="form-group">
						<label>Guru</label>
						<select name="guru" class="form-control" >
							@foreach($gu_ru as $guru)
								<option value="{{$guru->guru}}">{{$guru->guru}}</option>
							@endforeach						    	
						</select>
					</div>
					<div class="form-group">
						<label for="exampleFormControlTextarea1">Materi</label>
						<textarea name="materi" class="form-control" id="exampleFormControlTextarea1" rows="3">Materi</textarea>
					</div>
					<div class="form-group">
						<label for="exampleFormControlTextarea1">Catatan</label>
						<textarea name="catatan" class="form-control" id="exampleFormControlTextarea1" rows="3">Catatan</textarea>
					</div>
					<div class="form-group">
						<input name="waktu" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Waktu" value="{{ date('H:i:s') }}">
					</div>
					<div class="form-group">
						<input name="guru_id" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Submit</button>
				</div>
			</div>
		</form>
	</div>
</div>


<!-- Modal Edit -->
<div class="modal fade" id="editjurnal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="/jurnal/update" method="post"> 
      {{csrf_field()}}
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLabel">Edit Jurnal</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="jurnalid" id="jurnalid" value="">
          
          <div class="form-group">
            <label>Kelas</label>
            <select name="kelas" class="form-control" id="kelas">
              @foreach($ke_las as $kelas)
                <option value="{{ $kelas->kelas }}"> {{ $kelas->kelas }}</option>
              @endforeach   
            </select>
          </div>

          <div class="form-group">
            <label for="exampleFormControlSelect1">Keterangan Guru Mapel</label>
            <select name="ket_guru_mapel" class="form-control" id="ket_guru_mapel">
              <option value="Hadir">Hadir</option>
              <option value="Tidak Masuk">Tidak Masuk</option>
            </select>
          </div>

          <div class="form-group">
            <label for="exampleFormControlSelect1">Penugasan (Diisi Jika Guru Tidak Masuk)</label>
            <select name="penugasan" class="form-control" id="penugasan">
              <option value="Tidak Ada">Tidak Ada</option>
              <option value="Ada">Ada</option>
            </select>
          </div>

          <div class="form-group">
            <label for="exampleInputEmail1">Jam Ke</label>
            <input name="jamke" type="text" class="form-control" id="jamke" aria-describedby="emailHelp" placeholder="Diisi 1-11">
          </div>

          <div class="form-group">
            <label for="exampleInputEmail1">Jumlah Jam</label>
            <input name="jumlahjam" type="text" class="form-control" id="jumlahjam" aria-describedby="emailHelp" placeholder="Jumlah Jam">
          </div>

          <div class="form-group">
            <label>Mata Pelajaran</label>
            <select name="mapel" class="form-control" id="mapel">
              @foreach($ma_pel as $mapel)
                <option value="{{ $mapel->mapel }}"> {{ $mapel->mapel }}</option>
              @endforeach                 
            </select>
          </div>
             
          <div class="form-group">
            <label>Guru</label>
            <select name="guru" class="form-control" id="guru">
              @foreach($gu_ru as $guru)
                <option value="{{ $guru->guru }}"> {{ $guru->guru }}</option>
              @endforeach                 
            </select>
          </div>
             
          <div class="form-group">
            <label for="exampleFormControlTextarea1">Materi</label>
            <textarea name="materi" class="form-control" id="materi" rows="3"></textarea>
          </div>

          <div class="form-group">
            <label for="exampleFormControlTextarea1">Catatan</label>
            <textarea name="catatan" class="form-control" id="catatan" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>







<script>
document.addEventListener('DOMContentLoaded', function() {
    $(document).on('click', '.btn-edit-jurnal', function(e) {
        e.preventDefault();
        var modalEl = document.getElementById('editjurnal');
        if (modalEl) {
            var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show(this);
        }
    });

    if ($.fn.DataTable.isDataTable('#example3')) {
        $('#example3').DataTable().destroy();
    }
    
    $('#example3').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: "{{ url('/jurnal') }}",
            data: function (d) {
                d.view = "{{ request('view') }}";
                d.action = "{{ request('action') }}";
                d.kelas = "{{ request('kelas') }}";
                d.crtgl = "{{ request('crtgl') }}";
                d.start_date = "{{ request('start_date') }}";
                d.end_date = "{{ request('end_date') }}";
            }
        },
        columns: [
            @if(auth()->user()->hasPermission('jurnal_edit') || auth()->user()->hasPermission('jurnal_delete'))
            { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'kelas', name: 'kelas' },
            { data: 'ket_guru_mapel', name: 'ket_guru_mapel' },
            { data: 'penugasan', name: 'penugasan' },
            { data: 'jamke', name: 'jamke' },
            { data: 'jumlahjam', name: 'jumlahjam' },
            { data: 'mapel', name: 'mapel' },
            { data: 'guru', name: 'guru' },
            { data: 'materi', name: 'materi' },
            { data: 'catatan', name: 'catatan' },
            { data: 'created_at', name: 'created_at' }
        ],
        order: [],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat data...</span></div>',
            search: "Cari Jurnal (Semua 14.000+ Data):",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data jurnal",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Data jurnal tidak ditemukan"
        }
    });
});
</script>

@endsection


