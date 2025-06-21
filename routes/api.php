<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AgentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Agent API routes
Route::prefix('v1/agents')->group(function () {
    // Agent registration (no auth required for initial registration)
    Route::post('/register', [AgentController::class, 'register'])->name('api.agents.register');
    
    // Authenticated agent routes
    Route::middleware('api')->group(function () {
        Route::post('/{agentId}/metrics', [AgentController::class, 'metrics']);
        Route::post('/{agentId}/heartbeat', [AgentController::class, 'heartbeat']);
        Route::post('/{agentId}/alerts', [AgentController::class, 'alerts']);
        Route::get('/{agentId}/commands', [AgentController::class, 'commands']);
    });
});
