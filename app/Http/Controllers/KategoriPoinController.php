<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriPoin;

class KategoriPoinController extends Controller
{
    public function index(Request $request)
    {
        $kategori_poin = KategoriPoin::orderBy('nama_kategori', 'asc')->get();
        return view('kategori_poin.index', compact('kategori_poin'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'nama' => 'nullable|string|max:255',
            'nama_kategori' => 'nullable|string|max:255',
            'jenis' => 'required|in:pelanggaran,prestasi',
            'poin' => 'required|integer'
        ]);

        $data = $request->only(['jenis', 'poin', 'deskripsi']);
        $data['nama_kategori'] = $request->input('nama_kategori') ?: $request->input('nama');

        KategoriPoin::create($data);

        return redirect('/kategori-poin')->with('sukses', 'Kategori poin berhasil ditambahkan');
    }

    public function update(Request $request)
    {
        $request->validate([
            'kategoriid' => 'required|exists:kategori_poin,id',
            'nama' => 'nullable|string|max:255',
            'nama_kategori' => 'nullable|string|max:255',
            'jenis' => 'required|in:pelanggaran,prestasi',
            'poin' => 'required|integer'
        ]);

        $kategori = KategoriPoin::findOrFail($request->kategoriid);
        $data = $request->only(['jenis', 'poin', 'deskripsi']);
        $data['nama_kategori'] = $request->input('nama_kategori') ?: $request->input('nama');

        $kategori->update($data);

        return redirect('/kategori-poin')->with('sukses', 'Kategori poin berhasil diperbarui');
    }

    public function delete($id)
    {
        $kategori = KategoriPoin::findOrFail($id);
        $kategori->delete();

        return redirect('/kategori-poin')->with('sukses', 'Kategori poin berhasil dihapus');
    }
}
