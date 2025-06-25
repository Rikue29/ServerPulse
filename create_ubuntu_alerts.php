<?php

use App\Models\Alert;
use App\Models\Server;
use Illuminate\Support\Carbon;

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->boot();

echo "Clearing old test alerts...\n";
Alert::query()->where('message', 'like', 'Test Alert%')->delete();

$server = Server::where('name', 'UBUNTU')->first();

if (!$server) {
    echo "ERROR: Could not find the 'UBUNTU' server. Please ensure it exists.\n";
    exit(1);
}

echo "Found server: {$server->name} (ID: {$server->id})\n";

$alertsToCreate = [
    [
        'alert_type' => 'cpu',
        'metric_value' => 95.5,
        'threshold' => 90,
        'message' => 'Test Alert: CPU usage is critical',
    ],
    [
        'alert_type' => 'memory',
        'metric_value' => 85.2,
        'threshold' => 80,
        'message' => 'Test Alert: Memory usage is high',
    ],
    [
        'alert_type' => 'disk',
        'metric_value' => 78.0,
        'threshold' => 75,
        'message' => 'Test Alert: Disk space is medium',
    ],
];

echo "Creating new test alerts for server ID: {$server->id}...\n";

foreach ($alertsToCreate as $alertData) {
    Alert::create([
        'server_id' => $server->id,
        'alert_type' => $alertData['alert_type'],
        'metric_value' => $alertData['metric_value'],
        'threshold_id' => 1, // Default threshold ID
        'message' => $alertData['message'],
        'status' => 'unresolved',
        'alert_time' => Carbon::now(),
    ]);
}

echo "Successfully created " . count($alertsToCreate) . " test alerts.\n";
