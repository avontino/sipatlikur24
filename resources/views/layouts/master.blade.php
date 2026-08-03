<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SIPATLIKUR</title>
  <link rel="icon" type="image/png" href="{{ asset('adminlte/img/user2.png') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- AdminLTE 4 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/css/adminlte.min.css">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  
  <!-- DataTables Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  
  <!-- Google Fonts: Outfit -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 Editable (X-editable for Bootstrap 5) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap5-editable@5.2.3/dist/bootstrap5-editable/css/bootstrap-editable.css" rel="stylesheet">

  <!-- Select2 Bootstrap 5 Theme -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

  <style>
    :root {
      --primary-green: #009638;
      --dark-green: #004d1a;
      --light-green: #e8f5e9;
      --primary-blue: #009638;
      --dark-blue: #004d1a;
      --light-blue: #e8f5e9;
      --accent-color: #facc15;
      --bg-light: #f4f6f9;
      --lte-sidebar-bg: #004d1a;
      --lte-sidebar-hover-bg: #00732b;
    }

    body {
      font-family: 'Outfit', sans-serif !important;
      background-color: var(--bg-light);
    }

    /* Global Fix for Bootstrap 5 & AdminLTE Modals to prevent top cut-off */
    .modal-dialog {
      margin-top: 4rem !important;
      margin-bottom: 2rem !important;
    }

    .modal-dialog.modal-dialog-centered {
      margin-top: auto !important;
      margin-bottom: auto !important;
      min-height: calc(100% - 3.5rem) !important;
    }

    .modal {
      z-index: 1055 !important;
    }

    .modal-backdrop {
      z-index: 1050 !important;
    }

    @media (max-width: 767.98px) {
      body {
        padding-bottom: 70px !important;
      }
      .sinala-bottom-nav {
        box-shadow: 0 -4px 15px rgba(0,0,0,0.1) !important;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
      }
      /* Smooth horizontal slide for all responsive tables on mobile */
      .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
      }
      .table-responsive > .table {
        min-width: 680px !important;
      }
      /* Thumb-friendly mobile action buttons & tables */
      .table-responsive .btn, .table .btn-sm {
        display: block !important;
        width: 100% !important;
        margin-bottom: 6px !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        border-radius: 6px !important;
        text-align: center !important;
      }
      .card-header .btn {
        display: block !important;
        width: 100% !important;
        margin-top: 6px !important;
      }
    }

    /* Custom Sidebar Color */
    .app-sidebar {
      background-color: var(--dark-blue) !important;
    }

    .sidebar-wrapper {
      overflow-y: auto !important;
      height: calc(100vh - 60px) !important;
    }

    /* Sidebar text wrapping fix to prevent truncation */
    .sidebar-menu .nav-item .nav-link {
      white-space: normal !important;
      display: flex !important;
      align-items: center !important;
    }
    
    .sidebar-menu .nav-item .nav-link p {
      white-space: normal !important;
      overflow: visible !important;
      text-overflow: clip !important;
      margin-bottom: 0 !important;
      word-break: break-word !important;
    }
    
    .sidebar-brand {
      background-color: #001848 !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* Custom avatar initials style */
    .avatar-circle {
      width: 38px;
      height: 38px;
      background: linear-gradient(135deg, #1e5ba0 0%, #0a3d91 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-weight: 700;
      font-size: 14px;
      border: 1.5px solid rgba(255, 255, 255, 0.25);
      letter-spacing: 0.5px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }

    .user-panel {
      border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
      padding: 15px 20px !important;
      margin-bottom: 10px !important;
    }

    .user-panel .info {
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding-left: 12px;
    }

    .user-panel .info a {
      font-weight: 600 !important;
      font-size: 14px !important;
      color: #ffffff !important;
      line-height: 1.2;
    }

    .user-panel .info .status-text {
      font-size: 11px;
      color: #a0b0cb;
      font-weight: 500;
      margin-top: 2px;
      display: flex;
      align-items: center;
    }

    .user-panel .info .status-dot {
      width: 6px;
      height: 6px;
      background-color: #2ecc71;
      border-radius: 50%;
      display: inline-block;
      margin-right: 5px;
      box-shadow: 0 0 8px #2ecc71;
    }

    /* Sidebar navigation links */
    .app-sidebar .nav-link {
      color: rgba(255, 255, 255, 0.8) !important;
      font-weight: 500;
      border-radius: 8px;
      margin: 3px 12px;
      padding: 10px 15px;
      transition: all 0.2s ease;
      font-size: 14px;
    }

    .app-sidebar .nav-link i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
      font-size: 15px;
      opacity: 0.85;
    }

    .app-sidebar .nav-link:hover {
      background-color: rgba(255, 255, 255, 0.08) !important;
      color: #fff !important;
    }

    .app-sidebar .nav-link.active {
      background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%) !important;
      color: #fff !important;
      box-shadow: 0 4px 12px rgba(10, 61, 145, 0.3) !important;
      border-left: 3px solid var(--accent-color) !important;
      font-weight: 600;
    }

    /* Fix treeview arrow overlap and text clipping */
    .sidebar-menu .nav-link p {
      padding-right: 25px !important;
      position: relative !important;
      margin-bottom: 0 !important;
      display: block !important;
      width: 100% !important;
    }
    
    .sidebar-menu .nav-link p .nav-arrow {
      position: absolute !important;
      right: 0 !important;
      top: 50% !important;
      transform: translateY(-50%) !important;
    }

    /* Custom sidebar header styles */
    .app-sidebar .nav-header {
      font-family: 'Outfit', sans-serif !important;
      font-size: 10.5px !important;
      font-weight: 700 !important;
      color: rgba(255, 255, 255, 0.4) !important;
      padding: 15px 25px 5px 25px !important;
      letter-spacing: 0.8px;
      text-transform: uppercase;
    }

    /* Smooth sidebar scrollbar */
    .sidebar-wrapper::-webkit-scrollbar {
      width: 5px;
    }
    .sidebar-wrapper::-webkit-scrollbar-thumb {
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 4px;
    }

    /* Header / Navbar */
    .app-header {
      background-color: #ffffff !important;
      border-bottom: 1px solid #ebebeb;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .app-header .nav-link {
      color: var(--dark-blue) !important;
      font-weight: 600;
    }

    /* Cards */
    .card {
      border: none !important;
      border-radius: 14px !important;
      box-shadow: 0 5px 18px rgba(0, 0, 0, 0.04) !important;
      margin-bottom: 25px;
      overflow: hidden;
      background-color: #ffffff;
    }

    .card-header {
      background-color: #ffffff !important;
      border-bottom: 1px solid #ebebeb !important;
      padding: 18px 20px !important;
      font-weight: 700;
      font-size: 16px;
      color: var(--dark-blue);
    }

    /* Tables */
    .table-responsive {
      border-radius: 12px;
      overflow: hidden;
    }

    .table {
      margin-bottom: 0;
    }

    .table thead th {
      background-color: var(--light-blue) !important;
      color: var(--dark-blue) !important;
      font-weight: 600;
      border-bottom: 2px solid #dee2e6;
      padding: 14px 16px;
      font-size: 14px;
    }

    .table tbody td {
      padding: 14px 16px;
      vertical-align: middle;
      border-bottom: 1px solid #f2f2f2;
      font-size: 14.5px;
      color: #444;
    }

    .table tbody tr:hover {
      background-color: #fafbfc;
    }

    /* Buttons */
    .btn {
      border-radius: 8px;
      padding: 8px 18px;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.2s ease;
    }

    .btn-primary {
      background-color: var(--primary-blue) !important;
      border-color: var(--primary-blue) !important;
      box-shadow: 0 4px 12px rgba(10, 61, 145, 0.2);
    }

    .btn-primary:hover {
      background-color: var(--dark-blue) !important;
      border-color: var(--dark-blue) !important;
      box-shadow: 0 6px 16px rgba(10, 61, 145, 0.3);
      transform: translateY(-1px);
    }

    .btn-success {
      background-color: #2ecc71 !important;
      border-color: #2ecc71 !important;
      box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
    }

    .btn-success:hover {
      background-color: #27ae60 !important;
      border-color: #27ae60 !important;
      box-shadow: 0 6px 16px rgba(46, 204, 113, 0.3);
      transform: translateY(-1px);
    }

    /* Forms */
    .form-control, .form-select {
      border-radius: 8px;
      border: 1.5px solid #dcdcdc;
      padding: 10px 15px;
      font-size: 14.5px;
      color: #333;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3.5px rgba(10, 61, 145, 0.12);
      outline: none;
    }

    /* Footer */
    .app-footer {
      background-color: #ffffff !important;
      border-top: 1px solid #ebebeb !important;
      padding: 15px 20px !important;
      color: #777;
      font-size: 13.5px;
    }

    /* Premium box widgets styling */
    .small-box, .card.bg-info, .card.bg-warning, .card.bg-success, .card.bg-danger {
      border-radius: 14px !important;
      border: none !important;
      box-shadow: 0 4px 15px rgba(0,0,0,0.06) !important;
      overflow: hidden;
      color: #ffffff !important;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      margin-bottom: 20px;
    }

    .small-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
    }

    .small-box .inner {
      padding: 20px !important;
    }

    .small-box h3 {
      font-size: 28px !important;
      font-weight: 850 !important;
      margin-bottom: 5px !important;
      white-space: normal !important;
    }

    .small-box p {
      font-size: 14px !important;
      font-weight: 500 !important;
      opacity: 0.9;
      margin-bottom: 0 !important;
    }

    .bg-info, .small-box.bg-info {
      background: linear-gradient(135deg, #0a3d91 0%, #1e5ba0 100%) !important;
    }

    .bg-success, .small-box.bg-success {
      background: linear-gradient(135deg, #0e7845 0%, #1b9d5c 100%) !important;
    }

    .bg-warning, .small-box.bg-warning {
      background: linear-gradient(135deg, #b8860b 0%, #daa520 100%) !important;
      color: #ffffff !important;
    }
    
    .small-box.bg-warning p, .small-box.bg-warning h3 {
      color: #ffffff !important;
    }

    .bg-danger, .small-box.bg-danger {
      background: linear-gradient(135deg, #a82c2c 0%, #c94040 100%) !important;
    }

    .bg-secondary, .small-box.bg-secondary {
      background: linear-gradient(135deg, #4b5d67 0%, #6c7b84 100%) !important;
    }

    .bg-dark, .small-box.bg-dark {
      background: linear-gradient(135deg, #1f2d3d 0%, #343a40 100%) !important;
    }

    /* Large watermark card icon styling */
    .small-box {
      position: relative;
      overflow: hidden;
    }

    .small-box .icon {
      position: absolute;
      top: 10px;
      right: 15px;
      z-index: 1;
    }

    .small-box .icon i {
      font-size: 65px !important;
      color: rgba(255, 255, 255, 0.16) !important;
      transition: transform 0.3s ease-in-out;
    }

    .small-box:hover .icon i {
      transform: scale(1.18) rotate(6deg);
    }

    .small-box .inner {
      position: relative;
      z-index: 2;
    }

    /* Mobile Responsive optimizations */
    @media (max-width: 768px) {
      .login-card {
        padding: 25px 20px;
      }
      
      .table thead th, .table tbody td {
        padding: 10px 12px;
        font-size: 13px;
      }

      .small-box h3 {
        font-size: 22px !important;
      }

      .small-box h3 sup {
        font-size: 14px !important;
        display: inline-block;
        line-height: 1.2;
      }
    }

    /* Global layout & component spacing improvements */
    .form-inline, .form-inline-custom {
      display: flex !important;
      flex-wrap: wrap !important;
      gap: 0.5rem !important;
      align-items: center !important;
    }
    
    .form-inline > *, .form-inline-custom > * {
      margin-bottom: 0.25rem !important;
      margin-top: 0.25rem !important;
    }

    /* Consistent padding and margins for tables and cards */
    .card {
      margin-bottom: 1.5rem !important;
      border: none !important;
      border-radius: 8px !important;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }

    .card-header {
      border-bottom: 1px solid rgba(0,0,0,.075) !important;
      padding: 1rem 1.25rem !important;
    }

    .card-body {
      padding: 1.25rem !important;
    }

    /* General Spacing classes for buttons and forms */
    .btn, .form-control, .form-select, .form-control-sm, .form-select-sm {
      border-radius: 6px !important;
    }

    /* Fix spacing between stacked forms or elements */
    .form-group {
      margin-bottom: 1.25rem !important;
    }

    .form-group label {
      font-weight: 550 !important;
      margin-bottom: 0.4rem !important;
      font-size: 0.9rem !important;
      color: #495057 !important;
    }

    /* Gap helpers for flex elements */
    .d-flex.flex-wrap {
      gap: 0.5rem !important;
    }

    /* Fix inline textboxes and buttons inside lists/tables */
    .table td .btn {
      margin-right: 0.25rem !important;
      margin-bottom: 0.25rem !important;
    }

    /* Spacing for alert banners */
    .alert {
      border-radius: 8px !important;
      margin-bottom: 1.25rem !important;
    }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<!-- Site wrapper -->
<div class="app-wrapper">

  <!-- Navbar -->
  @include('layouts.includes._navbar')

  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Brand Logo -->
    <div class="sidebar-brand d-flex align-items-center justify-content-center">
      <a href="/dashboard" class="brand-link d-flex align-items-center text-decoration-none py-2">
        <img src="{{ asset('adminlte/img/user2.png') }}"
             alt="SIPATLIKUR Logo"
             class="brand-image img-fluid"
             style="max-height: 38px; object-fit: contain; margin-right: 10px;">
        <span class="brand-text text-white font-weight-bold" style="font-size: 20px; font-family: 'Outfit', sans-serif; letter-spacing: 0.5px;">SIPATLIKUR</span>
      </a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar-wrapper">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
        <div class="image">
          <div class="avatar-circle">
            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
          </div>
        </div>
        <div class="info ms-3">
          <a href="#" class="d-block text-decoration-none text-white font-weight-bold">{{auth()->user()->name}}</a>
          <span class="status-text">
            <span class="status-dot"></span> Online
          </span>
          @if(session()->has('tahun_ajaran'))
            <span class="badge bg-info mt-1 text-white font-weight-bold" style="font-size: 11px;">
              <i class="fas fa-calendar-alt me-1"></i> {{ session('tahun_ajaran') }} ({{ session('semester') }})
            </span>
          @endif
        </div>
      </div>

      <!-- Sidebar Menu -->
      @include('layouts.includes._sidebar')

      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <main class="app-main">
    <!-- Floating Mobile Notification Banner for iOS/Android -->
    <div id="iosNotifBanner" class="alert alert-info text-center m-3 shadow-lg" style="display:none; border-radius:14px; font-size:13px; position:relative; z-index:100; background:linear-gradient(135deg, #009638, #004d1a); color:#fff; border:none;">
      <div style="font-weight: 700; font-size: 14px;"><i class="fas fa-bell me-1" style="color:#f39c12;"></i> Notifikasi HP Belum Aktif</div>
      <div class="mt-1" style="font-size:12px; opacity:0.95;">Tekan tombol di bawah untuk mengaktifkan notifikasi di iPhone/Android Anda:</div>
      <button type="button" onclick="enableNotificationManual()" class="btn btn-warning btn-sm mt-2 font-weight-bold text-dark shadow" style="border-radius:20px; padding:7px 22px; font-size:13.5px;">
        🔔 Klik Di Sini Untuk Mengaktifkan
      </button>
    </div>

    <!-- Main content -->
    @yield('content')
    <!-- /.content -->
  </main>
  <!-- /.app-main -->

  <footer class="app-footer">
    <div class="float-end d-none d-sm-block">
      <b>Version</b> 1.1
    </div>
    <strong>&copy; {{ date('Y') }} <a href="#">SMP NEGERI 24 MALANG</a>.</strong> All rights reserved.
    @yield('footer')
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE 4 App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<!-- Bootstrap 5 Editable -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap5-editable@5.2.3/dist/bootstrap5-editable/js/bootstrap-editable.min.js"></script>

<script>
  $(function () {
    $("#example1").DataTable();
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
    });
    if (!$.fn.DataTable.isDataTable('#example3')) {
      $('#example3').DataTable({
        "scrollX": true,
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": false,
        "info": true,
        "autoWidth": true,
      });
    }
    if (!$.fn.DataTable.isDataTable('#example4')) {
      $('#example4').DataTable({
        "scrollX": true,
        "paging": false,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": true,
        "autoWidth": false,
      });
    }
    if (!$.fn.DataTable.isDataTable('#example5')) {
      $('#example5').DataTable({
        "scrollX": true,
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "pageLength": 25
      });
    }
  });
</script>

<!-- Edit Data Operator-->
<script>
	$('#edit').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var opid = button.data('myid')
	  var role = button.data('myrole')
	  var name = button.data('myname')
	  var username = button.data('myusername') // Extract info from data-* attributes
	  var walikelas_kelas = button.data('mywalikelas_kelas')
	  var additional_roles = button.data('myadditional_roles') || ''
	  var password = button.data('mypassword')
	  var modal = $(this)

	  modal.find('.modal-body #opid').val(opid)
	  modal.find('.modal-body #role').val(role)
	  modal.find('.modal-body #name').val(name)
	  modal.find('.modal-body #username').val(username)
	  modal.find('.modal-body #walikelas_kelas').val(walikelas_kelas)
	  modal.find('.modal-body #password').val(password)

	  // Reset checkboxes and set checks based on additional_roles
	  var roleArray = additional_roles.split(',')
	  modal.find('.modal-body input[type="checkbox"]').prop('checked', false)
	  roleArray.forEach(function(r) {
	      r = r.trim()
	      if (r) {
	          modal.find('.modal-body #edit_role_' + r).prop('checked', true)
	      }
	  })
	})
</script>

<!-- Edit Data Siswa-->
<script>
	$('#editsiswa').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var siswaid = button.data('myid')
	  var nis = button.data('mynis')
	  var nama = button.data('mynama')
	  var kelas = button.data('mykelas')
	  var sakit = button.data('mysakit')
	  var ijin = button.data('myijin')
	  var alpha = button.data('myalpha')
	  var dispen = button.data('mydispen')
	  var modal = $(this)

	  modal.find('.modal-body #siswaid').val(siswaid)
	  modal.find('.modal-body #nis').val(nis)
	  modal.find('.modal-body #nama').val(nama)
	  modal.find('.modal-body #kelas').val(kelas)
	  modal.find('.modal-body #sakit').val(sakit)
	  modal.find('.modal-body #ijin').val(ijin)
	  modal.find('.modal-body #alpha').val(alpha)
	  modal.find('.modal-body #dispen').val(dispen)
	})
</script>

<!-- Edit Data Surat-->
<script>
	$('#editsurat').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var srtid = button.data('myid')
	  var tglmasuk = button.data('mytglmasuk')
	  var nosurat = button.data('mynosurat')
	  var institusi = button.data('myinstitusi')
	  var perihal = button.data('myperihal')
	  var kodesurat = button.data('mykodesurat')
	  var ket = button.data('myket') // Extract info from data-* attributes
	  var modal = $(this)

	  modal.find('.modal-body #srtid').val(srtid)
	  modal.find('.modal-body #tglmasuk').val(tglmasuk)
	  modal.find('.modal-body #nosurat').val(nosurat)
	  modal.find('.modal-body #institusi').val(institusi)
	  modal.find('.modal-body #perihal').val(perihal)
	  modal.find('.modal-body #kodesurat').val(kodesurat)
	  modal.find('.modal-body #ket').val(ket)
	})
</script>

<!-- Edit Data Tamu-->
<script>
	$('#edittamu').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var tamuid = button.data('myid')
	  var nama = button.data('mynama')
	  var alamat = button.data('myalamat')
	  var email = button.data('myemail')
	  var instansi = button.data('myinstansi')
	  var maksud = button.data('mymaksud')
	  var telp = button.data('mytelp') // Extract info from data-* attributes
	  var modal = $(this)

	  modal.find('.modal-body #tamuid').val(tamuid)
	  modal.find('.modal-body #nama').val(nama)
	  modal.find('.modal-body #alamat').val(alamat)
	  modal.find('.modal-body #email').val(email)
	  modal.find('.modal-body #instansi').val(instansi)
	  modal.find('.modal-body #maksud').val(maksud)
	  modal.find('.modal-body #telp').val(telp)
	})
</script>

<!-- Edit Data Jurnal-->
<script>
	$('#editjurnal').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var jurnalid = button.data('myid')
	  var kelas = button.data('mykelas')
	  var ket_guru_mapel = button.data('myket_guru_mapel')
	  var penugasan = button.data('mypenugasan')
	  var jamke = button.data('myjamke')
	  var jumlahjam = button.data('myjumlahjam')
	  var mapel = button.data('mymapel') 
	  var guru = button.data('myguru')   
	  var materi = button.data('mymateri') 
	  var catatan = button.data('mycatatan') 
	  
	  var modal = $(this)

	  modal.find('.modal-body #jurnalid').val(jurnalid)
	  modal.find('.modal-body #kelas').val(kelas)
	  modal.find('.modal-body #ket_guru_mapel').val(ket_guru_mapel)
	  modal.find('.modal-body #penugasan').val(penugasan)
	  modal.find('.modal-body #jamke').val(jamke)
	  modal.find('.modal-body #jumlahjam').val(jumlahjam)
	  modal.find('.modal-body #mapel').val(mapel)
	  modal.find('.modal-body #guru').val(guru)
	  modal.find('.modal-body #materi').val(materi)
	  modal.find('.modal-body #catatan').val(catatan)
	})
</script>

<!-- Edit Data Kasus-->
<script>
	$('#editkasus').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var kasusid = button.data('myid')
	  var pelapor = button.data('mypelapor')
	  var kejadian = button.data('mykejadian')
	  var tempat = button.data('mytempat')
	  
	  var modal = $(this)

	  modal.find('.modal-body #kasusid').val(kasusid)
	  modal.find('.modal-body #pelapor').val(pelapor)
	  modal.find('.modal-body #kejadian').val(kejadian)
	  modal.find('.modal-body #tempat').val(tempat)
	})
</script>



<!-- Edit Data Jurnal Per Guru-->
<script>
	$('#editjurnalguru').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var jurnalid = button.data('myid')
	  var kelas = button.data('mykelas')
	  var ket_guru_mapel = button.data('myket_guru_mapel')
	  var penugasan = button.data('mypenugasan')
	  var jamke = button.data('myjamke')
	  var jumlahjam = button.data('myjumlahjam')
	  var mapel = button.data('mymapel') 
	  var guru = button.data('myguru') 
	  
	  var dispen = button.data('mydispen')  
	  var materi = button.data('mymateri') 
	  var catatan = button.data('mycatatan') 

	  var modal = $(this)

	  modal.find('.modal-body #jurnalid').val(jurnalid)
	  modal.find('.modal-body #kelas').val(kelas)
	  modal.find('.modal-body #ket_guru_mapel').val(ket_guru_mapel)
	  modal.find('.modal-body #penugasan').val(penugasan)
	  modal.find('.modal-body #jamke').val(jamke)
	  modal.find('.modal-body #jumlahjam').val(jumlahjam)
	  modal.find('.modal-body #mapel').val(mapel)
	  modal.find('.modal-body #guru').val(guru)
	  
	  modal.find('.modal-body #materi').val(materi)
	  modal.find('.modal-body #catatan').val(catatan)
	})
</script>

<!-- Tambah Data Jurnal dari Jadwal-->
<script>
	$('#editjadwal').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var jurnalid = button.data('myid')
	  var kelas = button.data('mykelas')
	  var jamke = button.data('myjamke')
	  var jumlahjam = button.data('myjumlahjam')
	  var mapel = button.data('mymapel') 
	  var guru = button.data('myguru')  
	  var materi = button.data('mymateri') 
	  var catatan = button.data('mycatatan') 

	   var j1 = button.data('myj1')
	  var j2 = button.data('myj2') 
	  var j3 = button.data('myj3') 
	  var j4 = button.data('myj4') 
	  var j5 = button.data('myj5') 
	  var j6 = button.data('myj6') 
	  var j7 = button.data('myj7') 
	  var j8 = button.data('myj8') 
	  var j9 = button.data('myj9') 
	  var j10 = button.data('myj10') 
	  var j11 = button.data('myj11')   

	  var modal = $(this)

	  modal.find('.modal-body #jurnalid').val(jurnalid)
	  modal.find('.modal-body #kelas').val(kelas)
	  modal.find('.modal-body #jamke').val(jamke)
	  modal.find('.modal-body #jumlahjam').val(jumlahjam)
	  modal.find('.modal-body #mapel').val(mapel)
	  modal.find('.modal-body #guru').val(guru)
	  modal.find('.modal-body #materi').val(materi)
	  modal.find('.modal-body #catatan').val(catatan)

	  	  modal.find('.modal-body #j1').val(j1)
	  modal.find('.modal-body #j2').val(j2)
	  modal.find('.modal-body #j3').val(j3)
	  modal.find('.modal-body #j4').val(j4)
	  modal.find('.modal-body #j5').val(j5)
	  modal.find('.modal-body #j6').val(j6)
	  modal.find('.modal-body #j7').val(j7)
	  modal.find('.modal-body #j8').val(j8)
	  modal.find('.modal-body #j9').val(j9)
	  modal.find('.modal-body #j10').val(j10)
	  modal.find('.modal-body #j11').val(j11)
	})
</script>
  
<!-- Edit Jadwal Pelajaran-->
<script>
		$('#editjadwalpel').on('show.bs.modal', function (event) {
		  var button = $(event.relatedTarget) // Button that triggered the modal
		  var jadwalid = button.data('myid')
		  var kelas = button.data('mykelas')
		  var jamke = button.data('myjamke')
		  var jumlahjam = button.data('myjumlahjam')
		  var mapel = button.data('mymapel') 
		  var guru = button.data('myguru')  
		  var hari = button.data('myhari')
		  var j1 = button.data('myj1')
		  var j2 = button.data('myj2') 
		  var j3 = button.data('myj3') 
		  var j4 = button.data('myj4') 
		  var j5 = button.data('myj5') 
		  var j6 = button.data('myj6') 
		  var j7 = button.data('myj7') 
		  var j8 = button.data('myj8') 
		  var j9 = button.data('myj9') 
		  var j10 = button.data('myj10') 
		  var j11 = button.data('myj11')   
		 

// Extract info from data-* attributes
		  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
		  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
		  var modal = $(this)

		  modal.find('.modal-body #jadwalid').val(jadwalid)
		  modal.find('.modal-body #kelas').val(kelas)
		  modal.find('.modal-body #jamke').val(jamke)
		  modal.find('.modal-body #jumlahjam').val(jumlahjam)
		  modal.find('.modal-body #mapel').val(mapel)
		  modal.find('.modal-body #guru').val(guru)
		  modal.find('.modal-body #hari').val(hari)
		  modal.find('.modal-body #j1').val(j1)
		  modal.find('.modal-body #j2').val(j2)
		  modal.find('.modal-body #j3').val(j3)
		  modal.find('.modal-body #j4').val(j4)
		  modal.find('.modal-body #j5').val(j5)
		  modal.find('.modal-body #j6').val(j6)
		  modal.find('.modal-body #j7').val(j7)
		  modal.find('.modal-body #j8').val(j8)
		  modal.find('.modal-body #j9').val(j9)
		  modal.find('.modal-body #j10').val(j10)
		  modal.find('.modal-body #j11').val(j11)

		})
	</script>
 
  <script>
	//edit perangkat
    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Tombol yang diklik
        var id = button.data('id'); // Ambil ID perangkat
        var guru = button.data('guru'); // Ambil nama guru
        var tp = button.data('tp'); // Ambil TP
        var modul = button.data('modul'); // Ambil modul
        var media = button.data('media'); // Ambil media
        var penilaian = button.data('penilaian'); // Ambil penilaian
        
        // Isi field modal dengan data yang dipilih
        var modal = $(this);
        modal.find('#guru').val(guru); // Guru tetap disabled dan diisi dengan nama
        modal.find('#tp').val(tp); // TP/ATP bisa diubah
        modal.find('#modul').val(modul); // Modul Ajar bisa diubah
        modal.find('#media').val(media); // Media Pembelajaran bisa diubah
        modal.find('#penilaian').val(penilaian); // Rencana Penilaian bisa diubah
        
        // Update action form untuk memastikan update berlaku pada perangkat yang benar
        modal.find('#editForm').attr('action', '/perangkat/' + id);
    });
</script>


<script>
  $(document).ready(function() {
    // Ketika modal akan dibuka
    $('#absensiModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget); // Tombol yang membuka modal
      var kelas = button.data('kelas');  // Mengambil kelas
      var tgl = button.data('tgl');      // Mengambil tanggal

      var modal = $(this);
      var tableBody = modal.find('#absensiTableBody'); // Menentukan body tabel di modal

      // Menampilkan kelas dan tanggal di modal
      modal.find('#modalKelas').text(kelas);
      
      // Mengubah format tanggal menjadi Tanggal Bulan Tahun
      var formattedDate = moment(tgl).format('DD MMMM YYYY');  // Menggunakan moment.js untuk format tanggal
      modal.find('#modalTanggal').text(formattedDate);

      // Clear tabel sebelumnya
      tableBody.empty();

      // Kirim request AJAX untuk mendapatkan data absensi
      $.ajax({
        url: '/jurnalh/absensi/' + kelas + '/' + tgl,  // URL untuk mendapatkan data absensi
        method: 'GET',
        success: function(response) {
          // Jika ada data absensi, tampilkan di tabel modal
          if (response.length > 0) {
            response.forEach(function(absensi) {
              var row = '<tr><td>' + absensi.nama + '</td><td>' + absensi.ket + '</td></tr>';
              tableBody.append(row);
            });
          } else {
            // Jika tidak ada data, tampilkan pesan
            var row = '<tr><td colspan="2" class="text-center">Tidak ada data absensi</td></tr>';
            tableBody.append(row);
          }
        },
        error: function() {
          // Jika ada error saat mengambil data
          var row = '<tr><td colspan="2" class="text-center">Gagal mengambil data absensi</td></tr>';
          tableBody.append(row);
        }
      });
    });
  });
</script>

<script>
  $(document).ready(function() {
    // Ketika modal akan dibuka
    $('#absensiguruModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget); // Tombol yang membuka modal
      var tgl = button.data('tgl');      // Mengambil tanggal

      var modal = $(this);
      var tableBody = modal.find('#absensiguruTableBody'); // Menentukan body tabel di modal

      
      // Mengubah format tanggal menjadi Tanggal Bulan Tahun
      var formattedDate = moment(tgl).format('DD MMMM YYYY');  // Menggunakan moment.js untuk format tanggal
      modal.find('#modalTanggal').text(formattedDate);

      // Clear tabel sebelumnya
      tableBody.empty();

      // Kirim request AJAX untuk mendapatkan data absensi
      $.ajax({
        url: '/jurnalh/absensiguru/' + tgl,  // URL untuk mendapatkan data absensi
        method: 'GET',
        success: function(response) {
          // Jika ada data absensi, tampilkan di tabel modal
          if (response.length > 0) {
            response.forEach(function(absensiguru) {
              var row = '<tr><td>' + absensiguru.guru + '</td><td>' + absensiguru.sia + '</td></tr>';
              tableBody.append(row);
            });
          } else {
            // Jika tidak ada data, tampilkan pesan
            var row = '<tr><td colspan="2" class="text-center">Tidak ada data absensi</td></tr>';
            tableBody.append(row);
          }
        },
        error: function() {
          // Jika ada error saat mengambil data
          var row = '<tr><td colspan="2" class="text-center">Gagal mengambil data absensi</td></tr>';
          tableBody.append(row);
        }
      });
    });
  });
</script>

<script>
$(document).ready(function() {
    // Event handler untuk perubahan dropdown kelas
    $('#kelasSelectModal').change(function() {
        var selectedKelas = $(this).val();
        var studentSelect = $('#studentSelectModal');
        
        // Reset dropdown siswa
        studentSelect.prop('disabled', true);
        studentSelect.val('');
        
        // Sembunyikan semua option siswa
        studentSelect.find('option').not(':first').hide();
        
        if (selectedKelas) {
            // Tampilkan hanya siswa dari kelas yang dipilih
            studentSelect.find('option[data-kelas="' + selectedKelas + '"]').show();
            studentSelect.prop('disabled', false);
        }
    });
    
    // Reset form ketika modal ditutup
    $('#tambahabsen').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $('#studentSelectModal').prop('disabled', true);
        $('#studentSelectModal').find('option').not(':first').hide();
    });
});
</script>

<script>
    $(document).ready(function() {
        // Ketika tombol export Excel diklik
        $('#exportExcelBtn').on('click', function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            if (start_date && end_date) {
                window.location.href = "/jurnal/export-excel?start_date=" + start_date + "&end_date=" + end_date;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Tanggal',
                    text: 'Silakan pilih rentang tanggal terlebih dahulu.',
                    confirmButtonColor: '#0a3d91'
                });
            }
        });

        // Ketika tombol export PDF diklik
        $('#exportPdfBtn').on('click', function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            if (start_date && end_date) {
                window.location.href = "/jurnal/export-pdf?start_date=" + start_date + "&end_date=" + end_date;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Tanggal',
                    text: 'Silakan pilih rentang tanggal terlebih dahulu.',
                    confirmButtonColor: '#0a3d91'
                });
            }
        });
    });
</script>



<script>
$(document).ready(function() {
    var changedFields = new Set();
    var saveTimeout;
    
    // Handle perubahan field
    $('.editable-field').on('input change', function() {
        var $this = $(this);
        var id = $this.data('id');
        var field = $this.data('field');
        
        console.log('Field changed:', field, 'Value:', $this.val(), 'ID:', id);
        
        changedFields.add(id + '-' + field);
        
        // Show individual save button
        var $saveBtn = $this.closest('tr').find('.save-row-btn');
        $saveBtn.show();
        
        // Show global save button
        $('#saveAllBtn').show();
        
        // Add visual indicator
        $this.addClass('field-changed');
        
        // Auto calculate nilai garjas B dan total if it's a score field
        if(['nlari', 'nup', 'nsitup', 'npushup', 'nshuttle'].includes(field)) {
            console.log('Triggering calculation for field:', field);
            calculateGarjasBAndTotal($this.closest('tr'));
        }
        
        // Auto save setelah 2 detik tidak ada perubahan
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(function() {
            if ($this.hasClass('field-changed')) {
                var $row = $this.closest('tr');
                var id = $this.data('id');
                console.log('Auto-saving row:', id);
                saveRowData($row, id);
            }
        }, 2000);
    });
    
    // Tambahkan event listener khusus untuk real-time calculation
    $(document).on('input keyup change', '.editable-field[data-field="nlari"], .editable-field[data-field="nup"], .editable-field[data-field="nsitup"], .editable-field[data-field="npushup"], .editable-field[data-field="nshuttle"]', function() {
        console.log('Real-time calculation triggered');
        calculateGarjasBAndTotal($(this).closest('tr'));
    });
    
    // Calculate Garjas B and Total for a row
    function calculateGarjasBAndTotal($row) {
        console.log('calculateGarjasBAndTotal called');
        console.log('Row:', $row);
        
        // Ambil nilai UP, SIT UP, PUSH UP, SHUTTLE
        var nup = parseFloat($row.find('input[data-field="nup"]').val()) || 0;
        var nsitup = parseFloat($row.find('input[data-field="nsitup"]').val()) || 0;
        var npushup = parseFloat($row.find('input[data-field="npushup"]').val()) || 0;
        var nshuttle = parseFloat($row.find('input[data-field="nshuttle"]').val()) || 0;
        
        console.log('Input values:', { nup, nsitup, npushup, nshuttle });
        
        // Hitung Nilai Garjas B = (nup + nsitup + npushup + nshuttle) / 4
        var nb = (nup + nsitup + npushup + nshuttle) / 4;
        
        console.log('Calculated nb:', nb);
        
        // Update tampilan Nilai Garjas B - coba beberapa selector
        var $nbBadge = $row.find('.nb-badge');
        if ($nbBadge.length === 0) {
            // Fallback selector
            $nbBadge = $row.find('span.badge-warning');
        }
        
        console.log('Found nb badge elements:', $nbBadge.length);
        
        if ($nbBadge.length > 0) {
            $nbBadge.text(nb.toFixed(2));
            console.log('Updated nb badge to:', nb.toFixed(2));
            
            // Tambahkan visual effect
            $nbBadge.css('background-color', '#28a745').delay(500).queue(function() {
                $(this).css('background-color', '').dequeue();
            });
        } else {
            console.log('nb-badge not found in row');
            console.log('Available elements in row:', $row.find('span, .badge').length);
        }
        
        // Ambil nilai lari
        var nlari = parseFloat($row.find('input[data-field="nlari"]').val()) || 0;
        
        // Hitung Total Nilai = (nlari + nb) / 2
        var total = (nlari + nb) / 2;
        
        console.log('Calculated total:', total, 'from nlari:', nlari, 'and nb:', nb);
        
        // Update tampilan Total - coba beberapa selector
        var $totalBadge = $row.find('.total-badge');
        if ($totalBadge.length === 0) {
            // Fallback selector
            $totalBadge = $row.find('span.badge-success');
        }
        
        console.log('Found total badge elements:', $totalBadge.length);
        
        if ($totalBadge.length > 0) {
            $totalBadge.text(total.toFixed(2));
            console.log('Updated total badge to:', total.toFixed(2));
            
            // Tambahkan visual effect
            $totalBadge.css('background-color', '#ffc107').delay(500).queue(function() {
                $(this).css('background-color', '').dequeue();
            });
        } else {
            console.log('total-badge not found in row');
        }
    }
    
    // Save individual row
    $('.save-row-btn').on('click', function() {
        var $btn = $(this);
        var id = $btn.data('id');
        var $row = $btn.closest('tr');
        
        console.log('Manual save triggered for row:', id);
        saveRowData($row, id);
    });
    
    // Save all changes
    $('#saveAllBtn').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
        
        var promises = [];
        var processedRows = new Set();
        
        $('.field-changed').each(function() {
            var $field = $(this);
            var id = $field.data('id');
            var $row = $field.closest('tr');
            
            // Group by row ID to avoid multiple saves for same row
            if (!processedRows.has(id)) {
                processedRows.add(id);
                promises.push(saveRowData($row, id));
            }
        });
        
        Promise.all(promises).then(function() {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Semua Perubahan');
            $('#saveAllBtn').hide();
            changedFields.clear();
            console.log('All changes saved successfully');
        }).catch(function(error) {
            console.error('Error saving all changes:', error);
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Semua Perubahan');
        });
    });
    
    // Save row data function
    function saveRowData($row, id) {
        return new Promise(function(resolve, reject) {
            var data = {
                _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}',
                _method: 'PUT'
            };
            
            var hasChanges = false;
            
            // Collect all data from the row
            $row.find('.editable-field').each(function() {
                var $field = $(this);
                var fieldName = $field.data('field');
                var value = $field.val();
                
                // Include field even if empty to allow clearing values
                data[fieldName] = value;
                
                if ($field.hasClass('field-changed')) {
                    hasChanges = true;
                }
            });
            
            console.log('Saving data for row', id, ':', data);
            
            if (!hasChanges) {
                console.log('No changes detected for row', id);
                resolve();
                return;
            }
            
            // Show loading di row
            $row.addClass('saving-row');
            
            $.ajax({
                url: '{{ url("garjas") }}/' + id,
                type: 'POST',
                data: data,
                timeout: 10000, // 10 second timeout
                success: function(response) {
                    console.log('Save success for row', id, ':', response);
                    
                    if (response.success) {
                        // Remove visual indicators
                        $row.find('.field-changed').removeClass('field-changed');
                        $row.find('.save-row-btn').hide();
                        $row.removeClass('saving-row');
                        
                        // Update total if returned
                        if (response.total !== undefined) {
                            $row.find('.total-badge').text(parseFloat(response.total).toFixed(2));
                            console.log('Updated total from server:', response.total);
                        }
                        
                        // Update nb if returned
                        if (response.nb !== undefined) {
                            $row.find('.nb-badge').text(parseFloat(response.nb).toFixed(2));
                            console.log('Updated nb from server:', response.nb);
                        }
                        
                        // Jika tidak ada response nb/total, hitung ulang
                        if (response.nb === undefined || response.total === undefined) {
                            console.log('Server did not return nb/total, recalculating...');
                            setTimeout(function() {
                                calculateGarjasBAndTotal($row);
                            }, 100);
                        }
                        
                        // Show success indicator briefly
                        $row.addClass('save-success');
                        setTimeout(function() {
                            $row.removeClass('save-success');
                        }, 2000);
                        
                        resolve();
                    } else {
                        console.error('Server returned error:', response);
                        throw new Error(response.message || 'Server error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error for row', id, ':', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        response: xhr.responseText,
                        error: error
                    });
                    
                    $row.removeClass('saving-row');
                    
                    // Show error indicator
                    $row.addClass('save-error');
                    setTimeout(function() {
                        $row.removeClass('save-error');
                    }, 3000);
                    
                    var errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                    
                    if (xhr.status === 0) {
                        errorMessage = 'Koneksi terputus. Periksa koneksi internet Anda.';
                    } else if (xhr.status === 419) {
                        errorMessage = 'Sesi telah berakhir. Silakan refresh halaman.';
                        location.reload();
                        return;
                    } else if (xhr.status === 422) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('\n');
                        }
                    } else if (xhr.status === 500) {
                        errorMessage = 'Terjadi kesalahan server. Silakan coba lagi.';
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: errorMessage,
                            confirmButtonColor: '#0a3d91'
                        });
                    } else {
                        alert('Error: ' + errorMessage);
                    }
                    reject(new Error(errorMessage));
                }
            });
        });
    }
    
    // Prevent form submission on Enter dan langsung save
    $('.editable-field').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            clearTimeout(saveTimeout); // Stop auto save
            $(this).trigger('change');
            var $row = $(this).closest('tr');
            var id = $(this).data('id');
            console.log('Enter pressed, saving row:', id);
            saveRowData($row, id);
        }
    });
    
    // Focus pada field saat diklik
    $('.editable-field').on('focus', function() {
        $(this).select();
    });
    
    // Detect browser tab visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Tab is hidden, save any pending changes
            if (changedFields.size > 0) {
                console.log('Tab hidden, saving pending changes');
                $('#saveAllBtn').click();
            }
        }
    });
    
    // Save before page unload
    window.addEventListener('beforeunload', function(e) {
        if (changedFields.size > 0) {
            e.preventDefault();
            e.returnValue = 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }
    });
    
    // Check for required meta tags
    if (!$('meta[name="csrf-token"]').length) {
        console.warn('CSRF token not found in meta tag');
    }
});
</script>

<script>
function toggleJamTerlambat() {
    var siaSelect = document.getElementById('exampleFormControlSelect1');
    var jamTerlambatGroup = document.getElementById('jam_terlambat_group');
    var jumlahInput = document.getElementById('jumlah');
    
    if (!siaSelect || !jamTerlambatGroup || !jumlahInput) return;
    
    if (siaSelect.value === 'Terlambat') {
        jamTerlambatGroup.style.display = 'block';
        jumlahInput.value = '0';
        jumlahInput.readOnly = true;
    } else {
        jamTerlambatGroup.style.display = 'none';
        jumlahInput.readOnly = false;
    }
}
</script>

<script>
function toggleModalJamTerlambat() {
    var siaSelect = document.getElementById('modal_sia');
    var jamTerlambatGroup = document.getElementById('modal_jam_terlambat_group');
    var jumlahInput = document.getElementById('modal_jumlah');
    
    if (!siaSelect || !jamTerlambatGroup || !jumlahInput) return;
    
    if (siaSelect.value === 'Terlambat') {
        jamTerlambatGroup.style.display = 'block';
        jumlahInput.value = '0';
        jumlahInput.readOnly = true;
    } else {
        jamTerlambatGroup.style.display = 'none';
        jumlahInput.readOnly = false;
    }
}

$(document).on('show.bs.modal', '#editijin', function (event) {
    var button = $(event.relatedTarget);
    
    $('#ijinid').val(button.data('myid'));
    $('#modal_tglmasuk').val(button.data('mytglmasuk'));
    $('#modal_guru').val(button.data('myguru'));
    $('#modal_mapel').val(button.data('mymapel'));
    $('#modal_sia').val(button.data('mysia'));
    $('#modal_jumlah').val(button.data('myjumlah'));
    $('#modal_jam_terlambat').val(button.data('myjamterlambat'));
    $('#modal_ket').val(button.data('myket'));
    
    // Toggle jam terlambat field based on selected value
    toggleModalJamTerlambat();
});
</script>

<script>
function toggleEditJamTerlambat() {
    var siaSelect = document.getElementById('exampleFormControlSelect1');
    var jamTerlambatGroup = document.getElementById('edit_jam_terlambat_group');
    var jumlahInput = document.getElementById('edit_jumlah');
    
    if (!siaSelect || !jamTerlambatGroup || !jumlahInput) return;
    
    if (siaSelect.value === 'Terlambat') {
        jamTerlambatGroup.style.display = 'block';
        jumlahInput.value = '0';
        jumlahInput.readOnly = true;
    } else {
        jamTerlambatGroup.style.display = 'none';
        jumlahInput.readOnly = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleEditJamTerlambat();
});
</script>@auth
<!-- SINALA Mobile Bottom Navigation Bar (Tailored Dynamic Role Navigation) -->
@php
  $authUser = auth()->user();
  $isAuthKetua = ($authUser->role === 'ketuakelas' || $authUser->hasRole('ketuakelas') || str_contains((string)$authUser->additional_roles, 'ketuakelas'));
  $isAuthSiswa = ($authUser->role === 'siswa' || $authUser->hasRole('siswa') || $isAuthKetua);
@endphp
<div class="sinala-bottom-nav d-block d-md-none fixed-bottom bg-white border-top shadow-lg py-2 px-1" style="z-index: 1050;">
  <div class="d-flex justify-content-around align-items-center text-center">
    
    <!-- 1. Dashboard Link -->
    <a href="/dashboard" class="text-decoration-none {{ Request::is('dashboard') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
      <i class="fas fa-chart-line d-block mb-1" style="font-size: 18px;"></i>
      <span>Dashboard</span>
    </a>

    @if($isAuthSiswa && $isAuthKetua)
      <!-- Siswa yang juga Ketua Kelas -->
      <!-- 2. Tambah Izin Saya -->
      <a href="/tambahijinsiswa" class="text-decoration-none {{ Request::is('tambahijinsiswa*') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-file-signature d-block mb-1" style="font-size: 18px;"></i>
        <span>Tambah Izin</span>
      </a>

      <!-- 3. Isi Jurnal Kelas -->
      <a href="/jurnalbaru" class="text-decoration-none {{ Request::is('jurnalbaru*') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-edit d-block mb-1" style="font-size: 18px;"></i>
        <span>Isi Jurnal</span>
      </a>

      <!-- 4. Riwayat Izin -->
      <a href="/ijinsiswa" class="text-decoration-none {{ Request::is('ijinsiswa*') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-history d-block mb-1" style="font-size: 18px;"></i>
        <span>Riwayat Izin</span>
      </a>
    @elseif($isAuthSiswa)
      <!-- Siswa Biasa -->
      <a href="/tambahijinsiswa" class="text-decoration-none {{ Request::is('tambahijinsiswa*') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-file-signature d-block mb-1" style="font-size: 18px;"></i>
        <span>Tambah Izin</span>
      </a>



      <a href="/ijinsiswa" class="text-decoration-none {{ Request::is('ijinsiswa*') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-history d-block mb-1" style="font-size: 18px;"></i>
        <span>Riwayat Izin</span>
      </a>
    @elseif($authUser->hasRole('admin'))
      <a href="/operator" class="text-decoration-none {{ Request::is('operator*') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-user-cog d-block mb-1" style="font-size: 18px;"></i>
        <span>Operator</span>
      </a>
      <a href="/jurnalh?view=kurikulum" class="text-decoration-none {{ Request::is('jurnalh*') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-book-open d-block mb-1" style="font-size: 18px;"></i>
        <span>Jurnal</span>
      </a>
      <a href="/ijinsiswa" class="text-decoration-none {{ Request::is('ijinsiswa*') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-id-card d-block mb-1" style="font-size: 18px;"></i>
        <span>Izin</span>
      </a>
    @elseif($authUser->hasRole('walikelas'))

      <a href="/jurnalh?view=walikelas" class="text-decoration-none {{ Request::is('jurnalh*') && request()->query('view')==='walikelas' ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-book-open d-block mb-1" style="font-size: 18px;"></i>
        <span>Jurnal</span>
      </a>
      <a href="/ijinsiswa?view=walikelas" class="text-decoration-none {{ Request::is('ijinsiswa*') && request()->query('view')==='walikelas' ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-id-card d-block mb-1" style="font-size: 18px;"></i>
        <span>Izin Kelas</span>
      </a>
    @elseif($authUser->hasRole('kurikulum'))

      <a href="/jurnalh?view=kurikulum" class="text-decoration-none {{ Request::is('jurnalh*') && request()->query('view')==='kurikulum' ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-book-open d-block mb-1" style="font-size: 18px;"></i>
        <span>Jurnal</span>
      </a>
      <a href="/ijinsiswa?view=kurikulum" class="text-decoration-none {{ Request::is('ijinsiswa*') && request()->query('view')==='kurikulum' ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-id-card d-block mb-1" style="font-size: 18px;"></i>
        <span>Izin Siswa</span>
      </a>
    @else

      <a href="/jurnalh" class="text-decoration-none {{ Request::is('jurnalh*') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-book-open d-block mb-1" style="font-size: 18px;"></i>
        <span>Jurnal</span>
      </a>
      <a href="/ijinsiswa" class="text-decoration-none {{ Request::is('ijinsiswa*') ? 'text-primary font-weight-bold' : 'text-secondary' }}" style="font-size: 11px;">
        <i class="fas fa-id-card d-block mb-1" style="font-size: 18px;"></i>
        <span>Izin</span>
      </a>
    @endif

    <!-- 5. Sidebar Menu Toggle (All Roles) -->
    <a href="#" data-lte-toggle="sidebar" class="text-decoration-none text-secondary" style="font-size: 11px;">
      <i class="fas fa-bars d-block mb-1" style="font-size: 18px;"></i>
      <span>Menu</span>
    </a>

  </div>
</div>
@endauth

<!-- Firebase Web FCM Client Script -->
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js"></script>
<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/firebase-messaging-sw.js')
    .then(function(registration) {
      console.log('Firebase Service Worker Registered');
    }).catch(function(err) {
      console.log('Service Worker Registration Failed: ', err);
    });
}

function fetchAndSendToken() {
  if (typeof firebase === 'undefined') return;
  
  const firebaseConfig = {
    apiKey: "AIzaSyCjcYek3pCosfdI0CJB3D08-BnP2HScIsY",
    authDomain: "sipatlikur.firebaseapp.com",
    projectId: "sipatlikur",
    storageBucket: "sipatlikur.firebasestorage.app",
    messagingSenderId: "521144391233",
    appId: "1:521144391233:web:b37d0780aecb6c68acd8c0",
    measurementId: "G-46KWQ3FQ41"
  };

  if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
  }

  try {
    const messaging = firebase.messaging();
    const vapidKey = "{{ env('FIREBASE_VAPID_KEY', 'BLKbgGABY4Nzxe3NZliOfeK7LjPvE7Ws1RkBWjEVRek_vWkOyOBe_agnmRK-kDEUXrYVxzGoWHoMJ0ZUDmkM-W8') }}";
    const getTokenOptions = vapidKey ? { vapidKey: vapidKey } : {};
    
    messaging.onMessage(function(payload) {
      console.log('Foreground FCM Message received:', payload);
      const title = payload.notification ? payload.notification.title : 'SIPATLIKUR Notifikasi';
      const body = payload.notification ? payload.notification.body : '';
      
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'info',
          title: title,
          text: body,
          showConfirmButton: false,
          timer: 6000,
          timerProgressBar: true
        });
      }
    });

    messaging.getToken(getTokenOptions).then(function(token) {
      if (token) {
        console.log('FCM Token generated:', token);
        $.ajax({
          url: '/update-fcm-token',
          type: 'POST',
          data: {
            fcm_token: token,
            device_info: 'Android Browser/FCM Web',
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function(res) {
            console.log('FCM token updated successfully on server');
            $('#iosNotifBanner').slideUp();
          }
        });
      } else {
        sendApkFallbackToken();
      }
    }).catch(function(err) {
      console.error('FCM getToken error:', err);
      sendApkFallbackToken();
    });
  } catch(e) {
    console.error('FCM messaging error:', e);
    sendApkFallbackToken();
  }
}

function sendApkFallbackToken() {
  try {
    let apkToken = localStorage.getItem('sipatlikur_apk_token');
    if (!apkToken) {
      apkToken = 'apk_device_' + Math.random().toString(36).substring(2, 15) + Date.now().toString(36);
      localStorage.setItem('sipatlikur_apk_token', apkToken);
    }

    $.ajax({
      url: '/update-fcm-token',
      type: 'POST',
      data: {
        fcm_token: apkToken,
        device_info: 'APK Android WebView',
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function(res) {
        console.log('APK WebView device token registered on server');
        $('#iosNotifBanner').slideUp();
      }
    });
  } catch (err) {
    console.error('sendApkFallbackToken error:', err);
  }
}

function enableNotificationManual() {
  fetchAndSendToken();

  if (typeof Notification !== 'undefined' && Notification.requestPermission) {
    Notification.requestPermission().then(function(permission) {
      if (permission === 'granted') {
        $('#iosNotifBanner').slideUp();
        fetchAndSendToken();
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'success',
            title: 'Notifikasi Berhasil Diaktifkan! 🎉',
            text: 'HP Anda siap menerima Push Notification dari SIPATLIKUR.',
            timer: 3000,
            showConfirmButton: false
          });
        }
      }
    });
  } else {
    $('#iosNotifBanner').slideUp();
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Notifikasi FCM Diaktifkan! 🎉',
        text: 'Perangkat Anda didaftarkan ke sistem Notifikasi SIPATLIKUR.',
        timer: 3000,
        showConfirmButton: false
      });
    }
  }
}

function initFCMToken() {
  fetchAndSendToken();

  if (typeof Notification !== 'undefined') {
    if (Notification.permission === 'granted') {
      $('#iosNotifBanner').hide();
    } else if (Notification.permission === 'default') {
      $('#iosNotifBanner').slideDown();
    } else {
      $('#iosNotifBanner').hide();
    }
  } else {
    $('#iosNotifBanner').hide();
  }
}

$(document).ready(function() {
  initFCMToken();
  if (typeof $.fn.select2 !== 'undefined') {
    $('.select2-user, .select2').select2({
      theme: 'bootstrap-5',
      placeholder: '-- Cari / Pilih Pegawai --',
      allowClear: true,
      width: '100%'
    });
  }
});
</script>

@stack('scripts')
@yield('scripts')

<style>
  .swal2-container.swal2-top-end,
  .swal2-container.swal2-top-right {
    top: 70px !important;
    right: 20px !important;
    z-index: 999999 !important;
  }
</style>

@if(session('sukses'))
<script>
$(document).ready(function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('sukses') }}",
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    }
});
</script>
@endif

@if(session('gagal') || session('error'))
<script>
$(document).ready(function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Perhatian / Kesalahan',
            text: "{{ session('gagal') ?? session('error') }}",
            confirmButtonColor: '#009638'
        });
    }
});
</script>
@endif

</body>
</html>
