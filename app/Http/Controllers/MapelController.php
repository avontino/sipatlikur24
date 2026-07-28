<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mapel;

class MapelController extends Controller
{
    public function index()
    {
        $data_mapel = Mapel::all();
        return view('mapel.index', compact('data_mapel'));
    }

    public function create(Request $request)
    {
        Mapel::create($request->all());
        return redirect('/mapel')->with('sukses', 'Data Mata Pelajaran Berhasil Ditambahkan');
    }

    public function update(Request $request)
    {
        $mapel = Mapel::findOrFail($request->mapelid);
        $mapel->update($request->all());
        return redirect('/mapel')->with('sukses', 'Data Mata Pelajaran Berhasil Diupdate');
    }

    public function delete($id)
    {
        $mapel = Mapel::findOrFail($id);
        $mapel->delete();
        return redirect('/mapel')->with('sukses', 'Data Mata Pelajaran Berhasil Dihapus');
    }
}
