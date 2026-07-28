<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PresensiApiController;
use App\Http\Controllers\Api\JurnalApiController;
use App\Http\Controllers\Api\JadwalApiController;
use App\Http\Controllers\Api\IjinSiswaApiController;
use App\Http\Controllers\Api\KasusApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Endpoint
Route::post('/login', [AuthApiController::class, 'login']);
Route::get('/semesters', [AuthApiController::class, 'getSemesters']);

// Protected API Endpoints (Wajib membawa Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Device Tokens
    Route::get('/user-profile', [AuthApiController::class, 'profile']);
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/save-fcm-token', [AuthApiController::class, 'saveFcmToken']);
    Route::post('/change-password', [AuthApiController::class, 'changePassword']);

    // Garjas (Kesamaptaan Jasmani)
    Route::get('/garjas', [PresensiApiController::class, 'getGarjas']);
    Route::post('/garjas/submit', [PresensiApiController::class, 'submitGarjas']);

    // Presensi & Permohonan Izin Guru
    Route::get('/presensi/today', [PresensiApiController::class, 'todayStatus']);
    Route::post('/presensi/submit', [PresensiApiController::class, 'storePresensi']);
    Route::post('/izin/submit', [PresensiApiController::class, 'storeIzin']);
    Route::get('/izin/list', [PresensiApiController::class, 'getIzinGuruList']);

    // Permohonan Izin Siswa
    Route::post('/izin-siswa/submit', [PresensiApiController::class, 'storeIzinSiswa']);
    Route::get('/izin-siswa/list', [PresensiApiController::class, 'getIzinSiswaList']);

    // Jurnal Harian Kelas
    Route::get('/jurnal/schedules', [JurnalApiController::class, 'getJadwalToday']);
    Route::get('/jurnal/warnings', [JurnalApiController::class, 'getJournalWarnings']);
    Route::post('/jurnal/submit', [JurnalApiController::class, 'storeJurnal']);

    // === NATIVE SCREEN ENDPOINTS ===

    // Jadwal Pelajaran
    Route::get('/jadwal', [JadwalApiController::class, 'index']);

    // Riwayat Jurnal & Rekap Jurnal
    Route::get('/jurnal/riwayat', [JurnalApiController::class, 'getRiwayatJurnal']);
    Route::get('/jurnal/rekap', [JurnalApiController::class, 'getRekapJurnal']);
    Route::post('/jurnal/update/{id}', [JurnalApiController::class, 'updateJurnal']);


    // Ijin Siswa (Daftar & Approval)
    Route::get('/ijin-siswa/daftar', [IjinSiswaApiController::class, 'index']);
    Route::post('/ijin-siswa/verifikasi/{id}', [IjinSiswaApiController::class, 'verifikasi']);
    Route::post('/ijin-siswa/tolak/{id}', [IjinSiswaApiController::class, 'tolak']);

    // Poin & SP Siswa
    Route::get('/poin-siswa', [KasusApiController::class, 'poinSiswa']);
});
