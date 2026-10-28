<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TkaSiswa;
use App\Models\Siswa;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TkaController extends Controller
{
    /**
     * Halaman Utama Menu TKA
     * Menyesuaikan tampilan untuk Siswa vs Admin/Kurikulum/Guru
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // ── 1. JIKA USER ADALAH SISWA ──
        if ($user->role === 'siswa') {
            // Ambil data nilai TKA berdasarkan NIS atau Nama
            $tkaList = TkaSiswa::where('nis', $user->username)
                ->orWhere('nama', $user->name)
                ->orderBy('created_at', 'asc')
                ->get();

            $diagnostik = $tkaList->firstWhere('jenis_tes', 'diagnostik');
            $to1        = $tkaList->firstWhere('jenis_tes', 'to_1');

            // Cek apakah siswa kelas 9
            $kelasUser = $user->kelas ?? ($tkaList->first()->kelas ?? '');
            $isKelas9 = preg_match('/^(IX|9)[-\s]?/i', (string)$kelasUser) || $tkaList->isNotEmpty();

            return view('tka.siswa', compact('user', 'tkaList', 'diagnostik', 'to1', 'isKelas9', 'kelasUser'));
        }

        // ── 2. JIKA USER ADALAH ADMIN / KURIKULUM / GURU / WALI KELAS ──
        $kelasList = ['IX-A', 'IX-B', 'IX-C', 'IX-D', 'IX-E', 'IX-F', 'IX-G'];

        $selectedKelas = $request->query('kelas', 'all');
        $selectedTes   = $request->query('jenis_tes', 'all');
        $search        = trim((string)$request->query('search', ''));

        // Normalisasi format kelas user (misal '9A', '9-A', 'IX A' -> 'IX-A')
        $userKelasNorm = null;
        if ($user->walikelas_kelas) {
            $raw = strtoupper(str_replace([' ', '-'], '', $user->walikelas_kelas));
            // Cek jika dimulai dengan 9 atau IX
            if (preg_match('/^(?:9|IX)([A-G])$/', $raw, $m)) {
                $userKelasNorm = 'IX-' . $m[1];
            }
        }

        // Jika user adalah wali kelas 9 dan tidak memilih filter, defaultkan ke kelas binaannya
        if ($selectedKelas === 'all' && $userKelasNorm && in_array($userKelasNorm, $kelasList)) {
            $selectedKelas = $userKelasNorm;
        }

        $query = TkaSiswa::query();

        if ($selectedKelas !== 'all') {
            // Cocokkan kelas baik format 'IX-A' maupun '9A' / 'IX A'
            $letter = substr($selectedKelas, -1);
            $query->where(function($q) use ($selectedKelas, $letter) {
                $q->where('kelas', $selectedKelas)
                  ->orWhere('kelas', '9' . $letter)
                  ->orWhere('kelas', 'IX ' . $letter)
                  ->orWhere('kelas', '9-' . $letter);
            });
        }

        if ($selectedTes !== 'all') {
            $query->where('jenis_tes', $selectedTes);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nis', 'LIKE', "%{$search}%")
                  ->orWhere('kelas', 'LIKE', "%{$search}%");
            });
        }

        $data_tka = $query->orderBy('kelas', 'asc')
                          ->orderBy('nama', 'asc')
                          ->paginate(25)
                          ->appends($request->query());

        // Statistik ringkas
        $totalSiswa = TkaSiswa::select('nis')->distinct()->count();
        $totalDiagnostik = TkaSiswa::where('jenis_tes', 'diagnostik')->count();
        $totalTO1 = TkaSiswa::where('jenis_tes', 'to_1')->count();

        return view('tka.index', compact(
            'data_tka',
            'kelasList',
            'selectedKelas',
            'selectedTes',
            'search',
            'totalSiswa',
            'totalDiagnostik',
            'totalTO1'
        ));
    }

    /**
     * Download / Cetak PDF Hasil TKA per Asesmen (Diagnostik atau TO-1)
     */
    public function downloadPdf(Request $request, $id)
    {
        $user = auth()->user();
        $tka = TkaSiswa::findOrFail($id);

        // Keamanan: Siswa hanya boleh download rapor miliknya sendiri
        if ($user->role === 'siswa') {
            if ($tka->nis !== $user->username && $tka->nama !== $user->name) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengunduh hasil TKA ini.');
            }
        }

        $logoBase64       = $this->getLogoBase64();
        $logoPemkotBase64 = $this->getLogoPemkotBase64();
        $kopHtml          = $this->getKopText();

        $pdf = Pdf::loadView('tka.pdf', compact('tka', 'logoBase64', 'logoPemkotBase64', 'kopHtml'))
                  ->setPaper('A4', 'portrait');

        $cleanNama = preg_replace('/[^a-zA-Z0-9_-]/', '_', $tka->nama);
        $filename = "TKA_{$tka->jenis_tes}_{$tka->kelas}_{$cleanNama}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Download / Cetak PDF Rekapitulasi Lengkap (Diagnostik + TO-1 dalam 1 Lembar)
     */
    public function downloadRekapPdf(Request $request, $nis)
    {
        $user = auth()->user();

        // Keamanan akses
        if ($user->role === 'siswa') {
            if ($nis !== $user->username) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengunduh rekap TKA ini.');
            }
        }

        $records = TkaSiswa::where('nis', $nis)->get();
        if ($records->isEmpty()) {
            return redirect()->back()->with('gagal', 'Data TKA siswa dengan NIS ' . $nis . ' tidak ditemukan.');
        }

        $identitas  = $records->first();
        $diagnostik = $records->firstWhere('jenis_tes', 'diagnostik');
        $to1        = $records->firstWhere('jenis_tes', 'to_1');

        $logoBase64       = $this->getLogoBase64();
        $logoPemkotBase64 = $this->getLogoPemkotBase64();
        $kopHtml          = $this->getKopText();

        $pdf = Pdf::loadView('tka.pdf_rekap', compact('identitas', 'diagnostik', 'to1', 'logoBase64', 'logoPemkotBase64', 'kopHtml'))
                  ->setPaper('A4', 'portrait');

        $cleanNama = preg_replace('/[^a-zA-Z0-9_-]/', '_', $identitas->nama);
        $filename = "Rekap_TKA_{$identitas->kelas}_{$cleanNama}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Import File Excel Nilai TKA oleh Admin/Kurikulum
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xls,xlsx|max:10240',
        ]);

        try {
            $file = $request->file('file_excel');
            $tempPath = $file->getRealPath();

            \Artisan::call('tka:import', ['file' => $tempPath]);
            $output = \Artisan::output();

            return redirect()->back()->with('sukses', 'Import data TKA berhasil diselesaikan! ' . $output);
        } catch (\Throwable $e) {
            return redirect()->back()->with('gagal', 'Gagal memproses import Excel: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Ambil logo SMPN 24 Malang dalam format base64 (Kiri)
     */
    private function getLogoBase64()
    {
        $logoPath = public_path('adminlte/img/user2.png');
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }

    /**
     * Helper: Ambil logo Pemkot Malang dalam format base64 (Kanan)
     */
    private function getLogoPemkotBase64()
    {
        // Cek file di data_lama atau public
        $candidates = [
            base_path('data_lama/logo_pemkot.png'),
            public_path('adminlte/img/logo_pemkot.png'),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        return null;
    }

    /**
     * Helper: Format data Kop Surat
     */
    private function getKopText()
    {
        return [
            'pemerintah' => 'PEMERINTAH KOTA MALANG',
            'dinas'      => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
            'sekolah'    => 'SMP NEGERI 24 MALANG',
            'alamat'     => 'Jl. Simpang Laksda Adi Sucipto, Kec. Blimbing, Kota Malang 65126',
            'kontak'     => 'Telp: (0341) 415415 | E-mail: admin@smpn24-mlg.sch.id',
        ];
    }
}
