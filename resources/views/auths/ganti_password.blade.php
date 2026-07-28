@extends('layouts.master')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark fw-bold"><i class="fas fa-key me-2 text-primary"></i> Pengaturan Akun</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-lg-5">
                
                @if(session('sukses'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2 fs-5"></i>
                        <div>{{ session('sukses') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('gagal'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2 fs-5"></i>
                        <div>{{ session('gagal') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle me-2 fs-5 mt-1"></i>
                        <div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="card card-primary card-outline border-top-primary shadow-sm rounded-3 overflow-hidden">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title m-0 fw-bold text-secondary">Ganti Password</h5>
                    </div>
                    <form action="/ganti-password" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="password_sekarang" class="form-label small fw-bold text-secondary">Password Saat Ini</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password_sekarang" id="password_sekarang" class="form-control border-start-0" placeholder="Masukkan password saat ini" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password_baru" class="form-label small fw-bold text-secondary">Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-key"></i></span>
                                    <input type="password" name="password_baru" id="password_baru" class="form-control border-start-0" placeholder="Minimal 6 karakter" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="konfirmasi_password_baru" class="form-label small fw-bold text-secondary">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-check-double"></i></span>
                                    <input type="password" name="konfirmasi_password_baru" id="konfirmasi_password_baru" class="form-control border-start-0" placeholder="Ulangi password baru" required>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light d-flex justify-content-end gap-2 py-3">
                            <a href="/dashboard" class="btn btn-secondary px-4"><i class="fas fa-arrow-left me-1"></i> Batal</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Simpan Password</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
