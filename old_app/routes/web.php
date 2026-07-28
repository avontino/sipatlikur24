<?php

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
    return view('home');
})->middleware('auth');

Route::get('/login','AuthController@login')->name('login');
Route::post('/postlogin','AuthController@postlogin');
Route::get('/logout','AuthController@logout');


Route::group(['middleware'=>'auth'],function(){
			Route::get('/dashboard','DashboardController@index');
			//route tambah jurnal dengan jadwal
			Route::get('/jurnalbaru','JurnalbaruController@index');
			Route::post('/jurnalbaru/update','JurnalbaruController@update');
			Route::post('/jurnalbaru/tambahabsen','JurnalbaruController@tambahabsen');
			Route::get('/jurnalbaru/{id}/delete','JurnalbaruController@delete');
			
			Route::get('/susulan','JurnalbaruController@susulan');
			Route::post('/susulan/updatesusulan','JurnalbaruController@updatesusulan');
			Route::post('/susulan/absensusulan','JurnalbaruController@absensusulan');

			//route jadwal
			Route::get('/jadwal','JadwalController@index');
			Route::post('/jadwal/create','JadwalController@create');
			Route::post('/jadwal/update','JadwalController@update');
			Route::get('/jadwal/{id}/delete','JadwalController@delete');
			Route::get('/jadwal/export','JadwalController@export');
			Route::post('/jadwal/import','JadwalController@import');

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
Route::get('/jurnal/export-excel', 'JurnalController@exportExcel');
Route::get('/jurnal/export-pdf', 'JurnalController@exportPDF');

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
			Route::get('/lihatkasus','KasusController@lihatk');

			Route::get('/kasus','KasusController@index');
			Route::post('/kasus/create','KasusController@create');
			Route::get('/kasus/export','KasusController@export');
			// Route::get('/kasus/{id}/edit','KasusController@edit');
			Route::post('/kasus/update','KasusController@update');
			Route::get('/kasus/{id}/delete','KasusController@delete');

			//route tambah operator
			Route::get('/operator','OperatorController@operator');
			Route::post('/operator/create','OperatorController@create');
			Route::get('/operator/export','OperatorController@export');
			Route::post('/operator/update','OperatorController@update');
			Route::get('/operator/{id}/delete','OperatorController@delete');
			Route::post('/operator/import','OperatorController@import');

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

						//route siswa
			Route::get('/siswa','SiswaController@index');
			Route::post('/siswa/create','SiswaController@create');
			Route::get('/siswa/export','SiswaController@export');
			Route::post('/siswa/import','SiswaController@import');
			Route::post('/siswa/update','SiswaController@update');
			Route::get('/siswa/{id}/delete','SiswaController@delete');
			// Route untuk update seluruh data siswa
			Route::post('/siswa/update-ijin', 'SiswaController@updateIjin');

			
									//route jurnal rekap jrekap
			Route::get('/jrekap','JrekapController@index');
			Route::post('/jrekap/create','JrekapController@create');
			Route::post('/jrekap/update','JrekapController@update');
			Route::get('/jrekap/{id}/delete','JrekapController@delete');
			Route::get('/jrekap/export','JrekapController@export');
			Route::get('/jrekap/exportkelas','JrekapController@exportkelas');
			Route::get('/jrekap/exportlaporan','JrekapController@exportlaporan');

							//route ijin siswa
							Route::get('/ijinsiswa','IjinsiswaController@index');
							Route::get('/ijinsiswa/{id}/verifikasi','IjinsiswaController@verifikasi');
							Route::get('/ijinsiswa/{id}/suratsalah','IjinsiswaController@suratsalah');
							Route::get('/tambahijinsiswa','IjinsiswaController@tambah');
							Route::POST('/tambahijinsiswa/create','IjinsiswaController@create');
  							Route::post('/tambahijinsiswa/uploadulang/{id}','IjinsiswaController@uploadUlang');

							Route::get('/ijinsiswa/{id}/cekout','IjinsiswaController@cekout');
							Route::post('/ijinsiswa/{id}/cekin','IjinsiswaController@cekin');
  							Route::get('/ijinsiswa/{id}/delete','IjinsiswaController@delete');

							// routes/web.php
  
  Route::get('/ijinsiswa/export','IjinsiswaController@export');
  
  Route::resource('tagihan', 'TagihanController')->except(['show']);
Route::get('tagihan/export', 'TagihanController@export')->name('tagihan.export');
Route::post('tagihan/import', 'TagihanController@import')->name('tagihan.import');
  Route::post('tagihan/delete-all', 'TagihanController@deleteAll')->name('tagihan.deleteAll');

  
  			//route pelanggaran dan prestasi
			Route::get('/poin','PoinController@index');
			Route::post('/poin/pelanggaran','PoinController@pelanggaran');
			Route::post('/poin/prestasi','PoinController@prestasi');
			
			//route pelanggaran
			Route::get('/pelanggaran','PelanggaranController@index');
			Route::post('/pelanggaran/create','PelanggaranController@create');
			Route::post('/pelanggaran/update','PelanggaranController@update');
			Route::get('/pelanggaran/{id}/delete','PelanggaranController@delete');
			Route::get('/pelanggaran/export','PelanggaranController@export');
			Route::post('/pelanggaran/import','PelanggaranController@import');
			
			//route prestasi
			Route::get('/prestasi','PrestasiController@index');
			Route::post('/prestasi/create','PrestasiController@create');
			Route::post('/prestasi/update','PrestasiController@update');
			Route::get('/prestasi/{id}/delete','PrestasiController@delete');
			Route::get('/prestasi/export','PrestasiController@export');
			Route::post('/prestasi/import','PrestasiController@import');
  
  Route::get('/jurnalh', 'JurnalhController@index');  // Untuk menampilkan jurnalh

Route::post('jurnalh', 'JurnalhController@store');  // Untuk menyimpan jurnalh
Route::put('jurnalh/{id}', 'JurnalhController@update');  // Untuk mengupdate jurnalh
Route::delete('jurnalh/{id}', 'JurnalhController@destroy');  // Untuk menghapus jurnalh



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
        // ... route existing lainnya ...
        
        // Export routes - hanya untuk pembina
        Route::get('/export/excel', 'GarjasController@exportExcel')->name('export.excel');
        Route::get('/export/pdf', 'GarjasController@exportPDF')->name('export.pdf');
    });




							




			
});