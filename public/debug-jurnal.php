<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "<style>body{font-family:monospace;font-size:13px;} pre{background:#f4f4f4;padding:8px;border-radius:4px;}</style>";

// Cek semua data FEIZHIFARA di siswa
echo "<h2>1. Semua data FEIZHIFARA di tabel siswa (semua tahun)</h2>";
$rows = DB::table('siswa')
    ->where('nama', 'LIKE', '%FEIZHIFARA%')
    ->orWhere('nama', 'LIKE', '%QONITAH%')
    ->orderBy('id', 'desc')
    ->get();
if ($rows->isEmpty()) {
    echo "<b style='color:red'>Tidak ditemukan!</b>";
} else {
    foreach ($rows as $r) {
        echo "<pre>ID: {$r->id} | NIS: {$r->nis} | Nama: {$r->nama} | Kelas: {$r->kelas} | TA: " . ($r->tahun_ajaran ?? 'NULL') . " | Sem: " . ($r->semester ?? 'NULL') . "</pre>";
    }
}

// Cek user FEIZHIFARA
echo "<h2>2. User FEIZHIFARA di tabel users</h2>";
$u = DB::table('users')
    ->where('name', 'LIKE', '%FEIZHIFARA%')
    ->orWhere('name', 'LIKE', '%QONITAH%')
    ->first();
if ($u) {
    echo "<pre>ID: {$u->id} | Name: {$u->name} | Username: {$u->username} | Role: {$u->role} | Additional Roles: " . ($u->additional_roles ?? 'NULL') . " | walikelas_kelas: " . ($u->walikelas_kelas ?? 'NULL') . "</pre>";
}

// Cek data siswa kelas 9B tahun ajaran aktif
echo "<h2>3. Siswa kelas 9B di TA 2026/2027</h2>";
$siswa9B = DB::table('siswa')
    ->where('kelas', '9B')
    ->where('tahun_ajaran', 'LIKE', '%2026/2027%')
    ->limit(5)->get();
if ($siswa9B->isEmpty()) {
    echo "<b style='color:orange'>Belum ada siswa 9B di TA 2026/2027</b>";
} else {
    echo "<b style='color:green'>Ada " . $siswa9B->count() . " siswa</b>";
    foreach ($siswa9B as $s) {
        echo "<pre>{$s->nis} | {$s->nama} | {$s->kelas} | {$s->tahun_ajaran}</pre>";
    }
}

// Cek tahun ajaran aktif
echo "<h2>4. Tahun Ajaran Aktif</h2>";
$ta = DB::table('tahun_ajaran')->where('status', 1)->first();
echo "<pre>" . json_encode((array)$ta, JSON_PRETTY_PRINT) . "</pre>";
