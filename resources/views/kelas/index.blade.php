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
                        <h3 class="fw-bold m-0" style="color: #004d1a;"><i class="fas fa-chalkboard me-2"></i>Data Kelas</h3>
                        @if(auth()->user()->role=='admin')
                        <button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#tambahKelas">
                            <i class="fas fa-plus me-1"></i> Tambah Kelas
                        </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example3" class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">No</th>
                                        <th>Kelas</th>
                                        <th>Jumlah Siswa</th>
                                        @if(auth()->user()->role=='admin')
                                        <th style="width: 200px;">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach($data_kelas as $kelas)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $kelas->kelas }}</td>
                                        <td>{{ $kelas->jumlah ?? '-' }}</td>
                                        @if(auth()->user()->role=='admin')
                                        <td>
                                            <button type="button" class="btn btn-warning btn-sm edit-btn text-dark" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editKelas"
                                                data-id="{{ $kelas->id }}"
                                                data-kelas="{{ $kelas->kelas }}"
                                                data-jumlah="{{ $kelas->jumlah }}">
                                                Edit
                                            </button>
                                            <a href="/kelas/{{ $kelas->id }}/delete" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kelas ini?')">Hapus</a>
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

<!-- Modal Tambah Kelas -->
@if(auth()->user()->role=='admin')
<div class="modal fade" id="tambahKelas" tabindex="-1" aria-labelledby="tambahKelasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/kelas/create" method="POST">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahKelasLabel">Tambah Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="kelas_name">Nama Kelas</label>
                        <input name="kelas" type="text" class="form-control" id="kelas_name" placeholder="Contoh: X 1" required>
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

<!-- Modal Edit Kelas -->
<div class="modal fade" id="editKelas" tabindex="-1" aria-labelledby="editKelasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/kelas/update" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="kelasid" id="edit_kelas_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKelasLabel">Edit Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="edit_kelas_name">Nama Kelas</label>
                        <input name="kelas" type="text" class="form-control" id="edit_kelas_name" required>
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
        var editModal = document.getElementById('editKelas');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var kelas = button.getAttribute('data-kelas');

            document.getElementById('edit_kelas_id').value = id;
            document.getElementById('edit_kelas_name').value = kelas;
        });
    });
</script>
@endif
@endsection
