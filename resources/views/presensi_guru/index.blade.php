@extends('layouts.master')

@section('content')
<section class="content pt-3">
    <div class="container-fluid">
        <!-- Top Title Bar -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                <h3 class="fw-bold m-0" style="color: #002366;">
                    <i class="fas fa-camera me-2"></i>Presensi Guru & Staf
                </h3>
                <div class="d-flex gap-2">
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'kurikulum')
                        <a href="/presensi-guru/shifts" class="btn btn-outline-primary btn-sm"><i class="fas fa-business-time me-1"></i> Kelola Shift & Roster</a>
                        <a href="/presensi-guru/setting" class="btn btn-outline-secondary btn-sm"><i class="fas fa-cog me-1"></i> Lokasi Office</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Left side: Webcam & Action Card -->
            <div class="col-md-6 col-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-camera me-2"></i>
                            <h5 class="m-0 fw-bold card-title d-inline-block">Kamera & Lokasi Presensi</h5>
                        </div>
                        @if(isset($activeShift))
                            <span class="badge bg-light text-primary fw-bold px-3 py-2">
                                <i class="fas fa-user-clock me-1"></i> {{ $activeShift->nama_shift }} ({{ substr($activeShift->jam_masuk,0,5) }} - {{ substr($activeShift->jam_pulang,0,5) }})
                            </span>
                        @endif
                    </div>
                    <div class="card-body text-center">
                        @if(isset($activeShift))
                        <div class="alert alert-info py-2 mb-3 text-center rounded small fw-semibold">
                            <i class="fas fa-clock me-1"></i> Jam Kerja Shift Aktif Anda: <strong>{{ substr($activeShift->jam_masuk,0,5) }} - {{ substr($activeShift->jam_pulang,0,5) }} WIB</strong>
                            @if($activeShift->is_overnight)
                                <span class="badge bg-dark ms-1"><i class="fas fa-moon me-1"></i> Shift Malam (H+1)</span>
                            @endif
                        </div>
                        @endif

                        <!-- Distance Radar / Indicator -->
                        <div id="distance-indicator" class="alert alert-secondary py-2 mb-3 text-center rounded small">
                            <i class="fas fa-satellite-dish me-1"></i> Mendeteksi lokasi GPS Anda...
                        </div>

                        <!-- Video Preview -->
                        <div class="position-relative d-inline-block bg-dark rounded overflow-hidden shadow-sm border mb-3" style="width: 100%; max-width: 400px; aspect-ratio: 4/3;">
                            <video id="webcam" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
                            <canvas id="canvas" class="d-none"></canvas>
                            <div id="camera-error" class="position-absolute top-0 start-0 w-100 h-100 d-none d-flex align-items-center justify-content-center bg-dark text-white p-3 text-center small">
                                <i class="fas fa-video-slash me-2"></i> Gagal membuka kamera. Harap beri izin kamera pada browser Anda.
                            </div>
                        </div>

                        <!-- Buttons for action -->
                        <div class="d-flex justify-content-center gap-3">
                            @if(!$todayPresensi)
                                <button type="button" id="btn-datang" class="btn btn-primary px-4" disabled onclick="takeSnap('datang')">
                                    <i class="fas fa-sign-in-alt me-1"></i> Absen Datang (Masuk)
                                </button>
                            @elseif($todayPresensi && !$todayPresensi->jam_pulang)
                                <button type="button" id="btn-pulang" class="btn btn-warning px-4 text-white" disabled onclick="takeSnap('pulang')">
                                    <i class="fas fa-sign-out-alt me-1"></i> Absen Pulang (Keluar)
                                </button>
                            @else
                                <div class="alert alert-success py-2 px-4 mb-0 rounded small">
                                    <i class="fas fa-check-circle me-1"></i> Anda sudah menyelesaikan presensi hari ini.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side: Info Card for Today Status -->
            <div class="col-md-6 col-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="m-0 fw-bold card-title"><i class="fas fa-info-circle me-2"></i> Status Presensi Hari Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Check-in block -->
                            <div class="col-6 text-center border-end">
                                <h6 class="fw-bold text-secondary mb-3">Jam Masuk (Datang)</h6>
                                @if($todayPresensi)
                                    <p class="h4 text-success fw-bold m-0">{{ $todayPresensi->jam_datang }}</p>
                                    <span class="badge bg-{{ $todayPresensi->status_datang == 'Terlambat' ? 'danger' : 'success' }} mt-2">
                                        {{ $todayPresensi->status_datang }} {{ $todayPresensi->menit_terlambat > 0 ? '(' . $todayPresensi->menit_terlambat . ' Menit)' : '' }}
                                    </span>
                                    <div class="mt-3">
                                        <img src="{{ asset($todayPresensi->foto_datang) }}" alt="Foto Masuk" class="img-thumbnail rounded shadow-xs" style="max-height: 120px;">
                                    </div>
                                @else
                                    <p class="text-muted small italic">Belum absen masuk</p>
                                    <div class="text-white-50 bg-secondary rounded p-3 d-inline-block opacity-25">
                                        <i class="fas fa-user-clock fa-2x"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Check-out block -->
                            <div class="col-6 text-center">
                                <h6 class="fw-bold text-secondary mb-3">Jam Keluar (Pulang)</h6>
                                @if($todayPresensi && $todayPresensi->jam_pulang)
                                    <p class="h4 text-warning fw-bold m-0">{{ $todayPresensi->jam_pulang }}</p>
                                     <span class="badge bg-{{ $todayPresensi->status_pulang == 'Pulang Sebelum Waktunya' ? 'warning' : 'success' }} mt-2 text-white">
                                         {{ $todayPresensi->status_pulang ?? 'Selesai' }} {{ $todayPresensi->menit_pulang_cepat > 0 ? '(' . $todayPresensi->menit_pulang_cepat . ' Menit Awal)' : '' }}
                                     </span>
                                    <div class="mt-3">
                                        <img src="{{ asset($todayPresensi->foto_pulang) }}" alt="Foto Pulang" class="img-thumbnail rounded shadow-xs" style="max-height: 120px;">
                                    </div>
                                @else
                                    <p class="text-muted small italic">Belum absen pulang</p>
                                    <div class="text-white-50 bg-secondary rounded p-3 d-inline-block opacity-25">
                                        <i class="fas fa-user-clock fa-2x"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="m-0 fw-bold text-dark card-title"><i class="fas fa-history me-2"></i> Riwayat Presensi Bulan Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive p-3">
                            <table class="table table-bordered table-striped align-middle text-center mb-0" id="tableRiwayatSaya" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Tanggal</th>
                                        <th>Shift</th>
                                        <th>Jam Datang</th>
                                        <th>Jam Pulang</th>
                                        <th>Status Masuk</th>
                                        <th>Status Pulang</th>
                                        <th>Foto Bukti Datang</th>
                                        <th>Foto Bukti Pulang</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- GPS and Camera JS Integration -->
<script>
    const schoolLat = {{ $config->latitude }};
    const schoolLng = {{ $config->longitude }};
    const allowedRadius = {{ $config->radius ?? $config->radius_meters ?? 100 }}; // meters

    let userLat = null;
    let userLng = null;

    // Initialize camera
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('canvas');
    const cameraError = document.getElementById('camera-error');
    const distIndicator = document.getElementById('distance-indicator');

    const btnDatang = document.getElementById('btn-datang');
    const btnPulang = document.getElementById('btn-pulang');

    // Get webcam feed
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
            .then(function (stream) {
                video.srcObject = stream;
            })
            .catch(function (err) {
                console.error("Camera access error:", err);
                cameraError.classList.remove('d-none');
            });
    } else {
        cameraError.classList.remove('d-none');
    }

    // Get Geolocation
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(
            function(position) {
                userLat = position.coords.latitude;
                userLng = position.coords.longitude;

                // Calculate distance using Haversine algorithm
                const distance = getDistance(userLat, userLng, schoolLat, schoolLng);
                const isWithinRange = distance <= allowedRadius;

                if (isWithinRange) {
                    distIndicator.className = 'alert alert-success py-2 mb-3 text-center rounded small';
                    distIndicator.innerHTML = `<i class="fas fa-check-circle me-1"></i> Posisi Terverifikasi. Jarak Anda: <strong>${Math.round(distance)} meter</strong> dari lokasi kerja (Dalam Radius).`;
                    
                    if (btnDatang) btnDatang.disabled = false;
                    if (btnPulang) btnPulang.disabled = false;
                } else {
                    distIndicator.className = 'alert alert-danger py-2 mb-3 text-center rounded small';
                    distIndicator.innerHTML = `<i class="fas fa-times-circle me-1"></i> Jarak Terlalu Jauh! Jarak Anda: <strong>${Math.round(distance)} meter</strong> dari lokasi kerja. Anda harus berada di radius 100m.`;
                    
                    if (btnDatang) btnDatang.disabled = true;
                    if (btnPulang) btnPulang.disabled = true;
                }
            },
            function(error) {
                console.error("GPS Access Error:", error);
                distIndicator.className = 'alert alert-warning py-2 mb-3 text-center rounded small';
                distIndicator.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> Gagal melacak lokasi GPS. Pastikan izin lokasi browser Anda aktif.`;
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    } else {
        distIndicator.className = 'alert alert-danger py-2 mb-3 text-center rounded small';
        distIndicator.innerHTML = `<i class="fas fa-times-circle me-1"></i> Browser Anda tidak mendukung pelacakan GPS Geolocation.`;
    }

    // Capture photo and post
    function takeSnap(tipe) {
        if (!userLat || !userLng) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'GPS Belum Siap',
                    text: 'Lokasi GPS belum dideteksi. Harap tunggu sebentar.',
                    confirmButtonColor: '#0a3d91'
                });
            } else {
                alert('Lokasi GPS belum dideteksi. Harap tunggu sebentar.');
            }
            return;
        }

        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;

        // Draw webcam preview to canvas
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL('image/png');

        // Submit via AJAX
        if (btnDatang) btnDatang.disabled = true;
        if (btnPulang) btnPulang.disabled = true;
        distIndicator.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> Mengirim presensi Anda...`;

        fetch('/presensi-guru/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                foto: dataUrl,
                lat: userLat,
                lng: userLng,
                tipe: tipe
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            if (res.status === 200) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Presensi Berhasil! 🎉',
                        text: res.body.message,
                        confirmButtonColor: '#0a3d91'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(res.body.message);
                    window.location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Presensi Gagal',
                        text: res.body.error || 'Terjadi kesalahan sistem.',
                        confirmButtonColor: '#0a3d91'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(res.body.error || 'Terjadi kesalahan sistem.');
                    window.location.reload();
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Koneksi',
                    text: 'Koneksi internet bermasalah. Gagal mengirim presensi.',
                    confirmButtonColor: '#0a3d91'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                alert('Koneksi internet bermasalah. Gagal mengirim presensi.');
                window.location.reload();
            }
        });
    }

    // Haversine distance calculator in JS
    function getDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // radius of Earth in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }
</script>

@push('scripts')
<script>
$(document).ready(function() {
    $('#tableRiwayatSaya').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/presensi-guru/data-riwayat',
        columns: [
            { data: 'no', name: 'no', orderable: false, searchable: false },
            { data: 'tanggal', name: 'tanggal' },
            { data: 'shift', name: 'shift' },
            { data: 'jam_datang', name: 'jam_datang' },
            { data: 'jam_pulang', name: 'jam_pulang' },
            { data: 'status_datang', name: 'status_datang' },
            { data: 'status_pulang', name: 'status_pulang' },
            { data: 'foto_datang', name: 'foto_datang', orderable: false, searchable: false },
            { data: 'foto_pulang', name: 'foto_pulang', orderable: false, searchable: false }
        ],
        language: {
            processing: '<div class="d-flex justify-content-center py-3"><div class="spinner-border text-primary" role="status"></div></div>',
            search: "Cari Riwayat:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            zeroRecords: "Belum ada riwayat presensi di bulan ini."
        }
    });
});
</script>
@endpush
@endsection
