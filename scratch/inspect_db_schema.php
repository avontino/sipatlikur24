<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$gurus = DB::table('users')->where('role', 'guru')->limit(10)->get();
echo "Guru Users:\n";
foreach ($gurus as $g) {
    echo "ID: {$g->id} | Username: {$g->username} | Name: {$g->name} | Role: {$g->role}\n";
}
