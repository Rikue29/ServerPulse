<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;
use App\Models\Server;
use App\Models\AlertThreshold;
use Carbon\Carbon;

echo "Creating test alerts...\n\n";

// Get server
$server = Server::first();
if (!$server) {
    echo "No server found. Please create a server first.\n";
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
        'alert_frequency' => 5,
        'notification_channel' => 'email',
        'created_by' => 1,
        'is_active' => true,
    ]);
}

// Clear existing alerts
Alert::where('server_id', $server->id)->where('status', 'triggered')->delete();
echo "Cleared existing alerts\n";

$alerts = [
    [
        'alert_type' => 'cpu',
        'alert_message' => 'CRITICAL: CPU usage at dangerous levels - immediate attention required',
        'metric_value' => 97.8,
        'time_offset' => 0
    ],
    [
        'alert_type' => 'cpu', 
        'alert_message' => 'High CPU usage detected - system performance impacted',
        'metric_value' => 92.4,
        'time_offset' => 8
    ],
    [
        'alert_type' => 'mem',
        'alert_message' => 'Memory usage elevated - investigate potential leaks',
        'metric_value' => 87.2,
        'time_offset' => 15
    ],
    [
        'alert_type' => 'disk',
        'alert_message' => 'Disk usage approaching capacity - cleanup needed',
        'metric_value' => 78.9,
        'time_offset' => 25
    ],
    [
        'alert_type' => 'cpu',
        'alert_message' => 'CPU usage slightly elevated during peak hours',
        'metric_value' => 82.1,
        'time_offset' => 35
    ],
    [
        'alert_type' => 'net',
        'alert_message' => 'Network latency increased - monitoring connectivity',
        'metric_value' => 65.7,
        'time_offset' => 45
    ]
];

$created = 0;
foreach ($alerts as $alert) {
    try {
        Alert::create([
            'server_id' => $server->id,
            'threshold_id' => $threshold->id,
            'alert_type' => $alert['alert_type'],
            'alert_message' => $alert['alert_message'],
            'metric_value' => $alert['metric_value'],
            'alert_time' => Carbon::now()->subMinutes($alert['time_offset']),
            'status' => 'triggered'
        ]);
        $created++;
        echo "Created {$alert['alert_type']} alert ({$alert['metric_value']}%)\n";
    } catch (Exception $e) {
        echo "Error creating alert: " . $e->getMessage() . "\n";
    }
}

// Create one resolved alert
try {
    Alert::create([
        'server_id' => $server->id,
        'threshold_id' => $threshold->id,
        'alert_type' => 'cpu',
        'alert_message' => 'CPU spike detected and resolved - temporary load increase',
        'metric_value' => 89.3,
        'alert_time' => Carbon::now()->subHours(2),
        'status' => 'resolved',
        'resolved_at' => Carbon::now()->subHour(),
        'resolved_by' => 1
    ]);
    $created++;
    echo "Created resolved CPU alert (89.3%)\n";
} catch (Exception $e) {
    echo "Error creating resolved alert: " . $e->getMessage() . "\n";
}

echo "\nSUCCESS: Created {$created} test alerts!\n";
echo "\nAlert Types Created:\n";
echo "- Critical CPU (97.8%) - Should have prominent red styling\n";
echo "- High CPU (92.4%) - Critical severity\n";
echo "- High Memory (87.2%) - Above 80% threshold\n";
echo "- Medium Disk (78.9%) - Above 75% threshold\n";
echo "- Medium CPU (82.1%) - Just above threshold\n";
echo "- Low Network (65.7%) - Informational\n";
echo "- Resolved CPU (89.3%) - Shows gray-out effect\n";

echo "\nVisit http://serverpulse.test/alerts to view and test!\n";
echo "\nYou can now:\n";
echo "- See different severity colors and styling\n";
echo "- Test the resolve button functionality\n";
echo "- Watch toast notifications\n";
echo "- See alerts gray out when resolved\n";
