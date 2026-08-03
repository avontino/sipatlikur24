<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<style>body{font-family:monospace;font-size:13px;} pre{background:#f4f4f4;padding:8px;border-radius:4px;} h2{color:#333;border-bottom:1px solid #ccc;}</style>";

echo "<h2>1. Semua Jadwal Avo di Tabel Jadwal</h2>";
$jadwals = DB::table('jadwal')
    ->where('guru', 'LIKE', '%Avo%')
    ->orWhere('guru', 'LIKE', '%Satriyatma%')
    ->get();

if ($jadwals->isEmpty()) {
    echo "<b style='color:red'>TIDAK ADA JADWAL AVO DI TABEL JADWAL!</b>";
} else {
    foreach ($jadwals as $j) {
        echo "<pre>";
        echo "ID: {$j->id} | Guru: '{$j->guru}' | Hari: '{$j->hari}' | Kelas: '{$j->kelas}' | Mapel: '{$j->mapel}' | TA: '" . ($j->tahun_ajaran ?? 'NULL') . "' | Sem: '" . ($j->semester ?? 'NULL') . "'";
        echo "</pre>";
    }
}

echo "<h2>2. Peringatan Dashboard (Simulasi Logic)</h2>";
$user = DB::table('users')->where('name', 'LIKE', '%Avo%')->first();
$dayOfWeek = now()->dayOfWeek; // 0=Sun
$hariEng = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
$hariInd = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

$hEng = $hariEng[$dayOfWeek];
$hInd = $hariInd[$dayOfWeek];

echo "Hari ini: Eng='{$hEng}', Ind='{$hInd}' (dayOfWeek={$dayOfWeek})<br>";
echo "Session TA: '" . session('tahun_ajaran') . "', Sem: '" . session('semester') . "'<br><br>";

$schedules = DB::table('jadwal')
    ->where(function($q) use ($user) {
        $q->where('guru', 'LIKE', '%' . $user->name . '%')
          ->orWhere('guru', 'LIKE', '%' . $user->username . '%');
    })
    ->where(function($q) use ($hEng, $hInd) {
        $q->where('hari', $hEng)->orWhere('hari', $hInd);
    })
    ->get();

echo "Total jadwal ditemukan untuk hari ini (semua TA): " . $schedules->count() . "<br>";
foreach ($schedules as $s) {
    echo "<pre>ID: {$s->id} | Hari: {$s->hari} | Kelas: {$s->kelas} | Mapel: {$s->mapel} | TA: '{$s->tahun_ajaran}' | Sem: '{$s->semester}'</pre>";
}
