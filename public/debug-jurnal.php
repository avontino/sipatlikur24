<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<style>body{font-family:monospace;font-size:13px;} pre{background:#f4f4f4;padding:8px;border-radius:4px;} h2{color:#333;border-bottom:1px solid #ccc;padding-bottom:5px;}</style>";

// Cek data Avo di 2026/2027
echo "<h2>1. Semua data Avo Satriyatma di 2026/2027</h2>";
$rows = DB::table('jurnal')
    ->where(function($q) {
        $q->where('guru', 'LIKE', '%Avo%')
          ->orWhere('guru', 'LIKE', '%Satriyatma%')
          ->orWhere('guru_id', 197);
    })
    ->where('tahun_ajaran', 'LIKE', '%2026%')
    ->get();

if ($rows->isEmpty()) {
    echo "<b style='color:red'>❌ TIDAK ADA data Avo di 2026/2027 sama sekali!</b>";
    echo "<br>Kemungkinan: sinkron belum dilakukan atau jadwal Avo belum ada.";
} else {
    foreach ($rows as $r) {
        echo "<pre>Guru: {$r->guru} | guru_id: {$r->guru_id} | Kelas: {$r->kelas} | Mapel: {$r->mapel} | TA: {$r->tahun_ajaran} | Sem: {$r->semester} | Tgl: {$r->created_at}</pre>";
    }
}

// Cek jadwal Avo untuk hari ini
echo "<h2>2. Jadwal Avo Satriyatma di tabel jadwal (TA 2026/2027)</h2>";
$jadwals = DB::table('jadwal')
    ->where(function($q) {
        $q->where('guru', 'LIKE', '%Avo%')
          ->orWhere('guru', 'LIKE', '%Satriyatma%');
    })
    ->where('tahun_ajaran', 'LIKE', '%2026%')
    ->get();

if ($jadwals->isEmpty()) {
    echo "<b style='color:red'>❌ Tidak ada jadwal Avo di TA 2026/2027!</b>";
} else {
    echo "<b style='color:green'>✅ Jadwal ditemukan: " . $jadwals->count() . " entri</b>";
    foreach ($jadwals as $j) {
        echo "<pre>Hari: {$j->hari} | Kelas: {$j->kelas} | Jamke: {$j->jamke} | Mapel: {$j->mapel} | TA: {$j->tahun_ajaran} | Sem: {$j->semester}</pre>";
    }
}

// Cek hari ini apa dan apakah ada jadwal
$hariIni = date('l'); // English
echo "<h2>3. Hari ini: <b>{$hariIni}</b> | Tanggal: " . date('Y-m-d') . "</h2>";

$jadwalHariIni = DB::table('jadwal')
    ->where(function($q) {
        $q->where('guru', 'LIKE', '%Avo%')
          ->orWhere('guru', 'LIKE', '%Satriyatma%');
    })
    ->where('hari', $hariIni)
    ->where('tahun_ajaran', 'LIKE', '%2026%')
    ->get();

if ($jadwalHariIni->isEmpty()) {
    echo "<b style='color:orange'>⚠️ Tidak ada jadwal Avo untuk hari {$hariIni} - mungkin tidak ada kelas hari ini</b>";
} else {
    echo "<b style='color:green'>✅ Ada jadwal hari ini!</b>";
    foreach ($jadwalHariIni as $j) {
        echo "<pre>Kelas: {$j->kelas} | Jamke: {$j->jamke} | Mapel: {$j->mapel}</pre>";
    }
}

// Cek apakah jurnalh untuk hari ini sudah disinkron
echo "<h2>4. Jurnalh yang sudah disinkron hari ini</h2>";
$today = date('Y-m-d');
$jurnalh = DB::table('jurnalh')
    ->whereDate('created_at', $today)
    ->select('kelas', 'tahun_ajaran', 'semester', 'created_at')
    ->get();

if ($jurnalh->isEmpty()) {
    echo "<b style='color:red'>❌ Belum ada sinkron jurnal harian hari ini!</b>";
} else {
    echo "<b style='color:green'>✅ Sudah ada sinkron: " . $jurnalh->count() . " kelas</b>";
    foreach ($jurnalh as $jh) {
        echo "<pre>Kelas: {$jh->kelas} | TA: {$jh->tahun_ajaran} | Sem: {$jh->semester}</pre>";
    }
}

// Cek jurnal hari ini
echo "<h2>5. Data jurnal yang dibuat hari ini ({$today})</h2>";
$jurnalHariIni = DB::table('jurnal')->whereDate('created_at', $today)->count();
echo "<pre>Total entri jurnal hari ini: <b>{$jurnalHariIni}</b></pre>";

// Cek nama Avo di jadwal (semua TA) untuk validasi nama
echo "<h2>6. Format nama Avo di tabel jadwal (semua TA)</h2>";
$namaAvoJadwal = DB::table('jadwal')
    ->where('guru', 'LIKE', '%Avo%')
    ->orWhere('guru', 'LIKE', '%Satriyatma%')
    ->distinct()
    ->pluck('guru');

if ($namaAvoJadwal->isEmpty()) {
    echo "<b style='color:red'>❌ Nama 'Avo' atau 'Satriyatma' tidak ditemukan di jadwal sama sekali!</b>";
} else {
    foreach ($namaAvoJadwal as $n) {
        echo "<pre>'{$n}'</pre>";
    }
}
