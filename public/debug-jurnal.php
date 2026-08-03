<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Mapel;
use App\Models\Guru;
use App\Models\Kelas;

echo "<style>body{font-family:monospace;font-size:13px;} pre{background:#f4f4f4;padding:8px;border-radius:4px;} h2{color:#333;border-bottom:1px solid #ccc;}</style>";

echo "<h2>Simulasi Render View ijin.index</h2>";

try {
    $ma_pel = Mapel::all();
    $gu_ru  = Guru::all();
    $ke_las = Kelas::all();

    $user = auth()->user() ?? DB::table('users')->where('role', 'guru')->first();
    echo "Simulasi login user: " . ($user ? $user->name . " (Role: {$user->role})" : 'NULL') . "<br>";

    $userRole = strtolower($user->role ?? '');
    $isGuru = ($userRole == 'guru');

    $query = \App\Models\Ijin::query();

    if (\Illuminate\Support\Facades\Schema::hasColumn('ijin', 'tahun_ajaran')) {
        if ($rawTa = session('tahun_ajaran')) {
            $cleanTa = trim(preg_replace('/\s*\(.*\)/', '', $rawTa));
            $query->where(function($q) use ($rawTa, $cleanTa) {
                $q->where('tahun_ajaran', $rawTa)
                  ->orWhere('tahun_ajaran', 'LIKE', '%' . $cleanTa . '%')
                  ->orWhereNull('tahun_ajaran');
            });
        }
    }

    if ($isGuru) {
        $hasUserId = \Illuminate\Support\Facades\Schema::hasColumn('ijin', 'user_id');
        $query->where(function($q) use ($user, $hasUserId) {
            if ($hasUserId) {
                $q->where('user_id', $user->id);
            }
            $q->orWhere('guru', 'LIKE', '%' . $user->name . '%');
        });
    }

    $data_ijin = $query->orderBy('created_at', 'desc')->get();
    echo "Total data_ijin fetched: " . $data_ijin->count() . "<br>";

    echo "Attempting to render view('ijin.index')...<br>";
    $html = view('ijin.index', ['data_ijin' => $data_ijin], compact('ma_pel', 'gu_ru', 'ke_las'))->render();
    echo "<b style='color:green'>BERHASIL RENDER VIEW SANGAT LANCAR! (Length: " . strlen($html) . " bytes)</b>";

} catch (\Throwable $e) {
    echo "<b style='color:red'>EXCEPTIONS / ERROR TERDETEKSI:</b><br>";
    echo "<pre>";
    echo "Pesan Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Baris: " . $e->getLine() . ")\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
