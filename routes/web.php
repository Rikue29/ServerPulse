<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\LogController;
use App\Http\Livewire\LogDetails;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Protected routes requiring authentication
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (Server List)
    Route::get('/dashboard', [ServerController::class, 'index'])->name('dashboard');
    
    // Server Management Routes
    Route::resource('servers', ServerController::class);
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Alert Route
    Route::post('/alerts/trigger', [AlertController::class, 'trigger']);

    // Logs Routes
    Route::get('/logs', \App\Livewire\LogsTable::class)->name('logs.index');
    Route::get('/logs/{log}', \App\Livewire\LogDetails::class)->name('logs.show');
});

require __DIR__.'/auth.php';