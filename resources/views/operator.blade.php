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

	@if(session('sukses_reset'))
	<div class="alert alert-warning alert-dismissible border-0 shadow-sm fade show" role="alert">
		<div class="d-flex align-items-center">
			<i class="fas fa-key me-2 fs-5 text-dark"></i>
			<div>
				<strong>{{ session('sukses_reset') }}</strong>
				<div class="small mt-1 text-muted">Harap catat password ini dan berikan kepada operator karena password ini tidak akan ditampilkan lagi demi keamanan.</div>
			</div>
		</div>
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
	@endif
	<div class="card">
			<div class="row">
				<div class="col-md-12">
					<div class="panel">			
								<div class="card-header bg-light py-3">
									<h3 class="fw-bold m-0" style="color: #004d1a;"><i class="fas fa-users-cog me-2"></i>Data Guru & Operator SMP NEGERI 24 MALANG</h3>
								</div>

								<div class="card-body">		

								  <form class="form-inline" method="GET" action="/operator">
								    @if(auth()->user()->hasPermission('operator_create'))
								  	<button type="button" class="btn btn-primary float-end mr-sm-2" data-bs-toggle="modal" data-bs-target="#tambah">Tambah Operator</button>
								    @endif

								    @if(auth()->user()->hasPermission('operator_export') || auth()->user()->hasPermission('operator_import'))
								    <button type="button" class=" btn btn-default btn-sm " data-bs-toggle="modal" data-bs-target="#exim">Export/Import</button>
								    @endif
								  </form>								  

								  </br>
									<ul class="nav nav-tabs mb-4" id="operatorTab" role="tablist">
									  <li class="nav-item" role="presentation">
									    <button class="nav-link active" id="siswa-tab" data-bs-toggle="tab" data-bs-target="#siswa-tab-pane" type="button" role="tab" aria-controls="siswa-tab-pane" aria-selected="true">
									      <i class="fas fa-user-graduate me-2"></i>Siswa ({{ $countSiswa ?? 0 }})
									    </button>
									  </li>
									  <li class="nav-item" role="presentation">
									    <button class="nav-link" id="guru-tab" data-bs-toggle="tab" data-bs-target="#guru-tab-pane" type="button" role="tab" aria-controls="guru-tab-pane" aria-selected="false">
									      <i class="fas fa-chalkboard-teacher me-2"></i>Guru / Tendik / Lainnya ({{ $countGuru ?? 0 }})
									    </button>
									  </li>
									  <li class="nav-item" role="presentation">
									    <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-tab-pane" type="button" role="tab" aria-controls="admin-tab-pane" aria-selected="false">
									      <i class="fas fa-user-shield me-2"></i>Admin ({{ $countAdmin ?? 0 }})
									    </button>
									  </li>
									  <li class="nav-item" role="presentation">
									    <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions-tab-pane" type="button" role="tab" aria-controls="permissions-tab-pane" aria-selected="false">
									      <i class="fas fa-user-lock me-2"></i>Pengaturan Hak Akses
									    </button>
									  </li>
									</ul>

									<div class="tab-content" id="operatorTabContent">
									  <!-- Tab Siswa -->
									  <div class="tab-pane fade show active" id="siswa-tab-pane" role="tabpanel" aria-labelledby="siswa-tab" tabindex="0">
									    <div class="table-responsive">
									      <table id="tableSiswa" class="table table-bordered table-striped align-middle" style="width:100%">
											<thead>
												<tr>
													<th>ROLE</th>
													<th>NAMA</th>
													<th>USERNAME</th>
													<th>PASSWORD</th>
													<th>AKSI</th>
												</tr>
											</thead>
											<tbody></tbody>
									      </table>
									    </div>
									  </div>

									  <!-- Tab Guru / Tendik / Lainnya -->
									  <div class="tab-pane fade" id="guru-tab-pane" role="tabpanel" aria-labelledby="guru-tab" tabindex="0">
									    <div class="table-responsive">
									      <table id="tableGuru" class="table table-bordered table-striped align-middle" style="width:100%">
											<thead>
												<tr>
													<th>ROLE</th>
													<th>NAMA</th>
													<th>USERNAME</th>
													<th>PASSWORD</th>
													<th>AKSI</th>
												</tr>
											</thead>
											<tbody></tbody>
									      </table>
									    </div>
									  </div>

									  <!-- Tab Admin -->
									  <div class="tab-pane fade" id="admin-tab-pane" role="tabpanel" aria-labelledby="admin-tab" tabindex="0">
									    <div class="table-responsive">
									      <table id="tableAdmin" class="table table-bordered table-striped align-middle" style="width:100%">
											<thead>
												<tr>
													<th>ROLE</th>
													<th>NAMA</th>
													<th>USERNAME</th>
													<th>PASSWORD</th>
													<th>AKSI</th>
												</tr>
											</thead>
											<tbody></tbody>
									      </table>
									    </div>
									  </div>

									  <!-- Tab Pengaturan Hak Akses -->
									  <div class="tab-pane fade" id="permissions-tab-pane" role="tabpanel" aria-labelledby="permissions-tab" tabindex="0">
									      <form action="/operator/save-permissions" method="POST">
									          @csrf
									          <div class="accordion" id="accordionPermissions">
									              @php
									                  $modules = [
									                      'jurnal' => 'Jurnal & Jadwal Pelajaran',
									                      'presensi' => 'Presensi Siswa',
									                      'izin' => 'Izin Siswa & Guru',
									                      'poin' => 'Kasus & Poin Pelanggaran',
									                      'tagihan' => 'Tagihan Siswa / Keuangan',
									                      'master' => 'Data Master (Kelas/Mapel/TA)',
									                      'operator' => 'Kelola Operator / Akun'
									                  ];
									                  $allRoles = ['guru', 'walikelas', 'kurikulum', 'kesiswaan', 'kesehatan', 'pembina', 'tamu', 'satpam', 'keuangan', 'lihat', 'kepala', 'surat', 'tendik', 'siswa', 'ketuakelas'];
									                  $actions = [
									                      'view' => 'Lihat',
									                      'create' => 'Tambah',
									                      'edit' => 'Edit',
									                      'delete' => 'Hapus',
									                      'export' => 'Ekspor',
									                      'import' => 'Impor'
									                  ];
									              @endphp

									              @foreach($modules as $modKey => $modLabel)
									              <div class="accordion-item mb-3 border rounded shadow-sm">
									                  <h2 class="accordion-header" id="heading-{{ $modKey }}">
									                      <button class="accordion-button collapsed fw-bold text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $modKey }}" aria-expanded="false" aria-controls="collapse-{{ $modKey }}">
									                          <i class="fas fa-folder me-2"></i> {{ $modLabel }}
									                      </button>
									                  </h2>
									                  <div id="collapse-{{ $modKey }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $modKey }}" data-bs-parent="#accordionPermissions">
									                      <div class="accordion-body p-0">
									                          <div class="table-responsive">
									                              <table class="table table-striped table-hover align-middle mb-0">
									                                  <thead class="table-light">
									                                      <tr>
									                                          <th style="width: 250px;">Role</th>
									                                          @foreach($actions as $actKey => $actLabel)
									                                          <th class="text-center">{{ $actLabel }}</th>
									                                          @endforeach
									                                      </tr>
									                                  </thead>
									                                  <tbody>
									                                      @foreach($allRoles as $roleName)
									                                      <tr>
									                                          <td class="fw-bold text-secondary">{{ strtoupper($roleName) }}</td>
									                                          @foreach($actions as $actKey => $actLabel)
									                                          @php
									                                              $permName = "{$modKey}_{$actKey}";
									                                          @endphp
									                                          <td class="text-center">
									                                              <input type="checkbox" name="permissions[{{ $roleName }}][]" value="{{ $permName }}" 
									                                                  @if(\App\Models\User::first() && \App\Models\User::first()->hasPermission($permName, $roleName)) checked @endif>
									                                          </td>
									                                          @endforeach
									                                      </tr>
									                                      @endforeach
									                                  </tbody>
									                              </table>
									                          </div>
									                      </div>
									                  </div>
									              </div>
									              @endforeach
									          </div>
									          <div class="mt-4">
									              <button type="submit" class="btn btn-primary px-4 py-2"><i class="fas fa-save me-2"></i> Simpan Matriks Hak Akses</button>
									          </div>
									      </form>
									  </div>
									</div>
					
								</div>
							</div>
				</div>
			</div>
		</div>	
	</div>


<!-- Modal Tambah -->
<div class="modal fade" id="tambah" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <h4 class="modal-title" id="myModalLabel">Tambah Operator</h4>
      </div>
      <div class="modal-body">
        <form action="/operator/create" method="POST"> 
				        	{{csrf_field()}}

						   <div class="form-group">
						    <label for="exampleInputEmail1">Role</label>
						    <input name="role" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Role">
						  </div>	

						  <div class="form-group">
						    <label for="exampleInputEmail1">Nama</label>
						    <input name="name" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Nama User">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Username</label>
						    <input name="username" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Username">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Password</label>
						    <input name="password" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Password">
						  </div>
						  
						  <div class="form-group">
						    <label for="walikelas_kelas">Kelas Perwalian / Kelas Ketua Kelas <small class="text-muted">(Pilih kelas jika Guru menjabat Wali Kelas atau Siswa menjabat Ketua Kelas)</small></label>
						    <select name="walikelas_kelas" class="form-control">
						        <option value="">-- Pilih Kelas (Jika ada) --</option>
						        @foreach(\App\Models\Kelas::orderBy('kelas', 'asc')->get() as $k)
						            <option value="{{ $k->kelas }}">{{ $k->kelas }}</option>
						        @endforeach
						    </select>
						  </div>
						  
						  <div class="form-group">
						    <label>Role Tambahan</label><br>
						    @foreach(['walikelas', 'ketuakelas', 'kurikulum', 'kesiswaan', 'kesehatan', 'pembina', 'tamu', 'satpam', 'keuangan', 'lihat', 'kepala', 'surat', 'siswa'] as $r)
						        <div class="form-check form-check-inline" style="display: inline-block; margin-right: 15px;">
						            <input class="form-check-input" type="checkbox" name="additional_roles[]" value="{{ $r }}" id="add_role_{{ $r }}">
						            <label class="form-check-label" for="add_role_{{ $r }}">{{ strtoupper($r) }}</label>
						        </div>
						    @endforeach
						  </div>
						 						  
				      </div>
				      <div class="modal-footer">
				        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				 		<button type="submit" class="btn btn-primary">Tambah</button>
						</form>
      </div>

    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <h4 class="modal-title" id="myModalLabel">Edit Operator</h4>
      </div>
      <div class="modal-body">
        <form action="/operator/update" method="post"> 
				        	{{csrf_field()}}
				        	<input type="hidden" name="opid" id="opid" value="">
						   <div class="form-group">
						    <label for="exampleInputEmail1">Role</label>
						    <input name="role" type="text" class="form-control" id="role" aria-describedby="emailHelp" placeholder="Role">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Nama</label>
						    <input name="name" type="text" class="form-control" id="name" aria-describedby="emailHelp" placeholder="Nama User">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Username</label>
						    <input name="username" type="text" class="form-control" id="username" aria-describedby="emailHelp" placeholder="Username">
						  </div>

						  <div class="form-group">
						    <label for="exampleInputEmail1">Password</label>
						    <input name="password" id="password" type="text" class="form-control" aria-describedby="emailHelp" placeholder="Password">
						  </div>
						  
						  <div class="form-group">
						    <label for="walikelas_kelas">Kelas Perwalian / Kelas Ketua Kelas <small class="text-muted">(Pilih kelas jika Guru menjabat Wali Kelas atau Siswa menjabat Ketua Kelas)</small></label>
						    <select name="walikelas_kelas" id="walikelas_kelas" class="form-control">
						        <option value="">-- Pilih Kelas (Jika ada) --</option>
						        @foreach(\App\Models\Kelas::orderBy('kelas', 'asc')->get() as $k)
						            <option value="{{ $k->kelas }}">{{ $k->kelas }}</option>
						        @endforeach
						    </select>
						  </div>
						  
						  <div class="form-group">
						    <label>Role Tambahan</label><br>
						    @foreach(['walikelas', 'ketuakelas', 'kurikulum', 'kesiswaan', 'kesehatan', 'pembina', 'tamu', 'satpam', 'keuangan', 'lihat', 'kepala', 'surat', 'siswa'] as $r)
						        <div class="form-check form-check-inline" style="display: inline-block; margin-right: 15px;">
						            <input class="form-check-input" type="checkbox" name="additional_roles[]" value="{{ $r }}" id="edit_role_{{ $r }}">
						            <label class="form-check-label" for="edit_role_{{ $r }}">{{ strtoupper($r) }}</label>
						        </div>
						    @endforeach
						  </div>
						 						  
				      </div>
				      <div class="modal-footer">
				        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				 		<button type="submit" class="btn btn-primary">Update</button>
						</form>
      </div>

    </div>
  </div>
</div>

<!-- Modal Export/Import -->
<div class="modal fade" id="exim" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel"><i class="fas fa-exchange-alt me-2"></i> Export / Import Data Operator</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Section: Export -->
        <div class="mb-4 pb-3 border-bottom">
          <h5 class="fw-bold text-primary mb-2"><i class="fas fa-file-export me-1"></i> Ekspor Data Operator</h5>
          <p class="text-muted small">Unduh seluruh data operator (termasuk Guru & Tendik) dalam bentuk file Excel.</p>
          <a href="/operator/export" class="btn btn-sm btn-success text-white"><i class="fas fa-file-excel me-1"></i> Unduh Data Operator (Excel)</a>
        </div>

        <!-- Section: Import -->
        <form action="/operator/import" method="POST" enctype="multipart/form-data"> 
          {{csrf_field()}}
          <h5 class="fw-bold text-primary mb-2"><i class="fas fa-file-import me-1"></i> Impor Data Operator</h5>
          <p class="text-muted small mb-3">Unggah data operator/guru baru menggunakan berkas Excel (.xlsx).</p>

          <div class="mb-3">
            <a href="/operator/template" class="btn btn-sm btn-info text-white"><i class="fas fa-file-download me-1"></i> Unduh Template Excel</a>
          </div>

          <div class="card bg-light p-3 mb-3 border-0 rounded">
            <h6 class="fw-bold text-secondary mb-2" style="font-size: 13px;"><i class="fas fa-info-circle me-1"></i> Petunjuk Penting:</h6>
            <ul class="mb-0 text-muted small ps-3">
              <li>File harus berformat <strong>.xlsx</strong>.</li>
              <li>Kolom Excel harus berurutan: <strong>A = Role</strong> (admin/guru/tendik), <strong>B = Nama Lengkap</strong>, <strong>C = Username</strong>, <strong>D = Password</strong>.</li>
              <li>Baris pertama (header) akan diabaikan secara otomatis oleh sistem.</li>
            </ul>
          </div>

          <div class="form-group mb-0">
            <label for="file" class="form-label small fw-bold">Pilih File Excel (.xlsx)</label>
            <input name="file" type="file" class="form-control" id="file" accept=".xlsx" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Mulai Impor</button>
      </div>
      </form>
    </div>
  </div>
</div>


@endsection

@section('footer')
<script>
  window.addEventListener('DOMContentLoaded', (event) => {
    var checkInterval = setInterval(function() {
      if (window.jQuery && $.fn.DataTable) {
        clearInterval(checkInterval);
        
        function makeOpTable(id, type) {
          if ($.fn.DataTable.isDataTable(id)) {
            $(id).DataTable().destroy();
          }
          return $(id).DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: {
              url: "{{ url('/operator') }}",
              data: function(d) {
                d.type = type;
              }
            },
            columns: [
              { data: 'role', name: 'role' },
              { data: 'name', name: 'name' },
              { data: 'username', name: 'username' },
              { data: 'password', name: 'password', orderable: false, searchable: false },
              { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ],
            language: {
              processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
              search: "Cari Operator:",
              lengthMenu: "Tampilkan _MENU_ data",
              info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
              infoEmpty: "Tidak ada data",
              zeroRecords: "Operator tidak ditemukan"
            }
          });
        }

        makeOpTable('#tableSiswa', 'siswa');
        makeOpTable('#tableGuru', 'guru');
        makeOpTable('#tableAdmin', 'admin');

        // Auto-adjust columns when tabs are switched to prevent layout squishing
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        });
      }
    }, 50);
  });
</script>
@endsection

