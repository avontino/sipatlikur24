<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;

class KelasController extends Controller
{
    public function index()
    {
        $tahunAjaran = session('tahun_ajaran') ?: '2026/2027';
        $studentCounts = \DB::table('siswa')
            ->where('tahun_ajaran', $tahunAjaran)
            ->select('kelas', \DB::raw('count(*) as total'))
            ->groupBy('kelas')
            ->pluck('total', 'kelas')
            ->toArray();

        $data_kelas = Kelas::all();
        foreach ($data_kelas as $kelas) {
            $kelas->jumlah = $studentCounts[$kelas->kelas] ?? 0;
        }

        return view('kelas.index', compact('data_kelas'));
    }

    public function create(Request $request)
    {
        Kelas::create($request->all());
        return redirect('/kelas')->with('sukses', 'Data Kelas Berhasil Ditambahkan');
    }

    public function update(Request $request)
    {
        $kelas = Kelas::findOrFail($request->kelasid);
        $kelas->update($request->all());
        return redirect('/kelas')->with('sukses', 'Data Kelas Berhasil Diupdate');
    }

    public function delete($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();
        return redirect('/kelas')->with('sukses', 'Data Kelas Berhasil Dihapus');
    }
}
