<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\KasusExport;
use Maatwebsite\Excel\Facades\Excel;

class KasusController extends Controller
{
    public function index(Request $request)
    {
        $data_kasus = \App\Models\Kasus::orderBy('created_at', 'desc')->get();
    	return view('kasus.index', ['data_kasus' => $data_kasus]);
    }

    public function tambahk()
    {	
    	return view('kasus.tambahkasus');
    }

    public function create(Request $request)
    {	
        $request->validate([
            'pelapor' => 'required|string',
            'kejadian' => 'required|string',
            'tempat' => 'required|string'
        ]);
    	  
    	\App\Models\Kasus::create([
            'pelapor' => $request->pelapor,
            'kejadian' => $request->kejadian,
            'tempat' => $request->tempat
        ]);

    	return back()->with('sukses','Pelaporan Kasus Berhasil Ditambahkan');
    }

    public function export() 
    {
        return Excel::download(new KasusExport, 'Kasus.xlsx');
    }

    public function update(Request $request)
    {
         $kasus=\App\Models\Kasus::findorFail($request->kasusid);
         $kasus->update($request->all());
         return redirect('/kasus')->with('sukses','Pelaporan Berhasil Diupdate');
    }

    public function delete($id)
    {
        $kasus=\App\Models\Kasus::find($id);
        $kasus->delete();
        return redirect('/kasus')->with('sukses','Pelaporan Kasus Berhasil Dihapus');
    }

}


