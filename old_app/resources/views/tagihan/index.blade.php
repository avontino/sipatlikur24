@extends('layouts.master')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <i class="fa fa-check-circle"></i> 
            {{ session('sukses') }}
        </div>
        @endif
<div class="card">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="card-header">
                            <h3 class="panel-title">Data Tagihan Siswa</h3>
                        </div>
                        <div class="card-body">
                        @if(auth()->user()->role == 'admin' || auth()->user()->role == 'keuangan')
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal">
        Tambah Data
    </button>
    <a href="{{ route('tagihan.export') }}" class="btn btn-success">Export Excel</a>
    <form action="{{ route('tagihan.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit" class="btn btn-info">Import Excel</button>
    </form>
                          <!-- Tombol Hapus Semua -->
     <form action="{{ route('tagihan.deleteAll') }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua data tagihan?')">
    {{ csrf_field() }}
    <button type="submit" class="btn btn-danger">Hapus Semua</button>
</form>
    @endif
    <table id="example3" class="table table-bordered">
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
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal{{ $item->id }}">
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
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
