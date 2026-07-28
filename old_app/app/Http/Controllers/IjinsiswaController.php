<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\IjinsiswaExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Ijinsiswa;
use App\Siswa;
use DB;

class IjinsiswaController extends Controller
{
    public function index(Request $request)
    {
         if(auth()->user()->role=='siswa'){
       $data_ijinsiswa= Ijinsiswa::where('nama',auth()->user()->name)->orderBy('created_at','desc')->get();
     
         return view('ijinsiswa.index',['data_ijinsiswa' => $data_ijinsiswa]);
         } elseif(auth()->user()->role=='walikelas') {
            $data_ijinsiswa= Ijinsiswa::where('kelas',auth()->user()->name)->orderBy('created_at','desc')->get();
            return view('ijinsiswa.index',['data_ijinsiswa' => $data_ijinsiswa]);   
         }
         else {
        $data_ijinsiswa=Ijinsiswa::orderBy('created_at','desc')->get();

    	return view('ijinsiswa.index',['data_ijinsiswa' => $data_ijinsiswa]);
        }
    }

    public function verifikasi($id)
{   
    // Ambil data ijin siswa berdasarkan ID
    $ijinsiswa = Ijinsiswa::find($id);
    if (!$ijinsiswa) {
        return redirect('/ijinsiswa')->with('gagal', 'Data ijin tidak ditemukan');
    }

    // Ambil data siswa terkait
    $siswa = Siswa::where('nama', $ijinsiswa->nama)->first();
    if (!$siswa) {
        return redirect('/ijinsiswa')->with('gagal', 'Data siswa tidak ditemukan');
    }

    switch ($ijinsiswa->ketijin) {
        case 'Ijin Pesiar':
            switch (auth()->user()->role) {
                case 'pembina':
                    Ijinsiswa::where('id', $id)->update([
                        'oksis' => 'ok',
                        'okkur' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                case 'kesehatan':
                    Ijinsiswa::where('id', $id)->update([
                        'okbin' => 'ok',
                        'okas' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                default:
                    return redirect('/ijinsiswa')->with('gagal', 'Peran tidak sesuai untuk jenis ijin ini');
            }
            
    
        case 'Ijin Bermalam Wajib':
            switch (auth()->user()->role) {
                case 'walikelas':
                    Ijinsiswa::where('id', $id)->update([
                        'okbin' => 'ok',
                        'okas' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                case 'kurikulum':
                    Ijinsiswa::where('id', $id)->update([
                        'okkur' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                case 'pembina':
                    Ijinsiswa::where('id', $id)->update([
                        'oksis' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                default:
                    return redirect('/ijinsiswa')->with('gagal', 'Peran tidak sesuai untuk jenis ijin ini');
            }
            
    
        case 'Ijin Bermalam Resmi':
            switch (auth()->user()->role) {
                case 'walikelas':
                    Ijinsiswa::where('id', $id)->update([
                        'okbin' => 'ok',
                        'okas' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                case 'kurikulum':
                    Ijinsiswa::where('id', $id)->update([
                        'okkur' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                case 'pembina':
                    Ijinsiswa::where('id', $id)->update([
                        'oksis' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                default:
                    return redirect('/ijinsiswa')->with('gagal', 'Peran tidak sesuai untuk jenis ijin ini');
            }
            
    
        case 'Ijin Jalan':
            switch (auth()->user()->role) {
                case 'walikelas':
                    Ijinsiswa::where('id', $id)->update([
                        'okbin' => 'ok',
                        'okas' => 'ok',
                        'okkur' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                case 'kurikulum':
                    Ijinsiswa::where('id', $id)->update([
                        'okkur' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                case 'pembina':
                    Ijinsiswa::where('id', $id)->update([
                        'oksis' => 'ok',
                    ]);
                    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
                default:
                    return redirect('/ijinsiswa')->with('gagal', 'Peran tidak sesuai untuk jenis ijin ini');
            }
            
    
        case 'Ijin Khusus':
            if (auth()->user()->role === 'kepala') {
                Ijinsiswa::where('id', $id)->update([
                    'oksis' => 'ok',
                    'okkur' => 'ok',
                    'okbin' => 'ok',
                    'okas' => 'ok',
                ]);
                return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
            } else {
                return redirect('/ijinsiswa')->with('gagal', 'Peran tidak sesuai untuk jenis ijin ini');
            }
    
        default:
            return redirect('/ijinsiswa')->with('gagal', 'Jenis ijin tidak dikenal');
    }
    
}

public function suratsalah($id)
{   
    // Ambil data ijin siswa berdasarkan ID
    $ijinsiswa = Ijinsiswa::find($id);
    if (!$ijinsiswa) {
        return redirect('/ijinsiswa')->with('gagal', 'Data ijin tidak ditemukan');
    }

    // Ambil data siswa terkait
    $siswa = Siswa::where('nama', $ijinsiswa->nama)->first();
    if (!$siswa) {
        return redirect('/ijinsiswa')->with('gagal', 'Data siswa tidak ditemukan');
    }

    // Tentukan field status berdasarkan peran pengguna
    $statusField = '';
    switch (auth()->user()->role) {
         case 'kepala':
            $statusField = 'filex';
            break;  
      case 'pembina':
            $statusField = 'filex';
            break;
        case 'kurikulum':
            $statusField = 'filex';
            break;
        case 'walikelas':
            $statusField = 'filex';
            break;
        case 'kesehatan':
            $statusField = 'filex';
            break;
        default:
            return redirect('/ijinsiswa')->with('gagal', 'Peran tidak dikenal');
    }

    // Update status verifikasi sesuai dengan peran pengguna
    Ijinsiswa::where('id', $id)->update([$statusField => 'Surat Salah']);
    
    return redirect('/ijinsiswa')->with('sukses', 'Verifikasi Berhasil');
}

    
    

        public function tambah()
    {   
        $siswa=Siswa::where('nama',auth()->user()->name)->first();
        $data_ijinsiswa=Ijinsiswa::where('nama',auth()->user()->name)->orderBy('created_at','desc')->get();
       
       
        return view('ijinsiswa.tambahijinsiswa',compact('siswa','data_ijinsiswa'));
    }


    public function create(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'kelas' => 'required',
            'ijin' => 'required',
            
        ]);
    
if ($request->hasFile('file')) {
    $file = $request->file('file');
    $fileContents = file_get_contents($file->getRealPath());
    $base64File = base64_encode($fileContents);

    $imageName = 'file_' . time() . '.' . $file->getClientOriginalExtension();
    \File::put(public_path('/storage/uploads/' . $imageName), base64_decode($base64File));

    // Simpan data beserta path file ke database
    $ijinsiswa = new IjinSiswa();
    $ijinsiswa->nama = $request->input('nama');
    $ijinsiswa->kelas = $request->input('kelas');
    $ijinsiswa->ketijin = $request->input('ijin');
    $ijinsiswa->oksis = 'belum';
    $ijinsiswa->okkur = 'belum';
    $ijinsiswa->okbin = 'belum';
    $ijinsiswa->okas = 'belum';
    $ijinsiswa->file_path = '/storage/uploads/' . $imageName;
    $ijinsiswa->save();

    return redirect()->back()->with('sukses', 'Ijin siswa berhasil ditambahkan!');
}

        else {
             // Save the data along with the file path
             $ijinsiswa = new IjinSiswa();
             $ijinsiswa->nama = $request->input('nama');
             $ijinsiswa->kelas = $request->input('kelas');
             $ijinsiswa->ketijin = $request->input('ijin');
             $ijinsiswa->oksis = 'belum';
             $ijinsiswa->okkur = 'belum';
             $ijinsiswa->okbin = 'belum';
             $ijinsiswa->okas = 'belum';
             $ijinsiswa->save();
     
             return redirect()->back()->with('sukses', 'Ijin siswa berhasil ditambahkan!');
        }
        
    }
    
   public function uploadUlang(Request $request, $id)
{
    $request->validate([
        'file' => 'required|mimes:jpg,jpeg,png|max:10240', // Limit file size to 10MB
    ]);

    $ijinsiswa = IjinSiswa::find($id);

    if ($request->file('file')) {
        $file = $request->file('file');

        // Check file size in bytes (10MB = 10 * 1024 * 1024 = 10485760 bytes)
        if ($file->getSize() > 10485760) {
            return redirect()->back()->with('gagal', 'File terlalu besar, maksimal ukuran file adalah 10MB.');
        }

      
		$fileContents = file_get_contents($file->getRealPath());
        $base64File = base64_encode($fileContents);
        $imageName = 'file_' . time() . '.' . $file->getClientOriginalExtension();
            \File::put(public_path('/storage/uploads/' . $imageName), base64_decode($base64File));
      	$ijinsiswa->filex = 'Surat Sesuai';
        $ijinsiswa->file_path = '/storage/uploads/' . $imageName;
        $ijinsiswa->save();      

        return redirect()->back()->with('sukses', 'File ijin berhasil diunggah ulang!');
    }

    return redirect()->back()->with('gagal', 'File gagal diunggah ulang!');
}



public function export(Request $request)
{
    // Validasi input untuk tanggal dan kelas
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'kelas' => 'nullable|string'
    ]);

    // Mengambil data berdasarkan filter yang dipilih
    $query = Ijinsiswa::whereBetween('created_at', [$request->start_date, $request->end_date]);

    if ($request->kelas !== 'all') {
        $query->where('kelas', $request->kelas);
    }

    $data_ijinsiswa = $query->get();

    // Menggunakan export dengan data yang sudah difilter
    return Excel::download(new IjinsiswaExport($data_ijinsiswa), 'data_ijinsiswa.xlsx');
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
        $ijinsiswa = Ijinsiswa::find($id);
        $ijinsiswa->delete();
        return redirect('/ijinsiswa')->with('sukses','Ijin Siswa Berhasil Dihapus');
    }

        public function import() 
    {
        Excel::import(new OperatorImport, request()->file('file'));
        
        return redirect('/operator')->with('sukses','Operator Berhasil Diupload');
    }

    public function cekout($id)
{
    // Find the Ijinsiswa record
    $ijinsiswa = Ijinsiswa::find($id);
    
    if (!$ijinsiswa) {
        return redirect('/ijinsiswa')->with('gagal', 'Data ijin tidak ditemukan');
    }

    // Update cekout time
    $ijinsiswa->cekout = now();

    // Calculate the duration based on ketijin
    $durasi = $this->calculateDuration($ijinsiswa->ketijin);

    // Store the duration in the durasi column
    $ijinsiswa->durasi = $durasi;

    // Save the updated record
    $ijinsiswa->save();

    // Find the corresponding Siswa record
    $siswa = Siswa::where('nama', $ijinsiswa->nama)->first();
    if (!$siswa) {
        return redirect('/ijinsiswa')->with('gagal', 'Data siswa tidak ditemukan');
    }

    // Determine which column to decrement based on ketijin
    switch ($ijinsiswa->ketijin) {
        case 'Ijin Pesiar':
            if ($siswa->ip > 0) {
                $siswa->decrement('ip');
                $siswa->save();
            }
            break;
        case 'Ijin Bermalam Wajib':
            if ($siswa->ib > 0) {
                $siswa->decrement('ib');
                $siswa->save();
            }
            break;
        case 'Ijin Bermalam Resmi':
            if ($siswa->ibr > 0) {
                $siswa->decrement('ibr');
                $siswa->save();
            }
            break;
        case 'Ijin Jalan':
            if ($siswa->ij > 0) {
                $siswa->decrement('ij');
                $siswa->save();
            }
            break;
        case 'Ijin Khusus':
            if ($siswa->ik > 0) {
                $siswa->decrement('ik');
                $siswa->save();
            }
            break;
        default:
            return redirect('/ijinsiswa')->with('gagal', 'Jenis ijin tidak dikenal');
    }

    return redirect('/ijinsiswa')->with('sukses', 'Cek Out Berhasil');
}

private function calculateDuration($ketijin)
{
    $durasi = '00:00:00'; // Default value

    switch ($ketijin) {
        case 'Ijin Pesiar':
            $durasi = '08:00:00';
            break;
        case 'Ijin Bermalam Wajib':
      		$durasi = '48:00:00';
            break;
        case 'Ijin GH Resmi':
            $durasi = '48:00:00';
            break;
        case 'Ijin Jalan':
            $durasi = '05:00:00';
            break;
        case 'Ijin Khusus':
            $durasi = '00:00:00';
            break;
        default:
            // Handle unknown jenis ijin if needed
            break;
    }

    return $durasi;
}



public function cekin(Request $request, $id)
{
    // Find the Ijinsiswa record
    $ijinsiswa = Ijinsiswa::find($id);
    
    if (!$ijinsiswa) {
        return redirect('/ijinsiswa')->with('gagal', 'Data ijin tidak ditemukan');
    }

    // Check if cekout is set before allowing cek in
    if (!$ijinsiswa->cekout) {
        return redirect('/ijinsiswa')->with('gagal', 'Silakan cek out terlebih dahulu');
    }

    // Update cekin time
    $ijinsiswa->cekin = now();

    // Calculate the time difference
    $cekoutTime = \Carbon\Carbon::parse($ijinsiswa->cekout);
    $cekinTime = \Carbon\Carbon::parse($ijinsiswa->cekin);
    $timeDifference = $cekinTime->diffInMinutes($cekoutTime); // Time difference in minutes

    // Determine the allowed duration based on ketijin
    $allowedDuration = $this->getAllowedDuration($ijinsiswa->ketijin);

    // Check if duration is 0
    if ($allowedDuration == 0) {
        $ijinsiswa->ket = 'Tepat Waktu';
        $ijinsiswa->wkt = 0; // No overtime
    } else {
        // Check if time difference exceeds the allowed duration
        if ($timeDifference > $allowedDuration) {
            $ijinsiswa->ket = 'Overtime';
            $ijinsiswa->wkt = $timeDifference; // Store the time difference
        } else {
            $ijinsiswa->ket = 'Tepat Waktu';
            $ijinsiswa->wkt = 0; // Store 0 if it's on time
        }
    }

    // Process the uploaded photo
    if ($request->has('file_bukti')) {
        // Decode the base64 image
        $image = $request->input('file_bukti');
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'bukti_' . time() . '.png';
        \File::put(public_path('/storage/uploads/' . $imageName), base64_decode($image));

        // Save image path to database
        $ijinsiswa->file_bukti = '/storage/uploads/' . $imageName;
    }

    // Save the updated record
    $ijinsiswa->save();

    return redirect('/ijinsiswa')->with('sukses', 'Cek In Berhasil');
}

private function getAllowedDuration($ketijin)
{
    $durasi = 0; // Default value in minutes

    switch ($ketijin) {
        case 'Ijin Pesiar':
            $durasi = 480; // 8 hours in minutes
            break;
        case 'Ijin GH Wajib':
        case 'Ijin GH Resmi':
            $durasi = 2880; // 48 hours in minutes
            break;
        case 'Ijin Jalan':
            $durasi = 300; // 5 hours in minutes
            break;
        case 'Ijin Khusus':
            $durasi = 0; // No duration required
            break;
        default:
            // Handle unknown jenis ijin if needed
            break;
    }

    return $durasi;
}





}


