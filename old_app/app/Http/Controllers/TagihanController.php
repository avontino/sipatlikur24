<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tagihan;
use App\Exports\TagihanExport;
use App\Imports\TagihanImport;
use Maatwebsite\Excel\Facades\Excel;
use Auth;

class TagihanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Jika role adalah admin atau keuangan, ambil semua data tagihan
        if ($user->role == 'admin' || $user->role == 'keuangan') {
            $tagihan = Tagihan::all();
        } 
        // Jika role adalah siswa, ambil data tagihan berdasarkan username siswa yang sedang login
        else if ($user->role == 'siswa') {
            $tagihan = Tagihan::where('nis', $user->username)->get();
        }

        return view('tagihan.index', compact('tagihan'));
    }

    public function store(Request $request)
{
    $request->validate([
        'nis' => 'required',
        'nama' => 'required',
        'kelas' => 'required',
        'dana_komite' => 'required|integer',
        'tagihan_lain' => 'required|integer',
    ]);

    Tagihan::create($request->all());
    return redirect()->route('tagihan.index')->with('sukses', 'Data berhasil ditambahkan');
}

public function update(Request $request, $id)
{
    $request->validate([
        'nis' => 'required',
        'nama' => 'required',
        'kelas' => 'required',
        'dana_komite' => 'required|integer',
        'tagihan_lain' => 'required|integer',
    ]);

    $tagihan = Tagihan::findOrFail($id);
    $tagihan->update($request->all());
    return redirect()->route('tagihan.index')->with('sukses', 'Data berhasil diperbarui');
}

public function destroy($id)
{
    $tagihan = Tagihan::findOrFail($id);
    $tagihan->delete();
    return redirect()->route('tagihan.index')->with('sukses', 'Data berhasil dihapus');
}

public function export()
{
    return Excel::download(new TagihanExport, 'tagihan.xlsx');
}

    public function import(Request $request)
    {
        $request->validate([
             'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new TagihanImport, $request->file('file'));
        return redirect()->route('tagihan.index')->with('sukses', 'Data berhasil diimport');
    }
  
  public function deleteAll()
{
    // Menghapus semua data di tabel 'tagihan' menggunakan DELETE
    \DB::table('tagihan')->delete();
    
    return redirect()->route('tagihan.index')->with('sukses', 'Semua data tagihan berhasil dihapus.');
}

}
