<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// 1. Add column if it doesn't exist
if (!Schema::hasColumn('users', 'walikelas_kelas')) {
    echo "Adding column 'walikelas_kelas' to 'users' table...\n";
    DB::statement('ALTER TABLE users ADD COLUMN walikelas_kelas VARCHAR(50) NULL DEFAULT NULL');
} else {
    echo "Column 'walikelas_kelas' already exists.\n";
}

// 2. Set NANING WAHYUNI (ID 1137) as Wali Kelas of 'X 1' for testing
echo "Setting NANING WAHYUNI (ID 1137) as Wali Kelas of 'X 1'...\n";
DB::table('users')->where('id', 1137)->update([
    'walikelas_kelas' => 'X 1'
]);

echo "Database successfully updated.\n";
