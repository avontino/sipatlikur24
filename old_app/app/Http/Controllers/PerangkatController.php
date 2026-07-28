<?php

namespace App\Http\Controllers;

use App\Perangkat;
use App\User;
use App\Guru;
use Illuminate\Http\Request;

class PerangkatController extends Controller
{
   public function index(Request $request)
    {   
        if (auth()->user()->role=='guru' ){
            // Ambil semua data perangkat jika tidak ada aksi sinkron
        $perangkats = Perangkat::where('guru',auth()->user()->name)->get();
        
        // Tampilkan view perangkat
        return view('perangkat.index', compact('perangkats'));
        } else {
        // Jika aksi sinkron diklik
        if ($request->has('action') && $request->action == 'sinkron') {
            // Ambil semua pengguna dengan role 'guru'
            $gurus = User::where('role', 'guru')->get();




            // Update kolom 'guru' pada perangkat dengan nama guru dari tabel users
            foreach ($gurus as $guru) {
                // Perangkat::whereNull('guru')->insert(['guru' => $guru->name]);

Perangkat::updateOrCreate(
    [
        'guru' => $guru->name,
    ],
    [
        'updated_at' => now(),
    ]
);


Guru::updateOrCreate(
    [
        'guru' => $guru->name,
    ],
    [
        'updated_at' => now(),
    ]
);
            }

            // Redirect kembali ke halaman perangkat dengan pesan sukses
            return redirect()->route('perangkat.index')->with('sukses', 'Data perangkat berhasil disinkronkan dengan guru');
        }

        // Ambil semua data perangkat jika tidak ada aksi sinkron
        $perangkats = Perangkat::all();
        
        // Tampilkan view perangkat
        return view('perangkat.index', compact('perangkats'));
    }
    }
    public function store(Request $request)
    {
        $request->validate([
            'guru' => 'required|string|max:255',
            'tp' => 'nullable|url',
            'modul' => 'nullable|url',
            'media' => 'nullable|url',
            'penilaian' => 'nullable|url',
        ]);

        Perangkat::create($request->all());
        return redirect()->route('perangkat.index');
    }

    public function edit($id)
    {
        $perangkat = Perangkat::findOrFail($id);
        return response()->json($perangkat);
    }

    public function update(Request $request, $id)
{
    $perangkat = Perangkat::findOrFail($id);

    // Update hanya kolom yang diubah
    if ($request->has('guru')) {
        $perangkat->guru = $request->guru; // Update kolom guru
    }
    if ($request->has('tp')) {
        $perangkat->tp = $request->tp; // Update kolom tp
    }
    if ($request->has('modul')) {
        $perangkat->modul = $request->modul; // Update kolom modul
    }
    if ($request->has('media')) {
        $perangkat->media = $request->media; // Update kolom media
    }
    if ($request->has('penilaian')) {
        $perangkat->penilaian = $request->penilaian; // Update kolom penilaian
    }

    $perangkat->save();

    return redirect()->route('perangkat.index')->with('sukses', 'Data perangkat berhasil diperbarui');
}


    public function destroy($id)
    {
        $perangkat = Perangkat::findOrFail($id);
        $perangkat->delete();
        return redirect()->route('perangkat.index');
    }

    


}
