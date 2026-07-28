<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\JadwalImport;
use App\Exports\JadwalExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Mapel;
use App\Guru;
use App\Kelas;
use DB;
use DateTime;


class JadwalController extends Controller
{

    public function index(Request $request)
    {	

        $ma_pel=Mapel::all();
        $gu_ru=Guru::all();
        $ke_las=Kelas::all();

    	$data_jadwal= \App\Jadwal::all();

    	return view('jadwal.index',['data_jadwal' => $data_jadwal],compact('ma_pel','gu_ru','ke_las'));


    }

    public function create(Request $request)
    {   
        
        \App\Jadwal::create($request->all());
        return redirect('/jadwal')->with('sukses','Jadwal Berhasil Ditambahkan');
    }

    public function update(Request $request)
    {
        $jadwal=\App\Jadwal::findorFail($request->jadwalid);
        $jadwal->update($request->all());
        return redirect('/jadwal')->with('sukses','Jadwal Berhasil Diupdate');
    }

   

    public function delete($id)
    {
        $jadwal=\App\Jadwal::find($id);
        $jadwal->delete();
        return redirect('/jadwal')->with('sukses','Jadwal Berhasil Dihapus');
    }

    public function import() 
    {
        Excel::import(new JadwalImport, request()->file('file'));
        
        return redirect('/jadwal')->with('sukses','Jadwal Berhasil Diupload');
    }

        public function export() 
    {
        return Excel::download(new JadwalExport, 'Jadwal.xlsx');
    }
}
