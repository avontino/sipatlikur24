<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<style>body{font-family:monospace;font-size:13px;} pre{background:#f4f4f4;padding:8px;border-radius:4px;} h2{color:#333;border-bottom:1px solid #ccc;}</style>";

$user = DB::table('users')->where('name', 'LIKE', '%Avo%')->first();
$todayStr = now()->toDateString();
$dayOfWeek = now()->dayOfWeek;

$hariMapAll = [
    0 => ['Sunday', 'sunday', 'Minggu', 'minggu'],
    1 => ['Monday', 'monday', 'Senin', 'senin'],
    2 => ['Tuesday', 'tuesday', 'Selasa', 'selasa'],
    3 => ['Wednesday', 'wednesday', 'Rabu', 'rabu'],
    4 => ['Thursday', 'thursday', 'Kamis', 'kamis'],
    5 => ['Friday', 'friday', 'Jumat', 'jumat', "Jum'at"],
    6 => ['Saturday', 'saturday', 'Sabtu', 'sabtu'],
];
$daysForToday = $hariMapAll[$dayOfWeek] ?? ['Monday', 'Senin'];

$rawTa = session('tahun_ajaran') ?? '2026/2027';
$rawSem = session('semester') ?? 'Ganjil';

echo "<h2>1. Info User & Session Hari Ini</h2>";
echo "User: " . ($user ? $user->name : 'NULL') . "<br>";
echo "Tanggal Hari Ini: {$todayStr} (dayOfWeek={$dayOfWeek})<br>";
echo "Filter Hari: " . implode(', ', $daysForToday) . "<br>";
echo "Session TA: '{$rawTa}', Sem: '{$rawSem}'<br>";

echo "<h2>2. Jadwal Avo Hari Ini di TA 2026/2027</h2>";
$cleanTa = trim(preg_replace('/\s*\(.*\)/', '', $rawTa));
$firstYear = explode('/', $cleanTa)[0] ?? $cleanTa;

$schedules = DB::table('jadwal')
    ->where(function($q) use ($user) {
        $q->where('guru', 'LIKE', '%' . $user->name . '%')
          ->orWhere('guru', 'LIKE', '%' . $user->username . '%');
    })
    ->whereIn('hari', $daysForToday)
    ->where(function($q) use ($rawTa, $cleanTa, $firstYear) {
        $q->where('tahun_ajaran', $rawTa)
          ->orWhere('tahun_ajaran', 'LIKE', '%' . $cleanTa . '%')
          ->orWhere('tahun_ajaran', 'LIKE', '%' . $firstYear . '%')
          ->orWhereNull('tahun_ajaran')
          ->orWhere('tahun_ajaran', '');
    })
    ->get();

echo "Total Jadwal Hari Ini: " . $schedules->count() . "<br>";
foreach ($schedules as $s) {
    echo "<pre>ID: {$s->id} | Hari: {$s->hari} | Kelas: {$s->kelas} | Mapel: {$s->mapel} | TA: '{$s->tahun_ajaran}' | Sem: '{$s->semester}'</pre>";
}

echo "<h2>3. Jurnal Avo yang Sudah Diisi Hari Ini ({$todayStr})</h2>";
$jurnalsToday = DB::table('jurnal')
    ->where(function($q) use ($user) {
        $q->where('guru', 'LIKE', '%' . $user->name . '%')
          ->orWhere('guru_id', $user->id);
    })
    ->whereDate('created_at', $todayStr)
    ->get();

echo "Total Jurnal Hari Ini: " . $jurnalsToday->count() . "<br>";
foreach ($jurnalsToday as $j) {
    echo "<pre>ID: {$j->id} | Kelas: {$j->kelas} | Mapel: {$j->mapel} | Materi: {$j->materi} | TA: {$j->tahun_ajaran} | Sem: {$j->semester} | Tgl: {$j->created_at}</pre>";
}

echo "<h2>4. Peringatan Jadwal Belum Diisi (Simulasi Result)</h2>";
$notFilled = [];
foreach ($schedules as $sch) {
    $filled = DB::table('jurnal')
        ->where(function($q) use ($user) {
            $q->where('guru', 'LIKE', '%' . $user->name . '%')
              ->orWhere('guru_id', $user->id);
        })
        ->where('kelas', $sch->kelas)
        ->where('mapel', $sch->mapel)
        ->whereDate('created_at', $todayStr)
        ->exists();

    if (!$filled) {
        $notFilled[] = $sch->kelas . ' (' . $sch->mapel . ')';
    } else {
        echo "<pre style='color:green'>✅ Schedule {$sch->kelas} ({$sch->mapel}) SUDAH DIISI hari ini.</pre>";
    }
}

if (empty($notFilled)) {
    echo "<b style='color:green'>🎉 SEMUA JADWAL HARI INI SUDAH DIISI! Maka peringatan memang SEHARUSNYA HILANG/TIDAK MUNCUL.</b>";
} else {
    echo "<b style='color:red'>⚠️ Jadwal yang BELUM diisi: " . implode(', ', $notFilled) . "</b>";
}
