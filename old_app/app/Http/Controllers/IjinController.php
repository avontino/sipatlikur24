<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\IjinExport;
use App\Exports\IjinRekapExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Mapel;
use App\Guru;
use App\Kelas;
use App\User;
use App\Ijin;
use Carbon\Carbon;
use DB;

class IjinController extends Controller
{	
    public function tambahi()
    {	
    	$ma_pel=Mapel::all();
    	$gu_ru=Guru::all();
    	$ke_las=Kelas::all();
    	return view('ijin.tambahijin',compact('ma_pel','gu_ru','ke_las'));
    }

    public function create(Request $request)
    {	
        // Validasi untuk field jam_terlambat jika status adalah "Terlambat"
        if($request->sia == 'Terlambat') {
            $request->validate([
                'jam_terlambat' => 'required',
            ]);
            // Set jumlah hari ke 0 untuk terlambat
            $request->merge(['jumlah' => 0]);
        } else {
            // Jika bukan terlambat, set jam_terlambat ke null
            $request->merge(['jam_terlambat' => null]);
        }
        
    	\App\Ijin::create($request->all());
    	return redirect('/tambahijin')->with('sukses','Ijin Berhasil Ditambahkan');
    }

    public function index(Request $request)
    {
        $ma_pel=Mapel::all();
        $gu_ru=Guru::all();
        $ke_las=Kelas::all();

        // Jika ingin menambahkan filter berdasarkan tanggal
        if ($request->filled('filter')) {
            $data_ijin= \App\Ijin::whereDate('tglmasuk', $request->filter)->orderBy('created_at','desc')->get();
        } else {
            $data_ijin= \App\Ijin::orderBy('created_at','desc')->get();
        }
        
    	return view('ijin.index',['data_ijin' => $data_ijin],compact('ma_pel','gu_ru','ke_las'));
    }

    public function export() 
    {
        return Excel::download(new IjinExport, 'Ijin.xlsx');
    }

    public function update(Request $request)
    {
        // Validasi untuk field jam_terlambat jika status adalah "Terlambat"
        if($request->sia == 'Terlambat') {
            $request->validate([
                'jam_terlambat' => 'required',
            ]);
            // Set jumlah hari ke 0 untuk terlambat
            $request->merge(['jumlah' => 0]);
        } else {
            // Jika bukan terlambat, set jam_terlambat ke null
            $request->merge(['jam_terlambat' => null]);
        }
        
        $ijin=\App\Ijin::findorFail($request->ijinid);
        $ijin->update($request->all());
        return redirect('/ijin')->with('sukses','Ijin Berhasil Diupdate');
    }

    public function delete($id)
    {
        $ijin=\App\Ijin::find($id);
        $ijin->delete();
        return redirect('/ijin')->with('sukses','Ijin Berhasil Dihapus');
    }

    //download excel rekap kehadiran
    public function rekaphadir() 
    {   
        // Langsung export tanpa update tabel users
        return Excel::download(new IjinRekapExport, 'RekapKehadiran.xlsx');
    }
}