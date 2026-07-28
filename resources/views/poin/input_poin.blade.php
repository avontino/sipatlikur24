@extends('layouts.master')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        @if(session('sukses'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <i class="fa fa-check-circle"></i> {{session('sukses')}}
        </div>
        @endif
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <h5 class="m-0 fw-bold text-dark card-title"><i class="fas fa-star me-2 text-warning"></i> Rekap Kredit Poin & Surat Peringatan (SP) Siswa</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example3" class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th style="width: 180px;" class="text-center">Poin Pelanggaran</th>
                                <th style="width: 180px;" class="text-center">Poin Prestasi</th>
                                <th style="width: 150px;" class="text-center">Input Poin</th>
                                <th style="width: 250px;">Aksi Surat Peringatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($data_siswa as $siswa)
                            @php 
                                $poin_pelanggaran = $siswa->totalPoinPelanggaran();
                                $poin_prestasi = $siswa->totalPoinPrestasi();
                            @endphp
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td class="font-monospace">{{ $siswa->nis }}</td>
                                <td class="fw-bold text-dark">{{ $siswa->nama }}</td>
                                <td>{{ $siswa->kelas }}</td>
                                <td class="text-center">
                                    @if($poin_pelanggaran > 0)
                                        <span class="badge bg-danger fs-6">{{ $poin_pelanggaran }} Poin</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">0 Poin</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($poin_prestasi > 0)
                                        <span class="badge bg-success fs-6">{{ $poin_prestasi }} Poin</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">0 Poin</span>
                                    @endif
                                </td>
                                 <td class="text-center">
                                     @if(auth()->user()->role=='admin' || auth()->user()->hasRole('pembina') || auth()->user()->hasRole('kesiswaan'))
                                         <button type="button" class="btn btn-primary btn-sm btn-isi-poin" 
                                                 data-id="{{ $siswa->id }}" 
                                                 data-nama="{{ $siswa->nama }}" 
                                                 data-kelas="{{ $siswa->kelas }}"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#isiPoinModal">
                                             <i class="fas fa-plus-circle me-1"></i> Isi Poin
                                         </button>
                                     @else
                                         <span class="text-muted small">Hanya Baca</span>
                                     @endif
                                 </td>
                                 <td>
                                     <div class="d-flex gap-1">
                                         @if($poin_pelanggaran >= 50 && $poin_pelanggaran < 75)
                                             <a href="/poin-siswa/{{ $siswa->id }}/cetak-sp/1" target="_blank" class="btn btn-warning btn-sm text-white">
                                                 <i class="fas fa-print me-1"></i> Cetak SP 1
                                             </a>
                                         @elseif($poin_pelanggaran >= 75 && $poin_pelanggaran < 100)
                                             <a href="/poin-siswa/{{ $siswa->id }}/cetak-sp/1" target="_blank" class="btn btn-warning btn-sm text-white me-1">
                                                 SP 1
                                             </a>
                                             <a href="/poin-siswa/{{ $siswa->id }}/cetak-sp/2" target="_blank" class="btn btn-danger btn-sm">
                                                 <i class="fas fa-print me-1"></i> Cetak SP 2
                                             </a>
                                         @elseif($poin_pelanggaran >= 100)
                                             <a href="/poin-siswa/{{ $siswa->id }}/cetak-sp/1" target="_blank" class="btn btn-warning btn-sm text-white me-1">
                                                 SP 1
                                             </a>
                                             <a href="/poin-siswa/{{ $siswa->id }}/cetak-sp/2" target="_blank" class="btn btn-danger btn-sm me-1">
                                                 SP 2
                                             </a>
                                             <a href="/poin-siswa/{{ $siswa->id }}/cetak-sp/3" target="_blank" class="btn bg-dark text-white btn-sm">
                                                 <i class="fas fa-print me-1"></i> Cetak SP 3
                                             </a>
                                         @else
                                             <span class="text-muted small">Tidak ada SP aktif</span>
                                         @endif
                                     </div>
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

<!-- Modal Isi Poin -->
<div class="modal fade" id="isiPoinModal" tabindex="-1" aria-labelledby="isiPoinModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="isiPoinModalLabel"><i class="fas fa-plus-circle me-2"></i> Input Kredit Poin Siswa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/poin-siswa/create" method="POST">
                @csrf
                <input type="hidden" name="siswa_id" id="modal_siswa_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Siswa</label>
                        <input type="text" class="form-control bg-light" id="modal_siswa_nama" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_kategori_poin_id" class="form-label fw-bold">Kategori Kasus / Prestasi</label>
                        <select name="kategori_poin_id" id="modal_kategori_poin_id" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategori_poin as $kp)
                                <option value="{{ $kp->id }}">{{ $kp->nama_kategori }} ({{ ucfirst($kp->jenis) }} - {{ $kp->poin }} Poin)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modal_pelapor" class="form-label fw-bold">Pelapor</label>
                        <input type="text" name="pelapor" id="modal_pelapor" class="form-control" placeholder="Nama Guru / Staf Pelapor" required>
                    </div>

                    <div class="mb-3">
                        <label for="modal_kejadian" class="form-label fw-bold">Kejadian Kasus / Catatan Prestasi</label>
                        <textarea name="kejadian" id="modal_kejadian" class="form-control" rows="3" placeholder="Detail kejadian atau prestasi siswa..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="modal_tempat" class="form-label fw-bold">Tempat Kejadian</label>
                        <input type="text" name="tempat" id="modal_tempat" class="form-control" placeholder="Tempat kejadian..." required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Poin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isiPoinButtons = document.querySelectorAll('.btn-isi-poin');
    const modalSiswaId = document.getElementById('modal_siswa_id');
    const modalSiswaNama = document.getElementById('modal_siswa_nama');
    
    isiPoinButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const kelas = this.getAttribute('data-kelas');
            
            modalSiswaId.value = id;
            modalSiswaNama.value = `${nama} (${kelas})`;
        });
    });
});
</script>
@endsection
