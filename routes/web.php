<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\BukuTamuController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SKDController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\RatingSyncController;
use Illuminate\Support\Facades\Route;

// Public routes - tampilkan petugas bertugas
Route::get('/', [PublicController::class, 'index'])->name('public');

// Public rating routes (no auth needed)
Route::get('/rating', [RatingController::class, 'publicForm'])->name('rating.public');
Route::post('/rating/verify-phone', [RatingController::class, 'verifyPhone'])->name('rating.verify');
Route::post('/rating/submit-public', [RatingController::class, 'submitPublic'])->name('rating.submit-public');
Route::get('/rating/{token}', [RatingController::class, 'show'])->name('rating.show');
Route::post('/rating/{token}', [RatingController::class, 'store'])->name('rating.store');

// Dashboard redirect berdasarkan role
Route::get('/dashboard', function () {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('petugas.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/jadwal', [AdminController::class, 'jadwal'])->name('jadwal');
        Route::get('/jadwal/template', [AdminController::class, 'downloadJadwalTemplate'])->name('jadwal.template');
        Route::post('/jadwal', [AdminController::class, 'storeJadwal'])->name('jadwal.store');
        Route::put('/jadwal/{jadwal}', [AdminController::class, 'updateJadwal'])->name('jadwal.update');
        Route::post('/jadwal/import', [AdminController::class, 'importJadwal'])->name('jadwal.import');
        Route::delete('/jadwal/{jadwal}', [AdminController::class, 'destroyJadwal'])->name('jadwal.destroy');
        Route::get('/penilaian', [AdminController::class, 'penilaian'])->name('penilaian');
        Route::get('/rekap', [AdminController::class, 'rekapLayanan'])->name('rekap');
        Route::get('/pegawai', [AdminController::class, 'pegawai'])->name('pegawai');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/settings/update', [AdminController::class, 'updateSetting'])->name('settings.update');
        Route::get('/logs', [AdminController::class, 'activityLogs'])->name('logs');
        Route::get('/backup', [BackupController::class, 'download'])->name('backup');
        Route::get('/ratings/sync', [RatingSyncController::class, 'sync'])->name('ratings.sync');
    });
    
    // Petugas routes
    Route::prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/dashboard', [PetugasController::class, 'dashboard'])->name('dashboard');
    });
    
    // Shared routes (both admin and petugas)
    Route::post('/buku-tamu', [BukuTamuController::class, 'store'])->name('buku-tamu.store');
    
    // API routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/my-services', [ServiceController::class, 'myServices'])->name('my-services');
        Route::get('/services', [ServiceController::class, 'allServices'])->name('services');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::put('/services/{service}/visitor', [ServiceController::class, 'updateVisitor'])->name('services.visitor.update');
        Route::get('/stats/petugas', [StatsController::class, 'petugas'])->name('stats.petugas');
        Route::get('/stats/admin', [StatsController::class, 'admin'])->name('stats.admin');
        Route::post('/skd/mark-as-filled', [SKDController::class, 'markAsFilled'])->name('skd.mark-as-filled');
        Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/reset-photo', [ProfileController::class, 'resetToDefault'])->name('profile.reset-photo');
        Route::get('/pengunjung/search', [BukuTamuController::class, 'searchPengunjung'])->name('pengunjung.search');
    });
});

require __DIR__.'/auth.php';
