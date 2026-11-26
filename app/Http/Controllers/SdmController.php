<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\SdmCuti;
use App\Models\SdmPegawai;
use App\Models\SdmTugasTambahan;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SdmController extends Controller
{
    private const ROMBEL_LIST = [
        'VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E', 'VII-F', 'VII-G',
        'VIII-A', 'VIII-B', 'VIII-C', 'VIII-D', 'VIII-E', 'VIII-F', 'VIII-G',
        'IX-A', 'IX-B', 'IX-C', 'IX-D', 'IX-E', 'IX-F', 'IX-G',
    ];

    /**
     * Cek apakah user boleh mengelola master data kepegawaian (Guru, Tendik, Tugas Tambahan).
     */
    private function canManageKepegawaian($user = null)
    {
        $user = $user ?: auth()->user();
        return $user && ($user->hasRole('admin') || $user->hasRole('kurikulum') || $user->hasRole('kepala'));
    }

    /**
     * Cek apakah user boleh mengelola data siswa SDM.
     */
    private function canManageSiswa($user = null)
    {
        $user = $user ?: auth()->user();
        return $user && ($user->hasRole('admin') || $user->hasRole('kesiswaan') || $user->hasRole('kurikulum'));
    }

    /**
     * Cek apakah user boleh menyetujui / menolak cuti.
     */
    private function canApproveCuti($user = null)
    {
        $user = $user ?: auth()->user();
        return $user && ($user->hasRole('admin') || $user->hasRole('kepala') || $user->hasRole('kurikulum'));
    }

    /**
     * Helper mengambil query siswa sesuai tahun ajaran aktif (jika kolom tahun_ajaran terisi).
     */
    private function activeSiswaQuery()
    {
        $ta = session('tahun_ajaran');
        $q = Siswa::query();
        if ($ta && Siswa::where('tahun_ajaran', $ta)->exists()) {
            $q->where('tahun_ajaran', $ta);
        }
        return $q;
    }

    /**
     * Helper mengambil ringkasan tugas mengajar otomatis dari tabel `jadwal`.
     */
    private function getTugasMengajarFromJadwal()
    {
        $ta = session('tahun_ajaran');
        $sem = session('semester');

        $q = Jadwal::query();
        if ($ta && Jadwal::where('tahun_ajaran', $ta)->exists()) {
            $q->where('tahun_ajaran', $ta);
        }
        if ($sem && Jadwal::where('semester', $sem)->exists()) {
            $q->where('semester', $sem);
        }

        return $q->select(
            'guru',
            'mapel',
            'kelas',
            DB::raw('SUM(CAST(COALESCE(jumlahjam, 1) AS UNSIGNED)) as total_jp'),
            DB::raw('COUNT(*) as jumlah_sesi')
        )
            ->whereNotNull('guru')
            ->where('guru', '!=', '')
            ->groupBy('guru', 'mapel', 'kelas')
            ->orderBy('guru', 'asc')
            ->orderBy('kelas', 'asc')
            ->get();
    }

    /**
     * Hitung total JP jadwal per nama guru (dinormalisasi lowercase).
     */
    private function getJpMapFromJadwal()
    {
        $rows = $this->getTugasMengajarFromJadwal();
        $map = [];
        $kelasMap = [];

        foreach ($rows as $r) {
            $key = mb_strtolower(trim((string) $r->guru));
            $map[$key] = ($map[$key] ?? 0) + (int) $r->total_jp;
            if (!empty($r->kelas)) {
                $kelasMap[$key][] = $r->kelas;
            }
        }

        foreach ($kelasMap as $k => $arr) {
            $kelasMap[$k] = array_values(array_unique($arr));
        }

        return ['jp' => $map, 'kelas' => $kelasMap];
    }

    /**
     * 1. Dashboard Analitik SDM (KPI, Ulang Tahun H-5..H, Grafik, Prediksi Pensiun).
     */
    public function dashboard()
    {
        $guruList = SdmPegawai::where('kategori', 'guru')->get();
        $tendikList = SdmPegawai::where('kategori', 'tendik')->get();
        $allStaff = $guruList->concat($tendikList);

        $totalGuru = $guruList->count();
        $totalTendik = $tendikList->count();
        $totalSiswa = $this->activeSiswaQuery()->count();

        // Hitung prediksi pensiun (<= 3 tahun ke depan)
        $pensiunList = $allStaff->filter(function ($p) {
            return $p->tanggal_lahir !== null && $p->sisa_tahun_pensiun !== null && $p->sisa_tahun_pensiun <= 3;
        })->sortBy('sisa_tahun_pensiun')->values();

        $totalMendekatiPensiun = $pensiunList->filter(function ($p) {
            return $p->sisa_tahun_pensiun <= 2;
        })->count();

        // Highlight Ulang Tahun H-5 s.d. Hari H
        $today = Carbon::today();
        $birthdays = collect();

        foreach ($allStaff as $p) {
            if (!$p->tanggal_lahir) {
                continue;
            }
            $dob = Carbon::parse($p->tanggal_lahir);
            $nextBday = Carbon::createFromDate($today->year, $dob->month, $dob->day)->startOfDay();
            if ($nextBday->lt($today)) {
                $nextBday->addYear();
            }
            $diffDays = $today->diffInDays($nextBday, false);

            if ($diffDays >= 0 && $diffDays <= 5) {
                $turningAge = $nextBday->year - $dob->year;
                $tglShort = $dob->format('d/m');
                $msg = $diffDays === 0
                    ? "Selamat Ulang Tahun ke-{$turningAge} untuk {$p->nama}. Semoga senantiasa diberikan kesehatan, keberkahan, dan kesuksesan dalam mengabdi di SMPN 24 Malang."
                    : "Salam hangat untuk {$p->nama}, menjelang hari ulang tahun pada tanggal {$tglShort}. Semoga sehat dan sukses selalu.";

                $birthdays->push((object) [
                    'pegawai' => $p,
                    'diffDays' => $diffDays,
                    'turningAge' => $turningAge,
                    'tglShort' => $tglShort,
                    'waUrl' => $p->wa_clean ? 'https://wa.me/' . $p->wa_clean . '?text=' . urlencode($msg) : null,
                ]);
            }
        }
        $birthdays = $birthdays->sortBy('diffDays')->values();

        // Data Grafik Status Kepegawaian
        $statusCounts = [
            'Guru PNS' => $guruList->where('status_kepegawaian', 'Guru PNS')->count(),
            'ASN PPPK (Guru & Tendik)' => $guruList->where('status_kepegawaian', 'Guru PPPK')->count()
                + $tendikList->filter(fn($t) => str_contains((string) $t->status_kepegawaian, 'PPPK'))->count(),
            'Non-ASN / Honorer' => $allStaff->filter(fn($s) => str_contains(strtolower((string) $s->status_kepegawaian), 'honorer'))->count(),
        ];

        // Data Grafik Golongan ASN
        $golonganCounts = [
            'Gol IV' => $allStaff->filter(fn($s) => str_starts_with((string) $s->golongan, 'IV'))->count(),
            'Gol III' => $allStaff->filter(fn($s) => str_starts_with((string) $s->golongan, 'III'))->count(),
            'PPPK IX' => $allStaff->where('golongan', 'IX')->count(),
            'PPPK V' => $allStaff->where('golongan', 'V')->count(),
            'Non-ASN' => $allStaff->filter(fn($s) => empty($s->golongan) || $s->golongan === '-')->count(),
        ];

        return view('sdm.dashboard', compact(
            'totalGuru',
            'totalTendik',
            'totalSiswa',
            'totalMendekatiPensiun',
            'pensiunList',
            'birthdays',
            'statusCounts',
            'golonganCounts'
        ));
    }

    /**
     * 2. Ringkasan Umum & Tugas Tambahan.
     */
    public function ringkasan()
    {
        $siswaList = $this->activeSiswaQuery()->get();
        $guruList = SdmPegawai::where('kategori', 'guru')->get();

        $agamaCounts = [
            'Islam' => $siswaList->filter(fn($s) => strcasecmp((string) $s->agama, 'Islam') === 0)->count(),
            'Kristen' => $siswaList->filter(fn($s) => strcasecmp((string) $s->agama, 'Kristen') === 0)->count(),
            'Katolik' => $siswaList->filter(fn($s) => strcasecmp((string) $s->agama, 'Katolik') === 0)->count(),
            'Hindu/Buddha/Lainnya' => $siswaList->filter(fn($s) => !in_array(strtolower((string) $s->agama), ['islam', 'kristen', 'katolik', '']))->count(),
            'Belum Diisi' => $siswaList->filter(fn($s) => empty($s->agama))->count(),
        ];

        $genderSiswaCounts = [
            'Laki-Laki' => $siswaList->filter(fn($s) => strtoupper((string) $s->jenis_kelamin) === 'L')->count(),
            'Perempuan' => $siswaList->filter(fn($s) => strtoupper((string) $s->jenis_kelamin) === 'P')->count(),
            'Belum Diisi' => $siswaList->filter(fn($s) => empty($s->jenis_kelamin))->count(),
        ];

        $golGuruCounts = [
            'IV/c - IV/d' => $guruList->filter(fn($g) => in_array($g->golongan, ['IV/c', 'IV/d']))->count(),
            'IV/a - IV/b' => $guruList->filter(fn($g) => in_array($g->golongan, ['IV/a', 'IV/b']))->count(),
            'III/a - III/d' => $guruList->filter(fn($g) => str_starts_with((string) $g->golongan, 'III'))->count(),
            'PPPK (IX)' => $guruList->where('golongan', 'IX')->count(),
            'Non-ASN (-)' => $guruList->filter(fn($g) => empty($g->golongan) || $g->golongan === '-')->count(),
        ];

        $tugasTambahan = SdmTugasTambahan::with('pegawai')->orderBy('id', 'desc')->get();

        return view('sdm.ringkasan', compact(
            'agamaCounts',
            'genderSiswaCounts',
            'golGuruCounts',
            'tugasTambahan'
        ));
    }

    /**
     * 3. Rekapitulasi SDM (Matriks Guru & Rombel Siswa).
     */
    public function rekap()
    {
        $guruList = SdmPegawai::where('kategori', 'guru')->get();
        $statuses = ['Guru PNS', 'Guru PPPK', 'Guru Honorer', 'Guru Honorer-Non Induk'];

        $matriksGuru = [];
        foreach ($statuses as $st) {
            $group = $guruList->where('status_kepegawaian', $st);
            $matriksGuru[] = [
                'status' => $st,
                'l' => $group->where('jenis_kelamin', 'L')->count(),
                'p' => $group->where('jenis_kelamin', 'P')->count(),
                'total' => $group->count(),
            ];
        }
        $lainnya = $guruList->filter(fn($g) => !in_array($g->status_kepegawaian, $statuses));
        if ($lainnya->count() > 0) {
            $matriksGuru[] = [
                'status' => 'Lainnya / Belum Diatur',
                'l' => $lainnya->where('jenis_kelamin', 'L')->count(),
                'p' => $lainnya->where('jenis_kelamin', 'P')->count(),
                'total' => $lainnya->count(),
            ];
        }

        $siswaList = $this->activeSiswaQuery()->get();
        $jenjangPrefixes = [
            ['label' => 'Kelas VII (7)', 'regex' => '/^(VII|7)[-\s]?/i'],
            ['label' => 'Kelas VIII (8)', 'regex' => '/^(VIII|8)[-\s]?/i'],
            ['label' => 'Kelas IX (9)', 'regex' => '/^(IX|9)[-\s]?/i'],
        ];

        $matriksSiswa = [];
        foreach ($jenjangPrefixes as $j) {
            $group = $siswaList->filter(fn($s) => preg_match($j['regex'], (string) $s->kelas));
            $rombelCount = $group->pluck('kelas')->unique()->count();
            $matriksSiswa[] = [
                'label' => $j['label'],
                'rombel' => $rombelCount,
                'l' => $group->filter(fn($s) => strtoupper((string) $s->jenis_kelamin) === 'L')->count(),
                'p' => $group->filter(fn($s) => strtoupper((string) $s->jenis_kelamin) === 'P')->count(),
                'total' => $group->count(),
            ];
        }

        return view('sdm.rekap', compact('matriksGuru', 'matriksSiswa'));
    }

    /**
     * 4. Sinkronisasi awal Guru & Tendik dari tabel `users` SIPATLIKUR (aman, hanya tambah yang belum ada).
     */
    public function syncFromUsers()
    {
        if (!$this->canManageKepegawaian()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyinkronkan data kepegawaian.');
        }

        $users = User::whereIn('role', ['guru', 'tendik', 'kurikulum', 'kesiswaan', 'kepala'])
            ->orderBy('name', 'asc')
            ->get();

        $added = 0;
        foreach ($users as $u) {
            $exists = SdmPegawai::where('user_id', $u->id)
                ->orWhereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower(trim($u->name))])
                ->exists();

            if (!$exists) {
                $isTendik = ($u->role === 'tendik');
                SdmPegawai::create([
                    'user_id' => $u->id,
                    'kategori' => $isTendik ? 'tendik' : 'guru',
                    'nama' => trim($u->name),
                    'nip' => '-',
                    'status_kepegawaian' => $isTendik ? 'Tendik PPPK' : 'Guru PNS',
                    'golongan' => '-',
                    'jabatan' => $isTendik ? 'Tenaga Administrasi' : 'Guru Mata Pelajaran',
                    'jenis_kelamin' => 'L',
                    'beban_jp' => $isTendik ? 0 : 24,
                ]);
                $added++;
            }
        }

        return redirect()->back()->with('sukses', "Sinkronisasi selesai! {$added} data Guru/Tendik baru dari akun SIPATLIKUR berhasil ditambahkan.");
    }

    /**
     * 5. Halaman Laporan & Kelola Data Guru.
     */
    public function laporanGuru(Request $request)
    {
        $query = SdmPegawai::where('kategori', 'guru')->with('tugasTambahan');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%")
                  ->orWhere('mapel', 'LIKE', "%{$search}%");
            });
        }
        if ($mapel = $request->query('mapel')) {
            $query->where('mapel', $mapel);
        }
        if ($status = $request->query('status')) {
            $query->where('status_kepegawaian', $status);
        }
        if ($golongan = $request->query('golongan')) {
            if ($golongan === '-') {
                $query->where(fn($q) => $q->whereNull('golongan')->orWhere('golongan', '-'));
            } else {
                $query->where('golongan', $golongan);
            }
        }

        $guruList = $query->orderBy('nama', 'asc')->get();
        $mapelOptions = SdmPegawai::where('kategori', 'guru')
            ->whereNotNull('mapel')
            ->where('mapel', '!=', '')
            ->distinct()
            ->orderBy('mapel', 'asc')
            ->pluck('mapel');

        $jadwalSummary = $this->getJpMapFromJadwal();
        $rombelList = self::ROMBEL_LIST;
        $canManage = $this->canManageKepegawaian();
        $usersList = User::whereIn('role', ['guru', 'kurikulum', 'kesiswaan', 'kepala'])->orderBy('name')->get();

        return view('sdm.guru', compact(
            'guruList',
            'mapelOptions',
            'jadwalSummary',
            'rombelList',
            'canManage',
            'usersList'
        ));
    }

    /**
     * 6. Halaman Laporan & Kelola Data Tendik.
     */
    public function laporanTendik(Request $request)
    {
        $query = SdmPegawai::where('kategori', 'tendik');
        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%")
                  ->orWhere('jabatan', 'LIKE', "%{$search}%");
            });
        }

        $tendikList = $query->orderBy('nama', 'asc')->get();
        $canManage = $this->canManageKepegawaian();
        $usersList = User::where('role', 'tendik')->orderBy('name')->get();

        return view('sdm.tendik', compact('tendikList', 'canManage', 'usersList'));
    }

    /**
     * Simpan / Update Pegawai (Guru atau Tendik).
     */
    public function storePegawai(Request $request)
    {
        if (!$this->canManageKepegawaian()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'kategori' => 'required|in:guru,tendik',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'nullable|date',
        ]);

        $data = [
            'user_id' => $request->user_id ?: null,
            'kategori' => $request->kategori,
            'nama' => trim($request->nama),
            'nip' => trim((string) ($request->nip ?: '-')),
            'status_kepegawaian' => $request->status_kepegawaian,
            'pangkat' => $request->pangkat,
            'golongan' => $request->golongan ?: '-',
            'jabatan' => $request->jabatan,
            'mapel' => $request->mapel,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tanggal_lahir' => $request->tanggal_lahir ?: null,
            'no_wa' => trim((string) $request->no_wa),
            'beban_jp' => (int) ($request->beban_jp ?? ($request->kategori === 'guru' ? 24 : 0)),
            'kelas_diampu' => $request->kelas_diampu ?? [],
        ];

        if ($request->filled('id')) {
            $pegawai = SdmPegawai::findOrFail($request->id);
            $pegawai->update($data);
            $msg = 'Data ' . ucfirst($request->kategori) . ' berhasil diperbarui!';
        } else {
            SdmPegawai::create($data);
            $msg = 'Data ' . ucfirst($request->kategori) . ' baru berhasil ditambahkan!';
        }

        return redirect()->back()->with('sukses', $msg);
    }

    /**
     * Hapus Pegawai SDM.
     */
    public function destroyPegawai($id)
    {
        if (!$this->canManageKepegawaian()) {
            abort(403, 'Akses ditolak.');
        }

        $pegawai = SdmPegawai::findOrFail($id);
        $nama = $pegawai->nama;
        SdmTugasTambahan::where('pegawai_id', $id)->delete();
        SdmCuti::where('pegawai_id', $id)->delete();
        $pegawai->delete();

        return redirect()->back()->with('sukses', "Data pegawai {$nama} berhasil dihapus.");
    }

    /**
     * 7. Halaman Tugas Mengajar (Ditarik Otomatis dari Tabel Jadwal).
     */
    public function tugasMengajar(Request $request)
    {
        $rows = $this->getTugasMengajarFromJadwal();

        if ($search = trim((string) $request->query('search', ''))) {
            $s = mb_strtolower($search);
            $rows = $rows->filter(function ($r) use ($s) {
                return str_contains(mb_strtolower((string) $r->guru), $s)
                    || str_contains(mb_strtolower((string) $r->mapel), $s)
                    || str_contains(mb_strtolower((string) $r->kelas), $s);
            });
        }

        $rekapPerGuru = $rows->groupBy('guru')->map(function ($items, $guruName) {
            return [
                'guru' => $guruName,
                'total_jp' => $items->sum('total_jp'),
                'kelas' => $items->pluck('kelas')->unique()->sort()->values(),
                'mapel' => $items->pluck('mapel')->unique()->values(),
            ];
        })->sortBy('guru');

        return view('sdm.tugas_mengajar', compact('rows', 'rekapPerGuru'));
    }

    /**
     * 8. Halaman Tugas Tambahan.
     */
    public function tugasTambahan()
    {
        $tugasList = SdmTugasTambahan::with('pegawai')->orderBy('id', 'desc')->get();
        $guruList = SdmPegawai::where('kategori', 'guru')->orderBy('nama', 'asc')->get();
        $canManage = $this->canManageKepegawaian();

        return view('sdm.tugas_tambahan', compact('tugasList', 'guruList', 'canManage'));
    }

    public function storeTugasTambahan(Request $request)
    {
        if (!$this->canManageKepegawaian()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'pegawai_id' => 'required|exists:sdm_pegawai,id',
            'nama_tugas' => 'required|string|max:255',
            'ekuivalensi_jp' => 'required|integer|min:0|max:40',
        ]);

        SdmTugasTambahan::create([
            'pegawai_id' => $request->pegawai_id,
            'nama_tugas' => trim($request->nama_tugas),
            'ekuivalensi_jp' => (int) $request->ekuivalensi_jp,
            'no_sk' => trim((string) $request->no_sk),
            'keterangan' => trim((string) $request->keterangan),
            'tahun_ajaran' => session('tahun_ajaran') ?: '2026/2027',
        ]);

        return redirect()->back()->with('sukses', 'Tugas tambahan berhasil disimpan!');
    }

    public function destroyTugasTambahan($id)
    {
        if (!$this->canManageKepegawaian()) {
            abort(403, 'Akses ditolak.');
        }

        SdmTugasTambahan::findOrFail($id)->delete();
        return redirect()->back()->with('sukses', 'Tugas tambahan berhasil dihapus.');
    }

    /**
     * 9. Halaman Pengajuan & Persetujuan Cuti Pegawai (Terpisah dari Izin Harian).
     */
    public function cuti()
    {
        $user = auth()->user();
        $canApprove = $this->canApproveCuti($user);
        $canManageAll = $this->canManageKepegawaian($user);

        $query = SdmCuti::with(['pegawai', 'approver'])->orderBy('id', 'desc');
        if (!$canManageAll && !$user->hasRole('lihat') && !$user->hasRole('tamu')) {
            // Guru/Tendik biasa hanya melihat cuti miliknya sendiri
            $myPegawaiIds = SdmPegawai::where('user_id', $user->id)
                ->orWhereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower(trim($user->name))])
                ->pluck('id');
            $query->where(function ($q) use ($user, $myPegawaiIds) {
                $q->where('user_id', $user->id);
                if ($myPegawaiIds->isNotEmpty()) {
                    $q->orWhereIn('pegawai_id', $myPegawaiIds);
                }
            });
        }

        $cutiList = $query->get();
        $pegawaiList = SdmPegawai::orderBy('kategori', 'asc')->orderBy('nama', 'asc')->get();
        $myPegawai = SdmPegawai::where('user_id', $user->id)
            ->orWhereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower(trim($user->name))])
            ->first();

        return view('sdm.cuti', compact('cutiList', 'pegawaiList', 'myPegawai', 'canApprove', 'canManageAll'));
    }

    public function storeCuti(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'pegawai_id' => 'required|exists:sdm_pegawai,id',
            'jenis_cuti' => 'required|string|max:80',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string',
        ]);

        SdmCuti::create([
            'pegawai_id' => $request->pegawai_id,
            'user_id' => $user->id,
            'jenis_cuti' => $request->jenis_cuti,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => trim($request->alasan),
            'status' => 'Diajukan',
        ]);

        return redirect()->back()->with('sukses', 'Pengajuan cuti pegawai berhasil dikirim!');
    }

    public function updateStatusCuti(Request $request, $id)
    {
        if (!$this->canApproveCuti()) {
            abort(403, 'Hanya Kepala Sekolah, Admin, atau Kurikulum yang dapat memproses persetujuan cuti.');
        }

        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak,Diajukan',
        ]);

        $cuti = SdmCuti::findOrFail($id);
        $cuti->update([
            'status' => $request->status,
            'disetujui_oleh' => auth()->id(),
            'catatan_pimpinan' => trim((string) $request->catatan_pimpinan),
        ]);

        return redirect()->back()->with('sukses', "Status cuti berhasil diubah menjadi: {$request->status}.");
    }

    public function destroyCuti($id)
    {
        $user = auth()->user();
        $cuti = SdmCuti::findOrFail($id);

        if (!$this->canApproveCuti($user) && $cuti->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak menghapus pengajuan cuti ini.');
        }

        $cuti->delete();
        return redirect()->back()->with('sukses', 'Data pengajuan cuti berhasil dihapus.');
    }

    /**
     * 10. Halaman Laporan & Kelola Data Siswa (Demografi SDM).
     */
    public function laporanSiswa(Request $request)
    {
        $q = $this->activeSiswaQuery();

        if ($search = trim((string) $request->query('search', ''))) {
            $q->where(function ($sub) use ($search) {
                $sub->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('nis', 'LIKE', "%{$search}%")
                    ->orWhere('nisn', 'LIKE', "%{$search}%");
            });
        }
        if ($kelas = $request->query('kelas')) {
            $q->where('kelas', $kelas);
        }

        $siswaList = $q->orderBy('kelas', 'asc')->orderBy('nama', 'asc')->paginate(50)->appends($request->query());
        $kelasOptions = $this->activeSiswaQuery()->select('kelas')->distinct()->orderBy('kelas', 'asc')->pluck('kelas');
        $canManage = $this->canManageSiswa();

        return view('sdm.siswa', compact('siswaList', 'kelasOptions', 'canManage'));
    }

    public function updateSiswaSdm(Request $request, $id)
    {
        if (!$this->canManageSiswa()) {
            abort(403, 'Akses ditolak.');
        }

        $siswa = Siswa::findOrFail($id);
        $siswa->update([
            'nisn' => trim((string) $request->nisn),
            'jenis_kelamin' => $request->jenis_kelamin,
            'agama' => $request->agama,
            'nama_ortu' => trim((string) $request->nama_ortu),
        ]);

        return redirect()->back()->with('sukses', "Data demografi siswa {$siswa->nama} berhasil diperbarui!");
    }

    // =========================================================================
    // TEMPLATE, IMPORT & EXPORT EXCEL (GURU, TENDIK, SISWA)
    // =========================================================================

    public function templateGuru()
    {
        $headers = ['Nama Lengkap', 'NIP', 'Status Kepegawaian', 'Pangkat', 'Golongan', 'Mapel Pokok', 'Jenis Kelamin (L/P)', 'Tanggal Lahir (YYYY-MM-DD)', 'No WA', 'Beban JP', 'Kelas Diampu (pisahkan koma)'];
        $sample = [
            ['Drs. Teguh Edy Purwanta', '196710202000031006', 'Guru PNS', 'Pembina Tk. I', 'IV/b', 'Matematika', 'L', '1967-10-20', '082230967721', '24', 'IX-A, IX-B'],
            ['Nada Barlian Fanikia, S.Pd', '-', 'Guru Honorer', '-', '-', 'Matematika', 'P', '2000-02-12', '085707726949', '24', 'VII-E, VII-F, VII-G'],
        ];
        return $this->downloadSpreadsheet($headers, $sample, 'Template_Import_Guru_SDM_SMPN24.xlsx', 'Template_Guru');
    }

    public function templateTendik()
    {
        $headers = ['Nama Lengkap', 'NIP', 'Status Kepegawaian', 'Pangkat', 'Golongan', 'Jabatan', 'Jenis Kelamin (L/P)', 'Tanggal Lahir (YYYY-MM-DD)', 'No WA'];
        $sample = [
            ['Siti Ilifdiani Maghfuro, S.S', '199111042025212032', 'Tendik PPPK', 'PPPK', 'IX', 'Tenaga Administrasi', 'P', '1991-11-04', '081230046549'],
            ['Soegiono', '198302222025211032', 'Tendik PPPK', 'PPPK', 'V', 'Pramubakti', 'L', '1983-02-22', '08976627787'],
        ];
        return $this->downloadSpreadsheet($headers, $sample, 'Template_Import_Tendik_SDM_SMPN24.xlsx', 'Template_Tendik');
    }

    public function templateSiswa()
    {
        $headers = ['NIS', 'Nama Lengkap', 'NISN', 'Kelas', 'Jenis Kelamin (L/P)', 'Agama', 'Nama Orang Tua'];
        $sample = [
            ['12345', 'Ahmad Fauzan', '0081234567', 'VII-A', 'L', 'Islam', 'Budi Santoso'],
            ['12346', 'Aulia Putri', '0087654321', 'VIII-C', 'P', 'Islam', 'Siti Aminah'],
        ];
        return $this->downloadSpreadsheet($headers, $sample, 'Template_Import_Siswa_SDM_SMPN24.xlsx', 'Template_Siswa');
    }

    public function importGuru(Request $request)
    {
        if (!$this->canManageKepegawaian()) abort(403);
        return $this->importPegawaiFromExcel($request, 'guru');
    }

    public function importTendik(Request $request)
    {
        if (!$this->canManageKepegawaian()) abort(403);
        return $this->importPegawaiFromExcel($request, 'tendik');
    }

    private function importPegawaiFromExcel(Request $request, string $kategori)
    {
        $request->validate(['file_excel' => 'required|file|mimes:xls,xlsx|max:10240']);

        try {
            $spreadsheet = IOFactory::load($request->file('file_excel')->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $count = 0;

            foreach ($rows as $idx => $row) {
                if ($idx === 0) continue; // skip header
                $nama = trim((string) ($row[0] ?? ''));
                if ($nama === '' || stripos($nama, 'contoh:') === 0) continue;

                $nip = trim((string) ($row[1] ?? '-'));
                $status = trim((string) ($row[2] ?? ($kategori === 'guru' ? 'Guru PNS' : 'Tendik PPPK')));
                $pangkat = trim((string) ($row[3] ?? ''));
                $golongan = trim((string) ($row[4] ?? '-'));

                if ($kategori === 'guru') {
                    $mapel = trim((string) ($row[5] ?? ''));
                    $jabatan = 'Guru Mata Pelajaran';
                    $jk = strtoupper(trim((string) ($row[6] ?? 'L'))) === 'P' ? 'P' : 'L';
                    $tglLahir = $this->parseExcelDate($row[7] ?? null);
                    $wa = trim((string) ($row[8] ?? ''));
                    $jp = is_numeric($row[9] ?? null) ? (int) $row[9] : 24;
                    $kelasRaw = trim((string) ($row[10] ?? ''));
                    $kelasArr = $kelasRaw !== '' ? array_values(array_filter(array_map('trim', explode(',', $kelasRaw)))) : [];
                } else {
                    $mapel = null;
                    $jabatan = trim((string) ($row[5] ?? 'Tenaga Administrasi'));
                    $jk = strtoupper(trim((string) ($row[6] ?? 'L'))) === 'P' ? 'P' : 'L';
                    $tglLahir = $this->parseExcelDate($row[7] ?? null);
                    $wa = trim((string) ($row[8] ?? ''));
                    $jp = 0;
                    $kelasArr = [];
                }

                // Cocokkan user_id jika nama mirip di users
                $matchedUser = User::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($nama)])->first();

                $matchCond = ($nip !== '' && $nip !== '-')
                    ? ['nip' => $nip, 'kategori' => $kategori]
                    : ['nama' => $nama, 'kategori' => $kategori];

                SdmPegawai::updateOrCreate($matchCond, [
                    'user_id' => $matchedUser ? $matchedUser->id : null,
                    'nama' => $nama,
                    'nip' => $nip !== '' ? $nip : '-',
                    'status_kepegawaian' => $status,
                    'pangkat' => $pangkat,
                    'golongan' => $golongan !== '' ? $golongan : '-',
                    'jabatan' => $jabatan,
                    'mapel' => $mapel,
                    'jenis_kelamin' => $jk,
                    'tanggal_lahir' => $tglLahir,
                    'no_wa' => $wa,
                    'beban_jp' => $jp,
                    'kelas_diampu' => $kelasArr,
                ]);
                $count++;
            }

            return redirect()->back()->with('sukses', "Import berhasil! {$count} data " . ucfirst($kategori) . " berhasil disimpan/diperbarui.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('gagal', 'Gagal mengimpor Excel: ' . $e->getMessage());
        }
    }

    public function importSiswa(Request $request)
    {
        if (!$this->canManageSiswa()) abort(403);
        $request->validate(['file_excel' => 'required|file|mimes:xls,xlsx|max:10240']);

        try {
            $spreadsheet = IOFactory::load($request->file('file_excel')->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $updated = 0;
            $created = 0;

            foreach ($rows as $idx => $row) {
                if ($idx === 0) continue;
                $nis = trim((string) ($row[0] ?? ''));
                $nama = trim((string) ($row[1] ?? ''));
                if ($nama === '' && $nis === '') continue;

                $nisn = trim((string) ($row[2] ?? ''));
                $kelas = trim((string) ($row[3] ?? ''));
                $jk = strtoupper(trim((string) ($row[4] ?? '')));
                $jk = in_array($jk, ['L', 'P']) ? $jk : null;
                $agama = trim((string) ($row[5] ?? ''));
                $ortu = trim((string) ($row[6] ?? ''));

                $siswa = null;
                if ($nis !== '') {
                    $siswa = Siswa::where('nis', $nis)->first();
                }
                if (!$siswa && $nama !== '') {
                    $siswa = Siswa::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($nama)])->first();
                }

                if ($siswa) {
                    $siswa->update(array_filter([
                        'nisn' => $nisn !== '' ? $nisn : $siswa->nisn,
                        'kelas' => $kelas !== '' ? $kelas : $siswa->kelas,
                        'jenis_kelamin' => $jk ?: $siswa->jenis_kelamin,
                        'agama' => $agama !== '' ? $agama : $siswa->agama,
                        'nama_ortu' => $ortu !== '' ? $ortu : $siswa->nama_ortu,
                    ], fn($v) => $v !== null));
                    $updated++;
                } else {
                    Siswa::create([
                        'nis' => $nis ?: $nisn,
                        'nama' => $nama,
                        'nisn' => $nisn,
                        'kelas' => $kelas ?: 'VII-A',
                        'jenis_kelamin' => $jk,
                        'agama' => $agama,
                        'nama_ortu' => $ortu,
                    ]);
                    $created++;
                }
            }

            return redirect()->back()->with('sukses', "Import Siswa selesai: {$updated} data diperbarui, {$created} data baru ditambahkan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('gagal', 'Gagal mengimpor data siswa: ' . $e->getMessage());
        }
    }

    public function exportGuru()
    {
        $jadwalSummary = $this->getJpMapFromJadwal();
        $headers = ['No', 'Nama Lengkap', 'NIP', 'Status', 'Pangkat', 'Golongan', 'Mapel', 'L/P', 'Tanggal Lahir', 'Total JP (Jadwal+Tambahan)', 'No WA'];
        $rows = [];
        foreach (SdmPegawai::where('kategori', 'guru')->with('tugasTambahan')->orderBy('nama')->get() as $i => $g) {
            $key = mb_strtolower(trim($g->nama));
            $jpJadwal = $jadwalSummary['jp'][$key] ?? $g->beban_jp;
            $jpTambahan = $g->tugasTambahan->sum('ekuivalensi_jp');
            $rows[] = [
                $i + 1,
                $g->nama,
                $g->nip ?: '-',
                $g->status_kepegawaian,
                $g->pangkat ?: '-',
                $g->golongan ?: '-',
                $g->mapel ?: '-',
                $g->jenis_kelamin,
                $g->tanggal_lahir ? $g->tanggal_lahir->format('Y-m-d') : '-',
                $jpJadwal + $jpTambahan,
                $g->no_wa ?: '-',
            ];
        }
        return $this->downloadSpreadsheet($headers, $rows, 'Data_Guru_SDM_SMPN24.xlsx', 'Data_Guru');
    }

    public function exportTendik()
    {
        $headers = ['No', 'Nama Lengkap', 'NIP', 'Status', 'Golongan', 'Jabatan', 'L/P', 'Tanggal Lahir', 'No WA'];
        $rows = [];
        foreach (SdmPegawai::where('kategori', 'tendik')->orderBy('nama')->get() as $i => $t) {
            $rows[] = [
                $i + 1,
                $t->nama,
                $t->nip ?: '-',
                $t->status_kepegawaian,
                $t->golongan ?: '-',
                $t->jabatan ?: '-',
                $t->jenis_kelamin,
                $t->tanggal_lahir ? $t->tanggal_lahir->format('Y-m-d') : '-',
                $t->no_wa ?: '-',
            ];
        }
        return $this->downloadSpreadsheet($headers, $rows, 'Data_Tendik_SDM_SMPN24.xlsx', 'Data_Tendik');
    }

    public function exportSiswa()
    {
        $headers = ['No', 'NIS', 'NISN', 'Nama Siswa', 'Kelas', 'L/P', 'Agama', 'Nama Orang Tua'];
        $rows = [];
        foreach ($this->activeSiswaQuery()->orderBy('kelas')->orderBy('nama')->get() as $i => $s) {
            $rows[] = [
                $i + 1,
                $s->nis ?: '-',
                $s->nisn ?: '-',
                $s->nama,
                $s->kelas,
                $s->jenis_kelamin ?: '-',
                $s->agama ?: '-',
                $s->nama_ortu ?: '-',
            ];
        }
        return $this->downloadSpreadsheet($headers, $rows, 'Data_Siswa_SDM_SMPN24.xlsx', 'Data_Siswa');
    }

    private function parseExcelDate($value)
    {
        if (empty($value)) return null;
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse(trim((string) $value))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function downloadSpreadsheet(array $headers, array $rows, string $filename, string $sheetTitle)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);

        $sheet->fromArray($headers, null, 'A1');
        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }

        // Bold header & auto-size columns
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        for ($c = 1; $c <= count($headers); $c++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
