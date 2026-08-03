<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<style>body{font-family:monospace;font-size:13px;} pre{background:#f4f4f4;padding:8px;border-radius:4px;} h2{color:#333;border-bottom:1px solid #ccc;}</style>";

// Cek user FEIZHIFARA
echo "<h2>1. User FEIZHIFARA di tabel users</h2>";
$user = DB::table('users')
    ->where('name', 'LIKE', '%FEIZHIFARA%')
    ->orWhere('name', 'LIKE', '%QONITAH%')
    ->first();

if ($user) {
    echo "<pre>";
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Username (NIS): {$user->username}\n";
    echo "Role: {$user->role}\n";
    echo "Additional Roles: " . ($user->additional_roles ?? 'NULL') . "\n";
    echo "Walikelas Kelas: " . ($user->walikelas_kelas ?? 'NULL') . "\n";
    echo "</pre>";
} else {
    echo "<b style='color:red'>User tidak ditemukan!</b>";
}

// Cek siswa dengan NIS yang sama
echo "<h2>2. Data Siswa dengan NIS = username user tsb</h2>";
if ($user) {
    $siswa = DB::table('siswa')
        ->where('nis', $user->username)
        ->orWhere('nama', 'LIKE', '%FEIZHIFARA%')
        ->orWhere('nama', 'LIKE', '%QONITAH%')
        ->first();

    if ($siswa) {
        echo "<pre>";
        echo "ID: {$siswa->id}\n";
        echo "NIS: {$siswa->nis}\n";
        echo "Nama: {$siswa->nama}\n";
        echo "Kelas: {$siswa->kelas}\n";
        echo "</pre>";
    } else {
        echo "<b style='color:red'>Siswa tidak ditemukan di tabel siswa!</b>";
    }
}

// Cek apakah ada data ketua kelas yang tersimpan di tabel ketuakelas/kelas
echo "<h2>3. Cek tabel kelas — apakah ada kolom ketua?</h2>";
$kelasData = DB::table('kelas')->where('nama_kelas', '9B')->orWhere('kelas', '9B')->first();
if ($kelasData) {
    echo "<pre>" . json_encode((array)$kelasData, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<b>Tidak ada row 9B di tabel kelas atau nama kolomnya berbeda</b>";
    // tampilkan sample
    $sample = DB::table('kelas')->first();
    if ($sample) {
        echo "<pre>Contoh row kelas: " . json_encode((array)$sample, JSON_PRETTY_PRINT) . "</pre>";
    }
}

// Cek apakah ada data siswa kelas 9B
echo "<h2>4. Sample siswa kelas 9B (3 siswa)</h2>";
$siswa9B = DB::table('siswa')->where('kelas', '9B')->limit(3)->get();
if ($siswa9B->isEmpty()) {
    echo "<b style='color:red'>Tidak ada siswa kelas 9B!</b>";
} else {
    foreach ($siswa9B as $s) {
        echo "<pre>NIS: {$s->nis} | Nama: {$s->nama} | Kelas: {$s->kelas}</pre>";
    }
}
