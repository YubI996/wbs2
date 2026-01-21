<?php

use App\Livewire\Auth\Login;
use App\Livewire\BuatLaporanWizard;
use App\Livewire\CekStatusLaporan;
use App\Livewire\LandingPage;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', LandingPage::class)->name('home');

// Report Submission Wizard
Route::get('/buat-laporan', BuatLaporanWizard::class)->name('buat-laporan');

// Check Report Status
Route::get('/cek-status', CekStatusLaporan::class)->name('cek-status');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Unified Login
Route::get('/login', Login::class)->name('login')->middleware('guest');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    
    return redirect()->route('home');
})->name('logout')->middleware('auth');

// Redirect to appropriate panel after login (for direct panel access)
Route::get('/dashboard', function () {
    $user = Auth::user();
    
    if (!$user) {
        return redirect()->route('login');
    }
    
    return match($user->role_id) {
        Role::INSPEKTUR => redirect('/inspektur'),
        Role::VERIFIKATOR => redirect('/verifikator'),
        Role::ADMIN => redirect('/admin'),
        default => redirect()->route('home'),
    };
})->name('dashboard')->middleware('auth');
