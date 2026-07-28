<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\OperatorExport;
use App\Imports\OperatorImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Guru;
use DB;

class OperatorController extends Controller
{
    public function operator(Request $request)
    {

		if($request->filled('cari')){ 

             $data_operator= \App\User::
             where('role','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
             ->orwhere('name','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
             ->orwhere('username','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
             ->get()
             ;
        }



        else{
    		$data_operator= \App\User::orderBy('created_at','desc')->get();
   
    	}

 

    	return view('operator',['data_operator' => $data_operator]);
    }


    public function create(Request $request)
    {	
    	$user = new \App\User;
    	$user->role=$request->role;
    	$user->name=$request->name;
    	$user->username=$request->username;
    	$user->password=bcrypt($request->password);
    	$user->remember_token=str_random(60);
    	$user->save();

        // $guru = new \App\Guru;        
        // $guru->guru=$request->name;
        
        // $guru->save();


    	return redirect('/operator')->with('sukses','Operator Berhasil Ditambahkan');
    }


    public function export() 
    {
        return Excel::download(new OperatorExport, 'Operator.xlsx');
    }

    public function edit($id)
    {   
        $kasus=\App\Kasus::find($id);
        return view('kasus/edit',['kasus'=>$kasus]);
    }

    public function update(Request $request)
    {   
        $user1 = \App\User::findorFail($request->opid);
        DB::table('gu_ru')->where('guru','LIKE',$user1->name)->update(['guru'=>$request->name]);

    	$user = \App\User::findorFail($request->opid);
    	$user->role=$request->role;
    	$user->name=$request->name;
    	$user->username=$request->username;
    	$user->password=bcrypt($request->password);
    	$user->remember_token=str_random(60);
    	$user->save();

        // $operator=\App\User::findorFail($request->opid);
        // $operator->update($request->all());
        return redirect('/operator')->with('sukses','Operator Berhasil Diupdate');
        // dd($request->all());
    }

    public function delete($id)
    {
        $operator=\App\User::find($id);

        $guru = DB::table('gu_ru')->where('guru','LIKE','%'.$operator->name.'%')->delete();

        $operator->delete();



        return redirect('/operator')->with('sukses','Operator Berhasil Dihapus');
    }

        public function import() 
    {
        Excel::import(new OperatorImport, request()->file('file'));

        
        
        return redirect('/operator')->with('sukses','Operator Berhasil Diupload');
    }

}


