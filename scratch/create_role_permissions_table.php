<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (!Schema::hasTable('role_permissions')) {
    echo "Creating table 'role_permissions'...\n";
    Schema::create('role_permissions', function ($table) {
        $table->increments('id');
        $table->string('role')->index();
        $table->string('permission');
        $table->timestamps();
    });
    echo "Table 'role_permissions' created successfully.\n";
} else {
    echo "Table 'role_permissions' already exists.\n";
}

echo "Database updates completed.\n";
