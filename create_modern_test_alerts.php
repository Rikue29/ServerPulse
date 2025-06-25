<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;
use App\Models\Server;
use App\Models\AlertThreshold;
use Carbon\Carbon;

// Get the server
$server = Server::first();
if (!$server) {
    echo "No server found. Please run create_test_server.php first.\n";
    exit;
}

// Get or create threshold
$threshold = AlertThreshold::where('server_id', $server->id)->first();
if (!$threshold) {
    $threshold = AlertThreshold::create([
        'server_id' => $server->id,
        'metric_type' => 'cpu',
        'threshold_value' => 80.0,
        'comparison_operator' => '>',
        'alert_frequency' => 5
    ]);
}

// Clear existing active alerts
Alert::where('status', 'triggered')->delete();

echo "Creating test alerts with different severities...\n";

// Critical CPU alert (95% - above 90% threshold)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'performance',
    'alert_message' => 'Critical CPU usage detected - immediate attention required',
    'metric_value' => 95.5,
    'alert_time' => Carbon::now(),
    'status' => 'triggered'
]);
echo "✓ Created Critical CPU alert (95.5%)\n";

// High Memory alert (85% - above 80% threshold)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'memory',
    'alert_message' => 'High memory usage detected',
    'metric_value' => 85.2,
    'alert_time' => Carbon::now()->subMinutes(15),
    'status' => 'triggered'
]);
echo "✓ Created High Memory alert (85.2%)\n";

// Medium Disk alert (78% - above 75% threshold)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'system',
    'alert_message' => 'Disk usage approaching capacity',
    'metric_value' => 78.3,
    'alert_time' => Carbon::now()->subMinutes(30),
    'status' => 'triggered'
]);
echo "✓ Created Medium Disk alert (78.3%)\n";

// Test alert with excessive CPU (98%)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'performance',
    'alert_message' => 'Excessive CPU usage - system overload detected!',
    'metric_value' => 98.1,
    'alert_time' => Carbon::now()->subMinutes(5),
    'status' => 'triggered'
]);
echo "✓ Created Excessive CPU alert (98.1%) - should have prominent styling\n";

// Low-priority Network alert (65%)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'network',
    'alert_message' => 'Network latency above normal',
    'metric_value' => 65.0,
    'alert_time' => Carbon::now()->subHour(),
    'status' => 'triggered'
]);
echo "✓ Created Low Network alert (65.0%)\n";

// Already resolved alert for testing
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'performance',
    'alert_message' => 'CPU usage was elevated but has returned to normal',
    'metric_value' => 88.0,
    'alert_time' => Carbon::now()->subHours(2),
    'status' => 'resolved',
    'resolved_at' => Carbon::now()->subHour(),
    'resolved_by' => 1
]);
echo "✓ Created Resolved CPU alert (88.0%)\n";

echo "\nTest alerts created successfully!\n";
echo "Visit http://serverpulse.test/alerts to see the new modern table.\n";
echo "Try resolving alerts to test the threshold-based resolution logic.\n";
