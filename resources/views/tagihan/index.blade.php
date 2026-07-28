@extends('layouts.master')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <i class="fa fa-check-circle"></i> 
            {{ session('sukses') }}
        </div>
        @endif
<div class="card shadow-sm border-0">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h3 class="card-title fw-bold m-0 text-dark"><i class="fas fa-money-bill-wave me-2 text-primary"></i> Data Tagihan Siswa</h3>
                            @if(auth()->user()->role == 'admin' || auth()->user()->role == 'keuangan')
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                                    <i class="fas fa-plus me-1"></i> Tambah Data
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#eximModal">
                                    <i class="fas fa-exchange-alt me-1"></i> Export / Import
                                </button>
                                <form action="{{ route('tagihan.deleteAll') }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua data tagihan?')">
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt me-1"></i> Hapus Semua</button>
                                </form>
                            </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <table id="example3" class="table table-bordered table-striped table-hover align-middle">
    <thead>
            <tr>
                <th>ID</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Dana Komite</th>
                <th>Tagihan Lain</th>
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'keuangan')
                <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($tagihan as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->nis }}</td>
                <td>{{ $item->nama }}</td>
                <td>{{ $item->kelas }}</td>
                <td>{{ 'Rp. ' . number_format($item->dana_komite, 0, ',', '.') }}</td>
<td>{{ 'Rp. ' . number_format($item->tagihan_lain, 0, ',', '.') }}</td>
@if(auth()->user()->role == 'admin' || auth()->user()->role == 'keuangan')
                <td>
                    <!-- Button trigger modal for Edit -->
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                        Edit
                    </button>

                    <!-- Delete Form -->
                    <form action="{{ route('tagihan.destroy', $item->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </td>
                @endif
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $item->id }}">Edit Data Tagihan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('tagihan.update', $item->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="nis">NIS</label>
                                    <input type="text" class="form-control" name="nis" value="{{ $item->nis }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="nama">Nama</label>
                                    <input type="text" class="form-control" name="nama" value="{{ $item->nama }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="kelas">Kelas</label>
                                    <input type="text" class="form-control" name="kelas" value="{{ $item->kelas }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="dana_komite">Dana Komite</label>
                                    <input type="number" class="form-control" name="dana_komite" value="{{ $item->dana_komite }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="tagihan_lain">Tagihan Lain</label>
                                    <input type="number" class="form-control" name="tagihan_lain" value="{{ $item->tagihan_lain }}" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </tbody>
    </table>
</div>
</div>
                    </div>
                </div>
            </div>
        </div>  

<!-- Modal Tambah Data -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Tambah Data Tagihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('tagihan.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nis">NIS</label>
                        <input type="text" class="form-control" name="nis" required>
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="kelas">Kelas</label>
                        <input type="text" class="form-control" name="kelas" required>
                    </div>
                    <div class="form-group">
                        <label for="dana_komite">Dana Komite</label>
                        <input type="number" class="form-control" name="dana_komite" required>
                    </div>
                    <div class="form-group">
                        <label for="tagihan_lain">Tagihan Lain</label>
                        <input type="number" class="form-control" name="tagihan_lain" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(auth()->user()->role == 'admin' || auth()->user()->role == 'keuangan')
<!-- Modal Export/Import -->
<div class="modal fade" id="eximModal" tabindex="-1" role="dialog" aria-labelledby="eximModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="eximModalLabel"><i class="fas fa-exchange-alt me-2"></i> Export / Import Tagihan Siswa</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Section: Export -->
        <div class="mb-4 pb-3 border-bottom">
          <h5 class="fw-bold text-primary mb-2"><i class="fas fa-file-export me-1"></i> Ekspor Data Tagihan</h5>
          <p class="text-muted small">Unduh seluruh data tagihan siswa dalam bentuk berkas Excel (.xlsx).</p>
          <a href="{{ route('tagihan.export') }}" class="btn btn-sm btn-success text-white"><i class="fas fa-file-excel me-1"></i> Unduh Tagihan (Excel)</a>
        </div>

        <!-- Section: Import -->
        <form action="{{ route('tagihan.import') }}" method="POST" enctype="multipart/form-data"> 
          @csrf
          <h5 class="fw-bold text-primary mb-2"><i class="fas fa-file-import me-1"></i> Impor Data Tagihan</h5>
          <p class="text-muted small mb-3">Unggah berkas Excel (.xlsx) untuk menambahkan atau merestart data tagihan siswa.</p>

          <div class="mb-3">
            <a href="{{ route('tagihan.template') }}" class="btn btn-sm btn-info text-white"><i class="fas fa-file-download me-1"></i> Unduh Template Excel</a>
          </div>

          <div class="card bg-light p-3 mb-3 border-0 rounded">
            <h6 class="fw-bold text-secondary mb-2" style="font-size: 13px;"><i class="fas fa-info-circle me-1"></i> Petunjuk Penting:</h6>
            <ul class="mb-0 text-muted small ps-3">
              <li>Berkas harus berformat <strong>.xlsx</strong> atau <strong>.xls</strong>.</li>
              <li>Kolom Excel harus berurutan: <strong>A = NIS</strong>, <strong>B = Nama Siswa</strong>, <strong>C = Kelas</strong>, <strong>D = Dana Komite</strong>, <strong>E = Tagihan Lain</strong>.</li>
              <li>Baris pertama (header) akan diabaikan secara otomatis oleh sistem.</li>
            </ul>
          </div>

          <div class="form-group mb-0">
            <label for="file" class="form-label small fw-bold">Pilih File Excel (.xlsx)</label>
            <input name="file" type="file" class="form-control" id="file" accept=".xlsx,.xls" required>
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
@endif

@endsection
