<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\IjinsiswaExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Ijinsiswa;
use App\Models\Siswa;
use DB;

class IjinsiswaController extends Controller
{
    public function index(Request $request)
    {
        $tahun_ajaran = session('tahun_ajaran');
        $view = $request->query('view');
        $user = auth()->user();

        // ── DataTables Server-Side Handler ──
        if ($request->ajax() || $request->has('draw')) {
            $query = Ijinsiswa::query();

            if ($user->role == 'siswa') {
                $query->where('nama', $user->name)->where('tahun_ajaran', $tahun_ajaran);
            } elseif ($view === 'walikelas' || (($user->hasRole('walikelas') || $user->walikelas_kelas) && !($user->hasRole('kurikulum') || $user->hasRole('admin') || $user->hasRole('lihat')))) {
                $kelas = $user->walikelas_kelas ?: $user->name;
                $query->where('kelas', $kelas)->where('tahun_ajaran', $tahun_ajaran);
            } else {
                $query->where('tahun_ajaran', $tahun_ajaran);
            }

            $totalRecords = $query->count();

            // Global search
            if ($searchValue = $request->input('search.value')) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('nama', 'LIKE', "%{$searchValue}%")
                      ->orWhere('kelas', 'LIKE', "%{$searchValue}%")
                      ->orWhere('ketijin', 'LIKE', "%{$searchValue}%")
                      ->orWhere('ket', 'LIKE', "%{$searchValue}%")
                      ->orWhere('filex', 'LIKE', "%{$searchValue}%");
                });
            }

            $filteredRecords = $query->count();

            $start  = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));

            $rows = $query->orderBy('created_at', 'desc')->skip($start)->take($length)->get();

            $isAdmin    = $user->hasRole('admin')    || $user->role == 'admin';
            $isPembina  = $user->hasRole('pembina')  || $user->role == 'pembina';
            $isKurikulum= $user->hasRole('kurikulum')|| $user->role == 'kurikulum';
            $isWali     = $user->hasRole('walikelas')|| $user->walikelas_kelas;
            $isKesehatan= $user->hasRole('kesehatan')|| $user->role == 'kesehatan';
            $isKepala   = $user->hasRole('kepala')   || $user->role == 'kepala';
            $isSiswa    = $user->role == 'siswa';
            $isSatpam   = $user->role == 'satpam';
            $showPembina    = ($isPembina || $isAdmin) && !$isSiswa && (!$request->filled('view') || $view === 'pembina');
            $showKurikulum  = ($isKurikulum || $isAdmin) && !$isSiswa && (!$request->filled('view') || $view === 'kurikulum');
            $showWali       = ($isWali || $isAdmin) && !$isSiswa && (!$request->filled('view') || $view === 'walikelas');
            $showKesehatan  = ($isKesehatan || $isAdmin) && !$isSiswa && (!$request->filled('view') || $view === 'kesehatan');

            $resultData = [];
            foreach ($rows as $ij) {
                $row = [];

                // Nama & Kelas (hanya jika bukan siswa)
                if (!$isSiswa) {
                    $row['nama']  = e($ij->nama);
                    $row['kelas'] = e($ij->kelas);
                }

                $row['ketijin'] = e($ij->ketijin);

                // Kepala Sekolah col
                if (($isKepala || $isAdmin) && !$isSiswa) {
                    $aksiKepala = '';
                    if ($ij->filex != 'Surat Salah') {
                        $aksiKepala .= '<a href="/ijinsiswa/'.$ij->id.'/verifikasi?as_role=kepala" class="btn btn-success btn-sm">Ijinkan</a> ';
                    }
                    $aksiKepala .= '<a href="/ijinsiswa/'.$ij->id.'/suratsalah?as_role=kepala" class="btn btn-danger btn-sm">Surat Salah</a>';
                    $row['kepala_aksi'] = $aksiKepala;
                }

                // Pembina status
                $row['ok_pembina'] = $ij->ok_pembina == 'belum'
                    ? '<td style="background-color:#ff0000" align="center"><span class="fas fa-minus-square"></span></td>'
                    : '<td style="background-color:#32CD32" align="center"><span class="fas fa-check-square"></span></td>';
                $row['ok_pembina_status'] = $ij->ok_pembina;

                if ($showPembina) {
                    $aksi = '';
                    if ($ij->filex != 'Surat Salah') {
                        $aksi .= '<a href="/ijinsiswa/'.$ij->id.'/verifikasi?as_role=pembina" class="btn btn-success btn-sm">Ijinkan</a> ';
                    }
                    $aksi .= '<a href="/ijinsiswa/'.$ij->id.'/suratsalah?as_role=pembina" class="btn btn-danger btn-sm">Surat Salah</a>';
                    $row['pembina_aksi'] = $aksi;
                }

                // Kurikulum status
                $row['ok_kurikulum_status'] = $ij->ok_kurikulum;
                if ($showKurikulum) {
                    $aksi = '';
                    if ($ij->filex != 'Surat Salah') {
                        $aksi .= '<a href="/ijinsiswa/'.$ij->id.'/verifikasi?as_role=kurikulum" class="btn btn-success btn-sm">Ijinkan</a> ';
                    }
                    $aksi .= '<a href="/ijinsiswa/'.$ij->id.'/suratsalah?as_role=kurikulum" class="btn btn-danger btn-sm">Surat Salah</a>';
                    $row['kurikulum_aksi'] = $aksi;
                }

                // Wali Kelas status
                $row['ok_walikelas_status'] = $ij->ok_walikelas;
                if ($showWali) {
                    $aksi = '';
                    if ($ij->filex != 'Surat Salah') {
                        $aksi .= '<a href="/ijinsiswa/'.$ij->id.'/verifikasi?as_role=walikelas" class="btn btn-success btn-sm">Ijinkan</a> ';
                    }
                    $aksi .= '<a href="/ijinsiswa/'.$ij->id.'/suratsalah?as_role=walikelas" class="btn btn-danger btn-sm">Surat Salah</a>';
                    $row['walikelas_aksi'] = $aksi;
                }

                // Kesehatan status
                $row['ok_kesehatan_status'] = $ij->ok_kesehatan;
                if ($showKesehatan) {
                    $aksi = '';
                    if ($ij->filex != 'Surat Salah') {
                        $aksi .= '<a href="/ijinsiswa/'.$ij->id.'/verifikasi?as_role=kesehatan" class="btn btn-success btn-sm">Ijinkan</a> ';
                    }
                    $aksi .= '<a href="/ijinsiswa/'.$ij->id.'/suratsalah?as_role=kesehatan" class="btn btn-danger btn-sm">Surat Salah</a>';
                    $row['kesehatan_aksi'] = $aksi;
                }

                // Waktu ijin
                $row['created_at'] = $ij->created_at ? $ij->created_at->format('d M Y - H:i:s') : '-';

                // Lihat surat
                if ($ij->file_path) {
                    $row['lihat_surat'] = '<a href="#" class="btn btn-info btn-sm" onclick="showLihatFileModal(\''.asset($ij->file_path).'\', \''.addslashes($ij->nama).'\'); return false;">Lihat</a>';
                } else {
                    $row['lihat_surat'] = '<span class="text">Tidak Ada</span>';
                }

                // Berangkat
                $allOk = ($ij->ok_pembina=='ok' && $ij->ok_kurikulum=='ok' && $ij->ok_walikelas=='ok' && $ij->ok_kesehatan=='ok');
                if ($allOk) {
                    if ($isSatpam) {
                        $row['berangkat'] = !$ij->cekout
                            ? '<a href="/ijinsiswa/'.$ij->id.'/cekout" class="btn btn-primary btn-sm">Berangkat</a>'
                            : \Carbon\Carbon::parse($ij->cekout)->format('d M Y - H:i:s');
                    } else {
                        $row['berangkat'] = $ij->cekout ? \Carbon\Carbon::parse($ij->cekout)->format('d M Y - H:i:s') : '<span class="text-danger">Belum Berangkat</span>';
                    }
                } else {
                    $row['berangkat'] = '<span class="text-danger">Belum Semua Disetujui</span>';
                }

                $row['durasi'] = e($ij->durasi ?? '-');

                // Kembali
                $kembali = '';
                if ($ij->cekout) {
                    if (!$ij->cekin) {
                        $kembali = $isSatpam
                            ? '<button type="button" class="btn btn-warning btn-sm" data-myid="'.$ij->id.'" data-bs-toggle="modal" data-bs-target="#modalCekIn">Kembali</button>'
                            : '<span class="text-danger">Belum Kembali</span>';
                    } else {
                        $kembali = \Carbon\Carbon::parse($ij->cekin)->format('d M Y - H:i:s');
                    }
                }
                $row['kembali'] = $kembali;

                // Bukti
                $row['bukti'] = $ij->file_bukti
                    ? '<a href="'.asset($ij->file_bukti).'" target="_blank" class="btn btn-sm btn-secondary">Lihat</a>'
                    : '-';

                // Keterangan & overtime & surat
                $row['keterangan'] = e($ij->keterangan ?? '-');
                $row['overtime']   = e($ij->overtime ?? '-');
                $row['filex']      = e($ij->filex ?? '-');

                // Aksi
                $aksiHtml = '';
                if ($isAdmin) {
                    $aksiHtml = '<a href="/ijinsiswa/'.$ij->id.'/delete" class="btn btn-danger btn-sm" onclick="return confirm(\'Hapus data ini?\')">Hapus</a>';
                }
                $row['aksi'] = $aksiHtml;

                $resultData[] = $row;
            }

            return response()->json([
                'draw'            => intval($request->input('draw')),
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data'            => $resultData,
            ]);
        }

        // ── Render halaman HTML biasa (kosong, DataTables akan isi via AJAX) ──
        return view('ijinsiswa.index', ['data_ijinsiswa' => collect()]);
    }


    public function verifikasi(Request $request, $id)
{
    $ijinsiswa = Ijinsiswa::find($id);
    if (!$ijinsiswa) {
        return redirect()->back()->with('gagal', 'Data ijin tidak ditemukan');
    }

    $user = auth()->user();
    $asRole = $request->query('as_role');

    // Admin / Kepala Sekolah can approve all levels directly
    if ($user->role === 'admin' || $user->role === 'kepala' || $user->hasRole('admin') || $user->hasRole('kepala') || $asRole === 'kepala') {
        Ijinsiswa::where('id', $id)->update([
            'ok_walikelas' => 'ok',
            'okbin' => 'ok',
            'ok_pembina' => 'ok',
            'oksis' => 'ok',
            'ok_kurikulum' => 'ok',
            'okkur' => 'ok',
            'ok_kesehatan' => 'ok',
            'okas' => 'ok',
            'filex' => 'Surat Sesuai',
        ]);
        try { $this->dispatchIjinNotification($id); } catch (\Throwable $e) {}
        return redirect()->back()->with('sukses', 'Verifikasi Izin Berhasil (Disetujui)');
    }

    // All teachers act as Guru Piket by default, or Wali Kelas if they are Wali Kelas for that student's class
    if ($asRole === 'walikelas' || ($user->walikelas_kelas && $user->walikelas_kelas == $ijinsiswa->kelas && $asRole !== 'piket')) {
        Ijinsiswa::where('id', $id)->update([
            'ok_walikelas' => 'ok',
            'okbin' => 'ok',
        ]);
    } else {
        // All teachers (role 'guru'), piket, pembina approve as Guru Piket
        Ijinsiswa::where('id', $id)->update([
            'ok_pembina' => 'ok',
            'oksis' => 'ok',
        ]);
    }

    // If both Wali Kelas and Guru Piket have approved, automatically mark Surat Sesuai
    $ijinUpdated = Ijinsiswa::find($id);
    $walikelasOk = (($ijinUpdated->ok_walikelas ?? $ijinUpdated->okbin) === 'ok');
    $piketOk     = (($ijinUpdated->ok_pembina ?? $ijinUpdated->oksis) === 'ok');

    if ($walikelasOk && $piketOk) {
        Ijinsiswa::where('id', $id)->update(['filex' => 'Surat Sesuai']);
    }

    try {
        $this->dispatchIjinNotification($id);
    } catch (\Throwable $e) {
        \Log::error("Failed to dispatch notification: " . $e->getMessage());
    }

    return redirect()->back()->with('sukses', 'Verifikasi Izin Berhasil Disimpan');
}

public function suratsalah(Request $request, $id)
{   
    $ijinsiswa = Ijinsiswa::find($id);
    if (!$ijinsiswa) {
        return redirect()->back()->with('gagal', 'Data ijin tidak ditemukan');
    }

    Ijinsiswa::where('id', $id)->update(['filex' => 'Surat Salah']);

    $student = \App\Models\User::where('role', 'siswa')->where('name', $ijinsiswa->nama)->first();
    if ($student) {
        try {
            $student->sendNotification(
                "Izin Ditolak (Surat Salah)",
                "Pengajuan {$ijinsiswa->ketijin} Anda ditolak (Surat Salah). Harap perbaiki / unggah ulang bukti surat.",
                '/ijinsiswa/tambah',
                'ijin'
            );
        } catch (\Throwable $e) {}
    }
    
    return redirect()->back()->with('sukses', 'Pengajuan Izin Ditandai Surat Salah');
}

    
    

    public function tambah()
    {   
        $user = auth()->user();
        $tahunAjaran = session('tahun_ajaran') ?: '2026/2027';
        $siswa = Siswa::where('nama', $user->name)->where('tahun_ajaran', $tahunAjaran)->first();
        if (!$siswa) {
            $siswa = Siswa::where('nis', $user->username)->orWhere('nama', $user->name)->first();
        }

        if (!$siswa) {
            $siswa = (object)[
                'kelas' => $user->kelas ?? '-',
                'nama' => $user->name
            ];
        }

        $data_ijinsiswa = Ijinsiswa::where('nama', $user->name)->orderBy('created_at', 'desc')->get();
       
        return view('ijinsiswa.tambahijinsiswa', compact('siswa', 'data_ijinsiswa'));
    }


    public function create(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required',
                'kelas' => 'required',
                'ijin' => 'required',
            ]);

            if (!$request->hasFile('file')) {
                return redirect()->back()->with('gagal', 'Wajib melampirkan foto / bukti surat izin!');
            }

            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension() ?: '');
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowedExts)) {
                return redirect()->back()->with('gagal', 'File yang diunggah harus berupa foto / gambar (JPG, JPEG, PNG)!');
            }

            $ijinsiswa = new IjinSiswa();
            $ijinsiswa->nama = $request->input('nama');
            $ijinsiswa->kelas = $request->input('kelas');
            $ijinsiswa->ketijin = $request->input('ijin');

            // Set default unverified status ('belum') for all authority fields
            $ijinsiswa->ok_pembina = 'belum';
            $ijinsiswa->ok_kurikulum = 'belum';
            $ijinsiswa->ok_walikelas = 'belum';
            $ijinsiswa->ok_kesehatan = 'belum';

            $ijinsiswa->oksis = 'belum';
            $ijinsiswa->okkur = 'belum';
            $ijinsiswa->okbin = 'belum';
            $ijinsiswa->okas = 'belum';

            $ijinsiswa->filex = 'Menunggu Verifikasi';
            $ijinsiswa->tahun_ajaran = session('tahun_ajaran') ?: '2026/2027';

            $uploadDir = public_path('uploads');
            if (!\File::exists($uploadDir)) {
                \File::makeDirectory($uploadDir, 0777, true, true);
            }

            $imageName = 'file_' . time() . '_' . uniqid() . '.' . ($ext ?: 'jpg');
            $file->move($uploadDir, $imageName);
            $ijinsiswa->file_path = '/uploads/' . $imageName;

            $ijinsiswa->save();

            try {
                $this->dispatchIjinNotification($ijinsiswa->id);
            } catch (\Throwable $e) {
                \Log::error("Failed to dispatch Ijin notification: " . $e->getMessage());
            }

            return redirect()->back()->with('sukses', 'Ijin siswa berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error("Error adding student permission: " . $e->getMessage());
            return redirect()->back()->with('gagal', 'Gagal menyimpan data ijin: ' . $e->getMessage());
        }
    }
    
    public function uploadUlang(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);

        $ijinsiswa = IjinSiswa::find($id);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $uploadDir = public_path('storage/uploads');
            if (!\File::exists($uploadDir)) {
                \File::makeDirectory($uploadDir, 0777, true, true);
            }

            $imageName = 'file_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $imageName);

            $ijinsiswa->filex = 'Surat Sesuai';
            $ijinsiswa->file_path = '/storage/uploads/' . $imageName;
            $ijinsiswa->save();      

            return redirect()->back()->with('sukses', 'File ijin berhasil diunggah ulang!');
        }

        return redirect()->back()->with('gagal', 'File gagal diunggah ulang!');
    }



public function export(Request $request)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'kelas' => 'nullable|string'
    ]);

    $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
    $endDate   = \Carbon\Carbon::parse($request->end_date)->endOfDay();

    // Mengambil data berdasarkan filter yang dipilih
    $query = Ijinsiswa::whereBetween('created_at', [$startDate, $endDate]);

    if (session('tahun_ajaran')) {
        $query->where('tahun_ajaran', session('tahun_ajaran'));
    }

    if ($request->filled('kelas') && $request->kelas !== 'all') {
        $query->where('kelas', $request->kelas);
    }

    $data_ijinsiswa = $query->orderBy('created_at', 'desc')->get();

    // Menggunakan export dengan data yang sudah difilter
    return Excel::download(new IjinsiswaExport($data_ijinsiswa), 'data_ijinsiswa.xlsx');
}

    public function edit($id)
    {   
        $kasus=\App\Models\Kasus::find($id);
        return view('kasus/edit',['kasus'=>$kasus]);
    }

    public function update(Request $request)
    {   
        $user1 = \App\Models\User::findorFail($request->opid);
        DB::table('gu_ru')->where('guru','LIKE',$user1->name)->update(['guru'=>$request->name]);

    	$user = \App\Models\User::findorFail($request->opid);
    	$user->role=$request->role;
    	$user->name=$request->name;
    	$user->username=$request->username;
    	$user->password=bcrypt($request->password);
    	$user->remember_token=\Illuminate\Support\Str::random(60);
    	$user->save();

        // $operator=\App\Models\User::findorFail($request->opid);
        // $operator->update($request->all());
        return redirect('/operator')->with('sukses','Operator Berhasil Diupdate');
        // dd($request->all());
    }

    public function delete($id)
    {
        $ijinsiswa = Ijinsiswa::find($id);
        $ijinsiswa->delete();
        return redirect('/ijinsiswa')->with('sukses','Ijin Siswa Berhasil Dihapus');
    }

        public function import() 
    {
        Excel::import(new OperatorImport, request()->file('file'));
        
        return redirect('/operator')->with('sukses','Operator Berhasil Diupload');
    }

    public function cekout($id)
{
    // Find the Ijinsiswa record
    $ijinsiswa = Ijinsiswa::find($id);
    
    if (!$ijinsiswa) {
        return redirect('/ijinsiswa')->with('gagal', 'Data ijin tidak ditemukan');
    }

    // Update cekout time
    $ijinsiswa->cekout = now();

    // Calculate the duration based on ketijin
    $durasi = $this->calculateDuration($ijinsiswa->ketijin);

    // Store the duration in the durasi column
    $ijinsiswa->durasi = $durasi;

    // Save the updated record
    $ijinsiswa->save();

    // Find the corresponding Siswa record
    $siswa = Siswa::where('nama', $ijinsiswa->nama)->where('tahun_ajaran', session('tahun_ajaran'))->first();
    if (!$siswa) {
        return redirect('/ijinsiswa')->with('gagal', 'Data siswa tidak ditemukan');
    }

    // Determine which column to decrement based on ketijin
    switch ($ijinsiswa->ketijin) {
        case 'Ijin Pesiar':
            if ($siswa->ip > 0) {
                $siswa->decrement('ip');
                $siswa->save();
            }
            break;
        case 'Ijin Bermalam':
            if ($siswa->ib > 0) {
                $siswa->decrement('ib');
                $siswa->save();
            }
            break;
        case 'Ijin Bermalam Resmi':
            if ($siswa->ibr > 0) {
                $siswa->decrement('ibr');
                $siswa->save();
            }
            break;
        case 'Ijin Jalan':
            if ($siswa->ij > 0) {
                $siswa->decrement('ij');
                $siswa->save();
            }
            break;
        case 'Ijin Khusus':
            if ($siswa->ik > 0) {
                $siswa->decrement('ik');
                $siswa->save();
            }
            break;
        default:
            return redirect('/ijinsiswa')->with('gagal', 'Jenis ijin tidak dikenal');
    }

    return redirect('/ijinsiswa')->with('sukses', 'Cek Out Berhasil');
}

private function calculateDuration($ketijin)
{
    $durasi = '00:00:00'; // Default value

    switch ($ketijin) {
        case 'Ijin Pesiar':
            $durasi = '08:00:00';
            break;
        case 'Ijin Bermalam':
       		$durasi = '48:00:00';
            break;
        case 'Ijin Bermalam Resmi':
        case 'Ijin GH Resmi':
            $durasi = '48:00:00';
            break;
        case 'Ijin Jalan':
            $durasi = '05:00:00';
            break;
        case 'Ijin Khusus':
            $durasi = '00:00:00';
            break;
        default:
            // Handle unknown jenis ijin if needed
            break;
    }

    return $durasi;
}



public function cekin(Request $request, $id)
{
    // Find the Ijinsiswa record
    $ijinsiswa = Ijinsiswa::find($id);
    
    if (!$ijinsiswa) {
        return redirect('/ijinsiswa')->with('gagal', 'Data ijin tidak ditemukan');
    }

    // Check if cekout is set before allowing cek in
    if (!$ijinsiswa->cekout) {
        return redirect('/ijinsiswa')->with('gagal', 'Silakan cek out terlebih dahulu');
    }

    // Update cekin time
    $ijinsiswa->cekin = now();

    // Calculate the time difference
    $cekoutTime = \Carbon\Carbon::parse($ijinsiswa->cekout);
    $cekinTime = \Carbon\Carbon::parse($ijinsiswa->cekin);
    $timeDifference = $cekinTime->diffInMinutes($cekoutTime); // Time difference in minutes

    // Determine the allowed duration based on ketijin
    $allowedDuration = $this->getAllowedDuration($ijinsiswa->ketijin);

    // Check if duration is 0
    if ($allowedDuration == 0) {
        $ijinsiswa->ket = 'Tepat Waktu';
        $ijinsiswa->wkt = 0; // No overtime
    } else {
        // Check if time difference exceeds the allowed duration
        if ($timeDifference > $allowedDuration) {
            $ijinsiswa->ket = 'Overtime';
            $ijinsiswa->wkt = $timeDifference; // Store the time difference
        } else {
            $ijinsiswa->ket = 'Tepat Waktu';
            $ijinsiswa->wkt = 0; // Store 0 if it's on time
        }
    }

    // Process the uploaded photo
    if ($request->has('file_bukti')) {
        // Decode the base64 image
        $image = $request->input('file_bukti');
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'bukti_' . time() . '.png';
        \File::put(public_path('/storage/uploads/' . $imageName), base64_decode($image));

        // Save image path to database
        $ijinsiswa->file_bukti = '/storage/uploads/' . $imageName;
    }

    // Save the updated record
    $ijinsiswa->save();

    return redirect('/ijinsiswa')->with('sukses', 'Cek In Berhasil');
}

private function getAllowedDuration($ketijin)
{
    $durasi = 0; // Default value in minutes

    switch ($ketijin) {
        case 'Ijin Pesiar':
            $durasi = 480; // 8 hours in minutes
            break;
        case 'Ijin Bermalam':
        case 'Ijin GH Wajib':
        case 'Ijin Bermalam Resmi':
        case 'Ijin GH Resmi':
            $durasi = 2880; // 48 hours in minutes
            break;
        case 'Ijin Jalan':
            $durasi = 300; // 5 hours in minutes
            break;
        case 'Ijin Khusus':
            $durasi = 0; // No duration required
            break;
        default:
            // Handle unknown jenis ijin if needed
            break;
    }

    return $durasi;
}

private function dispatchIjinNotification($id)
{
    $ijin = \App\Models\Ijinsiswa::find($id);
    if (!$ijin) return;

    $isApproved = ($ijin->ok_pembina === 'ok' && $ijin->ok_kurikulum === 'ok' && $ijin->ok_walikelas === 'ok' && $ijin->ok_kesehatan === 'ok');

    if ($isApproved) {
        // Send notification to Student
        $student = \App\Models\User::where('role', 'siswa')->where('name', $ijin->nama)->first();
        if ($student) {
            $student->sendNotification(
                "Izin Disetujui Sepenuhnya",
                "Pengajuan {$ijin->ketijin} Anda telah disetujui sepenuhnya. Anda sekarang dapat berangkat.",
                '/ijinsiswa',
                'ijin'
            );
        }
        return;
    }

    // Determine who is next
    if ($ijin->ketijin === 'Ijin Pesiar') {
        if ($ijin->ok_pembina === 'belum') {
            // Send to Pembina
            $pembinas = \App\Models\User::where('role', 'pembina')->get();
            foreach ($pembinas as $u) {
                $u->sendNotification("Verifikasi Ijin Pesiar", "Pengajuan Ijin Pesiar: {$ijin->nama} ({$ijin->kelas}) memerlukan verifikasi Pembina.", '/ijinsiswa', 'ijin');
            }
        }
        if ($ijin->ok_walikelas === 'belum') {
            // Send to Kesehatan
            $kesehatans = \App\Models\User::where('role', 'kesehatan')->get();
            foreach ($kesehatans as $u) {
                $u->sendNotification("Verifikasi Ijin Pesiar", "Pengajuan Ijin Pesiar: {$ijin->nama} ({$ijin->kelas}) memerlukan verifikasi Tim Kesehatan.", '/ijinsiswa', 'ijin');
            }
        }
    } else {
        // Bermalam / Bermalam Resmi / Jalan
        if ($ijin->ok_walikelas === 'belum') {
            // Send to Wali Kelas
            $wk = \App\Models\User::where(function($q) use ($ijin) {
                $q->where('role', 'walikelas')->where('name', $ijin->kelas);
            })->orWhere(function($q) use ($ijin) {
                $q->where('role', 'guru')->where('walikelas_kelas', $ijin->kelas);
            })->get();
            foreach ($wk as $u) {
                $u->sendNotification("Verifikasi Izin Siswa", "Pengajuan {$ijin->ketijin}: {$ijin->nama} ({$ijin->kelas}) memerlukan verifikasi Wali Kelas.", '/ijinsiswa?view=walikelas', 'ijin');
            }
        } elseif ($ijin->ok_kurikulum === 'belum') {
            // Send to Kurikulum
            $kurikulums = \App\Models\User::where('role', 'kurikulum')->get();
            foreach ($kurikulums as $u) {
                $u->sendNotification("Verifikasi Izin Siswa", "Pengajuan {$ijin->ketijin}: {$ijin->nama} ({$ijin->kelas}) telah disetujui Wali Kelas. Mohon verifikasi Kurikulum.", '/ijinsiswa', 'ijin');
            }
        } elseif ($ijin->ok_pembina === 'belum') {
            // Send to Pembina
            $pembinas = \App\Models\User::where('role', 'pembina')->get();
            foreach ($pembinas as $u) {
                $u->sendNotification("Verifikasi Izin Siswa", "Pengajuan {$ijin->ketijin}: {$ijin->nama} ({$ijin->kelas}) telah disetujui Kurikulum. Mohon verifikasi Pembina.", '/ijinsiswa', 'ijin');
            }
        }
    }
}
}


