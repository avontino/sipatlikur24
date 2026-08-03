<?php
// Script debug - jalankan di browser: https://sipatlikur.smpn24-mlg.sch.id/debug-jurnal
// Letakkan di public/debug-jurnal.php

// Bypass Laravel auth untuk debugging
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Cek struktur tabel jurnal
echo "<h2>1. Struktur Tabel Jurnal (beberapa baris pertama)</h2>";
$cols = DB::select("SHOW COLUMNS FROM jurnal");
echo "<table border=1><tr><th>Field</th><th>Type</th><th>Null</th></tr>";
foreach ($cols as $col) {
    echo "<tr><td>{$col->Field}</td><td>{$col->Type}</td><td>{$col->Null}</td></tr>";
}
echo "</table>";

// Ambil contoh data
echo "<h2>2. Contoh 5 Data Jurnal Terbaru</h2>";
$rows = DB::table('jurnal')->orderBy('id', 'desc')->limit(5)->get();
foreach ($rows as $row) {
    echo "<pre>";
    echo "ID: {$row->id}\n";
    echo "Guru: {$row->guru}\n";
    echo "Guru ID: " . ($row->guru_id ?? 'NULL') . "\n";
    echo "Kelas: {$row->kelas}\n";
    echo "Mapel: {$row->mapel}\n";
    echo "Tahun Ajaran: " . ($row->tahun_ajaran ?? 'NULL') . "\n";
    echo "Semester: " . ($row->semester ?? 'NULL') . "\n";
    echo "Created At: {$row->created_at}\n";
    echo "</pre><hr>";
}

// Cek user Avo Satriyatma
echo "<h2>3. Data User 'Avo Satriyatma'</h2>";
$user = DB::table('users')->where('name', 'LIKE', '%Avo%')->orWhere('name', 'LIKE', '%Satriyatma%')->get();
foreach ($user as $u) {
    echo "<pre>";
    echo "ID: {$u->id}\n";
    echo "Name: {$u->name}\n";
    echo "Username: {$u->username}\n";
    echo "Role: {$u->role}\n";
    echo "Additional Roles: " . ($u->additional_roles ?? 'NULL') . "\n";
    echo "Walikelas Kelas: " . ($u->walikelas_kelas ?? 'NULL') . "\n";
    echo "</pre>";
}

// Cek jurnal yang mengandung nama guru tsb
echo "<h2>4. Jurnal yang mengandung nama 'Avo' atau 'Satriyatma'</h2>";
$jurnals = DB::table('jurnal')
    ->where('guru', 'LIKE', '%Avo%')
    ->orWhere('guru', 'LIKE', '%Satriyatma%')
    ->limit(5)
    ->get();
if ($jurnals->isEmpty()) {
    echo "<b style='color:red'>TIDAK ADA DATA - Nama guru tidak cocok!</b>";
} else {
    foreach ($jurnals as $j) {
        echo "<pre>Guru: {$j->guru} | Kelas: {$j->kelas} | Mapel: {$j->mapel} | TA: " . ($j->tahun_ajaran ?? 'NULL') . " | Sem: " . ($j->semester ?? 'NULL') . "</pre>";
    }
}

// Cek data jurnal tahun ajaran aktif
echo "<h2>5. Tahun Ajaran yang ada di tabel jurnal (distinct)</h2>";
$tas = DB::table('jurnal')->select('tahun_ajaran', 'semester')->distinct()->orderBy('tahun_ajaran', 'desc')->limit(10)->get();
foreach ($tas as $ta) {
    echo "<pre>Tahun Ajaran: '" . ($ta->tahun_ajaran ?? 'NULL') . "' | Semester: '" . ($ta->semester ?? 'NULL') . "'</pre>";
}

// Cek Tahun Ajaran Aktif
echo "<h2>6. Tahun Ajaran Aktif di Tabel 'tahun_ajaran'</h2>";
$activeTa = DB::table('tahun_ajaran')->where('status', 1)->first();
if ($activeTa) {
    echo "<pre>";
    echo "ID: {$activeTa->id}\n";
    echo "Tahun Ajaran: {$activeTa->tahun_ajaran}\n";
    echo "Semester: {$activeTa->semester}\n";
    echo "Status: {$activeTa->status}\n";
    echo "</pre>";
} else {
    echo "<b style='color:red'>TIDAK ADA tahun ajaran aktif!</b>";
}

// Cek seluruh nama guru unik di jurnal
echo "<h2>7. Semua Guru Unik di Tabel Jurnal (10 sample)</h2>";
$gurus = DB::table('jurnal')->select('guru')->distinct()->limit(10)->get();
foreach ($gurus as $g) {
    echo "<pre>" . $g->guru . "</pre>";
}
