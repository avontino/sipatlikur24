<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\AbsenExport;
use Excel;
use App\Models\Siswa;
use App\Models\Kelas;
use DB;
use DateTime;

class AbsenController extends Controller
{
    public function index(Request $request)
    { 
        $tahun_ajaran = session('tahun_ajaran');
        $semester = session('semester');

        $sis_wa = Siswa::where('tahun_ajaran', $tahun_ajaran)->orderBy('nama','asc')->get();
        $ke_las = Kelas::all();

        $baseAbsen = \App\Models\Absen::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester);
            
        $view = $request->query('view');
        $isWali = auth()->user()->hasRole('walikelas') || auth()->user()->walikelas_kelas;
        $isKetuaKelas = auth()->user()->role === 'ketuakelas';
        $isOnlyWaliOrKetua = ($isWali || $isKetuaKelas) && !(auth()->user()->hasRole('kurikulum') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('lihat'));

        $isWaliView = ($view === 'walikelas' || $isOnlyWaliOrKetua);

        // Jika dalam mode Wali Kelas / Ketua Kelas, paksa filter hanya kelas perwaliannya saja
        if ($isWaliView) {
            $kelas = $isKetuaKelas ? auth()->user()->name : (auth()->user()->walikelas_kelas ?: auth()->user()->name);
            
            $sis_wa = Siswa::where('tahun_ajaran', $tahun_ajaran)->where('kelas', $kelas)->orderBy('nama','asc')->get();
            $ke_las = Kelas::where('kelas', $kelas)->get();

            $baseAbsen->where('kelas', $kelas);
            $sakit = (clone $baseAbsen)->where('ket','Sakit')->count();
            $ijin = (clone $baseAbsen)->where('ket','Ijin')->count();
            $alpha = (clone $baseAbsen)->where('ket','Alpha')->count();
            
            if ($request->get('action') == 'tanggal') {
                $data_absen = (clone $baseAbsen)->whereDate('created_at',$request->crtgl)->orderBy('created_at','desc')->get();
            } else {
                $data_absen = (clone $baseAbsen)->orderBy('created_at','desc')->get();
            }
            
            return view('absen.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las'))
                ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha);
        }

        // Mode siswa
        if (auth()->user()->role=='siswa') {
            $sakit = (clone $baseAbsen)->where('ket','Sakit')->where('nama',auth()->user()->name)->count();
            $ijin = (clone $baseAbsen)->where('ket','Ijin')->where('nama',auth()->user()->name)->count();
            $alpha = (clone $baseAbsen)->where('ket','Alpha')->where('nama',auth()->user()->name)->count();    
            $data_absen = (clone $baseAbsen)->where('nama',auth()->user()->name)->orderBy('created_at','desc')->get();
            
            return view('absen.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las'))
                ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha);
        }

        // Mode umum (Admin, Kurikulum, Staf, Guru Biasa)
        $sakit = (clone $baseAbsen)->where('ket','Sakit')->count();
        $ijin = (clone $baseAbsen)->where('ket','Ijin')->count();
        $alpha = (clone $baseAbsen)->where('ket','Alpha')->count();
        
        $action = $request->get('action');
        if ($action == 'kelas') {
            $baseAbsen->where('kelas', $request->kelas);
        } elseif ($action == 'tanggal') {
            $baseAbsen->whereDate('created_at', $request->crtgl);
        } elseif ($action == 'kelastgl') {
            $baseAbsen->where('kelas', $request->kelas)->whereDate('created_at', $request->crtgl);
        }
        
        $data_absen = $baseAbsen->orderBy('created_at','desc')->get();
        return view('absen.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las'))
            ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha);
    }

    public function create(Request $request)
    {   
        $tahun_ajaran = session('tahun_ajaran');
        $semester = session('semester');

        $user = auth()->user();
        $managedClass = $user->getManagedClass();
        if ($managedClass && $request->kelas !== $managedClass) {
            return redirect('/absen')->with('gagal', 'Anda hanya dapat menginput absensi untuk kelas perwalian Anda sendiri.');
        }

        // General validation of existence
        $existingAbsence = \App\Models\Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                    ->where('tahun_ajaran', $tahun_ajaran)
                                    ->where('semester', $semester)
                                    ->whereDate('created_at', $request->tgl)
                                    ->first();

        if ($existingAbsence) {
            $errMsg = 'Absensi Sudah Ada, Silahkan Cek Kembali';
            if ($request->ket == 'Sakit' || $request->ket == 'Ijin' || $request->ket == 'Alpha' || $request->ket == 'Dispen') {
                return redirect('/absen')->with('gagal', $errMsg);
            } else {
                return redirect('/absen')->with('error', $errMsg);
            }
        }

        // Get student data scoped by year
        $jumlahawal = DB::table('siswa')
            ->where('nama', 'LIKE', '%'.$request->nama.'%')
            ->where('tahun_ajaran', $tahun_ajaran)
            ->first();

        if ($request->ket == 'Sakit') {
            if ($jumlahawal) {
                DB::table('siswa')->where('id', $jumlahawal->id)->update(['sakit' => $jumlahawal->sakit + 1]);
            }
        } elseif ($request->ket == 'Ijin') {
            if ($jumlahawal) {
                DB::table('siswa')->where('id', $jumlahawal->id)->update(['ijin' => $jumlahawal->ijin + 1]);
            }
        } elseif ($request->ket == 'Alpha') {
            if ($jumlahawal) {
                DB::table('siswa')->where('id', $jumlahawal->id)->update(['alpha' => $jumlahawal->alpha + 1]);
            }
        } elseif ($request->ket == 'Dispen') {
            if ($jumlahawal) {
                DB::table('siswa')->where('id', $jumlahawal->id)->update(['dispen' => $jumlahawal->dispen + 1]);
            }
        }

        // Create attendance record
        \App\Models\Absen::create([
            'nama' => $request->nama,
            'kelas' => $request->kelas,
            'ket' => $request->ket,
            'tahun_ajaran' => $tahun_ajaran,
            'semester' => $semester,
            'created_at' => $request->tgl
        ]);

        return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');
    }

    public function update(Request $request)
    {
        $jadwal=\App\Models\Jadwal::findorFail($request->jadwalid);
        $jadwal->update($request->all());
        return redirect('/jadwal')->with('sukses','Jadwal Berhasil Diupdate');
    }

    public function delete($id)
    {
        $absen=\App\Models\Absen::findOrFail($id);
        $user = auth()->user();
        $managedClass = $user->getManagedClass();
        if ($managedClass && $absen->kelas !== $managedClass) {
            return redirect('/absen')->with('gagal', 'Anda hanya dapat menghapus absensi untuk kelas perwalian Anda sendiri.');
        }
        $tahun_ajaran = session('tahun_ajaran');

        $jumlahawal = DB::table('siswa')
            ->where('nama', 'LIKE', '%'.$absen->nama.'%')
            ->where('tahun_ajaran', $tahun_ajaran)
            ->first();

        if ($jumlahawal) {
            if ($absen->ket == 'Sakit') {
                DB::table('siswa')->where('id', $jumlahawal->id)->update(['sakit' => max(0, $jumlahawal->sakit - 1)]);
            } elseif ($absen->ket == 'Ijin') {
                DB::table('siswa')->where('id', $jumlahawal->id)->update(['ijin' => max(0, $jumlahawal->ijin - 1)]);
            } elseif ($absen->ket == 'Alpha') {
                DB::table('siswa')->where('id', $jumlahawal->id)->update(['alpha' => max(0, $jumlahawal->alpha - 1)]);
            } elseif ($absen->ket == 'Dispen') {
                DB::table('siswa')->where('id', $jumlahawal->id)->update(['dispen' => max(0, $jumlahawal->dispen - 1)]);
            }
        }

        $absen->delete();
        return redirect('/absen')->with('gagal','Absensi Berhasil Dihapus');
    }

    public function export(Request $request) 
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'kelas'      => 'nullable|string'
        ]);

        $query = \App\Models\Absen::query();

        if (session('tahun_ajaran')) {
            $query->where('tahun_ajaran', session('tahun_ajaran'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $endDate   = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->filled('kelas') && $request->kelas !== 'all') {
            $query->where('kelas', $request->kelas);
        }

        $data_absen = $query->orderBy('created_at', 'desc')->get();

        return \Excel::download(new AbsenExport($data_absen), 'Absen_Siswa.xlsx');
    }
}
