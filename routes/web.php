<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\Http\Controllers')->group(function () {
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/dashboard');
})->middleware('auth');

Route::get('/login','AuthController@login')->name('login');
Route::get('/auth/auto-login','AuthController@autoLogin');
Route::post('/postlogin','AuthController@postlogin')->middleware('throttle:30,1');
Route::get('/logout','AuthController@logout');


Route::group(['middleware'=>['auth', 'force.password.change']],function(){
			Route::get('/dashboard','DashboardController@index');
			Route::post('/dashboard/verifikasi-absensi', 'DashboardController@verifikasiAbsensi')->name('dashboard.verifikasi');
			Route::get('/notifications/{id}/read', 'DashboardController@readNotification')->name('notifications.read');
			Route::post('/notifications/mark-all-read', 'DashboardController@markAllNotificationsAsRead')->name('notifications.markAllRead');
			Route::post('/auth/switch-role','AuthController@switchRole');
			Route::post('/update-fcm-token', 'AuthController@updateFcmToken');
			Route::get('/ganti-password','AuthController@gantiPassword');
			Route::post('/ganti-password','AuthController@updatePassword');
			//route tambah jurnal dengan jadwal
			Route::get('/jurnalbaru','JurnalbaruController@index');
			Route::post('/jurnalbaru/update','JurnalbaruController@update');
			Route::post('/jurnalbaru/tambahabsen','JurnalbaruController@tambahabsen');
			Route::get('/jurnalbaru/{id}/delete','JurnalbaruController@delete');
			
			Route::get('/susulan','JurnalbaruController@susulan');
			Route::post('/susulan/updatesusulan','JurnalbaruController@updatesusulan');
			Route::post('/susulan/absensusulan','JurnalbaruController@absensusulan');

			//route jadwal viewing
			Route::get('/jadwal','JadwalController@index')->middleware('role:admin,guru,lihat,siswa');
			Route::get('/jadwal/export','JadwalController@export')->middleware('role:admin,guru,lihat');

			//route absen
			Route::get('/absen','AbsenController@index');
			Route::post('/absen/create','AbsenController@create');
			Route::post('/absen/update','AbsenController@update');
			Route::get('/absen/{id}/delete','AbsenController@delete');
			Route::get('/absen/export','AbsenController@export');
			Route::post('/absen/import','AbsenController@import');

			//route jurnal
			Route::get('/tambahjurnal','JurnalController@tambahj');
			Route::get('/lihatjurnal','JurnalController@lihatj');

			Route::get('/jurnal','JurnalController@index');
			Route::post('/jurnal/create','JurnalController@create');
			Route::post('/jurnal/createsusul','JurnalController@createsusul');
			Route::get('/jurnal/export','JurnalController@export');
			
			Route::get('/rekap-jurnal', 'JurnalController@rekapJurnal');
			Route::get('/jurnal/export-excel', 'JurnalController@exportExcel')->name('jurnal.exportExcel');
			Route::get('/jurnal/export-pdf', 'JurnalController@exportPDF')->name('jurnal.exportPDF');

			Route::get('/jurnal/exporttanggal','JurnalController@exporttanggal');
			
			Route::post('/jurnal/update','JurnalController@update');
			Route::get('/jurnal/{id}/delete','JurnalController@delete');

			Route::get('/edits','JurnalController@edits');
			Route::post('/jurnal/updates','JurnalController@updates');
			Route::get('/jurnal/editsexport','JurnalController@editsexport');

			//jurnal per guru
			Route::get('/jurnalguru','JurnalguruController@index');
			Route::get('/jurnalguru/export','JurnalguruController@export');
			// Route::get('/jurnalguru/{id}/edit','JurnalguruController@edit');
			Route::post('/jurnalguru/update','JurnalguruController@update');

			//ijin absen
			Route::get('/tambahijin','IjinController@tambahi');

			Route::get('/ijin','IjinController@index');
			Route::post('/ijin/create','IjinController@create');
			Route::get('/ijin/export','IjinController@export');
			// Route::get('/ijin/{id}/edit','IjinController@edit');
			Route::post('/ijin/update','IjinController@update');
			Route::get('/ijin/{id}/delete','IjinController@delete');
			Route::get('/ijin/rekaphadir','IjinController@rekaphadir');

			//route laporan kasus
			Route::get('/tambahkasus','KasusController@tambahk');
			Route::get('/lihatkasus','KasusController@index');

			Route::get('/kasus','KasusController@index');
			Route::post('/kasus/create','KasusController@create');
			Route::get('/kasus/export','KasusController@export');
			// Route::get('/kasus/{id}/edit','KasusController@edit');
			Route::post('/kasus/update','KasusController@update');
			Route::get('/kasus/{id}/delete','KasusController@delete');

			// Admin-only Routes (Operator, Kelas, Mapel, Siswa Mutation)
			Route::group(['middleware' => ['role:admin']], function() {
				//route tambah operator
				Route::get('/operator','OperatorController@operator');
				Route::post('/operator/create','OperatorController@create');
				Route::post('/operator/save-permissions','OperatorController@savePermissions');
				Route::get('/operator/export','OperatorController@export');
				Route::post('/operator/update','OperatorController@update');
				Route::get('/operator/{id}/delete','OperatorController@delete');
				Route::post('/operator/import','OperatorController@import');
				Route::get('/operator/template','OperatorController@downloadTemplate');

				//route kelas
				Route::get('/kelas','KelasController@index');
				Route::post('/kelas/create','KelasController@create');
				Route::post('/kelas/update','KelasController@update');
				Route::get('/kelas/{id}/delete','KelasController@delete');

				//route mapel
				Route::get('/mapel','MapelController@index');
				Route::post('/mapel/create','MapelController@create');
				Route::post('/mapel/update','MapelController@update');
				Route::get('/mapel/{id}/delete','MapelController@delete');

				//route kategori poin
				Route::get('/kategori-poin','KategoriPoinController@index');
				Route::post('/kategori-poin/create','KategoriPoinController@create');
				Route::post('/kategori-poin/update','KategoriPoinController@update');
				Route::get('/kategori-poin/{id}/delete','KategoriPoinController@delete');

				// route ijin guru approval
				Route::post('/ijin/{id}/approve', 'IjinController@approve')->name('ijin.approve');
				Route::post('/ijin/{id}/reject', 'IjinController@reject')->name('ijin.reject');

				// admin audit logs and backup/restore
				Route::get('/admin/logs', 'AdminController@logs')->name('admin.logs');
				Route::get('/admin/backup', 'AdminController@backupIndex')->name('admin.backup');
				Route::post('/admin/backup/download', 'AdminController@backupDownload')->name('admin.backup.download');
				Route::post('/admin/backup/restore', 'AdminController@backupRestore')->name('admin.backup.restore');

				//route siswa mutation
				Route::post('/siswa/create','SiswaController@create');
				Route::get('/siswa/template','SiswaController@downloadTemplate');
				Route::post('/siswa/import','SiswaController@import');
				Route::post('/siswa/update','SiswaController@update');
				Route::get('/siswa/{id}/delete','SiswaController@delete');
				Route::post('/siswa/update-ijin', 'SiswaController@updateIjin');
				
				// Reset password actions
				Route::post('/siswa/{id}/reset-password','SiswaController@resetPassword');
				Route::post('/operator/{id}/reset-password','OperatorController@resetPassword');

				//route jadwal mutation & template
				Route::post('/jadwal/create','JadwalController@create');
				Route::post('/jadwal/update','JadwalController@update');
				Route::get('/jadwal/{id}/delete','JadwalController@delete');
				Route::post('/jadwal/delete-multiple','JadwalController@deleteMultiple');
				Route::post('/jadwal/delete-all','JadwalController@deleteAll');
				Route::post('/jadwal/import','JadwalController@import');
				Route::get('/jadwal/template','JadwalController@downloadTemplate');
			});

			// Student points rekap & SP printing routes
			Route::get('/poin-siswa', 'PoinSiswaController@inputPoin')->middleware('role:admin,guru,lihat');
			Route::post('/poin-siswa/create', 'PoinSiswaController@create')->middleware('role:admin,guru,lihat');
			Route::get('/history-poin', 'PoinSiswaController@index');
			Route::get('/history-poin/{id}/delete', 'PoinSiswaController@delete');
			Route::get('/poin-siswa/{id}/cetak-sp/{level}', 'PoinSiswaController@cetakSP')->middleware('role:admin,guru,lihat');

			//route disposisi surat
			Route::get('/surat','SuratController@surat');
			Route::post('/surat/create','SuratController@create');
			Route::get('/surat/exportexcel','SuratController@exportExcel');
			Route::get('/surat/{id}/exportpdf','SuratController@exportPDF');
			Route::post('/surat/update','SuratController@update');
			Route::get('/surat/{id}/delete','SuratController@delete');

			//route buku tamu
			Route::get('/tamu','TamuController@tamu');
			Route::post('/tamu/create','TamuController@create');
			Route::get('/tamu/export','TamuController@export');
			Route::post('/tamu/update','TamuController@update');
			Route::get('/tamu/{id}/delete','TamuController@delete');

			//route siswa viewing
			Route::get('/siswa','SiswaController@index')->middleware('role:admin,guru,lihat,ketuakelas,walikelas');
			Route::get('/siswa/export','SiswaController@export')->middleware('role:admin,guru,lihat');

			//route jurnal rekap jrekap
			Route::get('/jrekap','JrekapController@index');
			Route::post('/jrekap/create','JrekapController@create');
			Route::post('/jrekap/update','JrekapController@update');
			Route::get('/jrekap/{id}/delete','JrekapController@delete');
			Route::get('/jrekap/export','JrekapController@export');
			Route::get('/jrekap/exportkelas','JrekapController@exportkelas');
			Route::get('/jrekap/exportlaporan','JrekapController@exportlaporan');

			//route ijin siswa
			Route::group(['middleware' => ['role:admin,guru,pembina,kesehatan,satpam,siswa,walikelas,kurikulum']], function() {
				Route::get('/ijinsiswa','IjinsiswaController@index');
				Route::get('/ijinsiswa/{id}/verifikasi','IjinsiswaController@verifikasi');
				Route::get('/ijinsiswa/{id}/suratsalah','IjinsiswaController@suratsalah');
				Route::get('/ijinsiswa/{id}/cekout','IjinsiswaController@cekout');
				Route::post('/ijinsiswa/{id}/cekin','IjinsiswaController@cekin');
				Route::get('/ijinsiswa/{id}/delete','IjinsiswaController@delete');
				Route::get('/ijinsiswa/export','IjinsiswaController@export');
			});

			Route::group(['middleware' => ['role:admin,guru,siswa']], function() {
				Route::get('/tambahijinsiswa','IjinsiswaController@tambah');
				Route::POST('/tambahijinsiswa/create','IjinsiswaController@create');
				Route::post('/tambahijinsiswa/uploadulang/{id}','IjinsiswaController@uploadUlang');
			});
  
			Route::group(['middleware' => ['role:admin,keuangan']], function() {
				Route::post('tagihan/import', 'TagihanController@import')->name('tagihan.import');
				Route::post('tagihan/delete-all', 'TagihanController@deleteAll')->name('tagihan.deleteAll');
				Route::post('tagihan', 'TagihanController@store')->name('tagihan.store');
				Route::get('tagihan/create', 'TagihanController@create')->name('tagihan.create');
				Route::put('tagihan/{tagihan}', 'TagihanController@update')->name('tagihan.update');
				Route::get('tagihan/{tagihan}/edit', 'TagihanController@edit')->name('tagihan.edit');
				Route::delete('tagihan/{tagihan}', 'TagihanController@destroy')->name('tagihan.destroy');
				Route::get('tagihan/export', 'TagihanController@export')->name('tagihan.export');
				Route::get('tagihan/template', 'TagihanController@downloadTemplate')->name('tagihan.template');
			});
			Route::get('tagihan', 'TagihanController@index')->name('tagihan.index')->middleware('role:admin,siswa,keuangan');

			//route pelanggaran dan prestasi (PoinController deprecated, use PoinSiswaController via /poin-siswa)
			// Route::get('/poin','PoinController@index');
			// Route::post('/poin/pelanggaran','PoinController@pelanggaran');
			// Route::post('/poin/prestasi','PoinController@prestasi');
			
			//route pelanggaran (controller deprecated/missing)
			// Route::get('/pelanggaran','PelanggaranController@index');
			// Route::post('/pelanggaran/create','PelanggaranController@create');
			// Route::post('/pelanggaran/update','PelanggaranController@update');
			// Route::get('/pelanggaran/{id}/delete','PelanggaranController@delete');
			// Route::get('/pelanggaran/export','PelanggaranController@export');
			// Route::post('/pelanggaran/import','PelanggaranController@import');
			
			//route prestasi (controller deprecated/missing)
			// Route::get('/prestasi','PrestasiController@index');
			// Route::post('/prestasi/create','PrestasiController@create');
			// Route::post('/prestasi/update','PrestasiController@update');
			// Route::get('/prestasi/{id}/delete','PrestasiController@delete');
			// Route::get('/prestasi/export','PrestasiController@export');
			// Route::post('/prestasi/import','PrestasiController@import');
  
			Route::get('/jurnalh', 'JurnalhController@index');  // Untuk menampilkan jurnalh

			Route::post('jurnalh', 'JurnalhController@store');  // Untuk menyimpan jurnalh
			Route::put('jurnalh/{id}', 'JurnalhController@update');  // Untuk mengupdate jurnalh
			Route::get('/jurnalh/{id}/delete', 'JurnalhController@delete');

			// Menangani request untuk mengambil data absensi berdasarkan kelas dan tanggal
			Route::get('jurnalh/absensi/{kelas}/{tgl}', 'JurnalhController@getAbsensi')->name('jurnalh.absensi');
			Route::get('jurnalh/absensiguru/{tgl}', 'JurnalhController@getAbsensiguru')->name('jurnalh.absensiguru');

			Route::get('jurnalh/export-excel', 'JurnalhController@exportExcel')->name('jurnalh.exportExcel');
			Route::get('jurnalh/export-pdf', 'JurnalhController@exportPDF')->name('jurnalh.exportPDF');

			//Perangkat Pembelajaran
			Route::resource('perangkat', 'PerangkatController');

			// Routes yang sudah ada
			Route::get('garjas', 'GarjasController@index')->name('garjas.index');
			Route::post('garjas', 'GarjasController@store')->name('garjas.store');
			Route::put('garjas/{id}', 'GarjasController@update')->name('garjas.update');
			Route::delete('garjas/{id}', 'GarjasController@destroy')->name('garjas.destroy');
			
			// Route baru untuk inline editing
			Route::patch('garjas/{id}/field', 'GarjasController@updateField')->name('garjas.updateField');
			
			// Route untuk sinkronisasi (hanya pembina)
			Route::post('garjas/sync', 'GarjasController@syncSiswa')->name('garjas.sync');
			
			// Route untuk mendapatkan data siswa
			Route::get('garjas/student-data', 'GarjasController@getStudentData')->name('garjas.studentData');
			
			Route::prefix('garjas')->name('garjas.')->group(function () {
				// Export routes - hanya untuk pembina
				Route::get('/export/excel', 'GarjasController@exportExcel')->name('export.excel');
				Route::get('/export/pdf', 'GarjasController@exportPDF')->name('export.pdf');
			});

			// Route Tahun Ajaran & Semester (Admin only)
			Route::group(['middleware' => ['role:admin']], function() {
				Route::get('/tahun-ajaran', 'TahunAjaranController@index');
				Route::post('/tahun-ajaran/create', 'TahunAjaranController@create');
				Route::post('/tahun-ajaran/toggle-status/{id}', 'TahunAjaranController@toggleStatus');
				Route::get('/tahun-ajaran/{id}/delete', 'TahunAjaranController@delete');
			});

			// Route Presensi Guru & Shift
			Route::group(['middleware' => ['role:admin,guru,tendik,satpam,pembina,kepala,walikelas']], function() {
				Route::get('/presensi-guru', 'PresensiGuruController@index');
				Route::get('/presensi-guru/data-riwayat', 'PresensiGuruController@getRiwayatSayaAjax');
				Route::post('/presensi-guru/store', 'PresensiGuruController@store');
			});

			Route::group(['middleware' => ['role:admin,kurikulum']], function() {
				Route::get('/presensi-guru/rekap', 'PresensiGuruController@rekap');
				Route::get('/presensi-guru/rekap/data-log', 'PresensiGuruController@getRekapLogAjax');
				Route::get('/presensi-guru/setting', 'PresensiGuruController@setting');
				Route::post('/presensi-guru/setting', 'PresensiGuruController@updateSetting');
				Route::get('/presensi-guru/shifts', 'PresensiGuruController@shiftIndex');
				Route::post('/presensi-guru/shifts/store', 'PresensiGuruController@shiftStore');
				Route::post('/presensi-guru/shifts/update/{id}', 'PresensiGuruController@shiftUpdate');
				Route::get('/presensi-guru/shifts/delete/{id}', 'PresensiGuruController@shiftDelete');
				Route::post('/presensi-guru/user-shift/update', 'PresensiGuruController@updateUserDefaultShift');
				Route::post('/presensi-guru/roster/store', 'PresensiGuruController@storeRosterSchedule');
				Route::get('/presensi-guru/roster/delete/{id}', 'PresensiGuruController@deleteRosterSchedule');
				Route::get('/presensi-guru/export-excel', 'PresensiGuruController@exportExcel');
				Route::get('/presensi-guru/export-pdf', 'PresensiGuruController@exportPDF');
			});

			Route::group(['middleware' => ['role:admin']], function() {
				Route::post('/presensi-guru/update', 'PresensiGuruController@update');
				Route::get('/presensi-guru/{id}/delete', 'PresensiGuruController@delete');
			});
	
	});
});