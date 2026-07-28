<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\SuratExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;


class SuratController extends Controller
{
    public function surat(Request $request)
    {

		// if ($request->filled('filter')) {
  //   	$data_surat= \App\Surat::whereDate('tglmasuk','LIKE','%'.$request->filter.'%')->paginate();

  //       }  elseif($request->filled('cari')){ 

  //            $data_surat= \App\Surat::
  //            where('institusi','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
  //            ->orwhere('perihal','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
  //            ->orwhere('kodesurat','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
  //            ->paginate()
  //            ;
  //       }



  //       else{
  //   		$data_surat= \App\Surat::orderBy('created_at','desc')->paginate(10);
   
  //   	}


        $data_surat= \App\Surat::orderBy('created_at','desc')->get();
    	return view('surat',['data_surat' => $data_surat]);
    }


    public function create(Request $request)
    {	
    	\App\Surat::create($request->all());
    	return redirect('/surat')->with('sukses','Surat Berhasil Ditambahkan');
    }


    public function exportExcel() 
    {
        return Excel::download(new SuratExport, 'Surat.xlsx');
    }

    public function exportPDF($id) 
    {   
        $surat=\App\Surat::where('id','LIKE',$id)->first();
        $pdf = PDF::loadView('export.suratpdf',['surat'=>$surat])->setPaper('A5', 'landscape');
        return $pdf->download('surat.pdf');

    }


    public function update(Request $request)
    {

        $surat=\App\Surat::findorFail($request->srtid);
        $surat->update($request->all());
        return redirect('/surat')->with('sukses','Surat Berhasil Diupdate');
        // dd($request->all());
    }

    public function delete($id)
    {
        $surat=\App\Surat::find($id);
        $surat->delete();
        return redirect('/surat')->with('sukses','Surat Berhasil Dihapus');
    }

}


