<?php

use App\Http\Controllers\Api\AduanController;
use App\Http\Controllers\Api\StatistikController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| WBS API untuk integrasi dengan SuperApps dan aplikasi eksternal.
| Semua route di sini menggunakan middleware 'api.key' untuk autentikasi.
|
*/

// Public endpoints (tanpa API key)
Route::get('/jenis-aduans', [AduanController::class, 'jenisAduans'])
    ->name('api.jenis-aduans');

Route::get('/statistik', [StatistikController::class, 'index'])
    ->name('api.statistik');

// Protected endpoints (dengan API key)
Route::middleware('api.key')->group(function () {
    
    // Create new aduan from SuperApps
    Route::post('/aduans', [AduanController::class, 'store'])
        ->name('api.aduans.store');
    
    // Check aduan status
    Route::post('/aduans/status', [AduanController::class, 'status'])
        ->name('api.aduans.status');
});
