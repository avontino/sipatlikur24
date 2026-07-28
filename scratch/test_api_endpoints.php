<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

echo "===========================================\n";
echo "SINALA MOBILE API TESTING RUNNER\n";
echo "===========================================\n\n";

// 1. Dapatkan satu user dari database untuk uji coba login
$user = DB::table('users')->orderBy('id', 'asc')->first();

if (!$user) {
    echo "Gagal: Tidak ada user di database untuk pengujian.\n";
    exit(1);
}

$username = $user->username;
$password = '123456'; // Default password di SINALA

echo "Mencoba login dengan:\n";
echo "- Username: {$username}\n";
echo "- Password: {$password}\n\n";

$apiUrl = 'http://127.0.0.1:8000/api';

echo "1. Mengirim POST request ke {$apiUrl}/login...\n";

try {
    $loginResponse = Http::post("{$apiUrl}/login", [
        'username' => $username,
        'password' => $password
    ]);

    if (!$loginResponse->successful()) {
        echo "Gagal: Login API mengembalikan status: " . $loginResponse->status() . "\n";
        echo "Response: " . $loginResponse->body() . "\n";
        echo "Tip: Pastikan server lokal Anda berjalan di http://localhost:8000\n";
        exit(1);
    }

    $loginData = $loginResponse->json();
    $token = $loginData['token'];

    echo "Sukses! Token Sanctum didapatkan:\n";
    echo "Token: {$token}\n\n";

    echo "-------------------------------------------\n";
    echo "2. Menguji endpoint terproteksi: GET {$apiUrl}/user-profile...\n";

    $profileResponse = Http::withToken($token)->get("{$apiUrl}/user-profile");

    if ($profileResponse->successful()) {
        echo "Sukses! Profil berhasil diambil dengan Token:\n";
        echo json_encode($profileResponse->json(), JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "Gagal mengambil profil. Status: " . $profileResponse->status() . "\n";
        echo "Response: " . $profileResponse->body() . "\n";
    }

    echo "-------------------------------------------\n";
    echo "3. Menguji endpoint jadwal: GET {$apiUrl}/jurnal/schedules...\n";

    $schedulesResponse = Http::withToken($token)->get("{$apiUrl}/jurnal/schedules");

    if ($schedulesResponse->successful()) {
        echo "Sukses! Jadwal hari ini berhasil diambil:\n";
        echo json_encode($schedulesResponse->json(), JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "Gagal mengambil jadwal. Status: " . $schedulesResponse->status() . "\n";
        echo "Response: " . $schedulesResponse->body() . "\n";
    }

} catch (\Exception $e) {
    echo "Terjadi Error saat request: " . $e->getMessage() . "\n";
    echo "Tip: Pastikan local server SINALA sedang berjalan (e.g. php artisan serve di port 8000)\n";
}

echo "===========================================\n";
echo "PENGUJIAN SELESAI\n";
echo "===========================================\n";
