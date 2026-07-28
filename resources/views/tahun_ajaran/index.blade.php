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
        @if(session('gagal'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('gagal') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-3">
                        <h3 class="fw-bold m-0" style="color: #002366;"><i class="fas fa-calendar-alt me-2"></i>Data Tahun Ajaran & Semester</h3>
                        @if(auth()->user()->role=='admin')
                        <button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#tambahTahunAjaran">
                            <i class="fas fa-plus me-1"></i> Tambah Tahun Ajaran
                        </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example3" class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">No</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Semester</th>
                                        <th>Status Aktif</th>
                                        @if(auth()->user()->role=='admin')
                                        <th style="width: 250px;">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach($data_tahun_ajaran as $ta)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $ta->tahun_ajaran }}</td>
                                        <td>{{ $ta->semester }}</td>
                                        <td>
                                            @if($ta->status == 1)
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Aktif (Muncul di Login)</span>
                                            @else
                                            <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i> Tidak Aktif</span>
                                            @endif
                                        </td>
                                        @if(auth()->user()->role=='admin')
                                        <td>
                                            <form action="/tahun-ajaran/toggle-status/{{ $ta->id }}" method="POST" class="d-inline">
                                                {{ csrf_field() }}
                                                <button type="submit" class="btn btn-sm {{ $ta->status == 1 ? 'btn-warning' : 'btn-success' }}">
                                                    {{ $ta->status == 1 ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            <a href="/tahun-ajaran/{{ $ta->id }}/delete" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?')">Hapus</a>
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

<!-- Modal Tambah Tahun Ajaran -->
@if(auth()->user()->role=='admin')
<div class="modal fade" id="tambahTahunAjaran" tabindex="-1" aria-labelledby="tambahTahunAjaranLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/tahun-ajaran/create" method="POST">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahTahunAjaranLabel">Tambah Tahun Ajaran & Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="tahun_ajaran_input">Tahun Ajaran</label>
                        <input name="tahun_ajaran" type="text" class="form-control" id="tahun_ajaran_input" placeholder="Contoh: 2025/2026" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="semester_input">Semester</label>
                        <select name="semester" class="form-control" id="semester_input" required>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
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
@endif
@endsection
