<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\JrekapExport;
use App\Exports\JrekapkelasExport;
use App\Exports\JrekapLaporanExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use App\Models\Kelas;
use DateTime;
use Carbon\Carbon;
use App\Models\Siswa;
use App\Models\Absen;


class JrekapController extends Controller
{

    public function index(Request $request)
    {	
        $ke_las=Kelas::all();

    $kosong = DB::table('jrekap')
    ->selectRaw("count(case when j1 = '0' then 1 end) as n1")
    ->selectRaw("count(case when j2 = '0' then 1 end) as n2")
    ->selectRaw("count(case when j3 = '0' then 1 end) as n3")
    ->selectRaw("count(case when j4 = '0' then 1 end) as n4")
    ->selectRaw("count(case when j5 = '0' then 1 end) as n5")
    ->selectRaw("count(case when j6 = '0' then 1 end) as n6")
    ->selectRaw("count(case when j7 = '0' then 1 end) as n7")
    ->selectRaw("count(case when j8 = '0' then 1 end) as n8")
    ->selectRaw("count(case when j9 = '0' then 1 end) as n9")
    ->selectRaw("count(case when j10 = '0' then 1 end) as n10")
    ->selectRaw("count(case when j11 = '0' then 1 end) as n11")
    ->first();
    $totalkosong=$kosong->n1+$kosong->n2+$kosong->n3+$kosong->n4+$kosong->n5+$kosong->n6+$kosong->n7+$kosong->n8+$kosong->n9+$kosong->n10+$kosong->n11;


    $ok = DB::table('jrekap')
    ->selectRaw("count(case when j1 = 'ok' then 1 end) as n1")
    ->selectRaw("count(case when j2 = 'ok' then 1 end) as n2")
    ->selectRaw("count(case when j3 = 'ok' then 1 end) as n3")
    ->selectRaw("count(case when j4 = 'ok' then 1 end) as n4")
    ->selectRaw("count(case when j5 = 'ok' then 1 end) as n5")
    ->selectRaw("count(case when j6 = 'ok' then 1 end) as n6")
    ->selectRaw("count(case when j7 = 'ok' then 1 end) as n7")
    ->selectRaw("count(case when j8 = 'ok' then 1 end) as n8")
    ->selectRaw("count(case when j9 = 'ok' then 1 end) as n9")
    ->selectRaw("count(case when j10 = 'ok' then 1 end) as n10")
    ->selectRaw("count(case when j11 = 'ok' then 1 end) as n11")
    ->first();
    $totalok=$ok->n1+$ok->n2+$ok->n3+$ok->n4+$ok->n5+$ok->n6+$ok->n7+$ok->n8+$ok->n9+$ok->n10+$ok->n11;

        if (auth()->user()->role=='ketuakelas') {

$kosong = DB::table('jrekap')->where('kelas',auth()->user()->name)
    ->selectRaw("count(case when j1 = '0' then 1 end) as n1")
    ->selectRaw("count(case when j2 = '0' then 1 end) as n2")
    ->selectRaw("count(case when j3 = '0' then 1 end) as n3")
    ->selectRaw("count(case when j4 = '0' then 1 end) as n4")
    ->selectRaw("count(case when j5 = '0' then 1 end) as n5")
    ->selectRaw("count(case when j6 = '0' then 1 end) as n6")
    ->selectRaw("count(case when j7 = '0' then 1 end) as n7")
    ->selectRaw("count(case when j8 = '0' then 1 end) as n8")
    ->selectRaw("count(case when j9 = '0' then 1 end) as n9")
    ->selectRaw("count(case when j10 = '0' then 1 end) as n10")
    ->selectRaw("count(case when j11 = '0' then 1 end) as n11")
    ->first();
    $totalkosong=$kosong->n1+$kosong->n2+$kosong->n3+$kosong->n4+$kosong->n5+$kosong->n6+$kosong->n7+$kosong->n8+$kosong->n9+$kosong->n10+$kosong->n11;


    $ok = DB::table('jrekap')->where('kelas',auth()->user()->name)
    ->selectRaw("count(case when j1 = 'ok' then 1 end) as n1")
    ->selectRaw("count(case when j2 = 'ok' then 1 end) as n2")
    ->selectRaw("count(case when j3 = 'ok' then 1 end) as n3")
    ->selectRaw("count(case when j4 = 'ok' then 1 end) as n4")
    ->selectRaw("count(case when j5 = 'ok' then 1 end) as n5")
    ->selectRaw("count(case when j6 = 'ok' then 1 end) as n6")
    ->selectRaw("count(case when j7 = 'ok' then 1 end) as n7")
    ->selectRaw("count(case when j8 = 'ok' then 1 end) as n8")
    ->selectRaw("count(case when j9 = 'ok' then 1 end) as n9")
    ->selectRaw("count(case when j10 = 'ok' then 1 end) as n10")
    ->selectRaw("count(case when j11 = 'ok' then 1 end) as n11")
    ->first();
    $totalok=$ok->n1+$ok->n2+$ok->n3+$ok->n4+$ok->n5+$ok->n6+$ok->n7+$ok->n8+$ok->n9+$ok->n10+$ok->n11;

        $data_jrekap= \App\Models\Jrekap::where('kelas',auth()->user()->name)
        ->orderBy('created_at','desc')->get();
        return view('jrekap.index',['data_jrekap' => $data_jrekap],compact('ke_las'))
        ->with('totalkosong', $totalkosong)->with('totalok', $totalok);

        } 
        elseif ($request->get('action')=='sinkron') {
        
            // Aksi
         $time = DB::table('jrekap')->select('created_at')
        ->whereDate('created_at',$request->tgl)
        ->first();

        $rekap = DB::table('jrekap')
        ->whereDate('created_at',$request->tgl)
        ->pluck('kelas')->toArray();

        $kelas=array(null,'X 1','X 2','X 3','X 4','X 5','X 6','X 7','X 8','XI 1','XI 2','XI 3','XI 4','XI 5','XI 6','XII 1','XII 2','XII 3','XII 4','XII 5','XII 6');

            for ($i=1; $i<=20; $i++){
            if (!in_array($kelas[$i],$rekap)){

            DB::table('jrekap')->insert(['kelas'=>$kelas[$i],'created_at'=>$request->tgl,'updated_at'=>$request->tgl]);

            } 
            }
            
         return redirect('/jrekap')->with('sukses','Sinkron Berhasil Dilakukan')
         ; 
     }

         elseif ($request->get('action')=='kelas') {

        
            // Aksi
$kosong = DB::table('jrekap')->where('kelas',$request->kelas)
    ->selectRaw("count(case when j1 = '0' then 1 end) as n1")
    ->selectRaw("count(case when j2 = '0' then 1 end) as n2")
    ->selectRaw("count(case when j3 = '0' then 1 end) as n3")
    ->selectRaw("count(case when j4 = '0' then 1 end) as n4")
    ->selectRaw("count(case when j5 = '0' then 1 end) as n5")
    ->selectRaw("count(case when j6 = '0' then 1 end) as n6")
    ->selectRaw("count(case when j7 = '0' then 1 end) as n7")
    ->selectRaw("count(case when j8 = '0' then 1 end) as n8")
    ->selectRaw("count(case when j9 = '0' then 1 end) as n9")
    ->selectRaw("count(case when j10 = '0' then 1 end) as n10")
    ->selectRaw("count(case when j11 = '0' then 1 end) as n11")
    ->first();
    $totalkosong=$kosong->n1+$kosong->n2+$kosong->n3+$kosong->n4+$kosong->n5+$kosong->n6+$kosong->n7+$kosong->n8+$kosong->n9+$kosong->n10+$kosong->n11;


    $ok = DB::table('jrekap')->where('kelas',$request->kelas)
    ->selectRaw("count(case when j1 = 'ok' then 1 end) as n1")
    ->selectRaw("count(case when j2 = 'ok' then 1 end) as n2")
    ->selectRaw("count(case when j3 = 'ok' then 1 end) as n3")
    ->selectRaw("count(case when j4 = 'ok' then 1 end) as n4")
    ->selectRaw("count(case when j5 = 'ok' then 1 end) as n5")
    ->selectRaw("count(case when j6 = 'ok' then 1 end) as n6")
    ->selectRaw("count(case when j7 = 'ok' then 1 end) as n7")
    ->selectRaw("count(case when j8 = 'ok' then 1 end) as n8")
    ->selectRaw("count(case when j9 = 'ok' then 1 end) as n9")
    ->selectRaw("count(case when j10 = 'ok' then 1 end) as n10")
    ->selectRaw("count(case when j11 = 'ok' then 1 end) as n11")
    ->first();
    $totalok=$ok->n1+$ok->n2+$ok->n3+$ok->n4+$ok->n5+$ok->n6+$ok->n7+$ok->n8+$ok->n9+$ok->n10+$ok->n11;

         $data_jrekap= \App\Models\Jrekap::where('kelas',$request->kelas)
        ->orderBy('created_at','desc')->get();
        return view('jrekap.index',['data_jrekap' => $data_jrekap],compact('ke_las'))
        ->with('totalkosong', $totalkosong)->with('totalok', $totalok); 
        // dd($request->kelas);
        }

        elseif ($request->get('action')=='tanggal') {

$kosong = DB::table('jrekap')->whereDate('created_at',$request->crtgl)
    ->selectRaw("count(case when j1 = '0' then 1 end) as n1")
    ->selectRaw("count(case when j2 = '0' then 1 end) as n2")
    ->selectRaw("count(case when j3 = '0' then 1 end) as n3")
    ->selectRaw("count(case when j4 = '0' then 1 end) as n4")
    ->selectRaw("count(case when j5 = '0' then 1 end) as n5")
    ->selectRaw("count(case when j6 = '0' then 1 end) as n6")
    ->selectRaw("count(case when j7 = '0' then 1 end) as n7")
    ->selectRaw("count(case when j8 = '0' then 1 end) as n8")
    ->selectRaw("count(case when j9 = '0' then 1 end) as n9")
    ->selectRaw("count(case when j10 = '0' then 1 end) as n10")
    ->selectRaw("count(case when j11 = '0' then 1 end) as n11")
    ->first();
    $totalkosong=$kosong->n1+$kosong->n2+$kosong->n3+$kosong->n4+$kosong->n5+$kosong->n6+$kosong->n7+$kosong->n8+$kosong->n9+$kosong->n10+$kosong->n11;


    $ok = DB::table('jrekap')->whereDate('created_at',$request->crtgl)
    ->selectRaw("count(case when j1 = 'ok' then 1 end) as n1")
    ->selectRaw("count(case when j2 = 'ok' then 1 end) as n2")
    ->selectRaw("count(case when j3 = 'ok' then 1 end) as n3")
    ->selectRaw("count(case when j4 = 'ok' then 1 end) as n4")
    ->selectRaw("count(case when j5 = 'ok' then 1 end) as n5")
    ->selectRaw("count(case when j6 = 'ok' then 1 end) as n6")
    ->selectRaw("count(case when j7 = 'ok' then 1 end) as n7")
    ->selectRaw("count(case when j8 = 'ok' then 1 end) as n8")
    ->selectRaw("count(case when j9 = 'ok' then 1 end) as n9")
    ->selectRaw("count(case when j10 = 'ok' then 1 end) as n10")
    ->selectRaw("count(case when j11 = 'ok' then 1 end) as n11")
    ->first();
    $totalok=$ok->n1+$ok->n2+$ok->n3+$ok->n4+$ok->n5+$ok->n6+$ok->n7+$ok->n8+$ok->n9+$ok->n10+$ok->n11;
            
            // Aksi
        
        $data_jrekap= \App\Models\Jrekap::whereDate('created_at',$request->crtgl)
        ->orderBy('created_at','desc')->get();
        return view('jrekap.index',['data_jrekap' => $data_jrekap],compact('ke_las'))
        ->with('totalkosong', $totalkosong)->with('totalok', $totalok);
        // dd($request->crtgl);
            
    } 

        else {


        $data_jrekap= \App\Models\Jrekap::orderBy('created_at','desc')->get();
        return view('jrekap.index',['data_jrekap' => $data_jrekap],compact('ke_las'))
        ->with('totalkosong', $totalkosong)->with('totalok', $totalok);
        }

    }


    public function export() 
    {
        return Excel::download(new JrekapExport, 'Jrekap.xlsx');
    }

    public function exportkelas() 
    {
        return Excel::download(new JrekapkelasExport, 'Jrekapkelas.xlsx');
    }

    public function exportlaporan() 
    {   
        $tanggalawal = Carbon::parse(request()->input('tglawal'))->startOfDay();
        $tanggalakhir = Carbon::parse(request()->input('tglakhir'))->endOfDay();

        //hitung jurnal kosong
 

    //proses update jurnal kosong di tabel kelas
        $kelas=array(null,'X 1','X 2','X 3','X 4','X 5','X 6','X 7','X 8','XI 1','XI 2','XI 3','XI 4','XI 5','XI 6','XII 1','XII 2','XII 3','XII 4','XII 5','XII 6');

            for ($i=1; $i<=20; $i++){
               $kosong = DB::table('jrekap')->where('kelas',$kelas[$i])
                ->selectRaw("count(case when j1 = '0' then 1 end) as n1")
                ->selectRaw("count(case when j2 = '0' then 1 end) as n2")
                ->selectRaw("count(case when j3 = '0' then 1 end) as n3")
                ->selectRaw("count(case when j4 = '0' then 1 end) as n4")
                ->selectRaw("count(case when j5 = '0' then 1 end) as n5")
                ->selectRaw("count(case when j6 = '0' then 1 end) as n6")
                ->selectRaw("count(case when j7 = '0' then 1 end) as n7")
                ->selectRaw("count(case when j8 = '0' then 1 end) as n8")
                ->selectRaw("count(case when j9 = '0' then 1 end) as n9")
                ->selectRaw("count(case when j10 = '0' then 1 end) as n10")
                ->selectRaw("count(case when j11 = '0' then 1 end) as n11")
                ->whereBetween('created_at',[$tanggalawal,$tanggalakhir])
                ->first();
                $totalkosong=$kosong->n1+$kosong->n2+$kosong->n3+$kosong->n4+$kosong->n5+$kosong->n6+$kosong->n7+$kosong->n8+$kosong->n9+$kosong->n10+$kosong->n11;

            $sakit=$data_absen= \App\Models\Absen::
             where('kelas',$kelas[$i])
             ->where('ket','Sakit')
             ->whereBetween('created_at',[$tanggalawal,$tanggalakhir])
             ->count();

            $ijin=$data_absen= \App\Models\Absen::
             where('kelas',$kelas[$i])
             ->where('ket','Ijin')
             ->whereBetween('created_at',[$tanggalawal,$tanggalakhir])
             ->count();

             $alpha=$data_absen= \App\Models\Absen::
             where('kelas',$kelas[$i])
             ->where('ket','Alpha')
             ->whereBetween('created_at',[$tanggalawal,$tanggalakhir])
             ->count();

            $dispen=$data_absen= \App\Models\Absen::
             where('kelas',$kelas[$i])
             ->where('ket','Dispen')
             ->whereBetween('created_at',[$tanggalawal,$tanggalakhir])
             ->count();

            DB::table('ke_las')->where('kelas',$kelas[$i])
            ->update(['jk'=>$totalkosong,'js'=>$sakit,'ji'=>$ijin,'ja'=>$alpha,'jd'=>$dispen]);
            
            }
           // dd('ok'); 

        return Excel::download(new JrekapLaporanExport, 'RekapLaporan.xlsx');
        
        
    }    
}
