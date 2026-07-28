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
    @if(session('gagal'))
  <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <i class="fa fa-check-circle"></i> 

    {{session('gagal')}}
  </div>
   @endif

<form id="bulkDeleteForm" method="POST" action="/jadwal/delete-multiple">
    {{ csrf_field() }}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h3 class="fw-bold m-0" style="color: #002366;"><i class="fas fa-calendar-week me-2"></i>Jadwal Pelajaran</h3>
                </div>
                <div class="col-md-8 text-md-end text-start mt-2 mt-md-0">
                    @if(auth()->user()->role=='admin')
                    <div class="d-flex gap-2 justify-content-md-end flex-wrap align-items-center">
                        <button type="button" class="btn btn-sm btn-light text-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#tambahjadwal">
                            <i class="fas fa-plus me-1"></i> Tambah Jadwal
                        </button>
                        <button type="button" class="btn btn-sm btn-light text-dark" data-bs-toggle="modal" data-bs-target="#exim">
                            <i class="fas fa-file-import me-1"></i> Export/Import
                        </button>
                        <button type="button" class="btn btn-sm btn-danger text-white fw-semibold" id="btnDeleteSelectedHeader" onclick="submitBulkDelete()" disabled>
                            <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih
                        </button>
                        <button type="button" class="btn btn-sm btn-danger text-white fw-semibold" onclick="confirmDeleteAll()">
                            <i class="fas fa-dumpster-fire me-1"></i> Hapus Semua
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- /.card-header -->
        <div class="card-body">
            <table id="tableJadwal" class="table table-bordered table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        @if(auth()->user()->role=='admin' OR auth()->user()->role=='kurikulum')
                        <th style="width: 40px; text-align: center;"><input type="checkbox" id="checkAll"></th>
                        @endif
                        <th>Kelas</th>
                        <th>Jam Ke</th>
                        <th>Jumlah Jam</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru</th>
                        <th>Hari</th>
                        @if(auth()->user()->role=='admin' OR auth()->user()->role=='kurikulum')
                        <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!-- /.card-body -->
    </div>
</form>

<form id="deleteAllForm" method="POST" action="/jadwal/delete-all" style="display:none;">
    {{ csrf_field() }}
</form>


   

          </div>
        </div>



<!-- Modal Tambah Jadwal -->
<div class="modal fade" id="tambahjadwal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        
        <h4 class="modal-title" id="exampleModalLabel">Tambah Jadwal</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/jadwal/create" method="POST"> 
                  {{csrf_field()}}
  

        <div class="form-group">
               
               <div class="form-group">
                <label >Kelas</label>
                <select name="kelas" class="form-control" >
                @foreach($ke_las as $kelas)
                  <option value="{{$kelas->kelas}}">{{$kelas->kelas}}</option>
                @endforeach                 
                </select>

                </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Jam Ke</label>
                <input name="jamke" type="text" class="form-control" id="jamke" aria-describedby="emailHelp" placeholder="Diisi 1-11" >
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jumlah Jam</label>
                <input name="jumlahjam" type="text" class="form-control" id="jumlahjam" aria-describedby="emailHelp" placeholder="Jumlah Jam" >
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
                <label >Guru</label>
                <select name="guru" class="form-control" >
                @foreach($gu_ru as $guru)
                  <option value="{{$guru->guru}}">{{$guru->guru}}</option>
                @endforeach                 
                </select>

                <div class="form-group">
                <label >Hari</label>
                <input name="hari" type="text" class="form-control" id="hari" aria-describedby="emailHelp" placeholder="Nama Hari Bahasa Inggris" >
                </div>

                <div class="form-group">
                <label >1</label>
                <input name="j1" type="text" class="form-control" id="j1" aria-describedby="emailHelp" value="0" >
                </div>

                <div class="form-group">
                <label >2</label>
                <input name="j2" type="text" class="form-control" id="j2" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >3</label>
                <input name="j3" type="text" class="form-control" id="j3" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >4</label>
                <input name="j4" type="text" class="form-control" id="j4" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >5</label>
                <input name="j5" type="text" class="form-control" id="j5" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >6</label>
                <input name="j6" type="text" class="form-control" id="j6" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >7</label>
                <input name="j7" type="text" class="form-control" id="j7" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >8</label>
                <input name="j8" type="text" class="form-control" id="j8" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >9</label>
                <input name="j9" type="text" class="form-control" id="j9" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >10</label>
                <input name="j10" type="text" class="form-control" id="j10" aria-describedby="emailHelp" value="0" >
                </div>

                                <div class="form-group">
                <label >11</label>
                <input name="j11" type="text" class="form-control" id="j11" aria-describedby="emailHelp" value="0" >
                </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Tambah Jadwal</button>
            </form>
            </div>

    </div>
  </div>
</div>

</div>
</div>
</div>
<!-- Modal Edit -->
<div class="modal fade" id="editjadwalpel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        
        <h4 class="modal-title" id="exampleModalLabel">Edit Jadwal</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/jadwal/update" method="POST"> 
                  {{csrf_field()}}
        <input type="hidden" name="jadwalid" id="jadwalid" value="">

        <div class="form-group">
               
               <div class="form-group">
                <label >Kelas</label>
                <select name="kelas" class="form-control" id="kelas">
                @foreach($ke_las as $kelas)
                      <option value="{{ $kelas->kelas }}"> {{ $kelas->kelas }}</option>
                @endforeach   
                </select>

                </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Jam Ke</label>
                <input name="jamke" type="text" class="form-control" id="jamke" aria-describedby="emailHelp" placeholder="Diisi 1-11" value="" >
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Jumlah Jam</label>
                <input name="jumlahjam" type="text" class="form-control" id="jumlahjam" aria-describedby="emailHelp" placeholder="Jumlah Jam" value="" >
              </div>

              <div class="form-group">
                <label >Mata Pelajaran</label>
                <select name="mapel" class="form-control" id="mapel">
                @foreach($ma_pel as $mapel)
                      <option value="{{ $mapel->mapel }}"> {{ $mapel->mapel }}</option>
                @endforeach                 
                </select>

                </div>
             
                <div class="form-group">
                <label >Guru</label>
                <select name="guru" class="form-control" id="guru">
                @foreach($gu_ru as $guru)
                      <option value="{{ $guru->guru }}"> {{ $guru->guru }}</option>
                @endforeach                 
                </select>
                @if(auth()->user()->role=='admin')
                <div class="form-group">
                <label >Hari</label>
                <input name="hari" type="text" class="form-control" id="hari" aria-describedby="emailHelp" placeholder="hari" value="">

                <div class="form-group">
                <label >1</label>
                <input name="j1" type="text" class="form-control" id="j1" aria-describedby="emailHelp" value="">
                </div>

                                <div class="form-group">
                <label >2</label>
                <input name="j2" type="text" class="form-control" id="j2" aria-describedby="emailHelp" value="">
                </div>

                                <div class="form-group">
                <label >3</label>
                <input name="j3" type="text" class="form-control" id="j3" aria-describedby="emailHelp" value="">
                </div>

                                <div class="form-group">
                <label >4</label>
                <input name="j4" type="text" class="form-control" id="j4" aria-describedby="emailHelp" value="">
                </div>

                                <div class="form-group">
                <label >5</label>
                <input name="j5" type="text" class="form-control" id="j5" aria-describedby="emailHelp" value="">
                </div>

                                <div class="form-group">
                <label >6</label>
                <input name="j6" type="text" class="form-control" id="j6" aria-describedby="emailHelp" value="">
                </div>

                                <div class="form-group">
                <label >7</label>
                <input name="j7" type="text" class="form-control" id="j7" aria-describedby="emailHelp" value="">
                </div>

                                <div class="form-group">
                <label >8</label>
                <input name="j8" type="text" class="form-control" id="j8" aria-describedby="emailHelp" value="">
                </div>

                                <div class="form-group">
                <label >9</label>
                <input name="j9" type="text" class="form-control" id="j9" aria-describedby="emailHelp" value="">
                </div>

                                <div class="form-group">
                <label >10</label>
                <input name="j10" type="text" class="form-control" id="j10" aria-describedby="emailHelp" value="">
                </div>

                <div class="form-group">
                <label >11</label>
                <input name="j11" type="text" class="form-control" id="j11" aria-describedby="emailHelp" value="">
                </div>
             @endif             
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Edit Jadwal</button>
            </form>
            </div>
    </div>
  </div>
</div>

</div>
</div>
</div>


<!-- Modal Export/Import -->
<div class="modal fade" id="exim" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel"><i class="fas fa-exchange-alt me-2"></i> Export / Import Jadwal Pelajaran</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Section: Export -->
        <div class="mb-4 pb-3 border-bottom">
          <h5 class="fw-bold text-primary mb-2"><i class="fas fa-file-export me-1"></i> Ekspor Jadwal Pelajaran</h5>
          <p class="text-muted small">Unduh jadwal pelajaran untuk seluruh kelas dalam bentuk file Excel.</p>
          <a href="/jadwal/export" class="btn btn-sm btn-success text-white"><i class="fas fa-file-excel me-1"></i> Unduh Jadwal (Excel)</a>
        </div>

        <!-- Section: Import -->
        <form action="/jadwal/import" method="POST" enctype="multipart/form-data"> 
          {{csrf_field()}}
          <h5 class="fw-bold text-primary mb-2"><i class="fas fa-file-import me-1"></i> Impor Jadwal Pelajaran</h5>
          <p class="text-muted small mb-3">Unggah data jadwal pelajaran baru menggunakan berkas Excel (.xlsx).</p>

          <div class="mb-3">
            <a href="/jadwal/template" class="btn btn-sm btn-info text-white"><i class="fas fa-file-download me-1"></i> Unduh Template Excel</a>
          </div>

          <div class="card bg-light p-3 mb-3 border-0 rounded">
            <h6 class="fw-bold text-secondary mb-2" style="font-size: 13px;"><i class="fas fa-info-circle me-1"></i> Petunjuk Penting:</h6>
            <ul class="mb-0 text-muted small ps-3">
              <li>File harus berformat <strong>.xlsx</strong>.</li>
              <li>Kolom Excel harus berurutan: <strong>A = Hari</strong>, <strong>B = Jam Ke</strong>, <strong>C = Kelas</strong>, <strong>D = Mata Pelajaran</strong>, <strong>E = Guru Pengajar</strong>.</li>
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


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isAdminOrKurikulum = {{ (auth()->user()->role=='admin' || auth()->user()->role=='kurikulum') ? 'true' : 'false' }};
        
        if ($.fn.DataTable.isDataTable('#tableJadwal')) {
            $('#tableJadwal').DataTable().destroy();
        }

        let cols = [];
        if (isAdminOrKurikulum) {
            cols.push({ data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' });
        }
        cols.push({ data: 'kelas', name: 'kelas' });
        cols.push({ data: 'jamke', name: 'jamke' });
        cols.push({ data: 'jumlahjam', name: 'jumlahjam' });
        cols.push({ data: 'mapel', name: 'mapel' });
        cols.push({ data: 'guru', name: 'guru' });
        cols.push({ data: 'hari', name: 'hari' });
        if (isAdminOrKurikulum) {
            cols.push({ data: 'aksi', name: 'aksi', orderable: false, searchable: false });
        }

        const tableJadwal = $('#tableJadwal').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: "{{ url('/jadwal') }}"
            },
            columns: cols,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
                search: "Cari Jadwal:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Jadwal tidak ditemukan"
            }
        });

        const checkAll = document.getElementById('checkAll');
        const btnDeleteSelected = document.getElementById('btnDeleteSelectedHeader');

        function toggleDeleteButton() {
            const checkedCount = $('#tableJadwal .checkItem:checked').length;
            if (btnDeleteSelected) {
                btnDeleteSelected.disabled = checkedCount === 0;
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                $('#tableJadwal .checkItem').prop('checked', checkAll.checked);
                toggleDeleteButton();
            });
        }

        $(document).on('change', '.checkItem', function() {
            if (checkAll) {
                const total = $('#tableJadwal .checkItem').length;
                const checked = $('#tableJadwal .checkItem:checked').length;
                checkAll.checked = (total > 0 && total === checked);
            }
            toggleDeleteButton();
        });
    });

    function submitBulkDelete() {
        if (confirm('Apakah Anda yakin ingin menghapus jadwal pelajaran yang terpilih?')) {
            document.getElementById('bulkDeleteForm').submit();
        }
    }

    function confirmDeleteAll() {
        if (confirm('Apakah Anda yakin ingin menghapus SELURUH data jadwal pelajaran? Tindakan ini akan mengosongkan semua jadwal dan tidak bisa dibatalkan!')) {
            document.getElementById('deleteAllForm').submit();
        }
    }
</script>

@endsection
