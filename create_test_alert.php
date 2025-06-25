<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Server;
use App\Models\AlertThreshold;
use App\Models\Alert;

try {
    $server = Server::first();
    $threshold = AlertThreshold::first();
    
    if (!$server || !$threshold) {
        echo "No server or threshold found\n";
        exit;
    }
    
    $alert = Alert::create([
        'server_id' => $server->id,
        'threshold_id' => $threshold->id,
        'alert_type' => 'performance',
        'alert_message' => 'Test alert for resolve button - High CPU usage detected',
        'metric_value' => 95.0,
        'threshold_value' => 80.0,
        'alert_time' => now(),
        'status' => 'triggered'
    ]);
    
    echo "Test alert created with ID: " . $alert->id . "\n";
    echo "Server: " . $server->name . "\n";
    echo "Alert message: " . $alert->alert_message . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
