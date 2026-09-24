<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\IjinExport;
use App\Exports\IjinRekapExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Mapel;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Ijin;
use Carbon\Carbon;
use DB;

class IjinController extends Controller
{	
    public function tambahi()
    {	
    	$ma_pel=Mapel::all();
    	$gu_ru=Guru::all();
    	$ke_las=Kelas::all();
    	return view('ijin.tambahijin',compact('ma_pel','gu_ru','ke_las'));
    }

    public function create(Request $request)
    {	
        try {
            $allowedSia = [
                'Tugas Kedinasan',
                'Sakit',
                'Izin Terlambat',
                'Izin Keluar Jam Dinas',
                'Izin Pulang Sebelum Waktunya',
                'Keperluan Pribadi',
                'Cuti',
                'Terlambat',
                'Ijin',
                'Alpha'
            ];

            $request->validate([
                'tglmasuk' => 'required|date',
                'sia' => 'required|in:' . implode(',', $allowedSia),
                'jumlah' => 'nullable',
            ]);

            $data = $request->except('attachment');

            // Penanganan input waktu spesifik
            if (in_array($request->sia, ['Izin Terlambat', 'Terlambat'])) {
                $data['jumlah'] = 0;
                if ($request->filled('jam_terlambat')) {
                    $data['jam_terlambat'] = $request->jam_terlambat;
                }
            } elseif ($request->sia == 'Izin Keluar Jam Dinas') {
                $data['jumlah'] = 0;
                $data['jam_keluar'] = $request->jam_keluar ?? null;
                $data['jam_kembali'] = $request->jam_kembali ?? null;
            } elseif ($request->sia == 'Izin Pulang Sebelum Waktunya') {
                $data['jumlah'] = 0;
                $data['jam_keluar'] = $request->jam_keluar ?? ($request->jam_terlambat ?? null);
            } else {
                $data['jumlah'] = $request->jumlah ?: 1;
            }

            // Tentukan guru: jika admin/kurikulum/piket memilih guru lain
            $user = auth()->user();
            $isManager = in_array($user->role, ['admin', 'kurikulum', 'pembina', 'kesiswaan', 'kepala']) 
                || $user->hasRole('admin') 
                || $user->hasRole('kurikulum');

            if ($request->filled('guru_id') && $isManager) {
                $targetUser = \App\Models\User::find($request->guru_id);
                if ($targetUser) {
                    $data['user_id'] = $targetUser->id;
                    $data['guru'] = $targetUser->name;
                } else {
                    $data['user_id'] = $user->id;
                    $data['guru'] = $request->guru ?: $user->name;
                }
            } else {
                $data['user_id'] = $user->id;
                $data['guru'] = $request->guru ?: $user->name;
            }

            // Otomatis tentukan mapel agar tidak melanggar field NOT NULL di database
            $cleanName = trim(preg_replace('/[,.].*$/', '', $data['guru']));
            $data['mapel'] = $request->mapel ?: (\App\Models\Jadwal::where('guru', $data['guru'])
                ->orWhere('guru', 'LIKE', '%' . $cleanName . '%')
                ->value('mapel') ?: '-');

            $data['approval_status'] = $isManager ? 'approved' : 'pending';
            
            // Otomatis tentukan tahun ajaran dan semester jika kosong di session
            $data['tahun_ajaran'] = session('tahun_ajaran');
            $data['semester'] = session('semester');
            if (empty($data['tahun_ajaran'])) {
                $m = (int)date('m');
                $y = (int)date('Y');
                $data['tahun_ajaran'] = $m >= 7 ? "$y/" . ($y + 1) : ($y - 1) . "/$y";
                $data['semester'] = $m >= 7 ? 'Ganjil' : 'Genap';
            }

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                if ($file && $file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension() ?: '');
                    $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowedExts)) {
                        return redirect()->back()->withInput()->with('gagal', 'Format lampiran harus berupa PDF, JPG, JPEG, PNG, atau WEBP.');
                    }
                    if ($file->getSize() > 5242880) { // 5 MB
                        return redirect()->back()->withInput()->with('gagal', 'Ukuran berkas lampiran maksimal adalah 5 MB.');
                    }
                    $uploadDir = public_path('uploads/ijin_guru');
                    if (!file_exists($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $fileName = 'permit_' . time() . '_' . uniqid() . '.' . $ext;
                    $file->move($uploadDir, $fileName);
                    $data['attachment'] = 'uploads/ijin_guru/' . $fileName;
                }
            }
            
            $ijin = \App\Models\Ijin::create($data);

            if ($data['approval_status'] === 'pending') {
                try {
                    $admins = \App\Models\User::whereIn('role', ['admin', 'kurikulum', 'kepala'])->get();
                    foreach ($admins as $u) {
                        $u->sendNotification(
                            "Pengajuan Izin Guru Baru",
                            "Pengajuan Izin Guru Baru: " . $data['guru'] . " ({$request->sia}). Mohon tinjau di menu Presensi & Izin Guru.",
                            '/ijin',
                            'ijin'
                        );
                    }
                } catch (\Throwable $ne) {
                    \Log::warning('Gagal kirim notifikasi izin: ' . $ne->getMessage());
                }
            }

            return redirect()->back()->with('sukses', 'Izin Guru (' . $data['guru'] . ' - ' . $request->sia . ') Berhasil Disimpan!');
        } catch (\Throwable $e) {
            \Log::error('IjinController create error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('gagal', 'Gagal menyimpan permohonan izin: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return redirect('/login');
            }

            $ma_pel = Mapel::all();
            $gu_ru  = Guru::all();
            $ke_las = Kelas::all();

            $userRole = strtolower($user->role ?? '');
            $isStaffOrAdmin = in_array($userRole, ['admin', 'kurikulum', 'kepala', 'kesiswaan', 'pembina'])
                || $user->hasRole('admin')
                || $user->hasRole('kurikulum');

            if ($request->filled('filter')) {
                $filterDate = $request->filter;
                $query = \App\Models\Ijin::where(function($q) use ($filterDate) {
                    $q->whereDate('tglmasuk', $filterDate)
                      ->orWhere(function($sub) use ($filterDate) {
                          $sub->where('jumlah', '>', 1)
                              ->whereDate('tglmasuk', '<=', $filterDate)
                              ->whereRaw("DATE_ADD(DATE(tglmasuk), INTERVAL (jumlah - 1) DAY) >= ?", [$filterDate]);
                      });
                });
            } else {
                $query = \App\Models\Ijin::query();
            }

            // Filter Tahun Ajaran Aktif (misal 2026/2027 Ganjil) jika kolom tersedia
            if (\Illuminate\Support\Facades\Schema::hasColumn('ijin', 'tahun_ajaran')) {
                if ($rawTa = session('tahun_ajaran')) {
                    $cleanTa = trim(preg_replace('/\s*\(.*\)/', '', $rawTa));
                    $query->where(function($q) use ($rawTa, $cleanTa) {
                        $q->where('tahun_ajaran', $rawTa)
                          ->orWhere('tahun_ajaran', 'LIKE', '%' . $cleanTa . '%')
                          ->orWhereNull('tahun_ajaran');
                    });
                }
            }

            $viewMode = $request->query('view');
            $isAdmin = $user->hasRole('admin') || ($user->role === 'admin');
            
            // Tentukan apakah menampilkan semua guru atau hanya izin pribadi:
            if ($isAdmin && $viewMode !== 'saya') {
                $showAll = true;
            } elseif ($isStaffOrAdmin && in_array($viewMode, ['kurikulum', 'semua', 'all'])) {
                $showAll = true;
            } else {
                $showAll = false;
            }

            // Jika $showAll false, filter hanya menampilkan izin milik akun yang sedang login
            if (!$showAll) {
                $hasUserId = \Illuminate\Support\Facades\Schema::hasColumn('ijin', 'user_id');
                $query->where(function($q) use ($user, $hasUserId) {
                    if ($hasUserId) {
                        $q->where('user_id', $user->id);
                    }
                    $q->orWhere('guru', 'LIKE', '%' . $user->name . '%');
                });
            }

            $data_ijin = $query->orderBy('created_at', 'desc')->get();

            return view('ijin.index', [
                'data_ijin' => $data_ijin,
                'showAll' => $showAll,
                'isStaffOrAdmin' => $isStaffOrAdmin
            ], compact('ma_pel', 'gu_ru', 'ke_las'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('IjinController index error: ' . $e->getMessage());
            return redirect('/dashboard')->with('gagal', 'Terjadi kesalahan saat memuat halaman izin: ' . $e->getMessage());
        }
    }

    public function export() 
    {
        return Excel::download(new IjinExport, 'Ijin.xlsx');
    }

    public function update(Request $request)
    {
        // Validasi untuk field jam_terlambat jika status adalah "Terlambat"
        if($request->sia == 'Terlambat') {
            $request->validate([
                'jam_terlambat' => 'required',
            ]);
            // Set jumlah hari ke 0 untuk terlambat
            $request->merge(['jumlah' => 0]);
        } else {
            // Jika bukan terlambat, set jam_terlambat ke null
            $request->merge(['jam_terlambat' => null]);
        }
        
        $ijin=\App\Models\Ijin::findorFail($request->ijinid);
        $ijin->update($request->all());
        return redirect('/ijin')->with('sukses','Ijin Berhasil Diupdate');
    }

    public function delete($id)
    {
        $ijin = \App\Models\Ijin::find($id);
        if (!$ijin) {
            return redirect('/ijin')->with('gagal', 'Data izin tidak ditemukan');
        }

        $user = auth()->user();
        $isManager = in_array($user->role, ['admin', 'kurikulum']) || $user->hasRole('admin') || $user->hasRole('kurikulum');
        $isOwner = ($ijin->user_id && $ijin->user_id == $user->id) || ($ijin->guru == $user->name);

        if (!$isManager && (!$isOwner || $ijin->approval_status != 'pending')) {
            return redirect('/ijin')->with('gagal', 'Anda tidak memiliki hak untuk menghapus izin ini.');
        }

        $ijin->delete();
        return redirect('/ijin')->with('sukses', 'Ijin Berhasil Dihapus');
    }

    //download excel rekap kehadiran
    public function rekaphadir() 
    {   
        // Langsung export tanpa update tabel users
        return Excel::download(new IjinRekapExport, 'RekapKehadiran.xlsx');
    }

    public function approve($id)
    {
        $ijin = \App\Models\Ijin::findOrFail($id);
        $ijin->approval_status = 'approved';
        $ijin->save();

        // Notify Guru
        $guru = \App\Models\User::find($ijin->user_id);
        if ($guru) {
            $date = \Carbon\Carbon::parse($ijin->tglmasuk)->format('d M Y');
            $guru->sendNotification(
                "Izin Guru Disetujui",
                "Pengajuan Izin Anda untuk tanggal {$date} ({$ijin->sia}) telah disetujui oleh Admin.",
                '/ijin',
                'ijin'
            );
        }

        return redirect('/ijin')->with('sukses', 'Izin guru berhasil disetujui');
    }

    public function reject($id)
    {
        $ijin = \App\Models\Ijin::findOrFail($id);
        $ijin->approval_status = 'rejected';
        $ijin->save();

        // Notify Guru
        $guru = \App\Models\User::find($ijin->user_id);
        if ($guru) {
            $date = \Carbon\Carbon::parse($ijin->tglmasuk)->format('d M Y');
            $guru->sendNotification(
                "Izin Guru Ditolak",
                "Pengajuan Izin Anda untuk tanggal {$date} ({$ijin->sia}) ditolak oleh Admin.",
                '/ijin',
                'ijin'
            );
        }

        return redirect('/ijin')->with('sukses', 'Izin guru berhasil ditolak');
    }

    /**
     * Konfirmasi kedatangan guru yang izin terlambat atau izin keluar dinas
     */
    public function konfirmasiTiba($id, Request $request)
    {
        $ijin = \App\Models\Ijin::findOrFail($id);
        $user = auth()->user();

        // Otorisasi: Guru bersangkutan, admin, kurikulum, kesiswaan, atau pembina
        $isOwner = ($ijin->user_id && $ijin->user_id == $user->id) || ($ijin->guru == $user->name);
        $isStaff = in_array($user->role, ['admin', 'kurikulum', 'kesiswaan', 'pembina']) || 
                   $user->hasRole('admin') || $user->hasRole('kurikulum');

        if (!$isOwner && !$isStaff) {
            return redirect()->back()->with('gagal', 'Anda tidak memiliki izin untuk mengonfirmasi kehadiran ini.');
        }

        $waktuTiba = $request->input('waktu_tiba', Carbon::now()->format('H:i:s'));
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('ijin', 'waktu_tiba')) {
            $ijin->waktu_tiba = $waktuTiba;
            $ijin->save();
        } else {
            $ijin->ket = trim(($ijin->ket ? $ijin->ket . ' ' : '') . '[Tiba: ' . substr($waktuTiba, 0, 5) . ']');
            $ijin->save();
        }

        return redirect()->back()->with('sukses', "Kehadiran guru {$ijin->guru} berhasil dikonfirmasi (Tiba pukul " . substr($waktuTiba, 0, 5) . " WIB) dan otomatis bertambah ke Hadir di Sekolah!");
    }

    /**
     * Batalkan status kedatangan guru
     */
    public function batalTiba($id)
    {
        $ijin = \App\Models\Ijin::findOrFail($id);
        $user = auth()->user();

        $isOwner = ($ijin->user_id && $ijin->user_id == $user->id) || ($ijin->guru == $user->name);
        $isStaff = in_array($user->role, ['admin', 'kurikulum', 'kesiswaan', 'pembina']) || 
                   $user->hasRole('admin') || $user->hasRole('kurikulum');

        if (!$isOwner && !$isStaff) {
            return redirect()->back()->with('gagal', 'Anda tidak memiliki izin.');
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('ijin', 'waktu_tiba')) {
            $ijin->waktu_tiba = null;
            $ijin->save();
        }

        return redirect()->back()->with('sukses', "Status kedatangan guru {$ijin->guru} berhasil dibatalkan.");
    }

    /**
     * Halaman Live Monitoring Rekap Presensi & Izin Guru Real-time
     * Konsep ala Google Apps Script: Guru yang tidak mengisi izin otomatis dianggap Hadir di Sekolah.
     */
    public function liveMonitoring(Request $request)
    {
        $targetDateStr = $request->input('tanggal', Carbon::today()->toDateString());
        $targetCarbon = Carbon::parse($targetDateStr);
        $hariIndonesia = $targetCarbon->isoFormat('dddd, D MMMM Y');

        // Ambil seluruh guru dan tendik aktif di sekolah
        $allTeachers = User::whereIn('role', ['guru', 'walikelas', 'tendik', 'kurikulum', 'kesiswaan', 'kepala'])
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'role', 'username']);

        $totalTeachers = $allTeachers->count();

        // Ambil data izin untuk tanggal terpilih yang tidak ditolak (termasuk izin multi-hari: durasi/jumlah)
        $ijinToday = Ijin::where('approval_status', '!=', 'rejected')
            ->where(function($q) use ($targetDateStr) {
                $q->whereDate('tglmasuk', $targetDateStr)
                  ->orWhere(function($sub) use ($targetDateStr) {
                      $sub->where('jumlah', '>', 1)
                          ->whereDate('tglmasuk', '<=', $targetDateStr)
                          ->whereRaw("DATE_ADD(DATE(tglmasuk), INTERVAL (jumlah - 1) DAY) >= ?", [$targetDateStr]);
                  });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Peta lookup izin guru
        $teachersIzinMap = [];
        $teachersIzinByName = [];

        foreach ($ijinToday as $ij) {
            if ($ij->user_id) {
                $teachersIzinMap[$ij->user_id] = $ij;
            }
            if ($ij->guru) {
                $teachersIzinByName[strtolower(trim($ij->guru))] = $ij;
            }
        }

        // Definisi Struktur Kategori (Mengadopsi dari Google Apps Script)
        $kategoriList = [
            'Tugas Kedinasan' => [
                'label' => 'Tugas Kedinasan (Dinas Luar)',
                'icon'  => 'fas fa-briefcase',
                'color' => '#0284c7', // Sky blue
                'badge' => 'bg-info text-white',
                'bar_color' => '#0284c7',
                'members' => []
            ],
            'Sakit' => [
                'label' => 'Sakit',
                'icon'  => 'fas fa-medkit',
                'color' => '#ea580c', // Orange
                'badge' => 'bg-warning text-dark',
                'bar_color' => '#ea580c',
                'members' => []
            ],
            'Izin Terlambat' => [
                'label' => 'Izin Terlambat',
                'icon'  => 'fas fa-clock',
                'color' => '#eab308', // Amber
                'badge' => 'bg-warning text-dark',
                'bar_color' => '#eab308',
                'members' => []
            ],
            'Izin Keluar Jam Dinas' => [
                'label' => 'Izin Keluar Saat Jam Dinas (Kembali)',
                'icon'  => 'fas fa-sign-out-alt',
                'color' => '#d97706', // Amber-600
                'badge' => 'bg-warning text-dark',
                'bar_color' => '#d97706',
                'members' => []
            ],
            'Izin Pulang Sebelum Waktunya' => [
                'label' => 'Izin Pulang Sebelum Waktunya',
                'icon'  => 'fas fa-door-open',
                'color' => '#f59e0b', // Amber-500
                'badge' => 'bg-warning text-dark',
                'bar_color' => '#f59e0b',
                'members' => []
            ],
            'Cuti' => [
                'label' => 'Cuti',
                'icon'  => 'fas fa-calendar-minus',
                'color' => '#8b5cf6', // Purple
                'badge' => 'bg-purple text-white',
                'bar_color' => '#8b5cf6',
                'members' => []
            ],
            'Keperluan Pribadi' => [
                'label' => 'Keperluan Pribadi',
                'icon'  => 'fas fa-user-clock',
                'color' => '#64748b', // Slate
                'badge' => 'bg-secondary text-white',
                'bar_color' => '#64748b',
                'members' => []
            ],
        ];

        $hadirList = [];
        $totalIzinCount = 0;

        foreach ($allTeachers as $teacher) {
            $tNameClean = strtolower(trim($teacher->name));
            $ijinData = $teachersIzinMap[$teacher->id] ?? ($teachersIzinByName[$tNameClean] ?? null);

            // Fallback substring jika ada sedikit perbedaan gelar/spasi
            if (!$ijinData) {
                foreach ($teachersIzinByName as $ijName => $ijObj) {
                    if (str_contains($tNameClean, $ijName) || str_contains($ijName, $tNameClean)) {
                        $ijinData = $ijObj;
                        break;
                    }
                }
            }

            if ($ijinData) {
                $sia = trim($ijinData->sia);

                // Normalisasi kategori target
                $targetKey = 'Keperluan Pribadi';
                if (stripos($sia, 'tugas') !== false || stripos($sia, 'dinas') !== false) {
                    $targetKey = 'Tugas Kedinasan';
                } elseif (stripos($sia, 'sakit') !== false) {
                    $targetKey = 'Sakit';
                } elseif (stripos($sia, 'keluar') !== false) {
                    $targetKey = 'Izin Keluar Jam Dinas';
                } elseif (stripos($sia, 'pulang') !== false) {
                    $targetKey = 'Izin Pulang Sebelum Waktunya';
                } elseif (stripos($sia, 'terlambat') !== false) {
                    $targetKey = 'Izin Terlambat';
                } elseif (stripos($sia, 'cuti') !== false) {
                    $targetKey = 'Cuti';
                } elseif (stripos($sia, 'pribadi') !== false || stripos($sia, 'ijin') !== false) {
                    $targetKey = 'Keperluan Pribadi';
                }

                // Cek status kedatangan guru untuk izin sementara (Terlambat & Keluar Jam Dinas)
                $isArrived = false;
                $waktuTibaFormatted = null;

                if ($targetKey === 'Izin Terlambat' || $targetKey === 'Izin Keluar Jam Dinas') {
                    if (!empty($ijinData->waktu_tiba)) {
                        $isArrived = true;
                        $waktuTibaFormatted = substr($ijinData->waktu_tiba, 0, 5) . ' WIB';
                    } elseif ($targetDateStr === Carbon::today()->toDateString()) {
                        $currTime = Carbon::now()->format('H:i:s');
                        if ($targetKey === 'Izin Terlambat' && !empty($ijinData->jam_terlambat) && $currTime >= $ijinData->jam_terlambat) {
                            $isArrived = true;
                            $waktuTibaFormatted = substr($ijinData->jam_terlambat, 0, 5) . ' WIB (Otomatis)';
                        } elseif ($targetKey === 'Izin Keluar Jam Dinas' && !empty($ijinData->jam_kembali) && $currTime >= $ijinData->jam_kembali) {
                            $isArrived = true;
                            $waktuTibaFormatted = substr($ijinData->jam_kembali, 0, 5) . ' WIB (Otomatis)';
                        }
                    }
                }

                // Format keterangan jam izin
                $jamKet = '';
                if ($ijinData->jam_keluar && $ijinData->jam_kembali) {
                    $jamKet = substr($ijinData->jam_keluar, 0, 5) . ' - ' . substr($ijinData->jam_kembali, 0, 5) . ' WIB';
                } elseif ($ijinData->jam_keluar) {
                    $jamKet = 'Jam ' . substr($ijinData->jam_keluar, 0, 5) . ' WIB';
                } elseif ($ijinData->jam_terlambat) {
                    $jamKet = 'Pukul ' . substr($ijinData->jam_terlambat, 0, 5) . ' WIB';
                } elseif ($ijinData->created_at) {
                    $jamKet = Carbon::parse($ijinData->created_at)->format('H:i') . ' WIB';
                }

                if ($isArrived) {
                    // Guru sudah berada di sekolah! Otomatis bertambah ke Hadir di Sekolah
                    $hadirList[] = [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'role' => $teacher->role,
                        'catatan' => ($targetKey === 'Izin Terlambat' ? 'Datang Terlambat' : 'Kembali ke Sekolah') . " ($waktuTibaFormatted)",
                        'status_hadir' => 'terlambat_hadir',
                    ];
                } else {
                    // Masih belum tiba / izin aktif
                    $totalIzinCount++;
                }

                $kategoriList[$targetKey]['members'][] = [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'role' => $teacher->role,
                    'mapel' => $ijinData->mapel ?? '-',
                    'jam' => $jamKet,
                    'keterangan' => $ijinData->ket,
                    'attachment' => $ijinData->attachment,
                    'approval' => $ijinData->approval_status,
                    'ijin_id' => $ijinData->id,
                    'is_arrived' => $isArrived,
                    'waktu_tiba' => $waktuTibaFormatted,
                    'can_confirm' => ($targetKey === 'Izin Terlambat' || $targetKey === 'Izin Keluar Jam Dinas'),
                ];
            } else {
                // ATURAN UTAMA: Tanpa GPS, yang tidak izin otomatis Hadir di Sekolah!
                $hadirList[] = [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'role' => $teacher->role,
                    'catatan' => null,
                    'status_hadir' => 'tepat_waktu',
                ];
            }
        }

        $totalHadir = count($hadirList);
        $persenHadir = $totalTeachers > 0 ? round(($totalHadir / $totalTeachers) * 100, 1) : 0;
        $ma_pel = Mapel::all();

        return view('ijin.live', compact(
            'targetDateStr', 'hariIndonesia', 'allTeachers', 'totalTeachers',
            'hadirList', 'totalHadir', 'persenHadir', 'kategoriList', 'totalIzinCount', 'ma_pel'
        ));
    }
}