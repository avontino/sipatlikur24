@extends('layouts.master')

@section('content')
<section class="content pt-3">
    <div class="container-fluid">
        @if(session('sukses'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                <h3 class="fw-bold m-0" style="color: #002366;"><i class="fas fa-gavel me-2"></i>Master Kategori Poin Kedisiplinan & Prestasi</h3>
                <button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="fas fa-plus me-1"></i> Tambah Kategori
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example3" class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">No</th>
                                <th>Nama Kategori</th>
                                <th style="width: 150px;">Jenis</th>
                                <th style="width: 120px;">Bobot Poin</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($kategori_poin as $k)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td class="fw-bold text-dark">{{ $k->nama }}</td>
                                <td>
                                    @if($k->jenis == 'pelanggaran')
                                        <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> Pelanggaran</span>
                                    @else
                                        <span class="badge bg-success"><i class="fas fa-trophy me-1"></i> Prestasi</span>
                                    @endif
                                </td>
                                <td class="fw-bold font-monospace text-center">{{ $k->poin }}</td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm text-white" 
                                        data-myid="{{ $k->id }}"
                                        data-mynama="{{ $k->nama }}"
                                        data-myjenis="{{ $k->jenis }}"
                                        data-mypoin="{{ $k->poin }}"
                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="/kategori-poin/{{ $k->id }}/delete" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="tambahModalLabel"><i class="fas fa-plus me-1 text-primary"></i> Tambah Kategori Poin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/kategori-poin/create" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-bold">Nama Kategori Pelanggaran / Prestasi</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Terlambat, Juara Lomba" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis" class="form-label small fw-bold">Jenis</label>
                        <select name="jenis" class="form-select" required>
                            <option value="pelanggaran">Pelanggaran</option>
                            <option value="prestasi">Prestasi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="poin" class="form-label small fw-bold">Bobot Poin</label>
                        <input type="number" name="poin" class="form-control" placeholder="Contoh: 10" required>
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

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editModalLabel"><i class="fas fa-edit me-1 text-warning"></i> Edit Kategori Poin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/kategori-poin/update" method="POST">
                @csrf
                <input type="hidden" name="kategoriid" id="kategoriid">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nama" class="form-label small fw-bold">Nama Kategori Pelanggaran / Prestasi</label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jenis" class="form-label small fw-bold">Jenis</label>
                        <select name="jenis" id="edit_jenis" class="form-select" required>
                            <option value="pelanggaran">Pelanggaran</option>
                            <option value="prestasi">Prestasi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_poin" class="form-label small fw-bold">Bobot Poin</label>
                        <input type="number" name="poin" id="edit_poin" class="form-control" required>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-myid');
            const nama = button.getAttribute('data-mynama');
            const jenis = button.getAttribute('data-myjenis');
            const poin = button.getAttribute('data-mypoin');

            editModal.querySelector('#kategoriid').value = id;
            editModal.querySelector('#edit_nama').value = nama;
            editModal.querySelector('#edit_jenis').value = jenis;
            editModal.querySelector('#edit_poin').value = poin;
        });
    }
});
</script>
@endsection
