<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (!Schema::hasColumn('users', 'additional_roles')) {
    echo "Adding column 'additional_roles' to 'users' table...\n";
    DB::statement('ALTER TABLE users ADD COLUMN additional_roles VARCHAR(255) NULL DEFAULT NULL');
} else {
    echo "Column 'additional_roles' already exists.\n";
}

echo "Database update completed.\n";
