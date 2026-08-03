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
        $request->validate([
            'tglmasuk' => 'required|date',
            'sia' => 'required|in:Sakit,Ijin,Alpha,Terlambat',
            'jumlah' => 'required',
            'attachment' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:2048'
        ], [
            'attachment.mimes' => 'Format lampiran harus berupa PDF, PNG, JPG, atau JPEG.',
            'attachment.max' => 'Ukuran lampiran maksimal adalah 2 MB.'
        ]);

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
        
        $data = $request->except('attachment');
        $data['user_id'] = auth()->user()->id;
        $data['approval_status'] = (auth()->user()->role == 'admin') ? 'approved' : 'pending';
        $data['tahun_ajaran'] = session('tahun_ajaran');
        $data['semester'] = session('semester');

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = 'permit_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/ijin_guru'), $fileName);
            $data['attachment'] = 'uploads/ijin_guru/' . $fileName;
        }
        
    	$ijin = \App\Models\Ijin::create($data);
        if ($data['approval_status'] === 'pending') {
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $u) {
                $u->sendNotification(
                    "Pengajuan Izin Guru Baru",
                    "Pengajuan Izin Guru Baru: " . auth()->user()->name . " ({$request->sia}). Mohon tinjau di menu Presensi & Izin Guru.",
                    '/ijin',
                    'ijin'
                );
            }
        }
    	return redirect('/tambahijin')->with('sukses','Ijin Berhasil Ditambahkan');
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
            $isGuru = ($userRole == 'guru');

            if ($request->filled('filter')) {
                $query = \App\Models\Ijin::whereDate('tglmasuk', $request->filter);
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

            if ($isGuru) {
                $hasUserId = \Illuminate\Support\Facades\Schema::hasColumn('ijin', 'user_id');
                $query->where(function($q) use ($user, $hasUserId) {
                    if ($hasUserId) {
                        $q->where('user_id', $user->id);
                    }
                    $q->orWhere('guru', 'LIKE', '%' . $user->name . '%');
                });
            }

            $data_ijin = $query->orderBy('created_at', 'desc')->get();

            return view('ijin.index', ['data_ijin' => $data_ijin], compact('ma_pel', 'gu_ru', 'ke_las'));
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
        $ijin=\App\Models\Ijin::find($id);
        $ijin->delete();
        return redirect('/ijin')->with('sukses','Ijin Berhasil Dihapus');
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
}