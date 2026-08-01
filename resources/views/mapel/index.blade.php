@extends('layouts.master')

@section('content')
<section class="content pt-3">
    <div class="container-fluid">
        <!-- Flash Messages -->
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-3">
                        <h3 class="fw-bold m-0" style="color: #004d1a;"><i class="fas fa-book me-2"></i>Data Mata Pelajaran</h3>
                        @if(auth()->user()->role=='admin')
                        <button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#tambahMapel">
                            <i class="fas fa-plus me-1"></i> Tambah Mapel
                        </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example3" class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">No</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Guru Pengampu Default</th>
                                        @if(auth()->user()->role=='admin')
                                        <th style="width: 200px;">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach($data_mapel as $mapel)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $mapel->mapel }}</td>
                                        <td>{{ $mapel->guru ?? '-' }}</td>
                                        @if(auth()->user()->role=='admin')
                                        <td>
                                            <button type="button" class="btn btn-warning btn-sm edit-btn text-dark" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editMapel"
                                                data-id="{{ $mapel->id }}"
                                                data-mapel="{{ $mapel->mapel }}"
                                                data-guru="{{ $mapel->guru }}">
                                                Edit
                                            </button>
                                            <a href="/mapel/{{ $mapel->id }}/delete" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus mata pelajaran ini?')">Hapus</a>
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

<!-- Modal Tambah Mapel -->
@if(auth()->user()->role=='admin')
<div class="modal fade" id="tambahMapel" tabindex="-1" aria-labelledby="tambahMapelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/mapel/create" method="POST">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahMapelLabel">Tambah Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="mapel_name">Nama Mata Pelajaran</label>
                        <input name="mapel" type="text" class="form-control" id="mapel_name" placeholder="Contoh: MATEMATIKA" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="mapel_guru">Guru Pengampu Default</label>
                        <input name="guru" type="text" class="form-control" id="mapel_guru" placeholder="Contoh: Ahmad, S.Pd.">
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

<!-- Modal Edit Mapel -->
<div class="modal fade" id="editMapel" tabindex="-1" aria-labelledby="editMapelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/mapel/update" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="mapelid" id="edit_mapel_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMapelLabel">Edit Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="edit_mapel_name">Nama Mata Pelajaran</label>
                        <input name="mapel" type="text" class="form-control" id="edit_mapel_name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_mapel_guru">Guru Pengampu Default</label>
                        <input name="guru" type="text" class="form-control" id="edit_mapel_guru">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var editModal = document.getElementById('editMapel');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var mapel = button.getAttribute('data-mapel');
            var guru = button.getAttribute('data-guru');

            document.getElementById('edit_mapel_id').value = id;
            document.getElementById('edit_mapel_name').value = mapel;
            document.getElementById('edit_mapel_guru').value = guru;
        });
    });
</script>
@endif
@endsection
