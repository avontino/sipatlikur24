@extends('layouts.master')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        @if(session('sukses'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('gagal'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-1"></i> {{ session('gagal') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Card Backup -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light">
                        <h5 class="m-0 fw-bold text-dark"><i class="fas fa-download me-2 text-success"></i> Backup Database</h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <p class="text-muted small">Unduh salinan cadangan lengkap database sekolah Anda sekarang. File cadangan akan berformat `.sql` dan berisi semua data master, jurnal, presensi, kedisiplinan, serta data siswa.</p>
                            <div class="alert alert-info border-0 rounded small" style="background: #e8f4fd; color: #1d6fa5;">
                                <i class="fas fa-info-circle me-1"></i> Disarankan untuk melakukan pencadangan database secara berkala sebelum melakukan perubahan besar pada data sekolah.
                            </div>
                        </div>
                        <form action="{{ route('admin.backup.download') }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 text-white py-2 fw-bold">
                                <i class="fas fa-file-download me-1"></i> Unduh File Backup (.sql)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Card Restore -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light">
                        <h5 class="m-0 fw-bold text-dark"><i class="fas fa-upload me-2 text-danger"></i> Restore Database</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Kembalikan kondisi database sekolah ke tanggal pencadangan sebelumnya dengan mengunggah file SQL hasil backup.</p>
                        
                        <div class="alert alert-danger border-0 rounded small" style="background: #fde8e8; color: #a51d1d;">
                            <i class="fas fa-exclamation-triangle me-1"></i> <strong>PENTING:</strong> Proses ini akan menghapus dan menimpa seluruh data database yang aktif saat ini. Data yang tidak dicadangkan akan hilang!
                        </div>

                        <form action="{{ route('admin.backup.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirmRestore(event)">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label small fw-bold">Pilih Berkas Cadangan (.sql)</label>
                                <input type="file" name="file" id="file" class="form-control" accept=".sql" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">
                                <i class="fas fa-undo-alt me-1"></i> Jalankan Restore Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function confirmRestore(e) {
    e.preventDefault();
    const form = e.target;
    
    // First warning
    const confirm1 = confirm("PERINGATAN UTAMA:\nApakah Anda yakin ingin merestore database? Seluruh data aktif di sistem saat ini akan diganti sepenuhnya dengan data dari file backup!");
    if (!confirm1) return false;
    
    // Second warning (extra safety check)
    const confirm2 = confirm("PERINGATAN AKHIR:\nTindakan ini tidak dapat dibatalkan. Pastikan file SQL yang diunggah valid dan berasal dari backup resmi. Lanjutkan restore?");
    if (confirm2) {
        form.submit();
    }
    return false;
}
</script>
@endsection
