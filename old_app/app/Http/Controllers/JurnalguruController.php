<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\JurnalguruExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Mapel;
use App\Guru;
use App\Kelas;
use App\Jurnalh;
use App\Jurnal;


class JurnalguruController extends Controller
{
    public function index(Request $request)
    {   
        $ma_pel=Mapel::all();
        $gu_ru=Guru::all();
        $ke_las=Kelas::all();

        // dd($gu_ru);

    	// if ($request->filled('filter')) {
    	// 	$data_jurnal= \App\Jurnal::whereDate('created_at','LIKE','%'.$request->filter.'%')
     //        ->where('guru',auth()->user()->name)
     //        ->orderBy('waktu','desc')
     //        ->paginate();

     //    }  elseif($request->filled('cari')){ 

     //         $data_jurnal= \App\Jurnal::
     //         where('kelas','LIKE','%'.$request->cari.'%')->where('guru',auth()->user()->name)->orderBy('created_at','desc')
     //         ->orwhere('ket_guru_mapel','LIKE','%'.$request->cari.'%')->where('guru',auth()->user()->name)->orderBy('created_at','desc')
     //         ->orwhere('absen','LIKE','%'.$request->cari.'%')->where('guru',auth()->user()->name)->orderBy('created_at','desc')
     //         ->orwhere('materi','LIKE','%'.$request->cari.'%')->where('guru',auth()->user()->name)->orderBy('created_at','desc')
     //         ->orwhere('catatan','LIKE','%'.$request->cari.'%')->where('guru',auth()->user()->name)->orderBy('created_at','desc')

     //         ->paginate()
     //         ;
     //    }





     //    else{
    	// 	$data_jurnal= \App\Jurnal::where('guru',auth()->user()->name)->orderBy('waktu','desc')->get()->paginate(10);
   
    	// }


        $data_jurnal= \App\Jurnal::where('guru_id',auth()->user()->id)->orderBy('created_at','desc')->get();
    	return view('jurnalguru.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));
    }


    public function export() 
    {
        return Excel::download(new JurnalguruExport, 'Jurnalguru.xlsx');
    }

    // public function edit($id)
    // {   
    //     $ma_pel=Mapel::all();
    //     $gu_ru=Guru::all();
    //     $ke_las=Kelas::all();
    //     $jurnal=\App\Jurnal::find($id);
    //     return view('jurnalguru/edit',['jurnal'=>$jurnal],compact('ma_pel','gu_ru','ke_las'));
    // }

public function update(Request $request)
    {
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

        
        //mengambil id jurnal untuk menyamakan tanggal
        $jurnal=Jurnal::findorFail($request->jurnalid);

        
// Cek apakah jurnalh untuk kelas dan tanggal tertentu sudah ada
$existingJurnalh = Jurnalh::where('kelas', $request->kelas)
    ->whereDate('created_at', $jurnal->created_at->toDateString()) // Periksa berdasarkan tanggal
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

$jurnal=\App\Jurnal::findorFail($request->jurnalid);
        $jurnal->update($request->all());
        return redirect('/jurnalguru')->with('sukses','Jurnal Berhasil Diupdate');
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

