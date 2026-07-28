<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PresensiGuru;
use App\Models\User;
use App\Models\Shift;
use App\Models\UserShiftSchedule;
use App\Exports\PresensiGuruExport;
use App\Exports\PresensiGuruExportMatrix;
use Excel;
use PDF;
use Carbon\Carbon;
use DB;

class PresensiGuruController extends Controller
{
    private function getConfig()
    {
        $config = DB::table('presensi_guru_settings')->first();
        if (!$config) {
            return (object) [
                'latitude' => -7.8012,
                'longitude' => 112.0123,
                'radius' => 100,
                'radius_meters' => 100,
                'jam_masuk' => '07:00:00',
                'jam_pulang' => '16:00:00'
            ];
        }
        if (!isset($config->radius)) {
            $config->radius = $config->radius_meters ?? 100;
        }
        if (!isset($config->radius_meters)) {
            $config->radius_meters = $config->radius ?? 100;
        }
        return $config;
    }

    public function index()
    {
        $today = Carbon::today()->toDateString();
        $config = $this->getConfig();
        $user = auth()->user();
        
        // Active shift for current user today
        $activeShift = $user->getActiveShift($today);

        // Load today's attendance for the logged in user
        $todayPresensi = PresensiGuru::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        // If no attendance today, check if user has an unclosed overnight shift check-in from yesterday (e.g. Shift Malam 23.00 - 07.00)
        if (!$todayPresensi) {
            $yesterday = Carbon::yesterday()->toDateString();
            $yesterdayPresensi = PresensiGuru::where('user_id', $user->id)
                ->where('tanggal', $yesterday)
                ->whereNull('jam_pulang')
                ->whereHas('shift', function($q) {
                    $q->where('is_overnight', true);
                })
                ->first();

            if ($yesterdayPresensi) {
                $todayPresensi = $yesterdayPresensi;
                if ($yesterdayPresensi->shift) {
                    $activeShift = $yesterdayPresensi->shift;
                }
            }
        }

        // Load monthly history for current user and active academic period
        $riwayat = PresensiGuru::where('user_id', $user->id)
            ->where('tahun_ajaran', session('tahun_ajaran'))
            ->where('semester', session('semester'))
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->with('shift')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('presensi_guru.index', compact('todayPresensi', 'riwayat', 'config', 'activeShift'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'foto' => 'required',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'tipe' => 'required|in:datang,pulang'
        ]);

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $config = $this->getConfig();
        $user = auth()->user();

        // 1. GPS Distance check (Haversine Formula) using database config
        $distance = $this->getDistance($request->lat, $request->lng, $config->latitude, $config->longitude);
        if ($distance > $config->radius) {
            return response()->json([
                'error' => 'Presensi Gagal. Anda berada di luar radius lokasi kerja. Jarak Anda: ' . round($distance) . ' meter (Batas: ' . $config->radius . ' meter).'
            ], 422);
        }

        // 2. Decode and save the Base64 photo
        $image = $request->foto;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'presensi_' . $user->id . '_' . $request->tipe . '_' . time() . '.png';
        
        // Ensure folder exists
        if (!\File::exists(public_path('/storage/uploads/'))) {
            \File::makeDirectory(public_path('/storage/uploads/'), 0755, true);
        }
        
        \File::put(public_path('/storage/uploads/' . $imageName), base64_decode($image));
        $filePath = '/storage/uploads/' . $imageName;

        // 3. Process Check-In / Check-Out
        if ($request->tipe === 'datang') {
            // Check if already checked in today
            $exists = PresensiGuru::where('user_id', $user->id)
                ->where('tanggal', $today)
                ->exists();

            if ($exists) {
                return response()->json(['error' => 'Anda sudah melakukan presensi datang hari ini.'], 422);
            }

            // Determine active shift for today
            $activeShift = $user->getActiveShift($today);
            $currentTime = $now->toTimeString();

            // Calculate late status using active shift's jam_masuk & tolerance
            $scheduledTime = Carbon::parse($today . ' ' . $activeShift->jam_masuk);
            if ($activeShift->toleransi_terlambat > 0) {
                $scheduledTimeWithTolerance = (clone $scheduledTime)->addMinutes($activeShift->toleransi_terlambat);
            } else {
                $scheduledTimeWithTolerance = $scheduledTime;
            }

            $status = $now->greaterThan($scheduledTimeWithTolerance) ? 'Terlambat' : 'Tepat Waktu';
            $menitTerlambat = 0;
            if ($status === 'Terlambat') {
                $menitTerlambat = $now->diffInMinutes($scheduledTime);
            }

            PresensiGuru::create([
                'user_id' => $user->id,
                'shift_id' => $activeShift->id,
                'nama' => $user->name,
                'tanggal' => $today,
                'jam_datang' => $currentTime,
                'foto_datang' => $filePath,
                'lat_datang' => $request->lat,
                'lng_datang' => $request->lng,
                'status_datang' => $status,
                'menit_terlambat' => $menitTerlambat,
                'tahun_ajaran' => session('tahun_ajaran'),
                'semester' => session('semester')
            ]);

            if ($status === 'Terlambat') {
                $adminsAndKurikulum = User::whereIn('role', ['admin', 'kurikulum', 'kepala'])->get();
                foreach ($adminsAndKurikulum as $u) {
                    $u->sendNotification(
                        "Keterlambatan Presensi Guru/Staf",
                        "Presensi: " . $user->name . " [" . $activeShift->nama_shift . "] terlambat melakukan presensi datang hari ini selama {$menitTerlambat} menit.",
                        '/dashboard',
                        'absen'
                    );
                }
            }

            return response()->json([
                'message' => 'Presensi Datang berhasil disimpan (' . $activeShift->nama_shift . '). Status: ' . $status . ($menitTerlambat > 0 ? ' (' . $menitTerlambat . ' Menit)' : '')
            ]);
        } else {
            // Absen Pulang
            $presensi = PresensiGuru::where('user_id', $user->id)
                ->where('tanggal', $today)
                ->whereNull('jam_pulang')
                ->first();

            // Fallback: check overnight shift check-in from yesterday if today is not found
            if (!$presensi) {
                $yesterday = Carbon::yesterday()->toDateString();
                $presensi = PresensiGuru::where('user_id', $user->id)
                    ->where('tanggal', $yesterday)
                    ->whereNull('jam_pulang')
                    ->first();
            }

            if (!$presensi) {
                return response()->json(['error' => 'Anda harus melakukan presensi datang terlebih dahulu.'], 422);
            }

            if ($presensi->jam_pulang) {
                return response()->json(['error' => 'Anda sudah melakukan presensi pulang untuk shift ini.'], 422);
            }

            $shift = $presensi->shift ?? $user->getActiveShift($presensi->tanggal);
            $currentTime = $now->toTimeString();

            // Target checkout datetime based on shift type
            if ($shift->is_overnight) {
                $checkoutTarget = Carbon::parse($presensi->tanggal . ' ' . $shift->jam_pulang)->addDay();
            } else {
                $checkoutTarget = Carbon::parse($presensi->tanggal . ' ' . $shift->jam_pulang);
            }

            $statusPulang = $now->lessThan($checkoutTarget) ? 'Pulang Sebelum Waktunya' : 'Selesai';
            $menitPulangCepat = 0;
            if ($statusPulang === 'Pulang Sebelum Waktunya') {
                $menitPulangCepat = $now->diffInMinutes($checkoutTarget);
            }

            $presensi->update([
                'jam_pulang' => $currentTime,
                'foto_pulang' => $filePath,
                'lat_pulang' => $request->lat,
                'lng_pulang' => $request->lng,
                'status_pulang' => $statusPulang,
                'menit_pulang_cepat' => $menitPulangCepat
            ]);

            if ($statusPulang === 'Pulang Sebelum Waktunya') {
                $adminsAndKurikulum = User::whereIn('role', ['admin', 'kurikulum', 'kepala'])->get();
                foreach ($adminsAndKurikulum as $u) {
                    $u->sendNotification(
                        "Presensi Pulang Cepat",
                        "Presensi: " . $user->name . " [" . $shift->nama_shift . "] melakukan presensi pulang sebelum waktunya (lebih cepat {$menitPulangCepat} menit).",
                        '/dashboard',
                        'absen'
                    );
                }
            }

            return response()->json([
                'message' => 'Presensi Pulang berhasil disimpan (' . $shift->nama_shift . '). Status: ' . $statusPulang . ($menitPulangCepat > 0 ? ' (' . $menitPulangCepat . ' Menit Awal)' : '')
            ]);
        }
    }

    public function rekap(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403, 'Unauthorized action.');
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $guruId = $request->input('guru_id', 'all');
        $roleFilter = $request->input('role_filter', 'all'); // 'all', 'guru', 'tendik'
        $viewMode = $request->input('view_mode', 'matrix'); // Default to matrix view

        $allowedRoles = ($roleFilter === 'guru') ? ['guru'] : (($roleFilter === 'tendik') ? ['tendik'] : ['guru', 'tendik']);

        $query = PresensiGuru::whereBetween('tanggal', [$startDate, $endDate])
            ->where('tahun_ajaran', session('tahun_ajaran'))
            ->where('semester', session('semester'))
            ->whereHas('user', function($q) use ($allowedRoles) {
                $q->whereIn('role', $allowedRoles);
            })
            ->with(['shift', 'user']);

        if ($guruId !== 'all') {
            $query->where('user_id', $guruId);
        }

        $rekap = $query->orderBy('tanggal', 'desc')->get();
        $gurus = User::whereIn('role', $allowedRoles)
            ->orderBy('name', 'asc')->get();
        $config = $this->getConfig();

        // Generate array of dates for Matrix view
        $periodDates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        while ($current->lte($end)) {
            $periodDates[] = $current->toDateString();
            $current->addDay();
        }

        // Map attendance data for matrix lookup: [user_id][tanggal] = record
        $matrixAttendance = [];
        foreach ($rekap as $r) {
            $matrixAttendance[$r->user_id][$r->tanggal] = $r;
        }

        // Matrix employees list
        $matrixPegawai = $gurus;
        if ($guruId !== 'all') {
            $matrixPegawai = $gurus->where('id', $guruId);
        }

        return view('presensi_guru.rekap', compact(
            'rekap', 'gurus', 'startDate', 'endDate', 'guruId', 'roleFilter', 'config',
            'viewMode', 'periodDates', 'matrixAttendance', 'matrixPegawai'
        ));
    }

    public function setting()
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }
        $config = $this->getConfig();
        $shifts = Shift::orderBy('id', 'asc')->get();
        return view('presensi_guru.setting', compact('config', 'shifts'));
    }

    public function updateSetting(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:1'
        ]);

        $updateData = [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'updated_at' => now(),
        ];
        if ($request->has('jam_masuk')) {
            $updateData['jam_masuk'] = $request->jam_masuk;
        }
        if ($request->has('jam_pulang')) {
            $updateData['jam_pulang'] = $request->jam_pulang;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('presensi_guru_settings', 'radius')) {
            $updateData['radius'] = $request->radius;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('presensi_guru_settings', 'radius_meters')) {
            $updateData['radius_meters'] = $request->radius;
        }
        DB::table('presensi_guru_settings')->where('id', 1)->update($updateData);

        return redirect('/presensi-guru/setting')->with('sukses', 'Pengaturan lokasi kantor berhasil diperbarui!');
    }

    /* -------------------------------------------------------------------------- */
    /*                         MANAJEMEN SHIFT & ROSTER                           */
    /* -------------------------------------------------------------------------- */

    public function shiftIndex()
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $shifts = Shift::orderBy('id', 'asc')->get();
        $pegawai = User::whereIn('role', ['guru', 'tendik', 'satpam', 'pembina', 'kepala', 'admin', 'kurikulum'])
            ->with('defaultShift')
            ->orderBy('name', 'asc')
            ->get();

        $todaySchedules = UserShiftSchedule::where('tanggal', Carbon::today()->toDateString())
            ->with(['user', 'shift'])
            ->get();

        return view('presensi_guru.shifts', compact('shifts', 'pegawai', 'todaySchedules'));
    }

    public function shiftStore(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $request->validate([
            'nama_shift' => 'required|string|max:255',
            'jam_masuk' => 'required',
            'jam_pulang' => 'required',
            'toleransi_terlambat' => 'nullable|integer|min:0'
        ]);

        Shift::create([
            'nama_shift' => $request->nama_shift,
            'jam_masuk' => $request->jam_masuk,
            'jam_pulang' => $request->jam_pulang,
            'is_overnight' => $request->has('is_overnight') ? true : false,
            'toleransi_terlambat' => $request->input('toleransi_terlambat', 0),
            'keterangan' => $request->keterangan
        ]);

        return redirect()->back()->with('sukses', 'Shift baru berhasil ditambahkan!');
    }

    public function shiftUpdate(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $shift = Shift::findOrFail($id);

        $request->validate([
            'nama_shift' => 'required|string|max:255',
            'jam_masuk' => 'required',
            'jam_pulang' => 'required',
            'toleransi_terlambat' => 'nullable|integer|min:0'
        ]);

        $shift->update([
            'nama_shift' => $request->nama_shift,
            'jam_masuk' => $request->jam_masuk,
            'jam_pulang' => $request->jam_pulang,
            'is_overnight' => $request->has('is_overnight') ? true : false,
            'toleransi_terlambat' => $request->input('toleransi_terlambat', 0),
            'keterangan' => $request->keterangan
        ]);

        return redirect()->back()->with('sukses', 'Data Shift berhasil diperbarui!');
    }

    public function shiftDelete($id)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        if ($id == 1) {
            return redirect()->back()->with('gagal', 'Shift Reguler Utama tidak dapat dihapus.');
        }

        $shift = Shift::findOrFail($id);
        $shift->delete();

        return redirect()->back()->with('sukses', 'Shift berhasil dihapus!');
    }

    public function updateUserDefaultShift(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'default_shift_id' => 'required|exists:shifts,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update(['default_shift_id' => $request->default_shift_id]);

        return redirect()->back()->with('sukses', 'Default Shift pegawai berhasil diperbarui!');
    }

    public function storeRosterSchedule(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'tanggal' => 'required|date'
        ]);

        UserShiftSchedule::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal
            ],
            [
                'shift_id' => $request->shift_id
            ]
        );

        return redirect()->back()->with('sukses', 'Roster shift harian berhasil disimpan!');
    }

    public function deleteRosterSchedule($id)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $schedule = UserShiftSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->back()->with('sukses', 'Roster shift harian berhasil dibatalkan/dihapus!');
    }

    public function update(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $request->validate([
            'id' => 'required|exists:presensi_guru,id',
            'tanggal' => 'required|date',
            'jam_datang' => 'nullable',
            'jam_pulang' => 'nullable',
            'status_datang' => 'required|in:Tepat Waktu,Terlambat',
            'menit_terlambat' => 'required|integer|min:0',
            'status_pulang' => 'required|in:Selesai,Pulang Sebelum Waktunya',
            'menit_pulang_cepat' => 'required|integer|min:0'
        ]);

        $presensi = PresensiGuru::findOrFail($request->id);
        $presensi->update([
            'tanggal' => $request->tanggal,
            'jam_datang' => $request->jam_datang ?: null,
            'jam_pulang' => $request->jam_pulang ?: null,
            'status_datang' => $request->status_datang,
            'menit_terlambat' => $request->menit_terlambat,
            'status_pulang' => $request->status_pulang,
            'menit_pulang_cepat' => $request->menit_pulang_cepat
        ]);

        return redirect()->back()->with('sukses', 'Data presensi berhasil diperbarui!');
    }

    public function delete($id)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $presensi = PresensiGuru::findOrFail($id);

        if ($presensi->foto_datang && \File::exists(public_path($presensi->foto_datang))) {
            \File::delete(public_path($presensi->foto_datang));
        }
        if ($presensi->foto_pulang && \File::exists(public_path($presensi->foto_pulang))) {
            \File::delete(public_path($presensi->foto_pulang));
        }

        $presensi->delete();

        return redirect()->back()->with('sukses', 'Data presensi berhasil dihapus!');
    }

    public function exportExcel(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $guruId = $request->input('guru_id', 'all');
        $roleFilter = $request->input('role_filter', 'all');
        $viewMode = $request->input('view_mode', 'matrix');

        $allowedRoles = ($roleFilter === 'guru') ? ['guru'] : (($roleFilter === 'tendik') ? ['tendik'] : ['guru', 'tendik']);

        $query = PresensiGuru::whereBetween('tanggal', [$startDate, $endDate])
            ->where('tahun_ajaran', session('tahun_ajaran'))
            ->where('semester', session('semester'))
            ->whereHas('user', function($q) use ($allowedRoles) {
                $q->whereIn('role', $allowedRoles);
            });

        if ($guruId !== 'all') {
            $query->where('user_id', $guruId);
        }

        $data = $query->orderBy('tanggal', 'desc')->get();

        if ($viewMode === 'matrix') {
            $gurus = User::whereIn('role', $allowedRoles)
                ->orderBy('name', 'asc')->get();

            $periodDates = [];
            $current = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            while ($current->lte($end)) {
                $periodDates[] = $current->toDateString();
                $current->addDay();
            }

            $matrixAttendance = [];
            foreach ($data as $r) {
                $matrixAttendance[$r->user_id][$r->tanggal] = $r;
            }

            $matrixPegawai = ($guruId !== 'all') ? $gurus->where('id', $guruId) : $gurus;

            return Excel::download(
                new PresensiGuruExportMatrix($matrixPegawai, $periodDates, $matrixAttendance, $startDate, $endDate),
                'rekap_matriks_presensi_guru_' . $startDate . '_to_' . $endDate . '.xlsx'
            );
        }

        return Excel::download(new PresensiGuruExport($data), 'rekap_presensi_guru_' . $startDate . '_to_' . $endDate . '.xlsx');
    }

    public function exportPDF(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            abort(403);
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $guruId = $request->input('guru_id', 'all');
        $roleFilter = $request->input('role_filter', 'all');
        $viewMode = $request->input('view_mode', 'matrix');

        $allowedRoles = ($roleFilter === 'guru') ? ['guru'] : (($roleFilter === 'tendik') ? ['tendik'] : ['guru', 'tendik']);

        $query = PresensiGuru::whereBetween('tanggal', [$startDate, $endDate])
            ->where('tahun_ajaran', session('tahun_ajaran'))
            ->where('semester', session('semester'))
            ->whereHas('user', function($q) use ($allowedRoles) {
                $q->whereIn('role', $allowedRoles);
            });

        if ($guruId !== 'all') {
            $query->where('user_id', $guruId);
        }

        $rekap = $query->orderBy('tanggal', 'desc')->get();

        if ($viewMode === 'matrix') {
            $gurus = User::whereIn('role', $allowedRoles)
                ->orderBy('name', 'asc')->get();

            $periodDates = [];
            $current = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            while ($current->lte($end)) {
                $periodDates[] = $current->toDateString();
                $current->addDay();
            }

            $matrixAttendance = [];
            foreach ($rekap as $r) {
                $matrixAttendance[$r->user_id][$r->tanggal] = $r;
            }

            $matrixPegawai = ($guruId !== 'all') ? $gurus->where('id', $guruId) : $gurus;

            $pdf = PDF::loadView('presensi_guru.pdf_matrix', compact('matrixPegawai', 'periodDates', 'matrixAttendance', 'startDate', 'endDate'));
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download('rekap_matriks_presensi_guru_' . $startDate . '_to_' . $endDate . '.pdf');
        }

        $pdf = PDF::loadView('presensi_guru.pdf', compact('rekap', 'startDate', 'endDate'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('rekap_presensi_guru_' . $startDate . '_to_' . $endDate . '.pdf');
    }

    public function getRiwayatSayaAjax(Request $request)
    {
        $user = auth()->user();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $query = PresensiGuru::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->with('shift');

        $totalRecords = $query->count();

        if ($searchValue = $request->input('search.value')) {
            $query->where(function($q) use ($searchValue) {
                $q->where('tanggal', 'LIKE', "%{$searchValue}%")
                  ->orWhere('status_datang', 'LIKE', "%{$searchValue}%")
                  ->orWhere('status_pulang', 'LIKE', "%{$searchValue}%")
                  ->orWhereHas('shift', function($sq) use ($searchValue) {
                      $sq->where('nama_shift', 'LIKE', "%{$searchValue}%");
                  });
            });
        }

        $filteredRecords = $query->count();

        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));

        $records = $query->orderBy('tanggal', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data = [];
        $no = $start + 1;
        foreach ($records as $r) {
            $shiftName = $r->shift->nama_shift ?? 'Reguler Guru & Tendik';
            
            $datangBadge = $r->jam_datang 
                ? '<span class="badge bg-success font-monospace fs-6">' . substr($r->jam_datang, 0, 5) . '</span>' 
                : '<span class="text-muted">-</span>';
                
            $pulangBadge = $r->jam_pulang 
                ? '<span class="badge bg-warning text-dark font-monospace fs-6">' . substr($r->jam_pulang, 0, 5) . '</span>' 
                : '<span class="text-muted">-</span>';

            $statusDatang = ($r->status_datang == 'Terlambat')
                ? '<span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i> Terlambat (' . ($r->menit_terlambat ?? 0) . ' Menit)</span>'
                : '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Tepat Waktu</span>';

            $statusPulang = '-';
            if ($r->status_pulang == 'Pulang Sebelum Waktunya') {
                $statusPulang = '<span class="badge bg-warning text-white"><i class="fas fa-running me-1"></i> Pulang Cepat (' . ($r->menit_pulang_cepat ?? 0) . ' Menit)</span>';
            } elseif ($r->status_pulang == 'Selesai') {
                $statusPulang = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Selesai</span>';
            }

            $fotoDatang = $r->foto_datang 
                ? '<img src="' . asset($r->foto_datang) . '" alt="Datang" class="rounded border shadow-xs" style="max-height: 40px;">' 
                : '-';

            $fotoPulang = $r->foto_pulang 
                ? '<img src="' . asset($r->foto_pulang) . '" alt="Pulang" class="rounded border shadow-xs" style="max-height: 40px;">' 
                : '-';

            $data[] = [
                'no' => $no++,
                'tanggal' => Carbon::parse($r->tanggal)->format('d-m-Y'),
                'shift' => '<span class="badge bg-info text-white fw-bold px-2 py-1">' . $shiftName . '</span>',
                'jam_datang' => $datangBadge,
                'jam_pulang' => $pulangBadge,
                'status_datang' => $statusDatang,
                'status_pulang' => $statusPulang,
                'foto_datang' => $fotoDatang,
                'foto_pulang' => $fotoPulang
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function getRekapLogAjax(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'kurikulum') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $guruId = $request->input('guru_id', 'all');
        $roleFilter = $request->input('role_filter', 'all');

        $allowedRoles = ($roleFilter === 'guru') ? ['guru'] : (($roleFilter === 'tendik') ? ['tendik'] : ['guru', 'tendik']);

        $query = PresensiGuru::whereBetween('tanggal', [$startDate, $endDate])
            ->where('tahun_ajaran', session('tahun_ajaran'))
            ->where('semester', session('semester'))
            ->whereHas('user', function($q) use ($allowedRoles) {
                $q->whereIn('role', $allowedRoles);
            })
            ->with(['shift', 'user']);

        if ($guruId !== 'all') {
            $query->where('user_id', $guruId);
        }

        $totalRecords = $query->count();

        if ($searchValue = $request->input('search.value')) {
            $query->where(function($q) use ($searchValue) {
                $q->where('tanggal', 'LIKE', "%{$searchValue}%")
                  ->orWhere('jam_datang', 'LIKE', "%{$searchValue}%")
                  ->orWhere('jam_pulang', 'LIKE', "%{$searchValue}%")
                  ->orWhere('status_datang', 'LIKE', "%{$searchValue}%")
                  ->orWhere('status_pulang', 'LIKE', "%{$searchValue}%")
                  ->orWhereHas('user', function($uq) use ($searchValue) {
                      $uq->where('name', 'LIKE', "%{$searchValue}%");
                  });
            });
        }

        $filteredRecords = $query->count();

        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));

        $records = $query->orderBy('tanggal', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data = [];
        $no = $start + 1;
        $isAdmin = auth()->user()->role === 'admin';

        foreach ($records as $r) {
            $namaUser = $r->user->name ?? $r->nama;

            $statusDatang = ($r->status_datang == 'Terlambat')
                ? '<span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i> Terlambat</span>'
                : '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Tepat Waktu</span>';

            $statusPulang = '-';
            if ($r->status_pulang == 'Pulang Sebelum Waktunya') {
                $statusPulang = '<span class="badge bg-warning text-white"><i class="fas fa-running me-1"></i> Pulang Cepat</span>';
            } elseif ($r->status_pulang == 'Selesai') {
                $statusPulang = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Selesai</span>';
            }

            $fotoDatang = $r->foto_datang 
                ? '<button type="button" class="btn btn-link p-0 border-0" onclick="showPhoto(\'' . asset($r->foto_datang) . '\', \'Bukti Datang - ' . addslashes($namaUser) . '\')"><img src="' . asset($r->foto_datang) . '" alt="Datang" class="rounded border shadow-xs" style="max-height: 40px;"></button>'
                : '-';

            $fotoPulang = $r->foto_pulang 
                ? '<button type="button" class="btn btn-link p-0 border-0" onclick="showPhoto(\'' . asset($r->foto_pulang) . '\', \'Bukti Pulang - ' . addslashes($namaUser) . '\')"><img src="' . asset($r->foto_pulang) . '" alt="Pulang" class="rounded border shadow-xs" style="max-height: 40px;"></button>'
                : '-';

            $aksi = '-';
            if ($isAdmin) {
                $aksi = '<div class="d-flex gap-1 justify-content-center">
                    <button type="button" class="btn btn-warning btn-sm text-white"
                        data-bs-toggle="modal"
                        data-bs-target="#editPresensiModal"
                        data-id="' . $r->id . '"
                        data-nama="' . htmlspecialchars($namaUser) . '"
                        data-tanggal="' . $r->tanggal . '"
                        data-datang="' . ($r->jam_datang ?? '') . '"
                        data-pulang="' . ($r->jam_pulang ?? '') . '"
                        data-status="' . ($r->status_datang ?? 'Tepat Waktu') . '"
                        data-menit="' . ($r->menit_terlambat ?? 0) . '"
                        data-statuspulang="' . ($r->status_pulang ?? 'Selesai') . '"
                        data-menitpulangcepat="' . ($r->menit_pulang_cepat ?? 0) . '"
                        onclick="populateEditModal(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <a href="/presensi-guru/' . $r->id . '/delete" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data presensi ini?\')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>';
            }

            $data[] = [
                'no' => $no++,
                'nama' => '<strong class="text-dark">' . e($namaUser) . '</strong>',
                'tanggal' => Carbon::parse($r->tanggal)->format('d-m-Y'),
                'jam_datang' => $r->jam_datang ?? '-',
                'jam_pulang' => $r->jam_pulang ?? '-',
                'status_datang' => $statusDatang,
                'menit_terlambat' => $r->menit_terlambat > 0 ? $r->menit_terlambat . ' Menit' : '-',
                'status_pulang' => $statusPulang,
                'menit_pulang_cepat' => $r->menit_pulang_cepat > 0 ? $r->menit_pulang_cepat . ' Menit' : '-',
                'foto_datang' => $fotoDatang,
                'foto_pulang' => $fotoPulang,
                'aksi' => $aksi
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    private function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
