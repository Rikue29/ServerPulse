<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlertController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/alerts/trigger', [AlertController::class, 'trigger']);

//To resolve alert path
Route::patch('/alerts/{alert}/resolve', [AlertController::class, 'resolve']);

