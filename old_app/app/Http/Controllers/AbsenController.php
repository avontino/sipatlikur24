<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\AbsenExport;
use Excel;
use App\Siswa;
use App\Kelas;
use DB;
use DateTime;


class AbsenController extends Controller
{

    public function index(Request $request)
    { 

       
        $sis_wa=Siswa::orderBy('nama','asc')->get();
        $ke_las=Kelas::all();
  
        $sakit=$data_absen= \App\Absen::
               where('ket','Sakit')
               ->count();
  
          $ijin=$data_absen= \App\Absen::
               where('ket','Ijin')
               ->count();
  
               $alpha=$data_absen= \App\Absen::
               where('ket','Alpha')
               ->count();
               
         
        
              if(auth()->user()->role=='ketuakelas' AND is_null($request->get('action'))){
  
              $sakit=$data_absen= \App\Absen::
               where('ket','Sakit')->where('kelas',auth()->user()->name)
               ->count();
  
          $ijin=$data_absen= \App\Absen::
               where('ket','Ijin')->where('kelas',auth()->user()->name)
               ->count();
  
               $alpha=$data_absen= \App\Absen::
               where('ket','Alpha')->where('kelas',auth()->user()->name)
               ->count();
  
              $data_absen= \App\Absen::where('kelas',auth()->user()->name)->orderBy('created_at','desc')->get();
              return view('absen.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las'))
              ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha);
          }  
  
          elseif($request->get('action')=='tanggal' AND auth()->user()->role=='ketuakelas') {
             $sakit=$data_absen= \App\Absen::
               where('ket','Sakit')->whereDate('created_at',$request->crtgl)->where('kelas',auth()->user()->name)
               ->count();
  
          $ijin=$data_absen= \App\Absen::
               where('ket','Ijin')->whereDate('created_at',$request->crtgl)->where('kelas',auth()->user()->name)
               ->count();
  
               $alpha=$data_absen= \App\Absen::
               where('ket','Alpha')->whereDate('created_at',$request->crtgl)->where('kelas',auth()->user()->name)
               ->count();
  
              $data_absen= \App\Absen::whereDate('created_at',$request->crtgl)->where('kelas',auth()->user()->name)->orderBy('created_at','desc')->get();
              return view('absen.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las'))
              ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha); 
  
          }
  
          elseif($request->get('action')=='kelas') {
              $sakit=$data_absen= \App\Absen::
               where('ket','Sakit')->where('kelas',$request->kelas)
               ->count();
  
          $ijin=$data_absen= \App\Absen::
               where('ket','Ijin')->where('kelas',$request->kelas)
               ->count();
  
               $alpha=$data_absen= \App\Absen::
               where('ket','Alpha')->where('kelas',$request->kelas)
               ->count();
  
              $data_absen= \App\Absen::where('kelas',$request->kelas)->orderBy('created_at','desc')->get();
              return view('absen.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las'))
              ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha);            
          }
  
           elseif($request->get('action')=='tanggal'){
              $sakit=$data_absen= \App\Absen::
               where('ket','Sakit')->whereDate('created_at',$request->crtgl)
               ->count();
  
          $ijin=$data_absen= \App\Absen::
               where('ket','Ijin')->whereDate('created_at',$request->crtgl)
               ->count();
  
               $alpha=$data_absen= \App\Absen::
               where('ket','Alpha')->whereDate('created_at',$request->crtgl)
               ->count();
  
              $data_absen= \App\Absen::whereDate('created_at',$request->crtgl)->orderBy('created_at','desc')->get();
              return view('absen.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las'))
              ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha); 
  
           }
  
           elseif($request->get('action')=='kelastgl') {
             $sakit=$data_absen= \App\Absen::
               where('ket','Sakit')->whereDate('created_at',$request->crtgl)->where('kelas',$request->kelas)
               ->count();
  
          $ijin=$data_absen= \App\Absen::
               where('ket','Ijin')->whereDate('created_at',$request->crtgl)->where('kelas',$request->kelas)
               ->count();
  
               $alpha=$data_absen= \App\Absen::
               where('ket','Alpha')->whereDate('created_at',$request->crtgl)->where('kelas',$request->kelas)
               ->count();
  
              $data_absen= \App\Absen::whereDate('created_at',$request->crtgl)->where('kelas',$request->kelas)->orderBy('created_at','desc')->get();
              return view('absen.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las'))
              ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha); 
              
          }
  
          elseif(auth()->user()->role=='siswa'){
              
               $sakit=$data_absen= \App\Absen::
               where('ket','Sakit')->where('nama',auth()->user()->name)
               ->count();
  
              $ijin=$data_absen= \App\Absen::
               where('ket','Ijin')->where('nama',auth()->user()->name)
               ->count();
  
               $alpha=$data_absen= \App\Absen::
               where('ket','Alpha')->where('nama',auth()->user()->name)
               ->count();    
          
          $data_absen= \App\Absen::where('nama',auth()->user()->name)->orderBy('created_at','desc')->get();
          return view('absen.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las'))
          ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha);
          }
          
          else {
  
        $data_absen= \App\Absen::orderBy('created_at','desc')->get();
          return view('absen.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las'))
          ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha);
        
        }


    }

    public function create(Request $request)
    {   
        
       if ($request->ket == 'Sakit') {

    // Cek apakah sudah ada absensi untuk siswa dengan tanggal yang sama
    $existingAbsence = \App\Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                ->whereDate('created_at', $request->tgl) // Cek berdasarkan tanggal
                                ->first();

    if ($existingAbsence) {
        // Jika absensi sudah ada, tampilkan error
        return redirect('/absen')->with('gagal', 'Absensi Sudah Ada, Silahkan Cek Kembali');
    }

    // Ambil data siswa
    $jumlahawal = DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->first();

    // Update jumlah sakit siswa
    DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->update(['sakit' => $jumlahawal->sakit + 1]);

    // Tambahkan data absensi
    \App\Absen::create(['nama' => $request->nama, 'kelas' => $request->kelas, 'ket' => $request->ket, 'created_at' => $request->tgl]);
    return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');

} elseif ($request->ket == 'Ijin') {

    // Cek apakah sudah ada absensi untuk siswa dengan tanggal yang sama
    $existingAbsence = \App\Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                ->whereDate('created_at', $request->tgl) // Cek berdasarkan tanggal
                                ->first();

    if ($existingAbsence) {
        // Jika absensi sudah ada, tampilkan error
        return redirect('/absen')->with('gagal', 'Absensi Sudah Ada, Silahkan Cek Kembali');
    }

    // Ambil data siswa
    $jumlahawal = DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->first();

    // Update jumlah ijin siswa
    DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->update(['ijin' => $jumlahawal->ijin + 1]);

    // Tambahkan data absensi
    \App\Absen::create(['nama' => $request->nama, 'kelas' => $request->kelas, 'ket' => $request->ket, 'created_at' => $request->tgl]);
    return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');

} elseif ($request->ket == 'Alpha') {

    // Cek apakah sudah ada absensi untuk siswa dengan tanggal yang sama
    $existingAbsence = \App\Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                ->whereDate('created_at', $request->tgl) // Cek berdasarkan tanggal
                                ->first();

    if ($existingAbsence) {
        // Jika absensi sudah ada, tampilkan error
        return redirect('/absen')->with('gagal', 'Absensi Sudah Ada, Silahkan Cek Kembali');
    }

    // Ambil data siswa
    $jumlahawal = DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->first();

    // Update jumlah alpha siswa
    DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->update(['alpha' => $jumlahawal->alpha + 1]);

    // Tambahkan data absensi
    \App\Absen::create(['nama' => $request->nama, 'kelas' => $request->kelas, 'ket' => $request->ket, 'created_at' => $request->tgl]);
    return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');

} elseif ($request->ket == 'Dispen') {

    // Cek apakah sudah ada absensi untuk siswa dengan tanggal yang sama
    $existingAbsence = \App\Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                ->whereDate('created_at', $request->tgl) // Cek berdasarkan tanggal
                                ->first();

    if ($existingAbsence) {
        // Jika absensi sudah ada, tampilkan error
        return redirect('/absen')->with('gagal', 'Absensi Sudah Ada, Silahkan Cek Kembali');
    }

    // Ambil data siswa
    $jumlahawal = DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->first();

    // Update jumlah dispen siswa
    DB::table('siswa')->where('nama', 'LIKE', '%'.$request->nama.'%')->update(['dispen' => $jumlahawal->dispen + 1]);

    // Tambahkan data absensi
    \App\Absen::create(['nama' => $request->nama, 'kelas' => $request->kelas, 'ket' => $request->ket, 'created_at' => $request->tgl]);
    return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');

} else {

    // Cek apakah sudah ada absensi untuk siswa dengan tanggal yang sama
    $existingAbsence = \App\Absen::where('nama', 'LIKE', '%'.$request->nama.'%')
                                ->whereDate('created_at', $request->tgl) // Cek berdasarkan tanggal
                                ->first();

    if ($existingAbsence) {
        // Jika absensi sudah ada, tampilkan error
        return redirect('/absen')->with('error', 'Absensi Sudah Ada, Silahkan Cek Kembali');
    }

    // Tambahkan data absensi
    \App\Absen::create(['nama' => $request->nama, 'kelas' => $request->kelas, 'ket' => $request->ket, 'created_at' => $request->tgl]);
    return redirect('/absen')->with('sukses', 'Absen Siswa Berhasil Ditambahkan');
}

       
        
    }

    public function update(Request $request)
    {
        $jadwal=\App\Jadwal::findorFail($request->jadwalid);
        $jadwal->update($request->all());
        return redirect('/jadwal')->with('sukses','Jadwal Berhasil Diupdate');
    }

   

    public function delete($id)
    {
       $absen=\App\Absen::find($id);
        $keterangan = DB::table('absen')->where('id','LIKE','%'.$absen->id.'%')->first();

         if ($keterangan->ket=='Sakit') {
             $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->first();
             DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->update(['sakit'=>$jumlahawal->sakit - 1]);

             $absen->delete();
             return redirect('/absen')->with('gagal','Absensi Berhasil Dihapus');
         }

         elseif ($keterangan->ket=='Ijin') {
             $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->first();
             DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->update(['ijin'=>$jumlahawal->ijin - 1]);

             $absen->delete();
             return redirect('/absen')->with('gagal','Absensi Berhasil Dihapus');
         }

         elseif ($keterangan->ket=='Alpha') {
             $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->first();
             DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->update(['alpha'=>$jumlahawal->alpha - 1]);

             $absen->delete();
             return redirect('/absen')->with('gagal','Absensi Berhasil Dihapus');
         }

         elseif ($keterangan->ket=='Dispen') {
             $jumlahawal = DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->first();
             DB::table('siswa')->where('nama','LIKE','%'.$keterangan->nama.'%')->update(['dispen'=>$jumlahawal->dispen - 1]);

             $absen->delete();
             return redirect('/absen')->with('gagal','Absensi Berhasil Dihapus');
         }

         else   
       
        $absen->delete();
             return redirect('/absen')->with('gagal','Absensi Berhasil Dihapus');
    }

        public function export() 
    {
        return Excel::download(new AbsenExport, 'Absen.xlsx');
    }
}
