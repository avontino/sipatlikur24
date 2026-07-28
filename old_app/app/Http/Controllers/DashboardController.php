<?php 

namespace App\Http\Controllers;

use DB;
use App\Ijinsiswa;
use App\Siswa;
use App\Kelas;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
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

        $data_jrekap= \App\Jrekap::where('kelas',auth()->user()->name)
        ->orderBy('created_at','desc')->get();

    //S/I/A
    
    $sakit=$data_absen= \App\Absen::
             where('ket','Sakit')->where('kelas',auth()->user()->name)
             ->whereDate('created_at',now())
             ->count();

        $ijin=$data_absen= \App\Absen::
             where('ket','Ijin')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
             ->count();

             $alpha=$data_absen= \App\Absen::
             where('ket','Alpha')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
             ->count();

            $data_absen= \App\Absen::where('kelas',auth()->user()->name)->whereDate('created_at',now())->orderBy('created_at','desc')->get();
            
            
            $ijinpesiar= Ijinsiswa::
             where('ketijin','Ijin Pesiar')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
             ->count();
             $ijinbermalamwajib= Ijinsiswa::
             where('ketijin','Ijin Bermalam Wajib')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
             ->count();
             $ijinbermalamresmi= Ijinsiswa::
             where('ketijin','Ijin Bermalam Resmi')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
             ->count();
             $ijinjalan= Ijinsiswa::
             where('ketijin','Ijin Jalan')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
             ->count();
             $ijinkhusus= Ijinsiswa::
             where('ketijin','Ijin Khusus')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
             ->count();

        return view('dashboards.index')->with('totalkosong', $totalkosong)->with('totalok', $totalok)
        ->with('ijinpesiar', $ijinpesiar)->with('ijinbermalamwajib', $ijinbermalamwajib)
        ->with('ijinjalan', $ijinjalan)->with('ijinbermalamresmi', $ijinbermalamresmi)->with('ijinkhusus', $ijinkhusus)
        ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha);
    } 

    elseif(auth()->user()->role=='guru'){
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

    //S/I/A

    $sakit= \App\Absen::
             where('ket','Sakit')->whereDate('created_at',now())
             ->count();

        $ijin= \App\Absen::
             where('ket','Ijin')->whereDate('created_at',now())
             ->count();

             $alpha= \App\Absen::
             where('ket','Alpha')->whereDate('created_at',now())
             ->count();

            $data_absen= \App\Absen::whereDate('created_at',now())->orderBy('created_at','desc')->get();

            $absenguru= DB::table('ijin')
                        ->where('guru',auth()->user()->name)
                        ->whereBetween('created_at',['2020-01-02', now()])
                        ->sum('jumlah');   
      
      $ijinpesiar= Ijinsiswa::
                where('ketijin','Ijin Pesiar')->whereDate('created_at',now())
                ->count();
                $ijinbermalamwajib= Ijinsiswa::
                where('ketijin','Ijin Bermalam Wajib')->whereDate('created_at',now())
                ->count();
                $ijinbermalamresmi= Ijinsiswa::
                where('ketijin','Ijin Bermalam Resmi')->whereDate('created_at',now())
                ->count();
                $ijinjalan= Ijinsiswa::
                where('ketijin','Ijin Jalan')->whereDate('created_at',now())
                ->count();
                $ijinkhusus= Ijinsiswa::
                where('ketijin','Ijin Khusus')->whereDate('created_at',now())
                ->count();

        return view('dashboards.index')->with('totalkosong', $totalkosong)->with('totalok', $totalok)
        ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha)->with('absenguru',$absenguru)
          ->with('ijinpesiar', $ijinpesiar)->with('ijinbermalamwajib', $ijinbermalamwajib)
        ->with('ijinjalan', $ijinjalan)->with('ijinbermalamresmi', $ijinbermalamresmi)->with('ijinkhusus', $ijinkhusus)
          ;

    } elseif(auth()->user()->role=='siswa'){
        
        $sis_wa=Siswa::orderBy('nama','asc')->get();
     $ke_las=Kelas::all();
           
            $sakit=$data_absen= \App\Absen::
            where('ket','Sakit')->where('nama',auth()->user()->name)
            ->count();

           $ijin=$data_absen= \App\Absen::
            where('ket','Ijin')->where('nama',auth()->user()->name)
            ->count();

            $alpha=$data_absen= \App\Absen::
            where('ket','Alpha')->where('nama',auth()->user()->name)
            ->count(); 
            
            //     $telat= \App\Absen::
            // where('ket','Terlambat')->where('nama',auth()->user()->name)
            // ->count();
            
            // $prestasi= DB::table('prestasi')
            //            ->where('nama',auth()->user()->name)
            //            ->sum('poin');   

            
            // $pelanggaran= DB::table('pelanggaran')
            //            ->where('nama',auth()->user()->name)
            //            ->sum('poin');
                       
           $hadir= DB::table('absen')->select('nama')

       ->where('nama',auth()->user()->name)
       ->whereDate('created_at',now())
       ->first();
            
            if (is_null($hadir)){
                $status='MASUK';
            } else {
                $status='TIDAK MASUK';
            }
      
      $tagihan_komite = DB::table('tagihan')
    ->where('nis', auth()->user()->username)
    ->value('dana_komite');

// Pastikan nilainya berupa string dan diformat sebagai Rupiah
$tagihan_komite = 'Rp. ' . number_format($tagihan_komite, 0, ',', '.');

$tagihan_lain = DB::table('tagihan')
->where('nis', auth()->user()->username)
->value('tagihan_lain');

// Pastikan nilainya berupa string dan diformat sebagai Rupiah
$tagihan_lain = 'Rp. ' . number_format($tagihan_lain, 0, ',', '.');
            
            $ijinpesiar= Ijinsiswa::
            where('ketijin','Ijin Pesiar')->where('nama',auth()->user()->name)->whereDate('created_at',now())
            ->count();
            $ijinbermalamwajib= Ijinsiswa::
            where('ketijin','Ijin Bermalam Wajib')->where('nama',auth()->user()->name)->whereDate('created_at',now())
            ->count();
            $ijinbermalamresmi= Ijinsiswa::
            where('ketijin','Ijin Bermalam Resmi')->where('nama',auth()->user()->name)->whereDate('created_at',now())
            ->count();
            $ijinjalan= Ijinsiswa::
            where('ketijin','Ijin Jalan')->where('nama',auth()->user()->name)->whereDate('created_at',now())
            ->count();
            $ijinkhusus= Ijinsiswa::
            where('ketijin','Ijin Khusus')->where('nama',auth()->user()->name)->whereDate('created_at',now())
            ->count();
            
       
       $data_absen= \App\Absen::where('nama',auth()->user()->name)->orderBy('created_at','desc')->get();
       return view('dashboards.index',['ab_sen' => $data_absen],compact('sis_wa','ke_las','ke_las'))
       ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha)
    //    ->with('telat',$telat)->with('prestasi',$prestasi)->with('pelanggaran',$pelanggaran)
    ->with('ijinpesiar', $ijinpesiar)->with('ijinbermalamwajib', $ijinbermalamwajib)
        ->with('ijinjalan', $ijinjalan)->with('ijinbermalamresmi', $ijinbermalamresmi)->with('ijinkhusus', $ijinkhusus)
       ->with('status',$status)  ->with('tagihan_komite',$tagihan_komite)->with('tagihan_lain',$tagihan_lain)
       ;
       }
   
    else {
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

    //S/I/A

	$ijinpesiar= Ijinsiswa::
             where('ketijin','Ijin Pesiar')->whereDate('created_at',now())
             ->count();
             $ijinbermalamwajib= Ijinsiswa::
             where('ketijin','Ijin Bermalam Wajib')->whereDate('created_at',now())
             ->count();
             $ijinbermalamresmi= Ijinsiswa::
             where('ketijin','Ijin Bermalam Resmi')->whereDate('created_at',now())
             ->count();
             $ijinjalan= Ijinsiswa::
             where('ketijin','Ijin Jalan')->whereDate('created_at',now())
             ->count();
             $ijinkhusus= Ijinsiswa::
             where('ketijin','Ijin Khusus')->whereDate('created_at',now())
             ->count();

             $sakit= \App\Absen::
             where('ket','Sakit')->whereDate('created_at',now())
             ->count();

        $ijin= \App\Absen::
             where('ket','Ijin')->whereDate('created_at',now())
             ->count();

             $alpha= \App\Absen::
             where('ket','Alpha')->whereDate('created_at',now())
             ->count();

            $data_ijin= \App\Ijinsiswa::whereDate('created_at',now())->orderBy('created_at','desc')->get();
            $absenguru= DB::table('ijin')->select('guru',DB::raw('SUM(jumlah) as total_absen'))
                        ->groupBy('guru')
                        ->whereDate('created_at',now())
                        ->get();   

        return view('dashboards.index')->with('ijinpesiar', $ijinpesiar)->with('ijinbermalamwajib', $ijinbermalamwajib)
        ->with('ijinjalan', $ijinjalan)->with('ijinbermalamresmi', $ijinbermalamresmi)->with('ijinkhusus', $ijinkhusus)
        // ->with('totalok', $totalok)
        ->with('sakit',$sakit)->with('ijin',$ijin)->with('alpha',$alpha)->with('absenguru',$absenguru);

    }
}
}
