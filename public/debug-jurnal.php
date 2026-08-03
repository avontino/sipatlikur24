<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<style>body{font-family:monospace;font-size:12px;} pre{background:#1e1e1e;color:#fff;padding:12px;border-radius:4px;overflow-x:auto;} h2{color:#333;border-bottom:1px solid #ccc;}</style>";

echo "<h2>1. Cek isi storage/logs/laravel.log Terbaru</h2>";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -60);
    echo "<pre>" . htmlspecialchars(implode("", $lastLines)) . "</pre>";
} else {
    echo "<b style='color:red'>File storage/logs/laravel.log tidak ditemukan!</b>";
}

echo "<h2>2. Cek Exception saat Panggil Route /ijin</h2>";
try {
    $request = \Illuminate\Http\Request::create('/ijin', 'GET');
    $response = $app->handle($request);
    echo "HTTP Status Code: " . $response->getStatusCode() . "<br>";
    if ($response->getStatusCode() === 500) {
        echo "<b style='color:red'>HTTP 500 Detected!</b><br>";
        if (isset($response->exception)) {
            echo "<pre>Exception: " . $response->exception->getMessage() . "\nFile: " . $response->exception->getFile() . ":" . $response->exception->getLine() . "\n\nTrace:\n" . $response->exception->getTraceAsString() . "</pre>";
        }
    } else {
        echo "<b style='color:green'>HTTP Status " . $response->getStatusCode() . " OK!</b>";
    }
} catch (\Throwable $e) {
    echo "<b style='color:red'>Caught Throwable during handle:</b><br>";
    echo "<pre>" . $e->getMessage() . "\nFile: " . $e->getFile() . ":" . $e->getLine() . "\n\n" . $e->getTraceAsString() . "</pre>";
}
