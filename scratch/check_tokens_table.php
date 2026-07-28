<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tokens = DB::table('personal_access_tokens')->limit(10)->get();
if ($tokens->isEmpty()) {
    echo "No tokens found in personal_access_tokens table.\n";
} else {
    foreach ($tokens as $t) {
        echo "ID: {$t->id}, Tokenable ID: {$t->tokenable_id}, Token: {$t->token}\n";
    }
}
