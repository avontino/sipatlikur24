@extends('layouts.master')

@section('content')
<section class="content pt-3">
    <div class="container-fluid">
        <div class="card shadow border-0" style="border-radius: 12px;">
            <div class="card-header bg-light py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h3 class="fw-bold m-0" style="color: #004d1a;"><i class="fas fa-history me-2"></i>Log Aktivitas Pengguna (Audit Trail)</h3>
                    <p class="text-muted mb-0 small" style="font-size: 12px;">Rekam jejak seluruh aktivitas pengguna dan perubahan data di sistem SIPATLIKUR</p>
                </div>
                <form action="/admin/logs" method="GET" class="d-flex gap-2 flex-grow-1 flex-md-grow-0" style="max-width: 380px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="cari" class="form-control" placeholder="Cari username, nama, atau aktivitas..." value="{{ request('cari') }}">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Cari</button>
                        @if(request()->filled('cari'))
                            <a href="/admin/logs" class="btn btn-outline-secondary" title="Reset Pencarian"><i class="fas fa-undo"></i></a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" style="font-size: 12px; min-width: 850px;">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2 px-3 text-center" style="width: 175px;">Waktu Kejadian</th>
                                <th class="py-2 px-3" style="width: 150px;">Username</th>
                                <th class="py-2 px-3" style="width: 180px;">Nama Pengguna</th>
                                <th class="py-2 px-3 text-center" style="width: 120px;">Role / Peran</th>
                                <th class="py-2 px-3">Deskripsi Aktivitas</th>
                                <th class="py-2 px-3 text-center" style="width: 130px;">IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="py-2 px-3 text-center small font-monospace text-muted">
                                    <i class="far fa-clock me-1 text-info"></i>{{ date('d M Y - H:i:s', strtotime($log->created_at)) }}
                                </td>
                                <td class="py-2 px-3 font-weight-bold text-primary">{{ $log->username }}</td>
                                <td class="py-2 px-3 text-dark font-weight-bold">{{ $log->nama_pengguna }}</td>
                                <td class="py-2 px-3 text-center">
                                    @php
                                        $badgeBg = 'bg-secondary';
                                        $r = strtolower($log->role);
                                        if (str_contains($r, 'admin')) $badgeBg = 'bg-danger';
                                        elseif (str_contains($r, 'guru')) $badgeBg = 'bg-primary';
                                        elseif (str_contains($r, 'walikelas')) $badgeBg = 'bg-success';
                                        elseif (str_contains($r, 'kurikulum')) $badgeBg = 'bg-info';
                                        elseif (str_contains($r, 'siswa')) $badgeBg = 'bg-warning text-dark';
                                    @endphp
                                    <span class="badge {{ $badgeBg }} px-2 py-1 text-capitalize" style="font-size: 10px;">{{ $log->role }}</span>
                                </td>
                                <td class="py-2 px-3 text-dark">
                                    @if(str_contains(strtolower($log->aktivitas), 'login'))
                                        <i class="fas fa-sign-in-alt text-success me-1"></i>
                                    @elseif(str_contains(strtolower($log->aktivitas), 'logout'))
                                        <i class="fas fa-sign-out-alt text-danger me-1"></i>
                                    @elseif(str_contains(strtolower($log->aktivitas), 'hapus') || str_contains(strtolower($log->aktivitas), 'basmi'))
                                        <i class="fas fa-trash-alt text-danger me-1"></i>
                                    @elseif(str_contains(strtolower($log->aktivitas), 'tambah') || str_contains(strtolower($log->aktivitas), 'impor'))
                                        <i class="fas fa-plus-circle text-primary me-1"></i>
                                    @elseif(str_contains(strtolower($log->aktivitas), 'update') || str_contains(strtolower($log->aktivitas), 'perbarui') || str_contains(strtolower($log->aktivitas), 'edit'))
                                        <i class="fas fa-edit text-warning me-1"></i>
                                    @else
                                        <i class="fas fa-info-circle text-secondary me-1"></i>
                                    @endif
                                    {{ $log->aktivitas }}
                                </td>
                                <td class="py-2 px-3 text-center small text-muted font-monospace">
                                    <span class="badge bg-light text-dark border px-2 py-1">{{ $log->ip_address }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary"></i>
                                    Belum ada catatan log aktivitas pengguna.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
            <div class="card-footer bg-light py-3">
                <div class="d-flex flex-column align-items-center gap-2">
                    {{ $logs->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
