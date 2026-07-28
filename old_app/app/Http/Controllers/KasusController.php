<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\KasusExport;
use Maatwebsite\Excel\Facades\Excel;

class KasusController extends Controller
{
    public function index(Request $request)
    {

    	// if ($request->filled('filter')) {
    	// 	$data_kasus= \App\Kasus::whereDate('created_at','LIKE','%'.$request->filter.'%')->orderBy('created_at','desc')->paginate();

     //    }  elseif($request->filled('cari')){ 

     //         $data_kasus= \App\Kasus::
     //         where('pelapor','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
     //         ->orwhere('kejadian','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
     //         ->orwhere('tempat','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
     //         ->paginate()
     //         ;
     //    }



     //    else{
    	// 	$data_kasus= \App\Kasus::orderBy('created_at','desc')->paginate(10);
   
    	// }


        $data_kasus= \App\Kasus::orderBy('created_at','desc')->get();
    	return view('kasus.index',['data_kasus' => $data_kasus]);
    }

    public function tambahk()
    {	
    	return view('kasus.tambahkasus');
    }

    public function lihatk(Request $request)
    {
    	//$data_jurnal= \App\Jurnal::all();
        
        // if($request->has('cari')){ 

        //      $data_kasus= \App\Kasus::
        //      where('pelapor','LIKE','%'.$request->cari.'%')->whereDate('created_at',now())->orderBy('created_at','desc')
        //      ->orwhere('kejadian','LIKE','%'.$request->cari.'%')->whereDate('created_at',now())->orderBy('created_at','desc')
        //      ->orwhere('tempat','LIKE','%'.$request->cari.'%')->whereDate('created_at',now())->orderBy('created_at','desc')
        //      ->get();
        // } else {
        
        //      $data_kasus= \App\Kasus::whereDate('created_at',now())->orderBy('created_at','desc')->get();
        // }

         $data_kasus= \App\Kasus::whereDate('created_at',now())->orderBy('created_at','desc')->get();

    	return view('kasus.lihatkasus',['data_kasus' => $data_kasus]);
    }

    public function create(Request $request)
    {	
    	  
    	\App\Kasus::create($request->all());
    	return back()->with('sukses','Pelaporan Kasus Berhasil Ditambahkan');
    }


    public function export() 
    {
        return Excel::download(new KasusExport, 'Kasus.xlsx');
    }

    // public function edit($id)
    // {   
    //     $kasus=\App\Kasus::find($id);
    //     return view('kasus/edit',['kasus'=>$kasus]);
    // }

    public function update(Request $request)
    {
        // $kasus=\App\Kasus::find($id);
         $kasus=\App\Kasus::findorFail($request->kasusid);
        $kasus->update($request->all());
        return redirect('/kasus')->with('sukses','Pelaporan Berhasil Diupdate');
    }

    public function delete($id)
    {
        $kasus=\App\Kasus::find($id);
        $kasus->delete();
        return redirect('/kasus')->with('sukses','Pelaporan Kasus Berhasil Dihapus');
    }

}


