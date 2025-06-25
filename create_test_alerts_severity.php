<?php

require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\Alert;
use App\Models\Server;
use App\Models\Threshold;

// Create test alerts with different severities
$server = Server::first();
if (!$server) {
    $server = Server::create([
        'name' => 'Test Server',
        'ip_address' => '192.168.1.100',
        'status' => 'online'
    ]);
}

$threshold = Threshold::first();
if (!$threshold) {
    $threshold = Threshold::create([
        'server_id' => $server->id,
        'metric_type' => 'cpu',
        'threshold_value' => 80.0,
        'comparison_operator' => '>',
        'alert_frequency' => 5
    ]);
}

// Critical CPU alert (95%)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'performance',
    'alert_message' => 'Critical CPU usage detected - immediate attention required',
    'metric_value' => 95.5,
    'alert_time' => now(),
    'status' => 'unresolved'
]);

// High Memory alert (85%)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'memory',
    'alert_message' => 'High memory usage detected',
    'metric_value' => 85.2,
    'alert_time' => now()->subMinutes(15),
    'status' => 'unresolved'
]);

// Medium Disk alert (78%)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'system',
    'alert_message' => 'Disk usage approaching capacity',
    'metric_value' => 78.3,
    'alert_time' => now()->subMinutes(30),
    'status' => 'unresolved'
]);

// Low Network alert (60%)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'network',
    'alert_message' => 'Network latency above normal',
    'metric_value' => 60.1,
    'alert_time' => now()->subHour(),
    'status' => 'unresolved'
]);

echo "Test alerts created successfully with different severity levels!\n";
