<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Livewire\LogDetails;
use App\Livewire\AlertsTable;
use Illuminate\Support\Facades\Route;

// Public agent installation routes (no auth required)
// Commented out due to missing controller
// Route::get('/agent/install.sh', [AutoRegisterController::class, 'installScript']);
// Route::get('/agent/download', [AutoRegisterController::class, 'downloadAgent']);
Route::get('/install', function() {
    return view('install-agent');
});

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Protected routes requiring authentication
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (Livewire Component)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Server Management Routes
    Route::resource('servers', ServerController::class);
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    //Alert Route
    // Commented out due to missing controller
    // Route::post('/alerts/trigger', [AlertController::class, 'trigger']);

    //Test Alert Route
    Route::get('/test-alerts', function () {
        return view('test-alerts');
    });


    //Route::get('/alerts', \App\Livewire\AlertsTable::class)->name('alerts.index');

    Route::get('/alerts', function () {
        return view('alerts');
    })->name('alerts.index');

    // Logs Routes
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [LogController::class, 'show'])->name('logs.show');
    Route::get('/logs/{log}/report', [LogController::class, 'generateReport'])->name('logs.report');
    Route::get('/logs/{log}/download', [LogController::class, 'downloadReport'])->name('logs.download');
    Route::get('/logs/export/csv', [LogController::class, 'exportCsv'])->name('logs.export');

    // Analytics Routes
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');


    // Settings Route
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

    // User Route
    Route::get('/user', [UserController::class, 'index'])->name('user');
});

require __DIR__.'/auth.php';