<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\JurnalExport;
use App\Exports\JurnalkelasExport;
use App\Exports\JurnalTanggalExport;
// use Maatwebsite\Excel\Facades\Excel;
use App\Mapel;
use App\Guru;
use App\Kelas;
use App\Jurnal;
use App\Siswa;
use App\User;
use App\Jurnalh;
use Excel;
use DB;
use DateTime;
use App\Exports\RekapJurnalExport;
use Barryvdh\DomPDF\Facade as PDF;

class JurnalController extends Controller
{   

    public function index(Request $request)
    {
        $ma_pel=Mapel::all();
        $gu_ru=Guru::all();
        $ke_las=Kelas::all();

        

        // Ambil data jurnal berdasarkan rentang tanggal yang dipilih
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $rekapData = DB::table('jurnal')
            ->select(DB::raw('guru, 
                COUNT(DISTINCT CASE WHEN ket_guru_mapel = "Hadir" THEN created_at END) AS hadir_per_hari, 
                SUM(CASE WHEN penugasan = "Ada" THEN 1 ELSE 0 END) AS penugasan, 
                SUM(CASE WHEN materi = "Jam Kosong" THEN 1 ELSE 0 END) AS jam_kosong'))
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('guru')
            ->get();

// Role Siswa
    if (auth()->user()->role == 'siswa') {
        // Mengambil kelas siswa yang sedang login
        $siswa = Siswa::where('nama', auth()->user()->name)->first(); // Sesuaikan dengan username atau nis
        $kelasSiswa = $siswa ? $siswa->kelas : null; // Ambil kelas siswa

        // dd($siswa);

        // Jika kelas ditemukan, ambil data jurnal berdasarkan kelas
        if ($kelasSiswa) {
            $data_jurnal = Jurnal::where('kelas', $kelasSiswa)
                ->orderBy('created_at', 'desc')
                ->get();

            // Pisahkan materi menjadi teks dan URL
            foreach ($data_jurnal as $jurnal) {
                $materi_text = '';
                $materi_url = '';

                // Memisahkan teks dan URL
                $materi = $jurnal->materi;
                $url_position = strrpos($materi, 'http'); // Menemukan posisi URL dalam string
                if ($url_position !== false) {
                    $materi_text = substr($materi, 0, $url_position); // Ambil teks sebelum URL
                    $materi_url = substr($materi, $url_position); // Ambil URL
                } else {
                    $materi_text = $materi; // Jika tidak ada URL, simpan materi sebagai teks biasa
                }

                // Menyimpan hasil pemisahan dalam properti jurnal
                $jurnal->materi_text = $materi_text;
                $jurnal->materi_url = $materi_url;
            }

            return view('jurnal.index', ['data_jurnal' => $data_jurnal], compact('ma_pel', 'gu_ru', 'ke_las'));
        }

        // Jika kelas tidak ditemukan
        return redirect()->back()->with('error', 'Kelas siswa tidak ditemukan.');
    }elseif (auth()->user()->role=='ketuakelas' AND is_null($request->crtgl)) {
        $data_jurnal= Jurnal::where('kelas',auth()->user()->name)
        ->orderBy('created_at','desc')->get();
        return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));

        } elseif (auth()->user()->role=='ketuakelas' AND $request->filled('crtgl')){
           $data_jurnal= Jurnal::where('kelas',auth()->user()->name)
        ->orderBy('created_at','desc')
        ->whereDate('created_at','LIKE','%'.$request->crtgl.'%')->get();
        return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las')); 
        }
       

         elseif ($request->get('action')=='kelas') {

        
            // Aksi
         $data_jurnal= Jurnal::where('kelas',$request->kelas)
        ->orderBy('created_at','desc')->get();
        return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las')); 
        // dd($request->kelas);
        }

        elseif ($request->get('action')=='tanggal') {
            // Aksi
        
        $data_jurnal= Jurnal::whereDate('created_at',$request->crtgl)
        ->orderBy('created_at','desc')->get();
        return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));
        // dd($request->crtgl);
            
    } 

        else {


        $data_jurnal= Jurnal::orderBy('created_at','desc')->whereBetween('created_at',['2020-01-07', now()])->get();

        // Pisahkan materi menjadi teks dan URL
    foreach ($data_jurnal as $jurnal) {
        $materi_text = '';
        $materi_url = '';

        // Memisahkan teks dan URL
        $materi = $jurnal->materi;
        $url_position = strrpos($materi, 'http'); // Menemukan posisi URL dalam string
        if ($url_position !== false) {
            $materi_text = substr($materi, 0, $url_position); // Ambil teks sebelum URL
            $materi_url = substr($materi, $url_position); // Ambil URL
               } else {
            $materi_text = $materi; // Jika tidak ada URL, simpan materi sebagai teks biasa
        }

        // Menyimpan hasil pemisahan dalam properti jurnal
        $jurnal->materi_text = $materi_text;
        $jurnal->materi_url = $materi_url;
        
    } 
        return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las','rekapData', 'startDate', 'endDate'));
        }
        // if (auth()->user()->role=='ketuakelas' AND $request->filled('filter')) {
        // $data_jurnal= \App\Jurnal::where('kelas',auth()->user()->name)
        // ->orderBy('created_at','desc')
        // ->whereDate('created_at','LIKE','%'.$request->filter.'%')->get();
        // return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));

        // }else if (auth()->user()->role=='ketuakelas' AND is_null($request->filter)) {
        // $data_jurnal= \App\Jurnal::where('kelas',auth()->user()->name)
        // ->orderBy('created_at','desc')
        // ->get();
        // return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));            
        // } 

        // elseif (auth()->user()->role=='lihat' AND $request->filled('filter')) {
        
        // $data_jurnal= \App\Jurnal::orderBy('created_at','desc')
        // ->whereDate('created_at','LIKE','%'.$request->filter.'%')->get();
        // return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));

        // }else if (auth()->user()->role=='lihat' AND is_null($request->filter)) {
        // $data_jurnal= \App\Jurnal::orderBy('created_at','desc')
        // ->get();
        // return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));            
        // }

        //  else if(auth()->user()->role=='lihat' AND $request->filled('cari')){

        // $data_jurnal= \App\Jurnal::orderBy('created_at','desc')
        // ->where('kelas','LIKE','%'.$request->cari.'%')->get();
        // return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));
        // } else if(auth()->user()->role=='lihat' AND is_null($request->cari)){

        // $data_jurnal= \App\Jurnal::orderBy('created_at','desc')
        // ->get();
        // return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));
        // } 
        //  else {
        // $data_jurnal= \App\Jurnal::orderBy('created_at','desc')->get();
        // return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));    
        // }

    	// if ($request->filled('filter')) {
    	// 	$data_jurnal= \App\Jurnal::whereDate('created_at','LIKE','%'.$request->filter.'%')->orderBy('waktu','desc')->get();\
        

     //    }  elseif($request->filled('cari')){ 

     //         $data_jurnal= \App\Jurnal::
     //         where('kelas','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
     //         ->get()
     //         ;

     //    }



     //    else{
    	// 	$data_jurnal= \App\Jurnal::orderBy('created_at','desc')->get();

   
    	// }



    	// return view('jurnal.index',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));
    }

    // public function caritgl(Request $request)
    // {   
    //     $ma_pel=Mapel::all();
    //     $gu_ru=Guru::all();
    //     $ke_las=Kelas::all();

    //     $data_jurnal= \App\Jurnal::whereDate('created_at','LIKE','%'.$request->filter.'%')->orderBy('created_at','desc')->get();
    //     return view('jurnal.caritgl',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));        
    // }

    //     public function carikls(Request $request)
    // {   
    //     $ma_pel=Mapel::all();
    //     $gu_ru=Guru::all();
    //     $ke_las=Kelas::all();

    //     $data_jurnal= \App\Jurnal::orderBy('created_at','desc')
    //     ->where('kelas','LIKE','%'.$request->cari.'%')->get();
    //     return view('jurnal.carikls',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));        
    // }     

    public function tambahj()
    {	
    	$ma_pel=Mapel::all();
    	$gu_ru=Guru::all();
    	$ke_las=Kelas::all();
        $jur_nal=Jurnal::all();
    	return view('jurnal.tambahjurnal',compact('ma_pel','gu_ru','ke_las','jur_nal'));
    }

    public function lihatj(Request $request)
    {
    	//$data_jurnal= \App\Jurnal::all();
    $ke_las=Kelas::all();    
    if($request->filled('cari')){ 
             $data_jurnal= Jurnal::
             where('kelas','LIKE','%'.$request->cari.'%')->orderBy('created_at','desc')
             ->whereDate('created_at',now())
             ->get()
             ;
        }
        else{   
        
    $data_jurnal= Jurnal::whereDate('created_at',now())->orderBy('created_at','desc')->get();

    	
    }
    return view('jurnal.lihatjurnal',['data_jurnal' => $data_jurnal],compact('ke_las'));
}
    
    public function create(Request $request)
    {	
        $guru = DB::table('jurnal')->select('guru')
        // $guru=\App\Jurnal::
        ->where('guru','LIKE','%'.$request->guru.'%')
        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',now())
        ->first();

        $time = DB::table('jurnal')->select('created_at')
        // $guru=\App\Jurnal::
        ->where('guru','LIKE','%'.$request->guru.'%')
        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',now())
        ->first();


        // dd($request->guru);
        // dd($guru);
    	if (is_null($guru) AND is_null($time)) {
            # code...

             // \App\Jurnal::create($request->all());
            
            // $user=DB::table('jurnal')->join('users','jurnal.guru','=','users.name')
            //   ->select('users.id')->where('jurnal.guru','LIKE','%'.$request->guru.'%')->first();
              $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();
             // dd($user);

             DB::table('jurnal')->insert(['kelas'=>$request->kelas,'ket_guru_mapel'=>$request->ket_guru_mapel,'penugasan'=>$request->penugasan,'jamke'=>$request->jamke,'jumlahjam'=>$request->jumlahjam,'mapel'=>$request->mapel,'guru'=>$request->guru,'absen'=>$request->absen,'dispen'=>$request->dispen,'materi'=>$request->materi,'catatan'=>$request->catatan,'waktu'=>$request->waktu,'guru_id'=>$user->id,'created_at'=>now(),'updated_at'=>now()]);

             // DB::table('jurnal')->where('guru_id','LIKE','0')->update(['guru_id'=>$user]);
             return redirect('/tambahjurnal')->with('sukses','Jurnal Berhasil Ditambahkan');


            
        }
        else{
           return redirect('/tambahjurnal')->with('gagal','Jurnal Sudah Ada, Tidak Bisa Ditambahkan Lagi !!!');

        }
    	
    	
    }

    public function createsusul(Request $request)
    {   
        
    //  \App\Jurnal::create($request->all());
        // $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();

        // \App\Jurnal::create(['kelas' => $request->kelas,
        // 'ket_guru_mapel'=> $request->ket_guru_mapel,
        // 'penugasan'=> $request->penugasan,
        // 'jamke'=> $request->jamke,
        // 'jumlahjam'=> $request->jumlahjam,
        // 'mapel'=> $request->mapel,
        // 'guru'=> $request->guru,
        // 'materi'=> $request->materi,
        // 'catatan'=> $request->catatan,
        // 'guru_id'=>$user->id,
        // 'created_at'=> $request->tgl
        // ]);
        
        //update di jrekap dan tambah di jurnal

        $guru = DB::table('jurnal')->select('guru')

        ->where('guru','LIKE','%'.$request->guru.'%')
        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',$request->tgl)
        ->first();

        $time = DB::table('jurnal')->select('created_at')

        ->where('guru','LIKE','%'.$request->guru.'%')
        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',$request->tgl)
        ->first();

        $tglpilih=$request->tgl;
        $skr=(new DateTime($tglpilih))->format('l');

        $jadwal = DB::table('jadwal')->where('hari','=',$skr)
        ->where('guru','LIKE','%'.$request->guru.'%')
        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->first();

        $rekap = DB::table('jrekap')->select('kelas')

        ->where('kelas','LIKE','%'.$request->kelas.'%')
        ->whereDate('created_at',$request->tgl)
        ->first();

        if (is_null($guru) AND is_null($time) AND is_null($rekap)) {

              $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();
              //tambah ke tabel jurnal
             DB::table('jurnal')->insert(['kelas'=>$jadwal->kelas,'ket_guru_mapel'=>$request->ket_guru_mapel,'penugasan'=>$request->penugasan,'jamke'=>$jadwal->jamke,'jumlahjam'=>$jadwal->jumlahjam,'mapel'=>$jadwal->mapel,'guru'=>$jadwal->guru,'materi'=>$request->materi,'catatan'=>$request->catatan,'guru_id'=>$user->id,'created_at'=>$request->tgl,'updated_at'=>$request->tgl]);
             //tambah ke tabel jrekap
            DB::table('jrekap')->insert(['kelas'=>$jadwal->kelas,'j1'=>$jadwal->j1,'j2'=>$jadwal->j2,'j3'=>$jadwal->j3,'j4'=>$jadwal->j4,'j5'=>$jadwal->j5,'j6'=>$jadwal->j6,'j7'=>$jadwal->j7,'j8'=>$jadwal->j8,'j9'=>$jadwal->j9,'j10'=>$jadwal->j10,'j11'=>$jadwal->j11,'created_at'=>$request->tgl,'updated_at'=>$request->tgl]);

        
        return redirect('/jurnal')->with('sukses','Jurnal Berhasil Ditambahkan');
    }
            elseif(is_null($guru) AND is_null($time) AND isset($rekap)){

            $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();
            //tambah ke tabel jurnal
             DB::table('jurnal')->insert(['kelas'=>$jadwal->kelas,'ket_guru_mapel'=>$request->ket_guru_mapel,'penugasan'=>$request->penugasan,'jamke'=>$jadwal->jamke,'jumlahjam'=>$jadwal->jumlahjam,'mapel'=>$jadwal->mapel,'guru'=>$jadwal->guru,'materi'=>$request->materi,'catatan'=>$request->catatan,'guru_id'=>$user->id,'created_at'=>$request->tgl,'updated_at'=>$request->tgl]);
             //update tabel jrekap yang sudah ada
        $row = DB::table('jrekap')
            ->where('kelas','LIKE','%'.$request->kelas.'%')
            ->whereDate('created_at',$request->tgl)
            ->first();

        $jam=collect([null,$row->j1,$row->j2,$row->j3,$row->j4,$row->j5,$row->j6,$row->j7,$row->j8,$row->j9,$row->j10,$row->j11]);

        $requestget=collect([null,$jadwal->j1,$jadwal->j2,$jadwal->j3,$jadwal->j4,$jadwal->j5,$jadwal->j6,$jadwal->j7,$jadwal->j8,$jadwal->j9,$jadwal->j10,$jadwal->j11]);
        
        $kolom=collect([null,'j1','j2','j3','j4','j5','j6','j7','j8','j9','j10','j11']);
        
        for ($i=1; $i<=11; $i++){
            
        
        if($jam[$i] != 'ok') {
            

            DB::table('jrekap')->where('kelas','LIKE','%'.$request->kelas.'%')
                ->whereDate('created_at',$request->tgl)
            ->update([$kolom[$i]=>$requestget[$i]]);    
        }
        }
      
            return redirect('/jurnal')->with('sukses','Jurnal Berhasil Ditambahkan');
            }

            else{
           return redirect('/jurnal')->with('gagal','Jurnal Sudah Ada, Tidak Bisa Ditambahkan Lagi !!!');

        }

        
    }

    public function export() 
    {
        return Excel::download(new JurnalExport, 'Jurnal.xlsx');
    }

    public function exporttanggal(Request $request) 
    {   
        $tanggal = $request->input('filterexport');
    
        return Excel::download(new JurnalTanggalExport($tanggal), 'JurnalTanggal.xlsx');
    }
    

    public function edits(Request $request)
    {   
       $ma_pel=Mapel::all();
        $gu_ru=Guru::all();
        $ke_las=Kelas::all();

           $data_jurnal= Jurnal::where('kelas',auth()->user()->name)
           ->whereDate('created_at',now())->orderBy('created_at','desc')->get();    
           return view('jurnal.edits',['data_jurnal' => $data_jurnal],compact('ma_pel','gu_ru','ke_las'));
}

//khusus update siswa
    public function updates(Request $request)
    {   
        // PERBAIKAN UTAMA: Konsistensi pemisah dan logika update yang lebih akurat

if ($request->penugasan == 'Ada') {
    // PERBAIKAN: Gunakan <hr> konsisten, bukan <br>
    $jurnalValue = $request->guru . "<hr>" . $request->mapel . "<hr>" . $this->formatMateri($request->materi) . "<hr>" . "<span class='badge badge-danger'>KBM Tanpa Guru</span>";
} else {
    // Jika tidak ada penugasan
    $jurnalValue = $request->guru . "<hr>" . $request->mapel . "<hr>" . $this->formatMateri($request->materi);
}

// Debug untuk melihat nilai yang dibentuk
\Log::info('=== JURNAL VALUE FORMATION ===');
\Log::info('Penugasan: ' . $request->penugasan);
\Log::info('Guru: ' . $request->guru);
\Log::info('Mapel: ' . $request->mapel);
\Log::info('Materi: ' . $request->materi);
\Log::info('Formatted Jurnal Value: ' . $jurnalValue);

// Pisahkan rentang jamke (misalnya "1-3")
$jamkeParts = explode('-', $request->jamke);
$startJamke = (int)$jamkeParts[0]; // Jamke awal
$endJamke = (int)($jamkeParts[1] ?? $startJamke); // Jamke akhir (jika ada)

        //mengambil id jurnal untuk menyamakan tanggal
        $jurnal=Jurnal::findorFail($request->jurnalid);

        
// Cek apakah jurnalh untuk kelas dan tanggal tertentu sudah ada
$existingJurnalh = Jurnalh::where('kelas', $request->kelas)
    ->whereDate('created_at', $jurnal->created_at->toDateString()) // Periksa berdasarkan tanggal
    ->first();

// Jika jurnalh untuk kelas dan tanggal hari ini tidak ada
if ($existingJurnalh == null) {
    // Menampilkan notifikasi pop-up jika data tidak ditemukan
    return redirect('/jurnalbaru')->with('gagal', 'Update Jurnal GAGAL, Jadwal hari ini belum disinkron!');
}

// FUNCTION YANG DIPERBAIKI untuk mengganti atau menambah data di kolom jurnal
function updateJurnalColumn($existingValue, $newValue, $targetGuru, $targetMapel) {
    \Log::info('=== UPDATE JURNAL COLUMN START ===');
    \Log::info('Existing Value: ' . ($existingValue ?? 'NULL'));
    \Log::info('New Value: ' . $newValue);
    \Log::info('Target Guru: "' . $targetGuru . '"');
    \Log::info('Target Mapel: "' . $targetMapel . '"');
    
    if (empty($existingValue)) {
        // Jika kolom kosong, langsung isi dengan data baru
        \Log::info('Column is empty, using new value directly');
        return $newValue;
    }
    
    // Bersihkan multiple <hr> yang berturut-turut
    $cleanedValue = preg_replace('/<hr>+/', '<hr>', $existingValue);
    $cleanedValue = trim($cleanedValue, '<hr>'); // Hapus <hr> di awal dan akhir
    
    // Split berdasarkan <hr> untuk mendapatkan segmen-segmen jurnal
    $segments = explode('<hr>', $cleanedValue);
    $updatedSegments = [];
    $found = false;
    
    \Log::info('Cleaned Value: ' . $cleanedValue);
    \Log::info('Total segments: ' . count($segments));
    
    // Parse new value segments untuk referensi
    $newSegments = explode('<hr>', $newValue);
    $newGuruSegment = $newSegments[0] ?? '';
    $newMapelSegment = $newSegments[1] ?? '';
    $newMateriSegment = $newSegments[2] ?? '';
    $newBadgeSegment = $newSegments[3] ?? '';
    
    \Log::info('New segments - Guru: "' . $newGuruSegment . '", Mapel: "' . $newMapelSegment . '"');
    
    // Process dalam grup 3 atau 4 segmen (guru, mapel, materi, [badge])
    for ($i = 0; $i < count($segments);) {
        if (isset($segments[$i], $segments[$i + 1], $segments[$i + 2])) {
            $guruSegment = trim($segments[$i]);
            $mapelSegment = trim($segments[$i + 1]);
            $materiSegment = trim($segments[$i + 2]);
            
            // Cek apakah ada badge segment
            $badgeSegment = '';
            $currentSegmentSize = 3;
            
            if (isset($segments[$i + 3]) && (strpos($segments[$i + 3], 'badge') !== false || strpos($segments[$i + 3], 'KBM') !== false)) {
                $badgeSegment = trim($segments[$i + 3]);
                $currentSegmentSize = 4;
            }
            
            // Extract clean guru dan mapel untuk perbandingan
            $cleanGuru = trim(strip_tags($guruSegment));
            $cleanMapel = trim(strip_tags($mapelSegment));
            
            // Normalize untuk perbandingan yang akurat
            $normalizedCleanGuru = preg_replace('/\s+/', ' ', strtoupper($cleanGuru));
            $normalizedTargetGuru = preg_replace('/\s+/', ' ', strtoupper(trim($targetGuru)));
            $normalizedCleanMapel = preg_replace('/\s+/', ' ', strtoupper($cleanMapel));
            $normalizedTargetMapel = preg_replace('/\s+/', ' ', strtoupper(trim($targetMapel)));
            
            // Debug log
            \Log::info('Processing Entry ' . (floor($i/3) + 1) . ':');
            \Log::info('  Guru existing: "' . $cleanGuru . '" normalized: "' . $normalizedCleanGuru . '"');
            \Log::info('  Guru target: "' . $targetGuru . '" normalized: "' . $normalizedTargetGuru . '"');
            \Log::info('  Mapel existing: "' . $cleanMapel . '" normalized: "' . $normalizedCleanMapel . '"');
            \Log::info('  Mapel target: "' . $targetMapel . '" normalized: "' . $normalizedTargetMapel . '"');
            \Log::info('  Current segment size: ' . $currentSegmentSize);
            \Log::info('  Has badge: ' . (!empty($badgeSegment) ? 'YES ("' . $badgeSegment . '")' : 'NO'));
            
            // PERBAIKAN UTAMA: Pencocokan berdasarkan guru dan mapel
            if ($normalizedCleanGuru === $normalizedTargetGuru && $normalizedCleanMapel === $normalizedTargetMapel) {
                \Log::info('  *** MATCH FOUND! Updating existing entry ***');
                
                // Update dengan data baru (materi dan badge bisa berubah)
                $updatedSegments[] = $newGuruSegment;
                $updatedSegments[] = $newMapelSegment;
                $updatedSegments[] = $newMateriSegment;
                
                // Tambahkan badge jika ada
                if (!empty($newBadgeSegment)) {
                    $updatedSegments[] = $newBadgeSegment;
                }
                
                $found = true;
                \Log::info('  Entry updated with new materi and badge status');
            } else {
                \Log::info('  No match, preserving existing entry');
                // Pertahankan data yang sudah ada
                $updatedSegments[] = $guruSegment;
                $updatedSegments[] = $mapelSegment;
                $updatedSegments[] = $materiSegment;
                
                // Pertahankan badge jika ada
                if (!empty($badgeSegment)) {
                    $updatedSegments[] = $badgeSegment;
                }
            }
            
            // Move to next group
            $i += $currentSegmentSize;
        } else {
            // Handle sisa segment yang tidak lengkap
            if (isset($segments[$i])) {
                $updatedSegments[] = $segments[$i];
            }
            $i++;
        }
    }
    
    // Jika tidak ditemukan guru dan mapel yang cocok, tambahkan sebagai entry baru
    if (!$found) {
        \Log::info('No matching entry found, adding as new entry');
        $updatedSegments[] = $newGuruSegment;
        $updatedSegments[] = $newMapelSegment;
        $updatedSegments[] = $newMateriSegment;
        
        if (!empty($newBadgeSegment)) {
            $updatedSegments[] = $newBadgeSegment;
        }
    }
    
    // Bersihkan segment kosong
    $updatedSegments = array_filter($updatedSegments, function($segment) {
        return !empty(trim($segment));
    });
    
    $result = implode('<hr>', $updatedSegments);
    \Log::info('Final result: ' . $result);
    \Log::info('=== UPDATE JURNAL COLUMN END ===');
    
    return $result;
}

// Update kolom berdasarkan rentang jamke
for ($i = $startJamke; $i <= $endJamke; $i++) {
    if ($i >= 1 && $i <= 11) {
        $col = 'j' . $i;
        $existingValue = $existingJurnalh->{$col};
        
        \Log::info('=== UPDATING COLUMN ' . $col . ' ===');
        \Log::info('Before update: ' . ($existingValue ?? 'NULL'));
        
        // Update kolom dengan preserving data guru/mapel lain tapi update yang sama
        $updatedValue = updateJurnalColumn(
            $existingValue, 
            $jurnalValue, 
            $request->guru, 
            $request->mapel
        );
        
        $existingJurnalh->{$col} = $updatedValue;
        
        \Log::info('After update: ' . $updatedValue);
        \Log::info('=== END UPDATING COLUMN ' . $col . ' ===');
    }
}

// Update timestamp
$existingJurnalh->updated_at = now();

// Simpan perubahan
$existingJurnalh->save();

\Log::info('=== JURNAL UPDATE COMPLETED ===');

        $jurnal=Jurnal::findorFail($request->jurnalid);
        $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();
        
        // $jurnal=\App\Jurnal::find($id);
        // $jurnal->update($request->all());

    $jurnal->update(['ket_guru_mapel'=>$request->ket_guru_mapel,'penugasan'=>$request->penugasan,'jamke'=>$request->jamke,'jumlahjam'=>$request->jumlahjam,'mapel'=>$request->mapel,'guru'=>$request->guru,'materi'=>$request->materi,'catatan'=>$request->catatan,'guru_id'=>$user->id,'created_at'=>now(),'updated_at'=>now()]);
        return redirect('/edits')->with('sukses','Jurnal Berhasil Diupdate');
    }

    public function update(Request $request)
    {
        $jurnal=Jurnal::findorFail($request->jurnalid);
        $user=DB::table('users')->where('name','LIKE','%'.$request->guru.'%')->first();
        
        // $jurnal=\App\Jurnal::find($id);
        // $jurnal->update($request->all());

    $jurnal->update(['ket_guru_mapel'=>$request->ket_guru_mapel,'penugasan'=>$request->penugasan,'jamke'=>$request->jamke,'jumlahjam'=>$request->jumlahjam,'mapel'=>$request->mapel,'guru'=>$request->guru,'materi'=>$request->materi,'catatan'=>$request->catatan,'guru_id'=>$user->id,'updated_at'=>now()]);
        return redirect('/jurnal')->with('sukses','Jurnal Berhasil Diupdate');
    }

    public function delete($id)
    {
        $jurnal=Jurnal::find($id);
        $jurnal->delete();
        return redirect('/jurnal')->with('sukses','Jurnal Berhasil Dihapus');
    }

    public function editsexport() 
    {
        return Excel::download(new JurnalkelasExport, 'Jurnalkelas.xlsx');
    }  

                  // Fungsi untuk memformat materi, jika ada link maka tampilkan dengan badge hijau
            private function formatMateri($materi)
            {
                // Pola untuk mendeteksi URL (https:// atau http://)
                $pattern = '/(https?:\/\/[a-zA-Z0-9\/?=&%#\.\-]+)/';
            
                // Cek apakah materi mengandung URL
                if (preg_match($pattern, $materi, $matches)) {
                    // Ambil bagian sebelum URL (misalnya: "Percabangan Matematika")
                    $textBeforeLink = trim(substr($materi, 0, strpos($materi, $matches[0])));
            
                    // Format untuk menampilkan teks dan menyembunyikan URL, dengan link yang dapat diklik
                    return '<span class="badge badge-success"><a href="' . $matches[0] . '" target="_blank" style="text-decoration: none; color: inherit;">' . $textBeforeLink . '</a></span>';
                }
            
                // Jika tidak ada URL, kembalikan materi seperti semula
                return $materi;
            }


    

    // Export ke Excel
public function exportExcel(Request $request)
{
    // Ambil tanggal dari request
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    if (!$startDate || !$endDate) {
        return redirect()->back()->with('gagal', 'Tanggal belum dipilih!');
    }

    // Melakukan export ke Excel
    return Excel::download(new RekapJurnalExport($startDate, $endDate), 'Rekap_Jurnal.xlsx');
}

// Export ke PDF
public function exportPDF(Request $request)
{
    // Ambil tanggal dari request
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

     // Gunakan Carbon untuk memformat tanggal dalam format "Hari, Tanggal Bulan Tahun"
    $startDateFormatted = Carbon::parse($startDate)->isoFormat('dddd, D MMMM YYYY');
    $endDateFormatted = Carbon::parse($endDate)->isoFormat('dddd, D MMMM YYYY');

    if (!$startDate || !$endDate) {
        return redirect()->back()->with('gagal', 'Tanggal belum dipilih!');
    }

    // Ambil data jurnal berdasarkan rentang tanggal
    $rekapData = DB::table('jurnal')
        ->select(DB::raw('guru, 
            COUNT(DISTINCT CASE WHEN ket_guru_mapel = "Hadir" THEN created_at END) AS hadir_per_hari, 
            SUM(CASE WHEN penugasan = "Ada" THEN 1 ELSE 0 END) AS penugasan, 
            SUM(CASE WHEN materi = "Jam Kosong" THEN 1 ELSE 0 END) AS jam_kosong'))
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('guru')
        ->get();

    // Generate PDF
    $pdf = PDF::loadView('jurnal.rekap_pdf', compact('rekapData', 'startDate', 'endDate', 'startDateFormatted', 'endDateFormatted'));

    return $pdf->download('Rekap_Jurnal.pdf');
}


}

