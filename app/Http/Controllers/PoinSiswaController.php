<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoinSiswa;
use App\Models\Siswa;
use App\Models\KategoriPoin;

class PoinSiswaController extends Controller
{
    public function index(Request $request)
    {
        $data_poin = PoinSiswa::with(['siswa', 'kategoriPoin'])->orderBy('created_at', 'desc')->get();
        return view('poin.index', compact('data_poin'));
    }

    public function inputPoin(Request $request)
    {
        $tahun_ajaran = session('tahun_ajaran');
        $view = $request->query('view');
        
        $isWali = auth()->user()->hasRole('walikelas') || auth()->user()->walikelas_kelas;
        $isOnlyWali = $isWali && !(auth()->user()->hasRole('kurikulum') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('lihat'));

        $query = Siswa::where('tahun_ajaran', $tahun_ajaran);

        if ($view === 'walikelas' || $isOnlyWali) {
            $kelas = auth()->user()->walikelas_kelas ?: auth()->user()->name;
            $query->where('kelas', $kelas);
        }

        $data_siswa = $query->orderBy('nama', 'asc')->get();
        $kategori_poin = KategoriPoin::orderBy('nama_kategori', 'asc')->get();

        return view('poin.input_poin', compact('data_siswa', 'kategori_poin'));
    }

    public function create(Request $request)
    {	
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'kategori_poin_id' => 'required|exists:kategori_poin,id',
            'pelapor' => 'required|string',
            'kejadian' => 'required|string',
            'tempat' => 'required|string'
        ]);

        $kategori = KategoriPoin::findOrFail($request->kategori_poin_id);
    	  
    	PoinSiswa::create([
            'siswa_id' => $request->siswa_id,
            'kategori_poin_id' => $request->kategori_poin_id,
            'poin' => $kategori->poin,
            'pelapor' => $request->pelapor,
            'kejadian' => $request->kejadian,
            'tempat' => $request->tempat
        ]);

    	return back()->with('sukses', 'Poin Siswa Berhasil Ditambahkan');
    }

    public function delete($id)
    {
        $poin = PoinSiswa::findOrFail($id);
        $poin->delete();
        return back()->with('sukses', 'Poin Siswa Berhasil Dihapus');
    }

    public function cetakSP($id, $level)
    {
        $siswa = Siswa::findOrFail($id);
        $total_poin = $siswa->totalPoinPelanggaran();

        $pelanggaran = $siswa->poinSiswa()->whereHas('kategoriPoin', function($q) {
            $q->where('jenis', 'pelanggaran');
        })->with('kategoriPoin')->orderBy('created_at', 'desc')->get();

        return view('poin.cetak_sp', compact('siswa', 'total_poin', 'pelanggaran', 'level'));
    }
}
