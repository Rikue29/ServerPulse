<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;
use App\Models\Server;
use App\Models\AlertThreshold;
use Carbon\Carbon;

$server = Server::first();
$threshold = AlertThreshold::first();

// Clear existing alerts to avoid confusion
Alert::where('status', 'triggered')->delete();

// Create test alerts with different metric values
// Some will be easily resolvable (low simulated current values)
// Others will fail resolution (high simulated current values)

echo "Creating test alerts for resolve functionality...\n";

// Alert 1: High CPU but likely to be resolvable
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'cpu',
    'alert_message' => 'CPU usage elevated - test alert',
    'metric_value' => 88.5,
    'alert_time' => Carbon::now(),
    'status' => 'triggered'
]);
echo "✓ Created CPU alert (88.5%) - should be resolvable\n";

// Alert 2: Critical CPU - less likely to be resolvable  
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'cpu',
    'alert_message' => 'Critical CPU usage detected',
    'metric_value' => 96.2,
    'alert_time' => Carbon::now()->subMinutes(5),
    'status' => 'triggered'
]);
echo "✓ Created Critical CPU alert (96.2%) - may fail resolution\n";

// Alert 3: Memory alert
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'mem',
    'alert_message' => 'Memory usage high',
    'metric_value' => 82.1,
    'alert_time' => Carbon::now()->subMinutes(10),
    'status' => 'triggered'
]);
echo "✓ Created Memory alert (82.1%)\n";

echo "\nTest alerts created! Visit the alerts page and test the resolve buttons.\n";
echo "The resolve functionality will:\n";
echo "- Gray out the alert row when resolved\n";
echo "- Show a success toast if resolution succeeds\n";
echo "- Show an error toast if current metric is still too high\n";
echo "- Use simulated current metric values for testing\n";
