@extends('layouts.master')

@section('content')
<section class="content pt-2">
  <div class="container-fluid">
    @include('sdm._nav', [
      'title' => 'Pengajuan & Persetujuan Cuti Pegawai',
      'subtitle' => 'Modul khusus Cuti Tahunan, Sakit, Melahirkan, Alasan Penting, dan Cuti Besar (terpisah dari Izin Harian)'
    ])

    @if(!auth()->user()->hasRole('tamu') && !auth()->user()->hasRole('lihat'))
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-paper-plane text-primary me-2"></i>Form Pengajuan Cuti Pegawai</h6>
      </div>
      <div class="card-body">
        <form action="/sdm/cuti" method="POST" class="row g-3">
          @csrf
          <div class="col-md-4">
            <label class="form-label small fw-bold">Nama Pegawai (Guru / Tendik) <span class="text-danger">*</span></label>
            @if($canManageAll)
              <select name="pegawai_id" class="form-select" required>
                <option value="">-- Pilih Pegawai --</option>
                @foreach($pegawaiList as $p)
                  <option value="{{ $p->id }}">[{{ ucfirst($p->kategori) }}] {{ $p->nama }}</option>
                @endforeach
              </select>
            @elseif($myPegawai)
              <input type="hidden" name="pegawai_id" value="{{ $myPegawai->id }}">
              <input type="text" class="form-control bg-light fw-bold" value="{{ $myPegawai->nama }}" readonly>
            @else
              <select name="pegawai_id" class="form-select" required>
                <option value="">-- Pilih Nama Anda --</option>
                @foreach($pegawaiList as $p)
                  <option value="{{ $p->id }}">{{ $p->nama }}</option>
                @endforeach
              </select>
            @endif
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-bold">Jenis Cuti <span class="text-danger">*</span></label>
            <select name="jenis_cuti" class="form-select" required>
              <option value="Cuti Tahunan">Cuti Tahunan</option>
              <option value="Cuti Sakit">Cuti Sakit</option>
              <option value="Cuti Melahirkan">Cuti Melahirkan</option>
              <option value="Cuti Alasan Penting">Cuti Alasan Penting</option>
              <option value="Cuti Besar">Cuti Besar</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_mulai" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-bold">Tanggal Selesai <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_selesai" class="form-control" required>
          </div>
          <div class="col-md-9">
            <label class="form-label small fw-bold">Alasan / Keterangan Cuti <span class="text-danger">*</span></label>
            <input type="text" name="alasan" class="form-control" placeholder="Keperluan keluarga, pemulihan kesehatan, dll." required>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary fw-bold w-100"><i class="fas fa-paper-plane me-1"></i> Ajukan Cuti</button>
          </div>
        </form>
      </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-calendar-check text-success me-2"></i>Riwayat Pengajuan Cuti Pegawai</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Nama Pegawai</th>
                <th>Jenis Cuti</th>
                <th>Periode</th>
                <th>Alasan</th>
                <th class="text-center">Status</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($cutiList as $i => $c)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td class="fw-bold">{{ $c->pegawai->nama ?? '-' }}</td>
                <td class="fw-semibold text-primary">{{ $c->jenis_cuti }}</td>
                <td class="font-monospace small">{{ $c->tanggal_mulai->format('d/m/Y') }} s.d. {{ $c->tanggal_selesai->format('d/m/Y') }}</td>
                <td>{{ $c->alasan }}</td>
                <td class="text-center">
                  @if($c->status === 'Disetujui')
                    <span class="badge bg-success">Disetujui</span>
                  @elseif($c->status === 'Ditolak')
                    <span class="badge bg-danger">Ditolak</span>
                  @else
                    <span class="badge bg-warning text-dark">Diajukan</span>
                  @endif
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    @if($canApprove && $c->status === 'Diajukan')
                      <form action="/sdm/cuti/{{ $c->id }}/status" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="status" value="Disetujui">
                        <button type="submit" class="btn btn-success" title="Setujui"><i class="fas fa-check"></i></button>
                      </form>
                      <form action="/sdm/cuti/{{ $c->id }}/status" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="status" value="Ditolak">
                        <button type="submit" class="btn btn-warning" title="Tolak"><i class="fas fa-times"></i></button>
                      </form>
                    @endif
                    @if($canApprove || $c->user_id === auth()->id())
                      <form action="/sdm/cuti/{{ $c->id }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengajuan cuti ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">Belum ada pengajuan cuti pegawai.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
