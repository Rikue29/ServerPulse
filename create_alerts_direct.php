<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;
use App\Models\Server;
use App\Models\AlertThreshold;
use Carbon\Carbon;

$server = Server::first();
$threshold = AlertThreshold::first();

if (!$server || !$threshold) {
    echo "Need server and threshold. Run create_test_server.php first.\n";
    exit;
}

// Clear existing
Alert::where('status', 'triggered')->delete();

// Use DB::insert to bypass Eloquent validations that might be causing issues
DB::table('alerts')->insert([
    [
        'server_id' => $server->id,
        'threshold_id' => $threshold->id,
        'alert_type' => 'performance', // Use the exact same value as existing alerts
        'alert_message' => 'CPU usage critical - 95%',
        'metric_value' => 95.5,
        'alert_time' => Carbon::now(),
        'status' => 'triggered',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ],
    [
        'server_id' => $server->id,
        'threshold_id' => $threshold->id,
        'alert_type' => 'memory',
        'alert_message' => 'Memory usage high - 85%',
        'metric_value' => 85.2,
        'alert_time' => Carbon::now()->subMinutes(10),
        'status' => 'triggered',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ],
    [
        'server_id' => $server->id,
        'threshold_id' => $threshold->id,
        'alert_type' => 'system',
        'alert_message' => 'Disk usage elevated - 78%',
        'metric_value' => 78.3,
        'alert_time' => Carbon::now()->subMinutes(20),
        'status' => 'triggered',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ],
    [
        'server_id' => $server->id,
        'threshold_id' => $threshold->id,
        'alert_type' => 'performance',
        'alert_message' => 'CPU resolved - was 88%',
        'metric_value' => 88.0,
        'alert_time' => Carbon::now()->subHour(),
        'status' => 'resolved',
        'resolved_at' => Carbon::now()->subMinutes(30),
        'resolved_by' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]
]);

echo "✅ Created 4 test alerts successfully!\n";
echo "Visit http://serverpulse.test/alerts to see them.\n";
echo "\nTest scenarios:\n";
echo "- Critical CPU (95.5%) - Should have red styling\n";
echo "- High Memory (85.2%) - Orange styling\n";
echo "- Medium Disk (78.3%) - Yellow styling\n"; 
echo "- Resolved CPU (88.0%) - Gray styling\n";
echo "\nTest the resolve buttons and watch for toast notifications!\n";
