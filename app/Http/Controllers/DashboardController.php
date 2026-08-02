<?php 

namespace App\Http\Controllers;

use DB;
use App\Models\Ijinsiswa;
use App\Models\Siswa;
use App\Models\Kelas;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $todayStr = now()->toDateString();
        $classesNotFilled = [];
        $waliClassNotFilled = false;
        $todayJurnalFilled = true;
        $guruScheduleNotFilled = [];
        $attendanceData = [];

        $dayOfWeek = now()->dayOfWeek; // 0 (Sun) - 6 (Sat)
        $isSchoolDay = ($dayOfWeek > 0);

        if ($isSchoolDay) {
            if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('kurikulum') || auth()->user()->hasRole('lihat')) {
                $allClasses = \App\Models\Kelas::pluck('kelas')->toArray();
                $todayJurnalh = \App\Models\Jurnalh::whereDate('created_at', $todayStr)->pluck('kelas')->toArray();
                $classesNotFilled = array_diff($allClasses, $todayJurnalh);
            }

            if (auth()->user()->hasRole('walikelas') || (auth()->user()->hasRole('guru') && auth()->user()->walikelas_kelas)) {
                $myClass = auth()->user()->walikelas_kelas ?: auth()->user()->name;
                $hasFilled = \App\Models\Jurnalh::where('kelas', $myClass)->whereDate('created_at', $todayStr)->exists();
                if (!$hasFilled) {
                    $waliClassNotFilled = true;
                }
            }

            if (auth()->user()->hasRole('ketuakelas')) {
                $myClass = auth()->user()->walikelas_kelas ?: auth()->user()->name;
                $hasFilled = \App\Models\Jurnalh::where('kelas', $myClass)->whereDate('created_at', $todayStr)->exists();
                if (!$hasFilled) {
                    $todayJurnalFilled = false;
                }
            }

            if (auth()->user()->hasRole('guru')) {
                $hariEng = [
                    0 => 'Sunday',
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday'
                ];
                $hariIni = $hariEng[$dayOfWeek];
                
                $mySchedules = \App\Models\Jadwal::where('guru', auth()->user()->name)
                    ->where('hari', $hariIni)
                    ->get();
                
                foreach ($mySchedules as $sch) {
                    $filled = \App\Models\Jurnal::where('guru', auth()->user()->name)
                        ->where('kelas', $sch->kelas)
                        ->where('mapel', $sch->mapel)
                        ->whereDate('created_at', $todayStr)
                        ->exists();
                    if (!$filled) {
                        $guruScheduleNotFilled[] = $sch->kelas . ' (' . $sch->mapel . ')';
                    }
                }
                $guruScheduleNotFilled = array_unique($guruScheduleNotFilled);
            }
        }

        if (auth()->user()->hasRole('guru') || auth()->user()->hasRole('tendik')) {
            $daysInMonth = now()->daysInMonth;
            $year = now()->year;
            $month = now()->month;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $dOfWeek = date('N', strtotime($dateStr));
                
                $status = ($dOfWeek == 7) ? 'libur' : 'alpha';

                $presensi = \App\Models\PresensiGuru::where('nama', auth()->user()->name)
                    ->whereDate('tanggal', $dateStr)
                    ->first();

                if ($presensi) {
                    $status = ($presensi->status_datang == 'Terlambat') ? 'terlambat' : 'tepat_waktu';
                } else {
                    $ijinQuery = \App\Models\Ijin::where('guru', auth()->user()->name)
                        ->whereDate('tglmasuk', $dateStr);
                    if (\Illuminate\Support\Facades\Schema::hasColumn('ijin', 'approval_status')) {
                        $ijinQuery->where('approval_status', 'approved');
                    }
                    $ijinRecord = $ijinQuery->first();
                    if ($ijinRecord) {
                        $status = 'izin';
                    }
                }

                $attendanceData[$dateStr] = $status;
            }
        }

        $unreadNotifications = auth()->user()->unreadNotifications;
        $managedClass = auth()->user()->getManagedClass();
        $todayVerification = null;
        $currentDetailStr = null;

        if ($managedClass) {
            $todayVerification = DB::table('verifikasi_absensi')
                ->where('kelas', $managedClass)
                ->whereDate('tanggal', $todayStr)
                ->first();
                
            $currentAbsen = \App\Models\Absen::where('kelas', $managedClass)
                ->whereDate('created_at', $todayStr)
                ->get();
            
            $currentSakit = $currentAbsen->where('ket', 'Sakit')->count();
            $currentIzin = $currentAbsen->where('ket', 'Ijin')->count();
            $currentAlpha = $currentAbsen->where('ket', 'Alpha')->count();
            $currentDispen = $currentAbsen->where('ket', 'Dispen')->count();
            
            $totalSiswa = \App\Models\Siswa::where('kelas', $managedClass)
                ->where('tahun_ajaran', session('tahun_ajaran'))
                ->count();
                
            $currentHadir = max(0, $totalSiswa - ($currentSakit + $currentIzin + $currentAlpha + $currentDispen));
            
            $currentDetailStr = ($currentSakit + $currentIzin + $currentAlpha + $currentDispen == 0)
                ? "NIHIL (Hadir Semua - {$totalSiswa} Siswa)"
                : "{$currentSakit} Sakit, {$currentIzin} Izin, {$currentAlpha} Alpha, {$currentDispen} Terlambat, {$currentHadir} Hadir dari {$totalSiswa} Siswa";
        }

        $verifikasiRekap = [];
        $totalClasses = 0;
        $totalVerified = 0;
        $totalUnverified = 0;
        
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('kurikulum') || auth()->user()->hasRole('kepala') || auth()->user()->hasRole('lihat') || auth()->user()->hasRole('walikelas') || auth()->user()->hasRole('guru') || auth()->user()->hasRole('pembina') || auth()->user()->hasRole('kesiswaan') || auth()->user()->hasRole('kesehatan')) {
            $allClasses = \App\Models\Kelas::pluck('kelas')->toArray();
            $totalClasses = count($allClasses);
            
            $verifikasiToday = DB::table('verifikasi_absensi')
                ->whereDate('tanggal', $todayStr)
                ->get()
                ->keyBy('kelas');
                
            foreach ($allClasses as $c) {
                $v = $verifikasiToday->get($c);
                if ($v) {
                    $totalVerified++;
                    $verifikasiRekap[] = [
                        'kelas' => $c,
                        'status' => 'Sudah Verifikasi',
                        'detail' => ($v->status == 'NIHIL') ? "NIHIL (Hadir Semua - {$v->total} Siswa)" : "{$v->sakit} Sakit, {$v->izin} Izin, {$v->alpha} Alpha, {$v->dispen} Terlambat, {$v->hadir} Hadir dari {$v->total} Siswa",
                        'verified_by' => \App\Models\User::find($v->verified_by)->name ?? 'Sistem',
                        'time' => \Carbon\Carbon::parse($v->updated_at)->format('H:i')
                    ];
                } else {
                    $totalUnverified++;
                    $verifikasiRekap[] = [
                        'kelas' => $c,
                        'status' => 'Belum Verifikasi',
                        'detail' => '-',
                        'verified_by' => '-',
                        'time' => '-'
                    ];
                }
            }
        }

        view()->share(compact(
            'classesNotFilled', 'waliClassNotFilled', 'todayJurnalFilled', 'guruScheduleNotFilled', 'attendanceData',
            'unreadNotifications', 'managedClass', 'todayVerification', 'currentDetailStr',
            'verifikasiRekap', 'totalClasses', 'totalVerified', 'totalUnverified'
        ));

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
    $totalkosong = $kosong ? ($kosong->n1+$kosong->n2+$kosong->n3+$kosong->n4+$kosong->n5+$kosong->n6+$kosong->n7+$kosong->n8+$kosong->n9+$kosong->n10+$kosong->n11) : 0;


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
    $totalok = $ok ? ($ok->n1+$ok->n2+$ok->n3+$ok->n4+$ok->n5+$ok->n6+$ok->n7+$ok->n8+$ok->n9+$ok->n10+$ok->n11) : 0;

        $data_jrekap= \App\Models\Jrekap::where('kelas',auth()->user()->name)
        ->orderBy('created_at','desc')->get();

    //S/I/A
    
    $sakit=$data_absen= \App\Models\Absen::
             where('ket','Sakit')->where('kelas',auth()->user()->name)
             ->whereDate('created_at',now())
             ->count();

        $ijin=$data_absen= \App\Models\Absen::
             where('ket','Ijin')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
             ->count();

             $alpha=$data_absen= \App\Models\Absen::
             where('ket','Alpha')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
             ->count();

            $data_absen= \App\Models\Absen::where('kelas',auth()->user()->name)->whereDate('created_at',now())->orderBy('created_at','desc')->get();
            
            
            $ijinpesiar= Ijinsiswa::
             where('ketijin','Ijin Pesiar')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
             ->count();
             $ijinbermalamwajib= Ijinsiswa::
             where('ketijin','Ijin Bermalam')->where('kelas',auth()->user()->name)->whereDate('created_at',now())
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
    $totalkosong = $kosong ? ($kosong->n1+$kosong->n2+$kosong->n3+$kosong->n4+$kosong->n5+$kosong->n6+$kosong->n7+$kosong->n8+$kosong->n9+$kosong->n10+$kosong->n11) : 0;


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
    $totalok = $ok ? ($ok->n1+$ok->n2+$ok->n3+$ok->n4+$ok->n5+$ok->n6+$ok->n7+$ok->n8+$ok->n9+$ok->n10+$ok->n11) : 0;

    //S/I/A

    $sakit= \App\Models\Absen::
             where('ket','Sakit')->whereDate('created_at',now())
             ->count();

        $ijin= \App\Models\Absen::
             where('ket','Ijin')->whereDate('created_at',now())
             ->count();

             $alpha= \App\Models\Absen::
             where('ket','Alpha')->whereDate('created_at',now())
             ->count();

            $data_absen= \App\Models\Absen::whereDate('created_at',now())->orderBy('created_at','desc')->get();

            $absenguru= DB::table('ijin')
                        ->where('guru',auth()->user()->name)
                        ->whereBetween('created_at',['2020-01-02', now()])
                        ->sum('jumlah');   
      
      $ijinpesiar= Ijinsiswa::
                where('ketijin','Ijin Pesiar')->whereDate('created_at',now())
                ->count();
                $ijinbermalamwajib= Ijinsiswa::
                where('ketijin','Ijin Bermalam')->whereDate('created_at',now())
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
           
            $sakit=$data_absen= \App\Models\Absen::
            where('ket','Sakit')->where('nama',auth()->user()->name)
            ->count();

           $ijin=$data_absen= \App\Models\Absen::
            where('ket','Ijin')->where('nama',auth()->user()->name)
            ->count();

            $alpha=$data_absen= \App\Models\Absen::
            where('ket','Alpha')->where('nama',auth()->user()->name)
            ->count(); 
            
            //     $telat= \App\Models\Absen::
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
            where('ketijin','Ijin Bermalam')->where('nama',auth()->user()->name)->whereDate('created_at',now())
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
            
       
       $data_absen= \App\Models\Absen::where('nama',auth()->user()->name)->orderBy('created_at','desc')->get();
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
    $totalkosong = $kosong ? ($kosong->n1+$kosong->n2+$kosong->n3+$kosong->n4+$kosong->n5+$kosong->n6+$kosong->n7+$kosong->n8+$kosong->n9+$kosong->n10+$kosong->n11) : 0;


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
    $totalok = $ok ? ($ok->n1+$ok->n2+$ok->n3+$ok->n4+$ok->n5+$ok->n6+$ok->n7+$ok->n8+$ok->n9+$ok->n10+$ok->n11) : 0;

    //S/I/A

	$ijinpesiar= Ijinsiswa::
             where('ketijin','Ijin Pesiar')->whereDate('created_at',now())
             ->count();
             $ijinbermalamwajib= Ijinsiswa::
             where('ketijin','Ijin Bermalam')->whereDate('created_at',now())
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

             $sakit= \App\Models\Absen::
             where('ket','Sakit')->whereDate('created_at',now())
             ->count();

        $ijin= \App\Models\Absen::
             where('ket','Ijin')->whereDate('created_at',now())
             ->count();

             $alpha= \App\Models\Absen::
             where('ket','Alpha')->whereDate('created_at',now())
             ->count();

            $data_ijin= \App\Models\Ijinsiswa::whereDate('created_at',now())->orderBy('created_at','desc')->get();
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

    public function verifikasiAbsensi(Request $request)
    {
        $user = auth()->user();
        $managedClass = $user->getManagedClass();
        if (!$managedClass) {
            return redirect()->back()->with('gagal', 'Anda tidak memiliki otoritas untuk memverifikasi absensi kelas.');
        }

        $todayStr = now()->toDateString();
        $tahun_ajaran = session('tahun_ajaran');
        $semester = session('semester');

        // Hitung detail kehadiran dari tabel absen untuk kelas ini hari ini
        $absenToday = \App\Models\Absen::where('kelas', $managedClass)
            ->whereDate('created_at', $todayStr)
            ->get();

        $sakit  = $absenToday->where('ket', 'Sakit')->count();
        $izin   = $absenToday->where('ket', 'Ijin')->count();
        $alpha  = $absenToday->where('ket', 'Alpha')->count();
        $dispen = $absenToday->where('ket', 'Dispen')->count();

        // Get total students in this class
        $totalSiswa = \App\Models\Siswa::where('kelas', $managedClass)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->count();

        $hadir  = max(0, $totalSiswa - ($sakit + $izin + $alpha + $dispen));
        $status = ($sakit + $izin + $alpha + $dispen == 0) ? 'NIHIL' : 'ADA_ABSEN';

        // Cek apakah sudah ada record verifikasi hari ini
        $existing = DB::table('verifikasi_absensi')
            ->where('kelas', $managedClass)
            ->whereDate('tanggal', $todayStr)
            ->first();

        $updateData = [
            'status'      => $status,
            'sakit'       => $sakit,
            'izin'        => $izin,
            'alpha'       => $alpha,
            'dispen'      => $dispen,
            'hadir'       => $hadir,
            'total'       => $totalSiswa,
            'verified_by' => $user->id,
            'updated_at'  => now(),
        ];

        if ($existing) {
            // Update record yang sudah ada (tanpa mengubah created_at)
            DB::table('verifikasi_absensi')
                ->where('kelas', $managedClass)
                ->whereDate('tanggal', $todayStr)
                ->update($updateData);
        } else {
            // Insert baru
            DB::table('verifikasi_absensi')->insert(array_merge($updateData, [
                'kelas'      => $managedClass,
                'tanggal'    => $todayStr,
                'created_at' => now(),
            ]));
        }

        // Kirim Notifikasi
        $detail = ($status == 'NIHIL')
            ? "NIHIL (Hadir Semua - {$totalSiswa} Siswa)"
            : "{$sakit} Sakit, {$izin} Izin, {$alpha} Alpha, {$dispen} Terlambat, {$hadir} Hadir dari {$totalSiswa} Siswa";
        $title   = "Absensi Pagi Kelas {$managedClass} Terverifikasi";
        $message = "Kelas {$managedClass} telah diverifikasi absensi pagi oleh "
            . ($user->role == 'ketuakelas' ? 'Ketua Kelas' : 'Wali Kelas')
            . " ({$detail}).";

        $kurikulumAndAdmin = \App\Models\User::whereIn('role', ['admin', 'kurikulum', 'kepala'])->get();
        foreach ($kurikulumAndAdmin as $u) {
            $u->sendNotification($title, $message, '/dashboard', 'absen');
        }

        if ($user->role == 'ketuakelas') {
            $wk = \App\Models\User::where(function($q) use ($managedClass) {
                $q->where('role', 'walikelas')->where('name', $managedClass);
            })->orWhere(function($q) use ($managedClass) {
                $q->where('role', 'guru')->where('walikelas_kelas', $managedClass);
            })->get();
            foreach ($wk as $w) {
                $w->sendNotification($title, "Ketua kelas Anda telah memverifikasi absensi pagi: {$detail}.", '/dashboard', 'absen');
            }
        }

        if ($user->role == 'walikelas' || ($user->role == 'guru' && $user->walikelas_kelas)) {
            $kk = \App\Models\User::where('role', 'ketuakelas')->where('name', $managedClass)->get();
            foreach ($kk as $k) {
                $k->sendNotification($title, "Wali kelas Anda telah memverifikasi absensi pagi hari ini: {$detail}.", '/dashboard', 'absen');
            }
        }

        $verb = $existing ? 'Diperbarui' : 'Tersimpan';
        return redirect('/dashboard')->with('sukses', "Absensi Pagi Berhasil {$verb}!");
    }

    public function readNotification($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
        $actionUrl = $data['action_url'] ?? '/dashboard';
        
        return redirect($actionUrl);
    }

    public function markAllNotificationsAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('sukses', 'Semua notifikasi berhasil ditandai sebagai dibaca.');
    }
}

