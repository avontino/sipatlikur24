<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$roles = DB::table('users')->distinct()->pluck('role');
echo "Unique roles in the 'users' table:\n";
foreach ($roles as $role) {
    echo "- " . ($role ?: '[empty]') . "\n";
}
