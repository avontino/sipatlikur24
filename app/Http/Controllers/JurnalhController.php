<?php

// app/Http/Controllers/JurnalhController.php

namespace App\Http\Controllers;

use App\Models\Jurnalh;
use App\Models\Jadwal;
use App\Models\Absen;
use App\Models\Ijin;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Exports\JurnalhExport;
use DB;

class JurnalhController extends Controller
{
    public function index(Request $request)
    {   
        $tahun_ajaran = session('tahun_ajaran');
        $semester = session('semester');
        
        if (empty($tahun_ajaran) || empty($semester)) {
            $activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
            if ($activeTa) {
                $tahun_ajaran = $activeTa->tahun_ajaran;
                $semester = $activeTa->semester;
                session(['tahun_ajaran' => $tahun_ajaran]);
                session(['semester' => $semester]);
                session(['tahun_ajaran_id' => $activeTa->id]);
            }
        }

        
        $view = $request->query('view');
        
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->role == 'admin';
        $isKurikulum = auth()->user()->hasRole('kurikulum') || auth()->user()->role == 'kurikulum';

        if ($isAdmin || ($isKurikulum && $view === 'kurikulum')) {

        // Memeriksa jika tombol "hapus_sinkron" ditekan
        if ($request->has('action') && $request->action == 'hapus_sinkron') {
            $targetDateStr = $request->input('tanggal_sinkron');
            if ($targetDateStr) {
                $currentDate = Carbon::parse($targetDateStr)->toDateString();
                Jurnalh::whereDate('created_at', $currentDate)->delete();
                Jurnal::whereDate('created_at', $currentDate)->delete();
                return redirect()->back()->with('sukses', 'Data sinkronisasi pada tanggal ' . $targetDateStr . ' berhasil dihapus!');
            }
            return redirect()->back()->with('gagal', 'Silakan pilih tanggal terlebih dahulu!');
        }

        // Memeriksa jika tombol "sinkron" ditekan
        if ($request->has('action') && $request->action == 'sinkron') {
            // Get the date to synchronize (fall back to today)
            $targetDateStr = $request->input('tanggal_sinkron', Carbon::now()->toDateString());
            $targetCarbon = Carbon::parse($targetDateStr);
            $today = $targetCarbon->format('l');  // English day name e.g. "Friday"
            $currentDate = $targetCarbon->toDateString();

            // ── Cari Tahun Ajaran & Semester yang COCOK dengan jadwal ──
            // Tidak pakai session karena bisa beda/stale. Cari TA yang jadwalnya ada untuk hari ini.
            $taFromJadwal = DB::table('jadwal')
                ->where('hari', $today)
                ->select('tahun_ajaran', 'semester')
                ->groupBy('tahun_ajaran', 'semester')
                ->orderByRaw("FIELD(tahun_ajaran, ?) DESC", [$tahun_ajaran]) // prioritaskan TA session jika ada
                ->first();

            if ($taFromJadwal) {
                $tahun_ajaran = $taFromJadwal->tahun_ajaran;
                $semester = $taFromJadwal->semester;
            }

            // Ambil data jadwal sesuai hari ini dan per kelas
            $jadwals = Jadwal::where('hari', $today)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', $semester)
                ->get(); // Ambil semua jadwal untuk hari ini

            if ($jadwals->isEmpty()) {
                return redirect()->back()->with('gagal',
                    "Tidak ada jadwal untuk hari {$today} ({$targetDateStr}) pada Tahun Ajaran {$tahun_ajaran} Semester {$semester}. Pastikan data jadwal sudah diimport."
                );
            }

            // ── VALIDASI: Blokir jika sudah pernah disinkronkan pada tanggal terpilih ──
            $alreadySynced = Jurnalh::where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', $semester)
                ->whereDate('created_at', $currentDate)
                ->exists();

            if ($alreadySynced) {
                return redirect()->back()->with('gagal',
                    "Jadwal untuk tanggal {$targetDateStr} sudah pernah disinkronkan sebelumnya. Tidak dapat melakukan sinkronisasi ulang."
                );
            }

            // Group jadwal berdasarkan kelas untuk memudahkan proses
            $jadwalsByKelas = $jadwals->groupBy('kelas');

            // Insert atau update data untuk setiap kelas
            foreach ($jadwalsByKelas as $kelas => $jadwalsKelas) {
                // Cek apakah sudah ada data untuk kelas dan tanggal tertentu
                $existing = Jurnalh::where('kelas', $kelas)
                                   ->where('tahun_ajaran', $tahun_ajaran)
                                   ->where('semester', $semester)
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
                    $jData['created_at'] = Carbon::parse($currentDate)->startOfDay();
                    $jData['updated_at'] = now();
                    $jData['tahun_ajaran'] = $tahun_ajaran;
                    $jData['semester'] = $semester;

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
        
        // Cek apakah data sudah ada
        $existingJurnal = Jurnal::where([
            'kelas' => $jadwal->kelas,
            'jamke' => $jadwal->jamke,
            'guru' => $jadwal->guru,
            'mapel' => $jadwal->mapel,
            'tahun_ajaran' => $tahun_ajaran,
            'semester' => $semester,
        ])
        ->whereDate('created_at', $currentDate)
        ->first();

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
                'guru_id' => $user ? $user->id : null,
                'tahun_ajaran' => $tahun_ajaran,
                'semester' => $semester,
                'created_at' => Carbon::parse($currentDate)->startOfDay(),
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
                $updateData['guru_id'] = $user ? $user->id : null;
                $updateData['jumlahjam'] = $jadwal->jumlahjam;
                $updateData['updated_at'] = now();
                
                // Update jika ada perubahan
                if (!empty($updateData)) {
                    $existingJurnal->update($updateData);
                }
        }
    } // end foreach jadwals

    // Send Notifications after sync is complete
    // 1. All Ketua Kelas for the classes synchronized today
    $syncedClasses = $jadwalsByKelas->keys();
    foreach ($syncedClasses as $kelasName) {
        $ketuaKelasUsers = \App\Models\User::where(function($q) {
                $q->where('role', 'ketuakelas')
                  ->orWhere('additional_roles', 'ketuakelas')
                  ->orWhere('additional_roles', 'LIKE', '%ketuakelas%');
            })
            ->where(function($q) use ($kelasName) {
                $q->where('walikelas_kelas', $kelasName)
                  ->orWhere('name', $kelasName)
                  ->orWhereIn('username', function($sub) use ($kelasName) {
                      $sub->select('nis')->from('siswa')->where('kelas', $kelasName);
                  });
            })
            ->get();

        foreach ($ketuaKelasUsers as $u) {
            $u->sendNotification(
                'Jurnal KBM Hari Ini Aktif',
                "Jurnal KBM hari ini untuk kelas {$kelasName} telah disinkronkan. Harap lakukan verifikasi absensi pagi dan isi jurnal.",
                '/jurnalbaru',
                'jurnal'
            );
        }
    }

    // 2. All Guru who have schedules today
    $syncedGuruNames = $jadwals->pluck('guru')->unique();
    foreach ($syncedGuruNames as $guruName) {
        $guruUsers = \App\Models\User::where('role', 'guru')->where('name', $guruName)->get();
        foreach ($guruUsers as $u) {
            $u->sendNotification(
                'Jadwal Mengajar Aktif',
                'Jadwal mengajar Anda hari ini telah aktif. Harap isi jurnal mengajar setelah kelas selesai.',
                '/jurnalbaru',
                'jurnal'
            );
        }
    }

    // 3. Kurikulum and Admin
    $adminAndKuri = \App\Models\User::whereIn('role', ['admin', 'kurikulum'])->get();
    $totalClassesSynced = count($syncedClasses);
    foreach ($adminAndKuri as $u) {
        $u->sendNotification(
            'Sinkronisasi Jurnal Sukses',
            "Sinkronisasi jurnal harian berhasil dilakukan untuk {$totalClassesSynced} kelas.",
            '/jurnalh?view=kurikulum',
            'jurnal'
        );
    }

    return redirect()->back()->with('sukses', 'Data berhasil disinkronkan!');
    } // end if sinkron action

    // Menampilkan data jurnalh yang sudah ada untuk admin/kurikulum
    // Jika AJAX/DataTables request, kembalikan JSON
    if ($request->ajax() || $request->has('draw')) {
        $query = Jurnalh::orderBy('created_at', 'desc');
        if ($tahun_ajaran && $semester) {
            $query->where('tahun_ajaran', $tahun_ajaran)->where('semester', $semester);
        }

        $totalRecords = $query->count();

        if ($searchValue = $request->input('search.value')) {
            $query->where(function($q) use ($searchValue) {
                $q->where('kelas', 'LIKE', "%{$searchValue}%");
                for ($i = 1; $i <= 11; $i++) {
                    $q->orWhere('j'.$i, 'LIKE', "%{$searchValue}%");
                }
            });
        }

        $filteredRecords = $query->count();
        $start  = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));

        $rows = $query->skip($start)->take($length)->get();

        $canDelete = (auth()->user()->role=='admin' || auth()->user()->role=='kurikulum'
            || auth()->user()->hasRole('admin') || auth()->user()->hasRole('kurikulum'))
            && request()->query('view') === 'kurikulum';

        $resultData = [];
        foreach ($rows as $jurnalh) {
            $row = [];
            $row['kelas'] = e($jurnalh->kelas);

            // Build j1-j11 HTML (same logic as blade)
            for ($i = 1; $i <= 11; $i++) {
                $columnData = $jurnalh->{'j'.$i} ?? '';
                $displayContent = '';
                if (!empty($columnData)) {
                    $segments = explode('<hr>', $columnData);
                    for ($j = 0; $j < count($segments);) {
                        if (isset($segments[$j], $segments[$j+1], $segments[$j+2])) {
                            $guruSegment  = $segments[$j];
                            $mapelSegment = $segments[$j+1];
                            $materiSegment= $segments[$j+2];
                            $badgeSegment = '';
                            $segmentSize  = 3;
                            if (isset($segments[$j+3]) && (strpos($segments[$j+3],'badge')!==false || strpos($segments[$j+3],'KBM Tanpa Guru')!==false)) {
                                $badgeSegment = $segments[$j+3];
                                $segmentSize  = 4;
                            }
                            $hasKBMBadge = !empty($badgeSegment) && strpos($badgeSegment,'KBM Tanpa Guru')!==false;
                            if ($hasKBMBadge) {
                                $segmentStyle = 'background-color:#fff3cd;padding:5px;margin:2px;border-radius:5px;border-left:4px solid #ffc107;';
                            } elseif (strpos($materiSegment,'Jam Kosong')!==false && !preg_match('/(Bab|Topik|Tugas|TP|Pelajaran)\s?\d/',$materiSegment)) {
                                $segmentStyle = 'background-color:#f8d7da;padding:5px;margin:2px;border-radius:5px;border-left:4px solid #dc3545;';
                            } else {
                                $segmentStyle = 'background-color:#eefdebff;padding:5px;margin:2px;border-radius:5px;border-left:4px solid #9af89fff;';
                            }
                            if ($j > 0) $displayContent .= '<hr style="margin:8px 0;border-color:#dee2e6;">';
                            $displayContent .= '<div style="'.$segmentStyle.'">';
                            $displayContent .= '<div style="font-weight:bold;color:#495057;margin-bottom:2px;">'.e($guruSegment).'</div>';
                            $displayContent .= '<div style="font-size:0.9em;color:#6c757d;margin-bottom:2px;">'.e($mapelSegment).'</div>';
                            $displayContent .= '<div style="color:#212529;">'.e($materiSegment).'</div>';
                            if (!empty($badgeSegment)) {
                                $displayContent .= '<div style="margin-top:5px;">'.$badgeSegment.'</div>';
                            }
                            $displayContent .= '</div>';
                            $j += $segmentSize;
                        } else {
                            if (!empty($segments[$j])) {
                                $displayContent .= '<div style="color:#212529;">'.e($segments[$j]).'</div>';
                            }
                            $j++;
                        }
                    }
                } else {
                    $displayContent = '<span class="text-muted">-</span>';
                }
                $row['j'.$i] = $displayContent;
            }

            // Absensi buttons
            $tgl = \Carbon\Carbon::parse($jurnalh->created_at)->format('Y-m-d');
            $row['absensi_siswa'] = '<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#absensiModal" data-kelas="'.e($jurnalh->kelas).'" data-tgl="'.$tgl.'">Lihat Absensi</button>';
            $row['absensi_guru']  = '<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#absensiguruModal" data-tgl="'.$tgl.'">Lihat Absensi</button>';
            $row['tanggal']       = \Carbon\Carbon::parse($jurnalh->created_at)->format('d-m-Y');
            $row['action']        = $canDelete
                ? '<a href="/jurnalh/'.$jurnalh->id.'/delete" class="btn btn-danger btn-sm" onclick="return confirm(\'Hapus?\')">Hapus</a>'
                : '-';

            $resultData[] = $row;
        }

        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $resultData,
        ]);
    }

    $jurnalhs = collect();
    return view('jurnalh.index', compact('jurnalhs'));
    } else {
        // NOT ADMIN/KURIKULUM: Wali Kelas, Ketua Kelas, Siswa, or Guru
        $user = auth()->user();
        $isWali = ($user->role=='walikelas' || ($user->role=='guru' && $user->walikelas_kelas));
        $isKetua = ($user->role=='ketuakelas' || $user->hasRole('ketuakelas') || str_contains((string)$user->additional_roles, 'ketuakelas'));

        $myClass = $user->walikelas_kelas;
        if (!$myClass) {
            $myClass = \App\Models\Siswa::where('username', $user->username)->orWhere('nama', $user->name)->value('kelas');
        }

        $isExplicitWaliView = ($view === 'walikelas' || $user->role === 'walikelas');

        if ($isKetua || $user->role=='siswa' || $isExplicitWaliView) {
            $kelasToSearch = $myClass ?: $user->name;
            $targetDate = $request->input('tanggal', date('Y-m-d'));

            $jurnalhs = Jurnalh::where('kelas', $kelasToSearch)
                           ->where('tahun_ajaran', $tahun_ajaran)
                           ->where('semester', $semester)
                           ->whereDate('created_at', $targetDate)
                           ->get();

            return view('jurnalh.index', compact('jurnalhs', 'myClass', 'targetDate'));

        } elseif ($user->role=='guru' || $user->hasRole('guru')) {
            $guruNama = $user->name; 
            $cleanGuruNama = trim(preg_replace('/,.*$/', '', $guruNama));
            $targetDate = $request->input('tanggal', date('Y-m-d'));

            $jurnalhs = Jurnalh::where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', $semester)
                ->whereDate('created_at', $targetDate)
                ->where(function ($query) use ($guruNama, $cleanGuruNama) {
                    for ($i = 1; $i <= 11; $i++) {
                        $query->orWhere('j' . $i, 'like', '%' . $guruNama . '%')
                              ->orWhere('j' . $i, 'like', '%' . $cleanGuruNama . '%');
                    }
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return view('jurnalh.index', compact('jurnalhs', 'myClass', 'targetDate'));
        } else {
            $jurnalhs = collect();
            return view('jurnalh.index', compact('jurnalhs'));
        }
    } // end else (not admin/kurikulum)
    } // end index()

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


public function exportPDF(Request $request)
{
    ini_set('memory_limit', '1024M');
    set_time_limit(300);

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

    public function delete($id)
    {
        $jurnalh = Jurnalh::findOrFail($id);
        $jurnalh->delete();
        return redirect('/jurnalh')->with('sukses', 'Data Jurnal Harian Berhasil Dihapus');
    }


    
    
    
    
    
    
    
    
}

