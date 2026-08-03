<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<style>body{font-family:monospace;font-size:13px;} pre{background:#f4f4f4;padding:8px;border-radius:4px;} h2{color:#333;border-bottom:1px solid #ccc;}</style>";

$user = DB::table('users')->where('name', 'LIKE', '%Maria Ignatia%')->first();

echo "<h2>1. User Record Maria Ignatia</h2>";
if ($user) {
    echo "<pre>";
    echo "ID: {$user->id}\nName: {$user->name}\nUsername: {$user->username}\nRole: {$user->role}\nWalikelas Kelas: " . ($user->walikelas_kelas ?? 'NULL') . "\nAdditional Roles: " . ($user->additional_roles ?? 'NULL');
    echo "</pre>";
    
    $uObj = \App\Models\User::find($user->id);
    echo "getManagedClass(): '{$uObj->getManagedClass()}'<br>";
} else {
    echo "<b style='color:red'>User Maria Ignatia tidak ditemukan!</b>";
}

echo "<h2>2. Tes Query Jurnal untuk Maria (view=walikelas)</h2>";
$ta = session('tahun_ajaran') ?? '2026/2027';
echo "TA: '{$ta}'<br>";

$query = DB::table('jurnal')
    ->where('kelas', $uObj ? $uObj->getManagedClass() : '9A');

if ($ta) {
    $cleanTa = trim(preg_replace('/\s*\(.*\)/', '', $ta));
    $query->where(function($q) use ($ta, $cleanTa) {
        $q->where('tahun_ajaran', $ta)
          ->orWhere('tahun_ajaran', 'LIKE', '%' . $cleanTa . '%')
          ->orWhereNull('tahun_ajaran');
    });
}

$rows = $query->orderBy('created_at', 'desc')->get();
echo "Total Rows untuk Kelas 9A: " . $rows->count() . "<br>";
foreach ($rows as $r) {
    echo "<pre>ID: {$r->id} | Kelas: {$r->kelas} | Mapel: {$r->mapel} | Guru: {$r->guru} | TA: {$r->tahun_ajaran} | Tgl: {$r->created_at}</pre>";
}
