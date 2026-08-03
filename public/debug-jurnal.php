<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "<style>body{font-family:monospace;font-size:13px;} pre{background:#f4f4f4;padding:8px;border-radius:4px;}</style>";

// Cek kolom tabel siswa
echo "<h2>1. Kolom tabel siswa</h2>";
$cols = DB::select("SHOW COLUMNS FROM siswa");
echo "<table border=1 cellpadding=4><tr><th>Field</th><th>Type</th><th>Null</th></tr>";
foreach ($cols as $c) { echo "<tr><td>{$c->Field}</td><td>{$c->Type}</td><td>{$c->Null}</td></tr>"; }
echo "</table>";

// Cek semua record FEIZHIFARA
echo "<h2>2. Semua record FEIZHIFARA di siswa</h2>";
$rows = DB::table('siswa')
    ->where('nama', 'LIKE', '%FEIZHIFARA%')
    ->orWhere('nama', 'LIKE', '%QONITAH%')
    ->get();
if ($rows->isEmpty()) echo "<b style='color:red'>Tidak ada!</b>";
foreach ($rows as $r) echo "<pre>" . json_encode((array)$r, JSON_PRETTY_PRINT) . "</pre>";

// Cek data user FEIZHIFARA
echo "<h2>3. User FEIZHIFARA di tabel users</h2>";
$u = DB::table('users')->where('name','LIKE','%FEIZHIFARA%')->orWhere('name','LIKE','%QONITAH%')->first();
if ($u) echo "<pre>" . json_encode((array)$u, JSON_PRETTY_PRINT) . "</pre>";
else echo "<b style='color:red'>Tidak ada!</b>";
