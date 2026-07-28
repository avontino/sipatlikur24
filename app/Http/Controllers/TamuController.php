<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\TamuExport;
use Maatwebsite\Excel\Facades\Excel;

class TamuController extends Controller
{
    public function tamu(Request $request)
    {

		// if ($request->filled('filter')) {
  //   	$data_tamu= \App\Models\Tamu::whereDate('created_at','LIKE','%'.$request->filter.'%')->paginate();

  //       }  elseif($request->filled('cari')){ 

  //            $data_tamu= \App\Models\Tamu::
  //            where('nama','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
  //            ->orwhere('instansi','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
  //            ->orwhere('maksud','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
  //            ->paginate()
  //            ;
  //       }



  //       else{
  //   		$data_tamu= \App\Models\Tamu::orderBy('created_at','desc')->paginate(10);
   
  //   	}


        $data_tamu= \App\Models\Tamu::orderBy('created_at','desc')->get();
    	return view('tamu',['data_tamu' => $data_tamu]);
    }


    public function create(Request $request)
    {	
    	\App\Models\Tamu::create($request->all());
    	return redirect('/tamu')->with('sukses','Tamu Berhasil Ditambahkan');
    }


    public function export() 
    {
        return Excel::download(new tamuExport, 'Tamu.xlsx');
    }


    public function update(Request $request)
    {

        $tamu=\App\Models\Tamu::findorFail($request->tamuid);
        $tamu->update($request->all());
        return redirect('/tamu')->with('sukses','Tamu Berhasil Diupdate');
        // dd($request->all());
    }

    public function delete($id)
    {
        $tamu=\App\Models\Tamu::find($id);
        $tamu->delete();
        return redirect('/tamu')->with('sukses','Tamu Berhasil Dihapus');
    }
}
