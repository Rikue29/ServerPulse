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
Route::get('/install', function() {
    return view('install-agent');
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
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

    // Alert Routes
    Route::post('/alerts/trigger', [App\Http\Controllers\AlertController::class, 'trigger'])->name('alerts.trigger');
    Route::post('/alerts/{id}/resolve', [App\Http\Controllers\AlertController::class, 'resolve'])->name('alerts.resolve');
    Route::get('/alerts/recent', [App\Http\Controllers\AlertController::class, 'recent'])->name('alerts.recent');
    Route::get('/alerts', [App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index');

    // Test Routes
    Route::get('/test-alerts', function () {
        return view('test-alerts');
    });
    
    Route::get('/test-resolve-debug', function () {
        return view('test-resolve-debug');
    });
    
    Route::get('/simple-test', function () {
        return view('simple-test');
    });
    
    Route::get('/simple-alert-test', function () {
        return view('simple-alert-test-page');
    });
    
    Route::post('/test-alerts/simulate', function () {
        try {
            $alertService = new \App\Services\AlertMonitoringService();
            $servers = \App\Models\Server::with('alertThresholds')->get();
            
            if ($servers->isEmpty()) {
                return response()->json(['error' => 'No servers found'], 404);
            }

            $server = $servers->filter(function($server) {
                return $server->alertThresholds->where('is_active', true)->count() > 0;
            })->first();
            
            if (!$server) {
                return response()->json(['error' => 'No servers with active thresholds found'], 404);
            }
            
            $result = $alertService->simulateCriticalAlert($server);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Critical alert simulated successfully!',
                    'alert_id' => $result['alert']->id ?? null,
                    'server_name' => $server->name,
                    'alert_type' => $result['alert']->alert_type ?? null,
                    'alert_message' => $result['alert']->alert_message ?? null,
                    'metric_value' => $result['alert']->metric_value ?? null,
                    'email_sent' => true
                ]);
            } else {
                return response()->json(['error' => 'Failed to simulate alert'], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Alert simulation error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to simulate alert: ' . $e->getMessage()], 500);
        }
    });
    
    Route::post('/test-alerts/monitor', function () {
        $alertService = new \App\Services\AlertMonitoringService();
        $results = $alertService->checkAllThresholds();
        $summary = $alertService->getSystemHealthSummary();
        
        return response()->json([
            'success' => true,
            'message' => 'Monitoring check completed',
            'alerts_triggered' => count($results),
            'results' => $results,
            'summary' => $summary
        ]);
    });

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

    // Server selection routes
    Route::get('/toggle-server/{id}', [ServerController::class, 'toggleServerSelection'])->name('toggle.server');
    Route::get('/select-all-servers', [ServerController::class, 'selectAllServers'])->name('select.all.servers');
    Route::get('/clear-server-selection', [ServerController::class, 'clearServerSelection'])->name('clear.server.selection');

    // Chart data route
    Route::get('/chart-data', [ServerController::class, 'getChartData'])->name('chart.data');
    // Test route for alert resolution
    Route::get('/test-resolve', function () {
        return view('test-resolve');
    });
});

// Test routes without authentication for debugging
Route::get('/test-alerts-direct', function () {
    $alerts = App\Models\Alert::with(['server', 'threshold'])->paginate(10);
    return view('test-alerts-direct', compact('alerts'));
});

Route::post('/test-resolve/{id}', function ($id) {
    try {
        $alert = App\Models\Alert::findOrFail($id);
        
        if ($alert->status === 'resolved') {
            return response()->json([
                'success' => false,
                'message' => 'Alert is already resolved.'
            ]);
        }

        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => 1, // Default admin user
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alert resolved successfully.'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to resolve alert: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/quick-login', function () {
    return view('quick-login');
});

Route::post('/quick-login', function () {
    $user = App\Models\User::first();
    if ($user) {
        Auth::login($user);
        return redirect('/alerts');
    }
    return redirect('/login');
});
require __DIR__.'/auth.php';