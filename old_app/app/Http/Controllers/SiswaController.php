<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\SiswaExport;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use App\Kelas;


class SiswaController extends Controller
{

    public function index(Request $request)
    {	
        $ke_las=Kelas::all();
		if (auth()->user()->role=='admin' OR auth()->user()->role=='guru' OR auth()->user()->role=='lihat') {
        $data_siswa= \App\Siswa::orderBy('nama','desc')->get();  
        return view('siswa.index',['data_siswa' => $data_siswa],compact('ke_las'));

        } else {
        $data_siswa= \App\Siswa::where('kelas',auth()->user()->name)
        ->orderBy('nama','asc')->get();  
        return view('siswa.index',['data_siswa' => $data_siswa],compact('ke_las'));
        }

    }

    public function create(Request $request)
    {	
    	
    	\App\Siswa::create($request->all());
    	return redirect('/siswa')->with('sukses','Siswa Berhasil Ditambahkan');
    }

    public function update(Request $request)
    {

        $siswa=\App\Siswa::findorFail($request->siswaid);
        $siswa->update($request->all());
        return redirect('/siswa')->with('sukses','Siswa Berhasil Diupdate');
        // dd($request->all());
    }

    public function delete($id)
    {
        $siswa=\App\Siswa::find($id);
        $siswa->delete();
        return redirect('/siswa')->with('sukses','Siswa Berhasil Dihapus');
    }

    public function daftar($nis)
    {	
    	// $ekstra=auth()->user()->role;
        $pilihan=auth()->user()->name;
        // $siswa=\App\Siswa::where('nis','LIKE','%'.$nis.'%');
        

        // DB::table($ekstra)->insertUsing(['id','nis','nama','kelas','kelamin','pilihan','nilai','deskripsi','created_at','updated_at'],$siswa);

        \App\Siswa::where('nis','LIKE','%'.$nis.'%')->update(['pilihan'=>$pilihan]);

        

        // $siswa->delete();

  //       $newTask = $siswa->replicate();
		// $newTask->save();
        
        return redirect('/siswa')->with('sukses','Siswa Berhasil Didaftarkan');
        
    }

    public function export()
    {
        return Excel::download(new SiswaExport, 'data_siswa.xlsx');
    }

 //    public function import() 
	// {
 //    	return Excel::import(new SiswaImport);
	// }

	public function import() 
    {
        Excel::import(new SiswaImport, request()->file('file'));
        
        return redirect('/siswa')->with('sukses','Siswa Berhasil Diupload');
    }

    public function updateIjin(Request $request)
{
    $jenis_ijin = $request->input('jenis_ijin');
    $jumlah = $request->input('jumlah');

    $kolom = '';

    switch ($jenis_ijin) {
        case 'Ijin Pesiar':
            $kolom = 'ip';
            break;
        case 'Ijin Bermalam Wajib':
            $kolom = 'ib';
            break;
        case 'Ijin Bermalam Resmi':
            $kolom = 'ibr';
            break;
        case 'Ijin Jalan':
            $kolom = 'ij';
            break;
        case 'Ijin Khusus':
            $kolom = 'ik';
            break;
    }

    if ($kolom != '') {
        DB::table('siswa')->update([$kolom => $jumlah]);
        return redirect('/siswa')->with('sukses','Data ijin berhasil diperbarui');
    } else {
        return back()->with('error', 'Jenis ijin tidak valid.');
    }
}

//     	public function export()
//     	{
//     		$siswa=\App\Siswa::all();
//     		return Excel::create('data_siswa',function($excel) use ($siswa){
//     			$excel->sheet('mysheet',function($sheet) use ($siswa){
//     				$sheet->fromArray($siswa);
//     			});
//     		})->download('xls');
//     	}
}
