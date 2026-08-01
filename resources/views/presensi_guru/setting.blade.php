@extends('layouts.master')

@section('content')
<!-- Leaflet CSS for Interactive Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-picker {
        height: 380px;
        width: 100%;
        border-radius: 10px;
        z-index: 1;
    }
</style>

<section class="content pt-3">
    <div class="container-fluid">
        <!-- Top Title Bar -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                <h3 class="fw-bold m-0" style="color: #004d1a;">
                    <i class="fas fa-map-marked-alt me-2"></i>Pengaturan Lokasi Kantor & Sekolah
                </h3>
                <div class="d-flex gap-2">
                    <a href="/presensi-guru/shifts" class="btn btn-outline-primary btn-sm"><i class="fas fa-business-time me-1"></i> Kelola Shift & Roster</a>
                    <a href="/presensi-guru" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Presensi Saya</a>
                </div>
            </div>
        </div>
        @if(session('sukses'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Left Side: Interactive Map Picker -->
            <div class="col-lg-7 col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="m-0 fw-bold card-title"><i class="fas fa-map-pin me-2"></i>Pilih Titik Lokasi Pada Peta</h5>
                        <button type="button" id="btnDetectGPS" class="btn btn-light btn-sm text-primary fw-bold">
                            <i class="fas fa-crosshairs me-1"></i> Deteksi Lokasi Saya
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            <i class="fas fa-info-circle me-1 text-info"></i> <strong>Petunjuk:</strong> Geser penanda (marker) atau klik di mana saja pada peta untuk menentukan titik pusat lokasi kantor/sekolah.
                        </p>
                        <div id="map-picker" class="shadow-sm border"></div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form Controls -->
            <div class="col-lg-5 col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <h5 class="m-0 fw-bold card-title"><i class="fas fa-sliders-h me-2"></i>Parameter Lokasi & Radius</h5>
                    </div>
                    <form method="POST" action="/presensi-guru/setting">
                        @csrf
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="latitude" class="form-label small fw-bold text-secondary">Lintang (Latitude)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-globe-americas"></i></span>
                                    <input type="text" name="latitude" id="latitude" class="form-control" value="{{ old('latitude', $config->latitude) }}" placeholder="Contoh: -7.8012" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="longitude" class="form-label small fw-bold text-secondary">Bujur (Longitude)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-globe-asia"></i></span>
                                    <input type="text" name="longitude" id="longitude" class="form-control" value="{{ old('longitude', $config->longitude) }}" placeholder="Contoh: 112.0123" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="radius" class="form-label small fw-bold text-secondary">Radius Toleransi Presensi (Meter)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-ruler-combined"></i></span>
                                    <input type="number" name="radius" id="radius" class="form-control" value="{{ old('radius', $config->radius ?? $config->radius_meters ?? 100) }}" placeholder="Contoh: 100" min="1" required>
                                    <span class="input-group-text">Meter</span>
                                </div>
                                <small class="text-muted">Pegawai hanya dapat presensi jika berada dalam jangkauan radius ini dari titik kantor.</small>
                            </div>

                            <div class="card bg-light border-0 rounded p-3 mb-2">
                                <h6 class="fw-bold text-dark small mb-1"><i class="fas fa-check-double text-success me-1"></i>Catatan Pengaturan Jam Kerja:</h6>
                                <p class="mb-0 text-muted small">
                                    Pengaturan jam masuk & pulang kerja kini telah dialihkan secara fleksibel pada menu <a href="/presensi-guru/shifts" class="fw-bold text-primary">Manajemen Shift & Roster Kerja</a>.
                                </p>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-end">
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="fas fa-save me-1"></i> Simpan Pengaturan Lokasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Leaflet JS for Map Picker -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let initialLat = parseFloat(document.getElementById('latitude').value) || -7.8012;
    let initialLng = parseFloat(document.getElementById('longitude').value) || 112.0123;
    let initialRadius = parseInt(document.getElementById('radius').value) || 100;

    // Initialize Leaflet Map
    const map = L.map('map-picker').setView([initialLat, initialLng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Draggable Marker
    let marker = L.marker([initialLat, initialLng], {
        draggable: true
    }).addTo(map);

    // Radius Circle
    let circle = L.circle([initialLat, initialLng], {
        color: '#009638',
        fillColor: '#009638',
        fillOpacity: 0.2,
        radius: initialRadius
    }).addTo(map);

    function updateCoordinates(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
        
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
    }

    // Drag Marker Event
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });

    // Map Click Event
    map.on('click', function(e) {
        updateCoordinates(e.latlng.lat, e.latlng.lng);
    });

    // Update coordinates when manual inputs change
    document.getElementById('latitude').addEventListener('change', function() {
        let lat = parseFloat(this.value) || initialLat;
        let lng = parseFloat(document.getElementById('longitude').value) || initialLng;
        map.setView([lat, lng], 16);
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
    });

    document.getElementById('longitude').addEventListener('change', function() {
        let lat = parseFloat(document.getElementById('latitude').value) || initialLat;
        let lng = parseFloat(this.value) || initialLng;
        map.setView([lat, lng], 16);
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
    });

    // Radius input change event
    document.getElementById('radius').addEventListener('input', function() {
        let rad = parseInt(this.value) || 100;
        circle.setRadius(rad);
    });

    // Auto Detect Current Device Location
    document.getElementById('btnDetectGPS').addEventListener('click', function() {
        if (navigator.geolocation) {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mendeteksi...';
            const btn = this;

            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 17);
                updateCoordinates(lat, lng);
                
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-crosshairs me-1"></i> Deteksi Lokasi Saya';
            }, function(error) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gagal Deteksi GPS',
                        text: 'Gagal mendeteksi lokasi GPS. Pastikan izin lokasi browser Anda aktif.',
                        confirmButtonColor: '#009638'
                    });
                } else {
                    alert('Gagal mendeteksi lokasi GPS. Pastikan izin lokasi browser Anda aktif.');
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-crosshairs me-1"></i> Deteksi Lokasi Saya';
            }, { enableHighAccuracy: true });
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Didukung',
                    text: 'Browser Anda tidak mendukung Geolocation.',
                    confirmButtonColor: '#009638'
                });
            } else {
                alert('Browser Anda tidak mendukung Geolocation.');
            }
        }
    });
});
</script>
@endpush
@endsection
