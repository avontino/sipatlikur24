<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\SiswaExport;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use App\Models\Kelas;


class SiswaController extends Controller
{

    public function index(Request $request)
    {	
        $ke_las    = Kelas::all();
        $user      = auth()->user();

        $query = \App\Models\Siswa::with('user');

        if ($rawTa = session('tahun_ajaran')) {
            $cleanTa = trim(preg_replace('/\s*\(.*\)/', '', $rawTa));
            $query->where(function($q) use ($rawTa, $cleanTa) {
                $q->where('tahun_ajaran', $rawTa)
                  ->orWhere('tahun_ajaran', 'LIKE', '%' . $cleanTa . '%');
            });
        }

        // Siswa hanya lihat datanya sendiri
        if ($user->role == 'siswa') {
            $query->where(function($q) use ($user) {
                $q->where('nama', $user->name)->orWhere('nis', $user->username);
            });
        }

        $view = $request->query('view');
        $isWali = $user->hasRole('walikelas') || $user->walikelas_kelas;
        $isOnlyWaliKelas = $isWali && !($user->hasRole('kurikulum') || $user->hasRole('admin') || $user->hasRole('lihat'));

        if ($view === 'walikelas' || $isOnlyWaliKelas) {
            $kelas = $user->walikelas_kelas ?: $user->name;
            $query->where('kelas', $kelas);
        }

        // Load semua data — DataTables yang handle search & paginasi
        $data_siswa = $query->orderBy('nama', 'asc')->get();

        return view('siswa.index', compact('data_siswa', 'ke_las'));
    }

    public function create(Request $request)
    {	
        $rawTa = session('tahun_ajaran');
        $rawSem = session('semester');

        if (!$rawTa || !$rawSem) {
            $activeTaObj = DB::table('tahun_ajaran')->where('status', 1)->first();
            if ($activeTaObj) {
                if (!$rawTa) {
                    $rawTa = $activeTaObj->tahun_ajaran;
                    session(['tahun_ajaran' => $rawTa]);
                }
                if (!$rawSem) {
                    $rawSem = $activeTaObj->semester;
                    session(['semester' => $rawSem]);
                }
            }
        }

        $cleanTa = $rawTa ? trim(preg_replace('/\s*\(.*\)/', '', $rawTa)) : '2026/2027';
        $tahun_ajaran = $cleanTa ?: '2026/2027';
        $semester = $rawSem ?: 'Ganjil';

        // Check if NIS already exists in current year
        $existingSiswa = \App\Models\Siswa::where('nis', $request->nis)
            ->where(function($q) use ($rawTa, $cleanTa) {
                if ($rawTa) {
                    $q->where('tahun_ajaran', $rawTa)
                      ->orWhere('tahun_ajaran', 'LIKE', '%' . $cleanTa . '%');
                }
            })
            ->first();

        if ($existingSiswa) {
            return redirect('/siswa')->with('gagal', 'Siswa dengan NIS tersebut sudah terdaftar di tahun ajaran ini.');
        }

        $data = $request->all();
        $data['tahun_ajaran'] = $tahun_ajaran;
        $data['semester'] = $semester;
        if (!isset($data['sakit']) || $data['sakit'] === '') $data['sakit'] = 0;
        if (!isset($data['ijin']) || $data['ijin'] === '') $data['ijin'] = 0;
        if (!isset($data['alpha']) || $data['alpha'] === '') $data['alpha'] = 0;
        if (!isset($data['dispen']) || $data['dispen'] === '') $data['dispen'] = 0;

    	\App\Models\Siswa::create($data);

        // Sync to users table (auto-create login account or update name)
        $exists = DB::table('users')->where('username', $request->nis)->exists();
        if (!$exists) {
            DB::table('users')->insert([
                'role' => 'siswa',
                'name' => $request->nama,
                'username' => $request->nis,
                'password' => bcrypt($request->nis),
                'needs_password_change' => 0,
                'remember_token' => \Illuminate\Support\Str::random(60),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            DB::table('users')
                ->where('username', $request->nis)
                ->update([
                    'name' => $request->nama,
                    'updated_at' => now()
                ]);
        }

        \App\Helpers\AuditLog::write('Menambahkan siswa baru: ' . $request->nama . ' (NIS: ' . $request->nis . ')');

    	return redirect('/siswa')->with('sukses','Siswa Berhasil Ditambahkan');
    }

    public function update(Request $request)
    {
        $siswa=\App\Models\Siswa::findorFail($request->siswaid);
        $oldNis = $siswa->nis;

        $siswa->update($request->all());

        // Update corresponding user in users table
        DB::table('users')
            ->where('username', $oldNis)
            ->update([
                'name' => $request->nama,
                'username' => $request->nis,
                'updated_at' => now()
            ]);

        \App\Helpers\AuditLog::write('Memperbarui data siswa: ' . $request->nama . ' (NIS: ' . $request->nis . ')');

        return redirect('/siswa')->with('sukses','Siswa Berhasil Diupdate');
    }

    public function delete($id)
    {
        $siswa=\App\Models\Siswa::find($id);
        if ($siswa) {
            \App\Helpers\AuditLog::write('Menghapus data siswa: ' . $siswa->nama . ' (NIS: ' . $siswa->nis . ')');
            // Delete corresponding user in users table
            DB::table('users')->where('username', $siswa->nis)->delete();
            $siswa->delete();
        }
        return redirect('/siswa')->with('sukses','Siswa Berhasil Dihapus');
    }

    public function daftar($nis)
    {	
    	// $ekstra=auth()->user()->role;
        $pilihan=auth()->user()->name;
        // $siswa=\App\Models\Siswa::where('nis','LIKE','%'.$nis.'%');
        

        // DB::table($ekstra)->insertUsing(['id','nis','nama','kelas','kelamin','pilihan','nilai','deskripsi','created_at','updated_at'],$siswa);

        \App\Models\Siswa::where('nis','LIKE','%'.$nis.'%')->update(['pilihan'=>$pilihan]);

        

        // $siswa->delete();

  //       $newTask = $siswa->replicate();
		// $newTask->save();
        
        return redirect('/siswa')->with('sukses','Siswa Berhasil Didaftarkan');
        
    }

    public function export(Request $request)
    {
        $kelas = $request->query('kelas');
        $view = $request->query('view');
        
        $isWali = auth()->user()->hasRole('walikelas') || auth()->user()->walikelas_kelas;
        $isOnlyWaliKelas = $isWali && !(auth()->user()->hasRole('kurikulum') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('lihat'));

        if ($view === 'walikelas' || $isOnlyWaliKelas) {
            $kelas = auth()->user()->walikelas_kelas ?: auth()->user()->name;
        }

        return Excel::download(new SiswaExport($kelas), 'Siswa.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\SiswaTemplateExport, 'template_import_siswa.xlsx');
    }

 //    public function import() 
	// {
 //    	return Excel::import(new SiswaImport);
	// }

	public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:5120'
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'Format file harus berupa .xlsx atau .xls.',
            'file.max' => 'Ukuran file tidak boleh lebih dari 5 MB.'
        ]);

        Excel::import(new SiswaImport, $request->file('file'));

        \App\Helpers\AuditLog::write('Mengimpor data siswa via Excel');
        
        return redirect('/siswa')->with('sukses','Siswa Berhasil Diupload');
    }

    public function updateIjin(Request $request)
{
    $jenis_ijin = $request->input('jenis_ijin');
    $jumlah = $request->input('jumlah');

    $kolom = '';

    switch ($jenis_ijin) {
        case 'Ijin Pesiar':
            $kolom = 'ip';
            break;
        case 'Ijin Bermalam':
            $kolom = 'ib';
            break;
        case 'Ijin Bermalam Resmi':
            $kolom = 'ibr';
            break;
        case 'Ijin Jalan':
            $kolom = 'ij';
            break;
        case 'Ijin Khusus':
            $kolom = 'ik';
            break;
    }

    if ($kolom != '') {
        DB::table('siswa')->update([$kolom => $jumlah]);
        return redirect('/siswa')->with('sukses','Data ijin berhasil diperbarui');
    } else {
        return back()->with('error', 'Jenis ijin tidak valid.');
    }
}

    public function resetPassword($id)
    {
        $siswa = \App\Models\Siswa::findOrFail($id);
        $user = \App\Models\User::where('username', $siswa->nis)->first();
        if ($user) {
            $tempPassword = \Illuminate\Support\Str::random(8);
            $user->password = bcrypt($tempPassword);
            $user->needs_password_change = 1;
            $user->save();
            
            \App\Helpers\AuditLog::write('Mereset password siswa: ' . $siswa->nama . ' (NIS: ' . $siswa->nis . ')');

            return redirect('/siswa')->with('sukses_reset', 'Password untuk siswa ' . $siswa->nama . ' berhasil direset sementara menjadi: ' . $tempPassword);
        }
        
        return redirect('/siswa')->with('gagal', 'Akun user untuk siswa tersebut tidak ditemukan.');
    }

}
