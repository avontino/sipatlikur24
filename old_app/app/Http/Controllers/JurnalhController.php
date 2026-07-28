<?php

// app/Http/Controllers/JurnalhController.php

namespace App\Http\Controllers;

use App\Jurnalh;
use App\Jadwal;
use App\Absen;
use App\Ijin;
use App\Jurnal;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as PDF;
use App\Exports\JurnalhExport;
use DB;

class JurnalhController extends Controller
{
    public function index(Request $request)
    {   
        
        if (auth()->user()->role=='ketuakelas' || auth()->user()->role=='walikelas') {
             // Menampilkan data jurnalh yang sudah ada
        $jurnalhs = Jurnalh::where('kelas',auth()->user()->name)->whereDate('created_at',now())->get(); // Menampilkan semua data jurnalh
        // dd($jurnalhs);
        return view('jurnalh.index', compact('jurnalhs'));

        } elseif (auth()->user()->role=='guru' ) {

           // Ambil nama guru yang sedang login
    $guruNama = auth()->user()->name; 

    // Mencari data jurnalh berdasarkan nama guru di salah satu kolom j1 sampai j11 pada hari ini
    $jurnalhs = Jurnalh::whereDate('created_at', now()) // Mengambil data berdasarkan tanggal hari ini
        ->where(function ($query) use ($guruNama) {
            // Cek nama guru pada setiap kolom j1 sampai j11
            for ($i = 1; $i <= 11; $i++) {
                $query->orWhere('j' . $i, 'like', '%' . $guruNama . '%');
            }
        })
        ->get(); // Mendapatkan semua data jurnalh yang memenuhi kriteria

        // dd($jurnalhs);

    // Menampilkan data jurnalh yang sudah ditemukan
    return view('jurnalh.index', compact('jurnalhs'));
        
        } elseif (auth()->user()->role=='admin' || auth()->user()->role=='kurikulum' ){

        // Memeriksa jika tombol "sinkron" ditekan
        if ($request->has('action') && $request->action == 'sinkron') {
    // Mendapatkan hari ini dalam bahasa Inggris (misalnya: 'Monday', 'Tuesday')
    $today = Carbon::now()->format('l');  // Menggunakan Carbon untuk mendapatkan nama hari dalam bahasa Inggris
    $currentDate = Carbon::now()->toDateString();  // Mendapatkan tanggal saat ini

    // Ambil data jadwal sesuai hari ini dan per kelas
    $jadwals = Jadwal::where('hari', $today)->get(); // Ambil semua jadwal untuk hari ini

    // Group jadwal berdasarkan kelas untuk memudahkan proses
    $jadwalsByKelas = $jadwals->groupBy('kelas');

    // Insert atau update data untuk setiap kelas
    foreach ($jadwalsByKelas as $kelas => $jadwalsKelas) {
        // Cek apakah sudah ada data untuk kelas dan tanggal tertentu
        $existing = Jurnalh::where('kelas', $kelas)
                           ->whereDate('created_at', $currentDate)
                           ->first();

        // Siapkan array untuk menyimpan data j1-j11
        $jData = [];
        for ($i = 1; $i <= 11; $i++) {
            $jData['j' . $i] = null; // Reset semua kolom j
        }

        if (!$existing) {
            // SINKRON AWAL - Kode yang sudah benar untuk multiple mapel
            foreach ($jadwalsKelas as $jadwal) {
                // Menyusun format untuk kolom j1, j2, ... j11
                $j_value = $jadwal->guru . "<hr>" . $jadwal->mapel . "<hr>" . $jadwal->materi;

                // Mengambil rentang jamke
                $jamkeParts = explode('-', $jadwal->jamke);
                $startJamke = (int)$jamkeParts[0];
                $endJamke = (int)($jamkeParts[1] ?? $startJamke);

                // Isi data untuk rentang jamke yang sesuai
                for ($i = $startJamke; $i <= $endJamke; $i++) {
                    if ($i >= 1 && $i <= 11) {
                        // Jika sudah ada data di jam tersebut, gabungkan dengan <hr>
                        if (!empty($jData['j' . $i])) {
                            $jData['j' . $i] .= "<hr>" . $j_value;
                        } else {
                            $jData['j' . $i] = $j_value;
                        }
                    }
                }
            }

            // Tambahkan data kelas dan tanggal
            $jData['kelas'] = $kelas;
            $jData['created_at'] = $currentDate;

            // Insert data baru
            Jurnalh::create($jData);
        } else {
            // SINKRON BERULANG - Dengan proteksi data yang sudah terisi
            // Gunakan data existing sebagai base
            for ($i = 1; $i <= 11; $i++) {
                $jData['j' . $i] = $existing->{'j' . $i}; // Gunakan data yang sudah ada
            }

            // Proses setiap jadwal untuk kelas ini
            foreach ($jadwalsKelas as $jadwal) {
                // Menyusun format untuk kolom j1, j2, ... j11
                $j_value = $jadwal->guru . "<hr>" . $jadwal->mapel . "<hr>" . $jadwal->materi;

                // Mengambil rentang jamke
                $jamkeParts = explode('-', $jadwal->jamke);
                $startJamke = (int)$jamkeParts[0];
                $endJamke = (int)($jamkeParts[1] ?? $startJamke);

                // Isi data untuk rentang jamke yang sesuai
                for ($i = $startJamke; $i <= $endJamke; $i++) {
                    if ($i >= 1 && $i <= 11) {
                        $existingData = $jData['j' . $i];
                        
                        if (empty($existingData) || is_null($existingData)) {
                            // Jika benar-benar kosong/null - ISI LANGSUNG
                            $jData['j' . $i] = $j_value;
                        } else if (trim($existingData) == 'Jam Kosong') {
                            // Jika isi data adalah "Jam Kosong" saja - ISI LANGSUNG
                            $jData['j' . $i] = $j_value;
                        } else {
                            // Jika sudah ada data, cek dulu apakah mengandung "KBM Tanpa Guru"
                            if (strpos($existingData, 'KBM Tanpa Guru') !== false) {
                                // Jangan ubah data yang mengandung "KBM Tanpa Guru"
                                continue; // Skip ke jam berikutnya
                            }
                            
                            // Jika tidak mengandung "KBM Tanpa Guru", lanjutkan proses normal
                            $parts = explode('<hr>', $existingData);
                            $mapelExists = false;
                            $newParts = [];
                            
                            // Cek setiap set data (guru<hr>mapel<hr>materi...)
                            for ($p = 0; $p < count($parts); $p += 3) {
                                if (isset($parts[$p]) && isset($parts[$p+1]) && isset($parts[$p+2])) {
                                    $guru = trim($parts[$p]);
                                    $mapel = trim($parts[$p+1]);
                                    $materi = trim($parts[$p+2]);
                                    
                                    // Cek apakah ada bagian tambahan untuk materi (misal badge)
                                    $fullMateri = $materi;
                                    $nextP = $p + 3;
                                    while ($nextP < count($parts) && 
                                           ($nextP + 1 >= count($parts) || 
                                            !isset($parts[$nextP+1]) || 
                                            ($nextP % 3 != 0))) {
                                        $fullMateri .= "<hr>" . trim($parts[$nextP]);
                                        $nextP++;
                                    }
                                    if ($nextP > $p + 3) {
                                        $p = $nextP - 3; // Adjust loop counter
                                    }
                                    
                                    // Jika mapel sama dengan jadwal yang sedang diproses
                                    if ($mapel == $jadwal->mapel) {
                                        $mapelExists = true;
                                        // Update hanya jika materi "Jam Kosong" murni
                                        if (trim($fullMateri) == 'Jam Kosong') {
                                            $newParts[] = $jadwal->guru;
                                            $newParts[] = $jadwal->mapel;
                                            $newParts[] = $jadwal->materi;
                                        } else {
                                            // Pertahankan data existing
                                            $newParts[] = $guru;
                                            $newParts[] = $mapel;
                                            $newParts[] = $fullMateri;
                                        }
                                    } else {
                                        // Pertahankan data mapel lain
                                        $newParts[] = $guru;
                                        $newParts[] = $mapel;
                                        $newParts[] = $fullMateri;
                                    }
                                }
                            }
                            
                            // Jika mapel belum ada, TAMBAHKAN
                            if (!$mapelExists) {
                                $newParts[] = $jadwal->guru;
                                $newParts[] = $jadwal->mapel;
                                $newParts[] = $jadwal->materi;
                            }
                            
                            // Update dengan data baru
                            $jData['j' . $i] = implode('<hr>', $newParts);
                        }
                    }
                }
            }

            // Update data existing
            $updateData = [];
            for ($i = 1; $i <= 11; $i++) {
                $columnName = 'j' . $i;
                // Update jika ada perubahan
                if ($jData[$columnName] != $existing->$columnName) {
                    $updateData[$columnName] = $jData[$columnName];
                }
            }
            
            // Update jika ada perubahan
            if (!empty($updateData)) {
                $existing->update($updateData);
            }
        }
    }

    // Bagian kedua: Update tabel Jurnal dengan proteksi yang sama
    foreach ($jadwals as $jadwal) {
        $user = DB::table('users')->where('name', 'LIKE', '%' . $jadwal->guru . '%')->first();
        
        if ($user) {
            // Cek apakah data sudah ada
            $existingJurnal = Jurnal::where([
                'kelas' => $jadwal->kelas,
                'jamke' => $jadwal->jamke,
                'guru' => $jadwal->guru,
                'mapel' => $jadwal->mapel,
                'created_at' => $currentDate,
            ])->first();

            if (!$existingJurnal) {
                // Jika belum ada, buat baru
                Jurnal::create([
                    'kelas' => $jadwal->kelas,
                    'jamke' => $jadwal->jamke,
                    'guru' => $jadwal->guru,
                    'mapel' => $jadwal->mapel,
                    'jumlahjam' => $jadwal->jumlahjam,
                    'materi' => $jadwal->materi,
                    'catatan' => $jadwal->catatan,
                    'guru_id' => $user->id,
                    'created_at' => $currentDate,
                    'updated_at' => now(),
                ]);
            } else {
                // Jika sudah ada, hanya update field yang masih "Jam Kosong"
                $updateData = [];
                
                // Cek dan update field yang berisi "Jam Kosong" (bukan "KBM Tanpa Guru")
                if (empty($existingJurnal->materi) || 
                    trim($existingJurnal->materi) == 'Jam Kosong') {
                    // Pastikan bukan "KBM Tanpa Guru"
                    if (strpos($existingJurnal->materi, 'KBM Tanpa Guru') === false) {
                        $updateData['materi'] = $jadwal->materi;
                    }
                }
                
                if (empty($existingJurnal->catatan) || 
                    trim($existingJurnal->catatan) == 'Jam Kosong') {
                    // Pastikan bukan "KBM Tanpa Guru"
                    if (strpos($existingJurnal->catatan, 'KBM Tanpa Guru') === false) {
                        $updateData['catatan'] = $jadwal->catatan;
                    }
                }
                
                // Selalu update guru_id dan jumlahjam karena ini data referensi
                $updateData['guru_id'] = $user->id;
                $updateData['jumlahjam'] = $jadwal->jumlahjam;
                $updateData['updated_at'] = now();
                
                // Update jika ada perubahan
                if (!empty($updateData)) {
                    $existingJurnal->update($updateData);
                }
            }
        }
    }

    return redirect('/jurnalh')->with('sukses', 'Data berhasil disinkronkan!');
}

        // Menampilkan data jurnalh yang sudah ada
        $jurnalhs = Jurnalh::orderBy('created_at', 'desc')->get();// Menampilkan semua data jurnalh
        return view('jurnalh.index', compact('jurnalhs'));
    } else {
        // Menampilkan data jurnalh yang sudah ada
        $jurnalhs = Jurnalh::whereDate('created_at',now())->get(); // Menampilkan semua data jurnalh
        // dd($jurnalhs);
        return view('jurnalh.index', compact('jurnalhs'));
    }
    }

    public function getAbsensi($kelas, $tgl)
    {
        // Ambil data absensi berdasarkan kelas dan tanggal
        $absensi = Absen::where('kelas', $kelas)
                             ->whereDate('created_at', $tgl)
                             ->get();

        // Kembalikan data absensi dalam bentuk JSON
        return response()->json($absensi);
    }

    public function getAbsensiguru($tgl)
    {
        // Ambil data absensi berdasarkan kelas dan tanggal
        $absensiguru = Ijin::whereDate('created_at', $tgl)
                             ->get();

        // Kembalikan data absensi dalam bentuk JSON
        return response()->json($absensiguru);
    }
    
// Fungsi untuk export ke Excel
public function exportExcel(Request $request)
{
    $startDate = $request->start_date;
    $endDate = $request->end_date;

    // Convert the start and end dates to the format 'Y-m-d' to ignore time
$startDate = Carbon::parse($startDate)->toDateString();
$endDate = Carbon::parse($endDate)->toDateString();

// Ambil data jurnalh berdasarkan rentang tanggal tanpa waktu
$jurnalhs = Jurnalh::whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate)
                  ->get();

    // Gunakan Maatwebsite Excel untuk export
    return Excel::download(new JurnalhExport($jurnalhs), 'jurnalh.xlsx');
}


// Fungsi untuk export ke PDF
public function exportPDF(Request $request)
{
   $startDate = $request->start_date;
$endDate = $request->end_date;

// Validate that both start and end dates are in a valid date format
$request->validate([
    'start_date' => 'required|date|date_format:Y-m-d',
    'end_date' => 'required|date|date_format:Y-m-d',
]);

// Convert the start and end dates to the format 'Y-m-d' to ignore time
$startDate = \Carbon\Carbon::parse($startDate)->toDateString();
$endDate = \Carbon\Carbon::parse($endDate)->toDateString();

// Ambil data jurnalh berdasarkan rentang tanggal tanpa waktu
$jurnalhs = Jurnalh::whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate)
                  ->get();

  
  
    // Menggunakan Barryvdh DomPDF untuk export PDF
    $pdf = PDF::loadView('jurnalh.pdf', compact('jurnalhs', 'startDate', 'endDate'))
              ->setPaper('folio', 'landscape');  // Set ukuran kertas ke folio dengan orientasi landscape

    return $pdf->download('jurnalh.pdf');
}


    
    
    
    
    
    
    
    
}

