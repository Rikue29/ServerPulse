<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlertController;
use App\Livewire\AlertsTable;
use App\Http\Controllers\AnalyticsController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/alerts/trigger', [AlertController::class, 'trigger']);

//To resolve alert path
Route::patch('/alerts/{alert}/resolve', [AlertController::class, 'resolve']);

Route::get('/server-status', [AnalyticsController::class, 'getServerStatus']);

