@extends('layouts.master')

@section('content')
<section class="content pt-3">
  <div class="container-fluid">
    <div class="content-header py-3 px-3 mb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 16px; border: 1px solid #e2e8f0;">
      <div class="row align-items-center">
        <div class="col-md-7">
          <div class="d-flex align-items-center">
            <div class="me-3 p-3 rounded-4 shadow-sm text-white" style="background: linear-gradient(135deg, #009638, #004d1a);">
              <i class="fas fa-bell fa-lg"></i>
            </div>
            <div>
              <h3 class="m-0 text-dark fw-bolder" style="font-size: 1.4rem;">Daftar Semua Notifikasi</h3>
              <p class="text-muted small mb-0 mt-1">
                Anda memiliki <strong>{{ $unreadCount }}</strong> notifikasi belum dibaca dari total <strong>{{ $notifications->total() }}</strong> notifikasi.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-5 mt-3 mt-md-0 text-md-end d-flex justify-content-md-end flex-wrap gap-2">
          @if($unreadCount > 0)
          <form action="{{ route('notifications.markAllRead') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm fw-bold rounded-pill px-3">
              <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
            </button>
          </form>
          @endif
          <form action="{{ route('notifications.clearRead') }}" method="POST" class="m-0" onsubmit="return confirm('Hapus semua notifikasi yang sudah dibaca?');">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm fw-bold rounded-pill px-3">
              <i class="fas fa-broom me-1"></i> Bersihkan yang Sudah Dibaca
            </button>
          </form>
        </div>
      </div>
    </div>

    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
      <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width: 50px;">No</th>
                <th style="width: 120px;">Status</th>
                <th>Judul & Pesan Notifikasi</th>
                <th style="width: 170px;">Waktu</th>
                <th class="text-center" style="width: 160px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($notifications as $i => $notif)
              @php
                $data = is_array($notif->data) ? $notif->data : json_decode($notif->data, true);
                $isUnread = is_null($notif->read_at);
                $cat = $data['category'] ?? '';
                $icon = match($cat) {
                    'jurnal' => 'fa-book text-primary',
                    'absen'  => 'fa-user-check text-success',
                    'ijin'   => 'fa-id-card text-warning',
                    'garjas' => 'fa-running text-info',
                    default  => 'fa-bell text-secondary',
                };
              @endphp
              <tr class="{{ $isUnread ? 'table-success bg-opacity-25' : '' }}">
                <td class="text-center">{{ $notifications->firstItem() + $i }}</td>
                <td>
                  @if($isUnread)
                    <span class="badge bg-danger">Belum Dibaca</span>
                  @else
                    <span class="badge bg-secondary">Sudah Dibaca</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex align-items-start gap-2">
                    <i class="fas {{ $icon }} mt-1"></i>
                    <div>
                      <div class="fw-bold text-dark">{{ $data['title'] ?? 'Notifikasi' }}</div>
                      <div class="text-muted small">{{ $data['message'] ?? '' }}</div>
                    </div>
                  </div>
                </td>
                <td class="small text-muted">
                  <div>{{ $notif->created_at ? $notif->created_at->format('d M Y, H:i') : '-' }}</div>
                  <div style="font-size: 11px;">{{ $notif->created_at ? $notif->created_at->diffForHumans() : '' }}</div>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('notifications.read', $notif->id) }}" class="btn btn-outline-primary" title="Buka & Tandai Dibaca">
                      <i class="fas fa-external-link-alt me-1"></i> Buka
                    </a>
                    <form action="{{ route('notifications.destroy', $notif->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus notifikasi ini?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center py-5 text-muted">
                  <i class="far fa-bell-slash fa-3x mb-2 opacity-50 d-block"></i>
                  Belum ada notifikasi.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      @if($notifications->hasPages())
      <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Menampilkan {{ $notifications->firstItem() }} - {{ $notifications->lastItem() }} dari {{ $notifications->total() }} notifikasi</small>
        {{ $notifications->links('pagination::bootstrap-5') }}
      </div>
      @endif
    </div>
  </div>
</section>
@endsection
