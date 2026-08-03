<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<style>body{font-family:monospace;font-size:13px;} pre{background:#f4f4f4;padding:8px;border-radius:4px;} h2{color:#333;border-bottom:1px solid #ccc;}</style>";

echo "<h2>1. Struktur Tabel 'ijin' (Izin Guru)</h2>";
try {
    $cols = DB::select("SHOW COLUMNS FROM ijin");
    echo "<table border=1 cellpadding=4><tr><th>Field</th><th>Type</th><th>Null</th></tr>";
    foreach ($cols as $c) {
        echo "<tr><td>{$c->Field}</td><td>{$c->Type}</td><td>{$c->Null}</td></tr>";
    }
    echo "</table>";
} catch (\Throwable $e) {
    echo "<b style='color:red'>Error SHOW COLUMNS FROM ijin: " . $e->getMessage() . "</b>";
}

echo "<h2>2. Tes Query IjinController::index()</h2>";
try {
    $user = DB::table('users')->where('role', 'guru')->first();
    echo "Testing as user: " . ($user ? $user->name : 'No guru user') . "<br>";
    
    $query = DB::table('ijin');
    
    // Cek apakah ada kolom user_id
    $hasUserId = \Illuminate\Support\Facades\Schema::hasColumn('ijin', 'user_id');
    echo "Has user_id column: " . ($hasUserId ? 'YES' : 'NO') . "<br>";

    $hasTahunAjaran = \Illuminate\Support\Facades\Schema::hasColumn('ijin', 'tahun_ajaran');
    echo "Has tahun_ajaran column: " . ($hasTahunAjaran ? 'YES' : 'NO') . "<br>";

    $data = $query->orderBy('created_at', 'desc')->limit(5)->get();
    echo "Total Rows Fetched: " . $data->count() . "<br>";
    foreach ($data as $r) {
        echo "<pre>" . json_encode((array)$r, JSON_PRETTY_PRINT) . "</pre>";
    }
} catch (\Throwable $e) {
    echo "<b style='color:red'>Error in Query: " . $e->getMessage() . "</b>";
}
