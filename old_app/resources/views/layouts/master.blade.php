<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SINALA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  
  <!-- DataTables -->
  <link rel="stylesheet" href="/plugins/datatables/dataTables.bootstrap4.css">
  
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="adminlte/css/adminlte.min.css">
  
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <!-- Edit Nilai -->
  <link rel="stylesheet" href="/css/bootstrap-editable.css">
</head>
<body class="hold-transition sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">

  <!-- Navbar -->
  @include('layouts.includes._navbar')

  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/dashboard" class="brand-link">
      <img src="adminlte/img/AdminLTELogo.png"
           alt="AdminLTE Logo"
           class="brand-image img-circle elevation-3"
           style="opacity: .8">
      <span class="brand-text font-weight-light">SINALA</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="adminlte/img/user2.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">{{auth()->user()->name}}</a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      @include('layouts.includes._sidebar')

      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <!-- Main content -->
    @yield('content')
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 1.1
    </div>
    <strong>&copy; 2024 <a href="https://smantarunajatim.sch.id/">SMAN TARUNA NALA</a>.</strong> All rights reserved.
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
<script src="/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- FastClick -->
<script src="/plugins/fastclick/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="adminlte/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="adminlte/js/demo.js"></script>
<!-- DataTables -->
<script src="/plugins/datatables/jquery.dataTables.js"></script>
<script src="/plugins/datatables/dataTables.bootstrap4.js"></script>

<!-- Tambahkan sebelum script custom modal -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<!-- Edit Nilai -->
<script src="/js/bootstrap-editable.js"></script>
<script src="/js/bootstrap-editable.min.js"></script>

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
    $('#example3').DataTable({
      "scrollX": true,
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": true,
    });
    $('#example4').DataTable({
      "scrollX": true,
      "paging": false,
      "lengthChange": false,
      "searching": false,
      "ordering": false,
      "info": true,
      "autoWidth": false,
    });
    $('#example5').DataTable({
      "scrollX": true,
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
    });
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
	  var modal = $(this)

	  modal.find('.modal-body #opid').val(opid)
	  modal.find('.modal-body #role').val(role)
	  modal.find('.modal-body #name').val(name)
	  modal.find('.modal-body #username').val(username)
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
                alert("Silakan pilih rentang tanggal terlebih dahulu.");
            }
        });

        // Ketika tombol export PDF diklik
        $('#exportPdfBtn').on('click', function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            if (start_date && end_date) {
                window.location.href = "/jurnal/export-pdf?start_date=" + start_date + "&end_date=" + end_date;
            } else {
                alert("Silakan pilih rentang tanggal terlebih dahulu.");
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
                    
                    alert('Error: ' + errorMessage);
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
    
    if (siaSelect.value === 'Terlambat') {
        jamTerlambatGroup.style.display = 'block';
        jumlahInput.value = '0';
        jumlahInput.readOnly = true;
    } else {
        jamTerlambatGroup.style.display = 'none';
        jumlahInput.readOnly = false;
    }
}

$('#editijin').on('show.bs.modal', function (event) {
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
</script>



</body>
</html>
