<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use DB;
use Schema;

echo "=== IJINSISWA TABLE COLUMNS ===\n";
$columns = DB::select("DESCRIBE ijinsiswa");
foreach ($columns as $col) {
    echo "{$col->Field} | {$col->Type} | Null:{$col->Null} | Default:{$col->Default}\n";
}
