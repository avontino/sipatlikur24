<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Jurnal;

echo "<style>body{font-family:monospace;font-size:13px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ccc;padding:6px;text-align:left;} th{background:#eee;} h2{color:#333;border-bottom:1px solid #ccc;}</style>";

$user = User::where('name', 'LIKE', '%Maria Ignatia%')->first();

if (!$user) {
    die("User Maria Ignatia tidak ditemukan!");
}

auth()->login($user);

echo "<h2>1. Data User & Managed Class</h2>";
echo "User ID: {$user->id}<br>";
echo "User Name: {$user->name}<br>";
echo "Role: {$user->role}<br>";
echo "Additional Roles: {$user->additional_roles}<br>";
echo "walikelas_kelas: " . ($user->walikelas_kelas ?? 'NULL') . "<br>";
echo "<b style='color:blue'>getManagedClass(): " . var_export($user->getManagedClass(), true) . "</b><br>";

echo "<h2>2. Hasil Query Jurnal (view=walikelas) di JurnalController</h2>";

$req = \Illuminate\Http\Request::create('/jurnal?view=walikelas', 'GET');
$controller = new \App\Http\Controllers\JurnalController();

// Panggil index via Reflection atau simulasi query
$managedClass = $user->getManagedClass();
echo "Kelas yang digunakan untuk filter: <b>" . var_export($managedClass, true) . "</b><br><br>";

$query = Jurnal::query();

if ($managedClass) {
    $query->where('kelas', $managedClass);
}

$results = $query->orderBy('created_at', 'desc')->take(10)->get();

echo "Total records ditemukan: " . $results->count() . "<br><br>";
echo "<table>";
echo "<tr><th>ID</th><th>Kelas</th><th>Mata Pelajaran</th><th>Guru</th><th>Materi</th><th>Tahun Ajaran</th><th>Semester</th><th>Tanggal</th></tr>";
foreach ($results as $row) {
    echo "<tr>";
    echo "<td>{$row->id}</td>";
    echo "<td>{$row->kelas}</td>";
    echo "<td>{$row->mapel}</td>";
    echo "<td>{$row->guru}</td>";
    echo "<td>{$row->materi}</td>";
    echo "<td>{$row->tahun_ajaran}</td>";
    echo "<td>{$row->semester}</td>";
    echo "<td>{$row->created_at}</td>";
    echo "</tr>";
}
echo "</table>";
