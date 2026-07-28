<?php

namespace App\Http\Controllers;

use App\Garjas;
use App\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\GarjasExport;
use Maatwebsite\Excel\Facades\Excel; // Tambahkan import ini
use PDF;

class GarjasController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));
        $kelas = $request->get('kelas', '');

        $query = Garjas::with('siswa')
                      ->byPeriod($bulan, $tahun)
                      ->orderBy('kelas')
                      ->orderBy('nama');

        if ($kelas) {
            $query->byKelas($kelas);
        }

        // Filter berdasarkan role
        if (Auth::user()->role == 'siswa') {
            $query->where('nis', Auth::user()->username);
        }

        $garjas = $query->get();
        $kelasList = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        
        return view('garjas.index', compact('garjas', 'bulan', 'tahun', 'kelas', 'kelasList'));
    }

    public function store(Request $request)
    {
        Log::info('Store method called', [
            'user_role' => Auth::user()->role,
            'request_data' => $request->all()
        ]);

        $rules = [
            'nis' => 'required|exists:siswa,nis',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020',
        ];

        // Validasi berbeda berdasarkan role
        if (Auth::user()->role == 'siswa') {
            $rules = array_merge($rules, [
                'lari' => 'nullable|integer|min:0',
                'up' => 'nullable|integer|min:0',
                'situp' => 'nullable|integer|min:0',
                'pushup' => 'nullable|integer|min:0',
                'shuttle' => 'nullable|numeric|min:0',
            ]);
        } else if (in_array(Auth::user()->role, ['pembina', 'admin'])) {
            $rules = array_merge($rules, [
                'nlari' => 'nullable|integer|between:0,100',
                'nup' => 'nullable|integer|between:0,100',
                'nsitup' => 'nullable|integer|between:0,100',
                'npushup' => 'nullable|integer|between:0,100',
                'nshuttle' => 'nullable|integer|between:0,100',
                // nb tidak perlu validasi karena otomatis dihitung
            ]);
        }

        try {
            $this->validate($request, $rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        DB::beginTransaction();
        
        try {
            // Ambil data siswa
            $siswa = Siswa::where('nis', $request->nis)->first();
            
            if (!$siswa) {
                throw new \Exception('Siswa tidak ditemukan');
            }
            
            // Cek apakah data sudah ada
            $garjas = Garjas::where('nis', $request->nis)
                           ->where('bulan', $request->bulan)
                           ->where('tahun', $request->tahun)
                           ->first();

            $data = $request->only(['nis', 'bulan', 'tahun']);
            $data['nama'] = $siswa->nama;
            $data['kelas'] = $siswa->kelas;

            if (Auth::user()->role == 'siswa') {
                // Siswa hanya bisa input data latihan
                $studentData = $request->only(['lari', 'up', 'situp', 'pushup', 'shuttle']);
                
                // Filter data yang kosong
                foreach ($studentData as $key => $value) {
                    if ($value === null || $value === '') {
                        unset($studentData[$key]);
                    }
                }
                
                $data = array_merge($data, $studentData);
                
                if ($garjas) {
                    // Cek apakah masih bisa edit
                    foreach ($studentData as $field => $value) {
                        if (!$garjas->canEditByStudent($field)) {
                            if ($request->ajax()) {
                                return response()->json([
                                    'success' => false,
                                    'errors' => [$field => ["Field {$field} tidak dapat diubah karena sudah dinilai oleh pembina."]]
                                ], 422);
                            }
                            return redirect()->back()
                                ->with('error', "Field {$field} tidak dapat diubah karena sudah dinilai oleh pembina.");
                        }
                    }
                    
                    Log::info('Updating existing garjas', ['id' => $garjas->id, 'data' => $data]);
                    $garjas->update($data);
                } else {
                    Log::info('Creating new garjas', ['data' => $data]);
                    $garjas = Garjas::create($data);
                }
            } else if (in_array(Auth::user()->role, ['pembina', 'admin'])) {
                // Pembina dan Admin hanya bisa input nilai (tanpa nb karena otomatis)
                $scoreData = $request->only(['nlari', 'nup', 'nsitup', 'npushup', 'nshuttle']);
                
                // Convert empty strings to null
                foreach ($scoreData as $key => $value) {
                    if ($value === '') {
                        $scoreData[$key] = null;
                    }
                }
                
                $data = array_merge($data, $scoreData);
                
                if ($garjas) {
                    Log::info('Updating existing garjas (pembina/admin)', ['id' => $garjas->id, 'data' => $data]);
                    $garjas->update($data);
                } else {
                    Log::info('Creating new garjas (pembina/admin)', ['data' => $data]);
                    $garjas = Garjas::create($data);
                }
            }

            DB::commit();
            
            Log::info('Data saved successfully', ['garjas_id' => $garjas->id, 'total' => $garjas->total, 'nb' => $garjas->nb]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil disimpan',
                    'data' => $garjas->fresh(),
                    'total' => $garjas->total,
                    'nb' => $garjas->nb
                ]);
            }

            return redirect()->route('garjas.index', [
                'bulan' => $request->bulan,
                'tahun' => $request->tahun
            ])->with('success', 'Data garjas berhasil disimpan.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving garjas data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update method called', [
            'id' => $id,
            'user_role' => Auth::user()->role,
            'request_data' => $request->all()
        ]);

        try {
            $garjas = Garjas::findOrFail($id);
        } catch (\Exception $e) {
            Log::error('Garjas not found', ['id' => $id]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['id' => ['Data tidak ditemukan']]
                ], 404);
            }
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
        
        // Cek authorization
        if (Auth::user()->role == 'siswa' && $garjas->nis != Auth::user()->username) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['authorization' => ['Tidak ada akses untuk mengedit data ini.']]
                ], 403);
            }
            return redirect()->back()->with('error', 'Tidak ada akses untuk mengedit data ini.');
        }

        $rules = [];

        if (Auth::user()->role == 'siswa') {
            $rules = [
                'lari' => 'nullable|integer|min:0',
                'up' => 'nullable|integer|min:0',
                'situp' => 'nullable|integer|min:0',
                'pushup' => 'nullable|integer|min:0',
                'shuttle' => 'nullable|numeric|min:0',
            ];

            // Cek field yang masih bisa diedit
            foreach (['lari', 'up', 'situp', 'pushup', 'shuttle'] as $field) {
                if ($request->filled($field) && !$garjas->canEditByStudent($field)) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'errors' => [$field => ["Field {$field} tidak dapat diubah karena sudah dinilai oleh pembina."]]
                        ], 422);
                    }
                    return redirect()->back()
                        ->with('error', "Field {$field} tidak dapat diubah karena sudah dinilai oleh pembina.");
                }
            }

            $data = [];
            foreach (['lari', 'up', 'situp', 'pushup', 'shuttle'] as $field) {
                if ($request->has($field)) {
                    $value = $request->get($field);
                    $data[$field] = ($value === '' || $value === null) ? null : $value;
                }
            }
            
        } else if (in_array(Auth::user()->role, ['pembina', 'admin'])) {
            $rules = [
                'nlari' => 'nullable|integer|between:0,100',
                'nup' => 'nullable|integer|between:0,100',
                'nsitup' => 'nullable|integer|between:0,100',
                'npushup' => 'nullable|integer|between:0,100',
                'nshuttle' => 'nullable|integer|between:0,100',
                // nb tidak perlu validasi karena otomatis
            ];

            $data = [];
            foreach (['nlari', 'nup', 'nsitup', 'npushup', 'nshuttle'] as $field) {
                if ($request->has($field)) {
                    $value = $request->get($field);
                    $data[$field] = ($value === '' || $value === null) ? null : $value;
                }
            }
        }

        // Validasi input hanya jika ada data
        if (!empty($data)) {
            try {
                $this->validate($request, $rules);
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Update validation failed', ['errors' => $e->errors()]);
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $e->errors()
                    ], 422);
                }
                throw $e;
            }
        }

        DB::beginTransaction();
        
        try {
            Log::info('Updating garjas', ['id' => $id, 'data' => $data]);
            
            // Update data jika ada
            if (!empty($data)) {
                $garjas->update($data);
            }
            
            // Refresh model dari database
            $garjas = $garjas->fresh();
            
            DB::commit();
            
            Log::info('Update successful', ['id' => $id, 'total' => $garjas->total, 'nb' => $garjas->nb]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil diperbarui',
                    'data' => $garjas,
                    'total' => $garjas->total,
                    'nb' => $garjas->nb
                ]);
            }

            return redirect()->route('garjas.index', [
                'bulan' => $garjas->bulan,
                'tahun' => $garjas->tahun
            ])->with('success', 'Data garjas berhasil diperbarui.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating garjas', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $garjas = Garjas::findOrFail($id);
        
        // Cek authorization
        if (Auth::user()->role == 'siswa' && $garjas->nis != Auth::user()->username) {
            return redirect()->back()->with('error', 'Tidak ada akses untuk menghapus data ini.');
        }

        $bulan = $garjas->bulan;
        $tahun = $garjas->tahun;
        
        $garjas->delete();

        return redirect()->route('garjas.index', [
            'bulan' => $bulan,
            'tahun' => $tahun
        ])->with('success', 'Data garjas berhasil dihapus.');
    }

    public function syncSiswa(Request $request)
    {
        // Hanya pembina dan admin yang bisa sinkron
        if (!in_array(Auth::user()->role, ['pembina', 'admin'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $this->validate($request, [
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020',
            'kelas' => 'nullable|string',
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $kelas = $request->kelas;

        // Ambil semua siswa
        $siswaQuery = Siswa::select('nis', 'nama', 'kelas');
        if ($kelas) {
            $siswaQuery->where('kelas', $kelas);
        }
        $siswas = $siswaQuery->get();

        $syncCount = 0;

        foreach ($siswas as $siswa) {
            // Cek apakah data garjas sudah ada
            $exists = Garjas::where('nis', $siswa->nis)
                           ->where('bulan', $bulan)
                           ->where('tahun', $tahun)
                           ->exists();

            if (!$exists) {
                Garjas::create([
                    'nis' => $siswa->nis,
                    'nama' => $siswa->nama,
                    'kelas' => $siswa->kelas,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]);
                $syncCount++;
            }
        }

        return redirect()->route('garjas.index', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kelas' => $kelas
        ])->with('success', "Berhasil sinkronisasi {$syncCount} data siswa.");
    }

    public function getStudentData(Request $request)
    {
        $nis = $request->get('nis');
        $siswa = Siswa::where('nis', $nis)->first();
        
        if ($siswa) {
            return response()->json([
                'success' => true,
                'data' => [
                    'nama' => $siswa->nama,
                    'kelas' => $siswa->kelas
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Siswa tidak ditemukan'
        ]);
    }

     public function exportExcel(Request $request)
    {
        // Validasi akses - hanya pembina dan admin yang bisa export
        if (!in_array(Auth::user()->role, ['pembina', 'admin'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $bulan = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));
        $kelas = $request->get('kelas', '');

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $bulanNama = $bulanNames[$bulan];
        $kelasText = $kelas ? "Kelas_{$kelas}" : 'Semua_Kelas';
        $fileName = "Garjas_{$bulanNama}_{$tahun}_{$kelasText}.xlsx";

        try {
            return Excel::download(new GarjasExport($bulan, $tahun, $kelas), $fileName);
        } catch (\Exception $e) {
            Log::error('Error exporting Excel', [
                'error' => $e->getMessage(),
                'bulan' => $bulan,
                'tahun' => $tahun,
                'kelas' => $kelas
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat export Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export data garjas ke PDF
     */
    public function exportPDF(Request $request)
    {
        // Validasi akses - hanya pembina dan admin yang bisa export
        if (!in_array(Auth::user()->role, ['pembina', 'admin'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $bulan = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));
        $kelas = $request->get('kelas', '');

        $query = Garjas::byPeriod($bulan, $tahun)
                      ->orderBy('kelas')
                      ->orderBy('nama');

        if ($kelas) {
            $query->byKelas($kelas);
        }

        $garjas = $query->get();

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $bulanNama = $bulanNames[$bulan];
        $kelasText = $kelas ? "Kelas {$kelas}" : 'Semua Kelas';
        $fileName = "Garjas_{$bulanNama}_{$tahun}" . ($kelas ? "_{$kelas}" : '_Semua_Kelas') . ".pdf";

        try {
            $pdf = PDF::loadView('garjas.export-pdf', compact('garjas', 'bulan', 'tahun', 'kelas', 'bulanNama', 'kelasText'));
            $pdf->setPaper('a4', 'landscape');
            
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Error exporting PDF', [
                'error' => $e->getMessage(),
                'bulan' => $bulan,
                'tahun' => $tahun,
                'kelas' => $kelas
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat export PDF: ' . $e->getMessage());
        }
    }
}