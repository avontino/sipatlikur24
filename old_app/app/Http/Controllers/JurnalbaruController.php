<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Exports\SiswaExport;
// use App\Imports\SiswaImport;
// use Maatwebsite\Excel\Facades\Excel;
use App\Absen;
use App\Siswa;
use App\Jrekap;
use DB;
use DateTime;
use App\Jurnalh;


class JurnalbaruController extends Controller
{

    public function index(Request $request)
    {	
        
        $ab_sen=Absen::where('kelas',auth()->user()->name)
        ->whereDate('created_at',now())
        ->get();  

        $sis_wa=Siswa::where('kelas',auth()->user()->name)
        ->get(); 

        //mendapatkan nama hari sekarang
		$skr=(new DateTime())->format('l');

        // dd($skr);
    	$data_jadwal= \App\Jadwal::where('kelas',auth()->user()->name)
        ->where('hari','=',$skr)
        ->get();  

    	return view('jurnalbaru.index',['data_jadwal' => $data_jadwal],compact('ab_sen','sis_wa'));


    }

    public function update(Request $request)
    {
   // Menentukan isi jurnal berdasarkan penugasan
// PERBAIKAN UTAMA: Konsistensi pemisah dan logika update yang lebih akurat

if ($request->penugasan == 'Ada') {
    // PERBAIKAN: Gunakan <hr> konsisten, bukan <br>
    $jurnalValue = $request->guru . "<hr>" . $request->mapel . "<hr>" . $this->formatMateri($request->materi) . "<hr>" . "<span class='badge badge-danger'>KBM Tanpa Guru</span>";
} else {
    // Jika tidak ada penugasan
    $jurnalValue = $request->guru . "<hr>" . $request->mapel . "<hr>" . $this->formatMateri($request->materi);
}

// Debug untuk melihat nilai yang dibentuk
\Log::info('=== JURNAL VALUE FORMATION ===');
\Log::info('Penugasan: ' . $request->penugasan);
\Log::info('Guru: ' . $request->guru);
\Log::info('Mapel: ' . $request->mapel);
\Log::info('Materi: ' . $request->materi);
\Log::info('Formatted Jurnal Value: ' . $jurnalValue);

// Pisahkan rentang jamke (misalnya "1-3")
$jamkeParts = explode('-', $request->jamke);
$startJamke = (int)$jamkeParts[0]; // Jamke awal
$endJamke = (int)($jamkeParts[1] ?? $startJamke); // Jamke akhir (jika ada)

// Cek apakah jurnalh untuk kelas dan tanggal tertentu sudah ada
$existingJurnalh = Jurnalh::where('kelas', $request->kelas)
    ->whereDate('created_at', now()) // Periksa berdasarkan tanggal
    ->first();

// Jika jurnalh untuk kelas dan tanggal hari ini tidak ada
if ($existingJurnalh == null) {
    // Menampilkan notifikasi pop-up jika data tidak ditemukan
    return redirect('/jurnalbaru')->with('gagal', 'Update Jurnal GAGAL, Jadwal hari ini belum disinkron!');
}

// FUNCTION YANG DIPERBAIKI untuk mengganti atau menambah data di kolom jurnal
function updateJurnalColumn($existingValue, $newValue, $targetGuru, $targetMapel) {
    \Log::info('=== UPDATE JURNAL COLUMN START ===');
    \Log::info('Existing Value: ' . ($existingValue ?? 'NULL'));
    \Log::info('New Value: ' . $newValue);
    \Log::info('Target Guru: "' . $targetGuru . '"');
    \Log::info('Target Mapel: "' . $targetMapel . '"');
    
    if (empty($existingValue)) {
        // Jika kolom kosong, langsung isi dengan data baru
        \Log::info('Column is empty, using new value directly');
        return $newValue;
    }
    
    // Bersihkan multiple <hr> yang berturut-turut
    $cleanedValue = preg_replace('/<hr>+/', '<hr>', $existingValue);
    $cleanedValue = trim($cleanedValue, '<hr>'); // Hapus <hr> di awal dan akhir
    
    // Split berdasarkan <hr> untuk mendapatkan segmen-segmen jurnal
    $segments = explode('<hr>', $cleanedValue);
    $updatedSegments = [];
    $found = false;
    
    \Log::info('Cleaned Value: ' . $cleanedValue);
    \Log::info('Total segments: ' . count($segments));
    
    // Parse new value segments untuk referensi
    $newSegments = explode('<hr>', $newValue);
    $newGuruSegment = $newSegments[0] ?? '';
    $newMapelSegment = $newSegments[1] ?? '';
    $newMateriSegment = $newSegments[2] ?? '';
    $newBadgeSegment = $newSegments[3] ?? '';
    
    \Log::info('New segments - Guru: "' . $newGuruSegment . '", Mapel: "' . $newMapelSegment . '"');
    
    // Process dalam grup 3 atau 4 segmen (guru, mapel, materi, [badge])
    for ($i = 0; $i < count($segments);) {
        if (isset($segments[$i], $segments[$i + 1], $segments[$i + 2])) {
            $guruSegment = trim($segments[$i]);
            $mapelSegment = trim($segments[$i + 1]);
            $materiSegment = trim($segments[$i + 2]);
            
            // Cek apakah ada badge segment
            $badgeSegment = '';
            $currentSegmentSize = 3;
            
            if (isset($segments[$i + 3]) && (strpos($segments[$i + 3], 'badge') !== false || strpos($segments[$i + 3], 'KBM') !== false)) {
                $badgeSegment = trim($segments[$i + 3]);
                $currentSegmentSize = 4;
            }
            
            // Extract clean guru dan mapel untuk perbandingan
            $cleanGuru = trim(strip_tags($guruSegment));
            $cleanMapel = trim(strip_tags($mapelSegment));
            
            // Normalize untuk perbandingan yang akurat
            $normalizedCleanGuru = preg_replace('/\s+/', ' ', strtoupper($cleanGuru));
            $normalizedTargetGuru = preg_replace('/\s+/', ' ', strtoupper(trim($targetGuru)));
            $normalizedCleanMapel = preg_replace('/\s+/', ' ', strtoupper($cleanMapel));
            $normalizedTargetMapel = preg_replace('/\s+/', ' ', strtoupper(trim($targetMapel)));
            
            // Debug log
            \Log::info('Processing Entry ' . (floor($i/3) + 1) . ':');
            \Log::info('  Guru existing: "' . $cleanGuru . '" normalized: "' . $normalizedCleanGuru . '"');
            \Log::info('  Guru target: "' . $targetGuru . '" normalized: "' . $normalizedTargetGuru . '"');
            \Log::info('  Mapel existing: "' . $cleanMapel . '" normalized: "' . $normalizedCleanMapel . '"');
            \Log::info('  Mapel target: "' . $targetMapel . '" normalized: "' . $normalizedTargetMapel . '"');
            \Log::info('  Current segment size: ' . $currentSegmentSize);
            \Log::info('  Has badge: ' . (!empty($badgeSegment) ? 'YES ("' . $badgeSegment . '")' : 'NO'));
            
            // PERBAIKAN UTAMA: Pencocokan berdasarkan guru dan mapel
            if ($normalizedCleanGuru === $normalizedTargetGuru && $normalizedCleanMapel === $normalizedTargetMapel) {
                \Log::info('  *** MATCH FOUND! Updating existing entry ***');
                
                // Update dengan data baru (materi dan badge bisa berubah)
                $updatedSegments[] = $newGuruSegment;
                $updatedSegments[] = $newMapelSegment;
                $updatedSegments[] = $newMateriSegment;
                
                // Tambahkan badge jika ada
                if (!empty($newBadgeSegment)) {
                    $updatedSegments[] = $newBadgeSegment;
                }
                
                $found = true;
                \Log::info('  Entry updated with new materi and badge status');
            } else {
                \Log::info('  No match, preserving existing entry');
                // Pertahankan data yang sudah ada
                $updatedSegments[] = $guruSegment;
                $updatedSegments[] = $mapelSegment;
                $updatedSegments[] = $materiSegment;
                
                // Pertahankan badge jika ada
                if (!empty($badgeSegment)) {
                    $updatedSegments[] = $badgeSegment;
                }
            }
            
            // Move to next group
            $i += $currentSegmentSize;
        } else {
            // Handle sisa segment yang tidak lengkap
            if (isset($segments[$i])) {
                $updatedSegments[] = $segments[$i];
            }
            $i++;
        }
    }
    
    // Jika tidak ditemukan guru dan mapel yang cocok, tambahkan sebagai entry baru
    if (!$found) {
        \Log::info('No matching entry found, adding as new entry');
        $updatedSegments[] = $newGuruSegment;
        $updatedSegments[] = $newMapelSegment;
        $updatedSegments[] = $newMateriSegment;
        
        if (!empty($newBadgeSegment)) {
            $updatedSegments[] = $newBadgeSegment;
        }
    }
    
    // Bersihkan segment kosong
    $updatedSegments = array_filter($updatedSegments, function($segment) {
        return !empty(trim($segment));
    });
    
    $result = implode('<hr>', $updatedSegments);
    \Log::info('Final result: ' . $result);
    \Log::info('=== UPDATE JURNAL COLUMN END ===');
    
    return $result;
}

// Update kolom berdasarkan rentang jamke
for ($i = $startJamke; $i <= $endJamke; $i++) {
    if ($i >= 1 && $i <= 11) {
        $col = 'j' . $i;
        $existingValue = $existingJurnalh->{$col};
        
        \Log::info('=== UPDATING COLUMN ' . $col . ' ===');
        \Log::info('Before update: ' . ($existingValue ?? 'NULL'));
        
        // Update kolom dengan preserving data guru/mapel lain tapi update yang sama
        $updatedValue = updateJurnalColumn(
            $existingValue, 
            $jurnalValue, 
            $request->guru, 
            $request->mapel
        );
        
        $existingJurnalh->{$col} = $updatedValue;
        
        \Log::info('After update: ' . $updatedValue);
        \Log::info('=== END UPDATING COLUMN ' . $col . ' ===');
    }
}

// Update timestamp
$existingJurnalh->updated_at = now();

// Simpan perubahan
$existingJurnalh->save();

\Log::info('=== JURNAL UPDATE COMPLETED ===');


      
      

        $guru = DB::table('jurnal')->select('guru')

        ->where('guru','LIKE','%'.$request->guru.'%')
        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',now())
        ->first();

        $time = DB::table('jurnal')->select('created_at')

        ->where('guru','LIKE','%'.$request->guru.'%')
        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',$request)
        ->first();

        $rekap = DB::table('jrekap')->select('kelas')

        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',now())
        ->first();
        
        $tm = DB::table('ijin')->select('guru')

        ->where('guru','LIKE','%'.$request->guru.'%')
        ->whereDate('created_at',now())
        ->first();

        if (is_null($guru) AND is_null($time) AND is_null($rekap)) {

              $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();
              //tambah ke tabel jurnal
             DB::table('jurnal')->insert(['kelas'=>$request->kelas,'ket_guru_mapel'=>$request->ket_guru_mapel,'penugasan'=>$request->penugasan,'jamke'=>$request->jamke,'jumlahjam'=>$request->jumlahjam,'mapel'=>$request->mapel,'guru'=>$request->guru,'materi'=>$request->materi,'catatan'=>$request->catatan,'guru_id'=>$user->id,'created_at'=>now(),'updated_at'=>now()]);
             //tambah ke tabel jrekap
            DB::table('jrekap')->insert(['kelas'=>$request->kelas,'j1'=>$request->j1,'j2'=>$request->j2,'j3'=>$request->j3,'j4'=>$request->j4,'j5'=>$request->j5,'j5'=>$request->j5,'j6'=>$request->j6,'j7'=>$request->j7,'j8'=>$request->j8,'j9'=>$request->j9,'j10'=>$request->j10,'j11'=>$request->j11,'created_at'=>now(),'updated_at'=>now()]);

        //tambah ijin guru jika guru tidak masuk
            if($request->ket_guru_mapel=='Tidak Masuk' AND is_null($tm)){
            
            DB::table('ijin')->insert(['tglmasuk'=>now(),'mapel'=>$request->mapel,'guru'=>$request->guru,'sia'=>'Ijin','jumlah'=>1,'ket'=>'-','created_at'=>now(),'updated_at'=>now()]);
            return redirect('/jurnalbaru')->with('sukses','Jurnal Berhasil Ditambahkan');

            }

            else {
            return redirect('/jurnalbaru')->with('sukses','Jurnal Berhasil Ditambahkan');
            }
    }
            elseif(is_null($guru) AND is_null($time) AND isset($rekap)){

            $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();
            //tambah ke tabel jurnal
             DB::table('jurnal')->insert(['kelas'=>$request->kelas,'ket_guru_mapel'=>$request->ket_guru_mapel,'penugasan'=>$request->penugasan,'jamke'=>$request->jamke,'jumlahjam'=>$request->jumlahjam,'mapel'=>$request->mapel,'guru'=>$request->guru,'materi'=>$request->materi,'catatan'=>$request->catatan,'guru_id'=>$user->id,'created_at'=>now(),'updated_at'=>now()]);
             //update tabel jrekap yang sudah ada
        $row = DB::table('jrekap')
            ->where('kelas','LIKE','%'.$request->kelas.'%')
            ->whereDate('created_at',now())
            ->first();

        $jam=collect([null,$row->j1,$row->j2,$row->j3,$row->j4,$row->j5,$row->j6,$row->j7,$row->j8,$row->j9,$row->j10,$row->j11]);

        $requestget=collect([null,$request->j1,$request->j2,$request->j3,$request->j4,$request->j5,$request->j6,$request->j7,$request->j8,$request->j9,$request->j10,$request->j11]);
        
        $kolom=collect([null,'j1','j2','j3','j4','j5','j6','j7','j8','j9','j10','j11']);
        
        for ($i=1; $i<=11; $i++){
            
        if($jam[$i] != 'ok') {            
            DB::table('jrekap')->where('kelas','LIKE','%'.$request->kelas.'%')
                ->whereDate('created_at',now())
            ->update([$kolom[$i]=>$requestget[$i]]);    
        }
        }
      
            //tambah ijin guru jika guru tidak masuk
           if($request->ket_guru_mapel=='Tidak Masuk' AND is_null($tm)){
            
            DB::table('ijin')->insert(['tglmasuk'=>now(),'mapel'=>$request->mapel,'guru'=>$request->guru,'sia'=>'Ijin','jumlah'=>1,'ket'=>'-','created_at'=>now(),'updated_at'=>now()]);
            return redirect('/jurnalbaru')->with('sukses','Jurnal Berhasil Ditambahkan');

            }

            else {
            return redirect('/jurnalbaru')->with('sukses','Jurnal Berhasil Ditambahkan');
            }
            }

            else {
              
              $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();
                DB::table('jurnal')->where('guru','LIKE','%'.$request->guru.'%')
                ->where('jamke','LIKE','%'.$request->jamke.'%')
                ->whereDate('created_at',now())
                // ->first();
                // dd($datajurnal);

                ->update(['ket_guru_mapel'=>$request->ket_guru_mapel,'penugasan'=>$request->penugasan,'jamke'=>$request->jamke,'jumlahjam'=>$request->jumlahjam,'mapel'=>$request->mapel,'guru'=>$request->guru,'materi'=>$request->materi,'catatan'=>$request->catatan,'guru_id'=>$user->id,'updated_at'=>now()]);
        
        // $jurnal=\App\Jurnal::find($id);
        // $jurnal->update($request->all());

           return redirect('/jurnalbaru')->with('sukses','Jurnal Berhasil Diupdate !!!');

        }
    }

    public function tambahabsen(Request $request)
    {   
        if ($request->ket == 'Sakit') {

    // Cek apakah sudah ada absensi untuk siswa dengan tanggal yang sama
    $existingAbsence = Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                ->whereDate('created_at', now()) // Cek berdasarkan tanggal
                                ->first();

    if ($existingAbsence) {
        // Jika absensi sudah ada, tampilkan error
        return redirect('/absen')->with('gagal', 'Absensi Sudah Ada, Silahkan Cek Kembali');
    }

    // Ambil data siswa
    $jumlahawal = DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->first();

    // Update jumlah sakit siswa
    DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->update(['sakit' => $jumlahawal->sakit + 1]);

    // Tambahkan data absensi
    Absen::create(['nama' => $request->nama, 'kelas' => $request->kelas, 'ket' => $request->ket, 'created_at' => $request->tgl]);
    return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');

} elseif ($request->ket == 'Ijin') {

    // Cek apakah sudah ada absensi untuk siswa dengan tanggal yang sama
    $existingAbsence = Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                ->whereDate('created_at', now()) // Cek berdasarkan tanggal
                                ->first();

    if ($existingAbsence) {
        // Jika absensi sudah ada, tampilkan error
        return redirect('/absen')->with('gagal', 'Absensi Sudah Ada, Silahkan Cek Kembali');
    }

    // Ambil data siswa
    $jumlahawal = DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->first();

    // Update jumlah ijin siswa
    DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->update(['ijin' => $jumlahawal->ijin + 1]);

    // Tambahkan data absensi
    Absen::create(['nama' => $request->nama, 'kelas' => $request->kelas, 'ket' => $request->ket, 'created_at' => $request->tgl]);
    return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');

} elseif ($request->ket == 'Alpha') {

    // Cek apakah sudah ada absensi untuk siswa dengan tanggal yang sama
    $existingAbsence = Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                ->whereDate('created_at', now()) // Cek berdasarkan tanggal
                                ->first();

    if ($existingAbsence) {
        // Jika absensi sudah ada, tampilkan error
        return redirect('/absen')->with('gagal', 'Absensi Sudah Ada, Silahkan Cek Kembali');
    }

    // Ambil data siswa
    $jumlahawal = DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->first();

    // Update jumlah alpha siswa
    DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->update(['alpha' => $jumlahawal->alpha + 1]);

    // Tambahkan data absensi
   \Absen::create(['nama' => $request->nama, 'kelas' => $request->kelas, 'ket' => $request->ket, 'created_at' => $request->tgl]);
    return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');

} elseif ($request->ket == 'Dispen') {

    // Cek apakah sudah ada absensi untuk siswa dengan tanggal yang sama
    $existingAbsence = Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                ->whereDate('created_at', now()) // Cek berdasarkan tanggal
                                ->first();

    if ($existingAbsence) {
        // Jika absensi sudah ada, tampilkan error
        return redirect('/absen')->with('gagal', 'Absensi Sudah Ada, Silahkan Cek Kembali');
    }

    // Ambil data siswa
    $jumlahawal = DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->first();

    // Update jumlah dispen siswa
    DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->update(['dispen' => $jumlahawal->dispen + 1]);

    // Tambahkan data absensi
    Absen::create(['nama' => $request->nama, 'kelas' => $request->kelas, 'ket' => $request->ket, 'created_at' => $request->tgl]);
    return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');

} else {

    // Cek apakah sudah ada absensi untuk siswa dengan tanggal yang sama
    $existingAbsence = Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                ->whereDate('created_at', now()) // Cek berdasarkan tanggal
                                ->first();

    if ($existingAbsence) {
        // Jika absensi sudah ada, tampilkan error
        return redirect('/absen')->with('error', 'Absensi Sudah Ada, Silahkan Cek Kembali');
    }

    // Tambahkan data absensi
    Absen::create(['nama' => $request->nama, 'kelas' => $request->kelas, 'ket' => $request->ket, 'created_at' => $request->tgl]);
    return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');
}
       
        }

    public function delete($id)
    {
        $absen=\App\Absen::find($id);
        $keterangan = DB::table('absen')->where('id','LIKE','%'.$absen->id.'%')->first();

         if ($keterangan->ket=='Sakit') {
             $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->first();
             DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->update(['sakit'=>$jumlahawal->sakit - 1]);

             $absen->delete();
             return redirect('/jurnalbaru')->with('gagal','Absensi Berhasil Dihapus');
         }

         elseif ($keterangan->ket=='Ijin') {
             $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->first();
             DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->update(['ijin'=>$jumlahawal->ijin - 1]);

             $absen->delete();
             return redirect('/jurnalbaru')->with('gagal','Absensi Berhasil Dihapus');
         }

         elseif ($keterangan->ket=='Alpha') {
             $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->first();
             DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->update(['alpha'=>$jumlahawal->alpha - 1]);

             $absen->delete();
             return redirect('/jurnalbaru')->with('gagal','Absensi Berhasil Dihapus');
         }

         elseif ($keterangan->ket=='Dispen') {
             $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->first();
             DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->update(['dispen'=>$jumlahawal->dispen - 1]);

             $absen->delete();
             return redirect('/jurnalbaru')->with('gagal','Absensi Berhasil Dihapus');
         }

         else   
       
        $absen->delete();
             return redirect('/jurnalbaru')->with('gagal','Absensi Berhasil Dihapus');

                  
    }

    public function susulan(Request $request) {

        $kosong = DB::table('jrekap')->where('kelas',auth()->user()->name)
    ->selectRaw("count(case when j1 = '0' then 1 end) as n1")
    ->selectRaw("count(case when j2 = '0' then 1 end) as n2")
    ->selectRaw("count(case when j3 = '0' then 1 end) as n3")
    ->selectRaw("count(case when j4 = '0' then 1 end) as n4")
    ->selectRaw("count(case when j5 = '0' then 1 end) as n5")
    ->selectRaw("count(case when j6 = '0' then 1 end) as n6")
    ->selectRaw("count(case when j7 = '0' then 1 end) as n7")
    ->selectRaw("count(case when j8 = '0' then 1 end) as n8")
    ->selectRaw("count(case when j9 = '0' then 1 end) as n9")
    ->selectRaw("count(case when j10 = '0' then 1 end) as n10")
    ->selectRaw("count(case when j11 = '0' then 1 end) as n11")
    ->first();
    $totalkosong=$kosong->n1+$kosong->n2+$kosong->n3+$kosong->n4+$kosong->n5+$kosong->n6+$kosong->n7+$kosong->n8+$kosong->n9+$kosong->n10+$kosong->n11;


    $ok = DB::table('jrekap')->where('kelas',auth()->user()->name)
    ->selectRaw("count(case when j1 = 'ok' then 1 end) as n1")
    ->selectRaw("count(case when j2 = 'ok' then 1 end) as n2")
    ->selectRaw("count(case when j3 = 'ok' then 1 end) as n3")
    ->selectRaw("count(case when j4 = 'ok' then 1 end) as n4")
    ->selectRaw("count(case when j5 = 'ok' then 1 end) as n5")
    ->selectRaw("count(case when j6 = 'ok' then 1 end) as n6")
    ->selectRaw("count(case when j7 = 'ok' then 1 end) as n7")
    ->selectRaw("count(case when j8 = 'ok' then 1 end) as n8")
    ->selectRaw("count(case when j9 = 'ok' then 1 end) as n9")
    ->selectRaw("count(case when j10 = 'ok' then 1 end) as n10")
    ->selectRaw("count(case when j11 = 'ok' then 1 end) as n11")
    ->first();
    $totalok=$ok->n1+$ok->n2+$ok->n3+$ok->n4+$ok->n5+$ok->n6+$ok->n7+$ok->n8+$ok->n9+$ok->n10+$ok->n11;

        $ab_sen=Absen::where('kelas',auth()->user()->name)
        ->whereDate('created_at',now())
        ->get();  

        $sis_wa=Siswa::where('kelas',auth()->user()->name)
        ->get(); 

        $data_jadwal= \App\Jadwal::where('kelas',auth()->user()->name)
        ->get();



        return view('jurnalbaru.susulan',['data_jadwal' => $data_jadwal],compact('ab_sen','sis_wa'))
        ->with('totalkosong', $totalkosong)->with('totalok', $totalok);        
    }

public function updatesusulan(Request $request)
    {             

        $guru = DB::table('jurnal')->select('guru')

        ->where('guru','LIKE','%'.$request->guru.'%')
        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',$request->tgl)
        ->first();

        $time = DB::table('jurnal')->select('created_at')

        ->where('guru','LIKE','%'.$request->guru.'%')
        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',$request->tgl)
        ->first();

        $rekap = DB::table('jrekap')->select('kelas')

        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',$request->tgl)
        ->first();

        if (is_null($guru) AND is_null($time) AND is_null($rekap)) {

              $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();
              //tambah ke tabel jurnal
             DB::table('jurnal')->insert(['kelas'=>$request->kelas,'ket_guru_mapel'=>$request->ket_guru_mapel,'penugasan'=>$request->penugasan,'jamke'=>$request->jamke,'jumlahjam'=>$request->jumlahjam,'mapel'=>$request->mapel,'guru'=>$request->guru,'materi'=>$request->materi,'catatan'=>$request->catatan,'guru_id'=>$user->id,'created_at'=>$request->tgl,'updated_at'=>$request->tgl]);
             //tambah ke tabel jrekap
            DB::table('jrekap')->insert(['kelas'=>$request->kelas,'j1'=>$request->j1,'j2'=>$request->j2,'j3'=>$request->j3,'j4'=>$request->j4,'j5'=>$request->j5,'j5'=>$request->j5,'j6'=>$request->j6,'j7'=>$request->j7,'j8'=>$request->j8,'j9'=>$request->j9,'j10'=>$request->j10,'j11'=>$request->j11,'created_at'=>$request->tgl,'updated_at'=>$request->tgl]);

        
        return redirect('/susulan')->with('sukses','Jurnal Berhasil Ditambahkan');
    }
            elseif(is_null($guru) AND is_null($time) AND isset($rekap)){

            $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();
            //tambah ke tabel jurnal
             DB::table('jurnal')->insert(['kelas'=>$request->kelas,'ket_guru_mapel'=>$request->ket_guru_mapel,'penugasan'=>$request->penugasan,'jamke'=>$request->jamke,'jumlahjam'=>$request->jumlahjam,'mapel'=>$request->mapel,'guru'=>$request->guru,'materi'=>$request->materi,'catatan'=>$request->catatan,'guru_id'=>$user->id,'created_at'=>$request->tgl,'updated_at'=>$request->tgl]);
             //update tabel jrekap yang sudah ada
        $row = DB::table('jrekap')
            ->where('kelas','LIKE','%'.$request->kelas.'%')
            ->whereDate('created_at',$request->tgl)
            ->first();

        $jam=collect([null,$row->j1,$row->j2,$row->j3,$row->j4,$row->j5,$row->j6,$row->j7,$row->j8,$row->j9,$row->j10,$row->j11]);

        $requestget=collect([null,$request->j1,$request->j2,$request->j3,$request->j4,$request->j5,$request->j6,$request->j7,$request->j8,$request->j9,$request->j10,$request->j11]);
        
        $kolom=collect([null,'j1','j2','j3','j4','j5','j6','j7','j8','j9','j10','j11']);
        
        for ($i=1; $i<=11; $i++){
            
        if($jam[$i] != 'ok') {            
            DB::table('jrekap')->where('kelas','LIKE','%'.$request->kelas.'%')
                ->whereDate('created_at',$request->tgl)
            ->update([$kolom[$i]=>$requestget[$i]]);    
        }
        }
      
            return redirect('/susulan')->with('sukses','Jurnal Berhasil Ditambahkan');
            }

            else {
           return redirect('/susulan')->with('gagal','Jurnal Sudah Ada, Tidak Bisa Ditambahkan Lagi !!!');

        }
    }

    public function absensusulan(Request $request)
    {   
        if ($request->ket=='Sakit') {
            
         $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$request->nama.'%')->first();

         DB::table('siswa')->where('nama','LIKE','%'.$request->nama.'%')->update(['sakit'=>$jumlahawal->sakit + 1]);

        // \App\Absen::create($request->all());
         DB::table('absen')->insert(['nama'=>$request->nama,'kelas'=>$request->kelas,'ket'=>$request->ket,'created_at'=>$request->tgl,'updated_at'=>now()]);

        return redirect('/susulan')->with('sukses','Absen Siswa Berhasil Ditambahkan');

        } elseif ($request->ket=='Ijin') {
            $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$request->nama.'%')->first();

         DB::table('siswa')->where('nama','LIKE','%'.$request->nama.'%')->update(['ijin'=>$jumlahawal->ijin + 1]);

         DB::table('absen')->insert(['nama'=>$request->nama,'kelas'=>$request->kelas,'ket'=>$request->ket,'created_at'=>$request->tgl,'updated_at'=>now()]);
        return redirect('/susulan')->with('sukses','Absen Siswa Berhasil Ditambahkan');

        } elseif ($request->ket=='Alpha') {
            $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$request->nama.'%')->first();

         DB::table('siswa')->where('nama','LIKE','%'.$request->nama.'%')->update(['alpha'=>$jumlahawal->alpha + 1]);

         DB::table('absen')->insert(['nama'=>$request->nama,'kelas'=>$request->kelas,'ket'=>$request->ket,'created_at'=>$request->tgl,'updated_at'=>now()]);
        return redirect('/susulan')->with('sukses','Absen Siswa Berhasil Ditambahkan');

        } elseif ($request->ket=='Dispen') {
            $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$request->nama.'%')->first();

         DB::table('siswa')->where('nama','LIKE','%'.$request->nama.'%')->update(['dispen'=>$jumlahawal->dispen + 1]);

         DB::table('absen')->insert(['nama'=>$request->nama,'kelas'=>$request->kelas,'ket'=>$request->ket,'created_at'=>$request->tgl,'updated_at'=>now()]);
        return redirect('/susulan')->with('sukses','Absen Siswa Berhasil Ditambahkan');
        } else

         DB::table('absen')->insert(['nama'=>$request->nama,'kelas'=>$request->kelas,'ket'=>$request->ket,'created_at'=>$request->tgl,'updated_at'=>now()]);
        return redirect('/susulan')->with('sukses','Absen Siswa Berhasil Ditambahkan');
       
        } 
  
  // Fungsi untuk memformat materi, jika ada link maka tampilkan dengan badge hijau
        private function formatMateri($materi)
        {
            // Pola untuk mendeteksi URL (https:// atau http://)
            $pattern = '/(https?:\/\/[a-zA-Z0-9\/?=&%#\.\-]+)/';
        
            // Cek apakah materi mengandung URL
            if (preg_match($pattern, $materi, $matches)) {
                // Ambil bagian sebelum URL (misalnya: "Percabangan Matematika")
                $textBeforeLink = trim(substr($materi, 0, strpos($materi, $matches[0])));
        
                // Format untuk menampilkan teks dan menyembunyikan URL, dengan link yang dapat diklik
                return '<span class="badge badge-success"><a href="' . $matches[0] . '" target="_blank" style="text-decoration: none; color: inherit;">' . $textBeforeLink . '</a></span>';
            }
        
            // Jika tidak ada URL, kembalikan materi seperti semula
            return $materi;
        }
        

}
