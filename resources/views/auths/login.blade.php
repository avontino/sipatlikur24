<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SIPATLIKUR - Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Google Fonts: Outfit -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --primary-green: #009638;
      --dark-green: #004d1a;
      --light-green: #e8f5e9;
      --primary-blue: #009638;
      --dark-blue: #004d1a;
      --light-blue: #e8f5e9;
      --accent-color: #facc15;
    }

    * {
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }

    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      position: relative;
      background: linear-gradient(135deg, rgba(0, 150, 56, 0.88) 0%, rgba(0, 77, 26, 0.95) 100%), 
                  url("{{ asset('adminlte/img/background.png') }}") no-repeat center center;
      background-size: cover;
      font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      overflow-y: auto;
    }

    .login-container {
      width: 100%;
      max-width: 420px;
      padding: 20px;
    }

    .login-card {
      background: rgba(255, 255, 255, 0.96);
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.8);
      padding: 40px 30px;
      backdrop-filter: blur(8px);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .login-card:hover {
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
    }

    .login-header {
      text-align: center;
      margin-bottom: 35px;
    }

    .login-logo-img {
      width: 95px;
      height: 95px;
      object-fit: contain;
      margin-bottom: 15px;
      filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.15));
    }

    .login-title {
      font-size: 32px;
      font-weight: 800;
      color: var(--primary-blue);
      letter-spacing: 1px;
      margin: 0 0 5px 0;
    }

    .login-subtitle {
      font-size: 13px;
      font-weight: 600;
      color: #666;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin: 0;
    }

    .input-wrapper {
      position: relative;
      margin-bottom: 22px;
    }

    .input-wrapper i {
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--primary-blue);
      font-size: 17px;
      transition: color 0.3s ease;
    }

    .input-wrapper .form-control {
      height: 52px;
      border-radius: 30px;
      border: 1.5px solid #dcdcdc;
      padding-left: 52px;
      padding-right: 20px;
      font-size: 15px;
      font-weight: 500;
      color: #333;
      background-color: rgba(255, 255, 255, 0.9);
      transition: all 0.3s ease;
    }

    .input-wrapper .form-control::placeholder {
      color: #a0a0a0;
      font-weight: 400;
    }

    .input-wrapper .form-control:focus {
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 4px rgba(10, 61, 145, 0.15);
      background-color: #ffffff;
      outline: none;
    }

    .input-wrapper .form-control:focus + i {
      color: var(--dark-blue);
    }

    .btn-signin {
      width: 100%;
      height: 52px;
      background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
      border: none;
      border-radius: 30px;
      color: #ffffff;
      font-size: 16px;
      font-weight: 700;
      letter-spacing: 0.5px;
      box-shadow: 0 6px 20px rgba(10, 61, 145, 0.3);
      transition: all 0.3s ease;
      cursor: pointer;
      margin-top: 10px;
    }

    .btn-signin:hover {
      background: linear-gradient(135deg, var(--dark-blue) 0%, #001848 100%);
      box-shadow: 0 8px 25px rgba(10, 61, 145, 0.4);
      transform: translateY(-1.5px);
    }

    .btn-signin:active {
      transform: translateY(0.5px);
    }

    .login-footer {
      text-align: center;
      margin-top: 25px;
    }

    .forgot-link {
      color: var(--primary-blue);
      font-weight: 600;
      font-size: 14px;
      text-decoration: none;
      transition: color 0.2s ease;
    }

    .forgot-link:hover {
      color: var(--dark-blue);
      text-decoration: underline;
    }

    /* Glassmorphism Fullscreen Loading Overlay */
    #loginLoadingOverlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 24, 72, 0.85);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      z-index: 99999;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.35s ease, visibility 0.35s ease;
    }

    #loginLoadingOverlay.active {
      opacity: 1;
      visibility: visible;
    }

    .loading-box {
      background: rgba(255, 255, 255, 0.96);
      padding: 35px 45px;
      border-radius: 24px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
      text-align: center;
      max-width: 380px;
      width: 90%;
      transform: scale(0.85);
      transition: transform 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    #loginLoadingOverlay.active .loading-box {
      transform: scale(1);
    }

    .loading-logo-container {
      position: relative;
      width: 95px;
      height: 95px;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .loading-logo-img {
      width: 62px;
      height: 62px;
      object-fit: contain;
      z-index: 2;
      animation: pulseLogo 1.5s infinite ease-in-out;
    }

    .loading-spinner-ring {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: 4px solid rgba(10, 61, 145, 0.15);
      border-top: 4px solid #0a3d91;
      border-right: 4px solid #f39c12;
      border-radius: 50%;
      animation: spinRing 1s infinite linear;
    }

    @keyframes spinRing {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @keyframes pulseLogo {
      0%, 100% { transform: scale(1); filter: drop-shadow(0 0 8px rgba(10, 61, 145, 0.4)); }
      50% { transform: scale(1.1); filter: drop-shadow(0 0 15px rgba(243, 156, 18, 0.6)); }
    }

    .loading-title {
      font-weight: 800;
      color: #002366;
      font-size: 19px;
      margin-bottom: 6px;
      letter-spacing: 0.5px;
    }

    .loading-subtext {
      font-size: 13.5px;
      color: #6c757d;
      margin: 0;
      font-weight: 500;
    }

    .loading-progress-bar {
      width: 100%;
      height: 5px;
      background: #e9ecef;
      border-radius: 10px;
      margin-top: 20px;
      overflow: hidden;
    }

    .loading-progress-fill {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, #0a3d91, #f39c12);
      border-radius: 10px;
      transition: width 0.4s ease;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <img src="{{ asset('adminlte/img/user2.png') }}" alt="SIPATLIKUR Logo" class="login-logo-img">
        <h1 class="login-title">SIPATLIKUR</h1>
        <h2 class="login-subtitle">SMP Negeri 24 Malang</h2>
      </div>

      <form id="loginForm" action="{{ url('/postlogin') }}" method="post">
        {{ csrf_field() }}
        
        <div class="input-wrapper">
          <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="username">
          <i class="fas fa-user"></i>
        </div>

        <div class="input-wrapper">
          <input type="password" name="password" class="form-control" placeholder="Password" required autocomplete="current-password">
          <i class="fas fa-lock"></i>
        </div>

        <div class="input-wrapper">
          <select name="tahun_ajaran_id" class="form-control" style="appearance: auto; padding-left: 20px; border-radius: 30px;" required>
            @foreach($active_semesters as $as)
              <option value="{{ $as->id }}" {{ $as->status == 1 ? 'selected' : '' }}>{{ $as->tahun_ajaran }} - {{ $as->semester }}</option>
            @endforeach
          </select>
        </div>

        <button type="submit" class="btn-signin">Sign In</button>
      </form>

      <!-- QR CODE DOWNLOAD APK SECTION -->
      <div class="mt-4 pt-3 border-top text-center">
        <div class="d-flex align-items-center justify-content-center gap-3 p-3 shadow-sm border" style="border-radius: 16px !important; background-color: #f8fafc !important; border-color: #e2e8f0 !important;">
          <div style="background: white; padding: 6px; border-radius: 12px; border: 1px solid #cbd5e1; flex-shrink: 0;">
            <img src="{{ asset('images/qr-download-apk.png') }}" alt="QR Code Download APK SIPATLIKUR" style="width: 85px; height: 85px; object-fit: contain;">
          </div>
          <div class="text-start" style="flex: 1;">
            <div class="fw-bold mb-1" style="font-size: 13px; color: #0f172a !important;">
              <i class="fab fa-android text-success me-1"></i> Download APK Mobile
            </div>
            <div class="small mb-2" style="font-size: 11px; line-height: 1.3; color: #475569 !important;">
              Scan QR Code ini menggunakan HP Anda untuk mengunduh aplikasi Android SIPATLIKUR.
            </div>
            <a href="https://sipatlikur.smpn24-mlg.sch.id/SIPATLIKUR24.apk" target="_blank" class="btn btn-sm btn-success text-white py-1 px-3 text-decoration-none fw-semibold shadow-sm" style="font-size: 11px; border-radius: 20px; background-color: #009638 !important; border-color: #009638 !important;">
              <i class="fas fa-download me-1"></i> Download APK Langsung
            </a>
          </div>
        </div>
      </div>

      <div class="login-footer">
        <a href="#" class="forgot-link">Lupa password? Silahkan menghubungi Tim IT</a>
      </div>
    </div>
  </div>

  <!-- Loading Overlay Graphic Animation -->
  <div id="loginLoadingOverlay">
    <div class="loading-box">
      <div class="loading-logo-container">
        <div class="loading-spinner-ring"></div>
        <img src="{{ asset('adminlte/img/user2.png') }}" alt="SIPATLIKUR Logo" class="loading-logo-img">
      </div>
      <div class="loading-title">Authenticating SIPATLIKUR</div>
      <div class="loading-subtext">Memverifikasi kredensial...</div>
      <div class="loading-progress-bar">
        <div class="loading-progress-fill"></div>
      </div>
    </div>
  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- Bootstrap 5 Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        const $btn = $('.btn-signin');
        const originalBtnText = $btn.html();
        const $overlay = $('#loginLoadingOverlay');
        const $progress = $('.loading-progress-fill');
        const $subtext = $('.loading-subtext');

        // Tampilkan animasi loading & disable tombol
        $overlay.addClass('active');
        $progress.css('width', '40%');
        $subtext.text('Memverifikasi kredensial akun...');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Memproses...');

        $.ajax({
          type: 'POST',
          url: $(this).attr('action'),
          data: $(this).serialize(),
          success: function(response) {
            $progress.css('width', '100%');
            $subtext.text('Login Berhasil! Menyiapkan Dashboard...');
            setTimeout(function() {
              window.location.href = '/dashboard';
            }, 400);
          },
          error: function(xhr) {
            // Sembunyikan loading jika gagal
            $overlay.removeClass('active');
            $progress.css('width', '0%');
            $btn.prop('disabled', false).html(originalBtnText);

            if (xhr.status === 401) {
              Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: 'Username atau Password salah.',
                confirmButtonColor: '#0a3d91'
              });
            } else if (xhr.status === 429) {
              Swal.fire({
                icon: 'warning',
                title: 'Batas Login Terlampaui',
                text: 'Terlalu banyak percobaan login. Silakan tunggu 1 menit lalu coba lagi.',
                confirmButtonColor: '#0a3d91'
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan sistem, silakan coba lagi.',
                confirmButtonColor: '#0a3d91'
              });
            }
          }
        });
      });
    });
  </script>
</body>
</html>
