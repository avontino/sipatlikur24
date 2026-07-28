<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TahunAjaran;
use DB;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $data_tahun_ajaran = TahunAjaran::orderBy('tahun_ajaran', 'desc')
            ->orderBy('semester', 'desc')
            ->get();
        return view('tahun_ajaran.index', compact('data_tahun_ajaran'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required',
            'semester' => 'required|in:Ganjil,Genap',
        ]);

        // Check if combination already exists
        $exists = TahunAjaran::where('tahun_ajaran', $request->tahun_ajaran)
            ->where('semester', $request->semester)
            ->exists();

        if ($exists) {
            return redirect('/tahun-ajaran')->with('gagal', 'Tahun Ajaran dan Semester tersebut sudah terdaftar.');
        }

        TahunAjaran::create([
            'tahun_ajaran' => $request->tahun_ajaran,
            'semester' => $request->semester,
            'status' => 0 // Inactive by default
        ]);

        return redirect('/tahun-ajaran')->with('sukses', 'Tahun Ajaran & Semester Berhasil Ditambahkan');
    }

    public function toggleStatus($id)
    {
        $ta = TahunAjaran::findOrFail($id);
        if ($ta->status == 0) {
            // Nonaktifkan semua tahun ajaran lain agar hanya 1 yang aktif
            TahunAjaran::where('id', '!=', $id)->update(['status' => 0]);
            $ta->status = 1;
            
            // Perbarui session aktif
            session(['tahun_ajaran' => $ta->tahun_ajaran]);
            session(['semester' => $ta->semester]);
            session(['tahun_ajaran_id' => $ta->id]);
        } else {
            $ta->status = 0;
            // Jika dinonaktifkan, cari tahun ajaran lain yang aktif jika ada
            $activeOther = TahunAjaran::where('status', 1)->first();
            if ($activeOther) {
                session(['tahun_ajaran' => $activeOther->tahun_ajaran]);
                session(['semester' => $activeOther->semester]);
                session(['tahun_ajaran_id' => $activeOther->id]);
            } else {
                session()->forget(['tahun_ajaran', 'semester', 'tahun_ajaran_id']);
            }
        }
        $ta->save();

        return redirect('/tahun-ajaran')->with('sukses', 'Status Tahun Ajaran & Semester Berhasil Diubah');
    }

    public function delete($id)
    {
        $ta = TahunAjaran::findOrFail($id);
        $ta->delete();

        return redirect('/tahun-ajaran')->with('sukses', 'Tahun Ajaran & Semester Berhasil Dihapus');
    }
}
