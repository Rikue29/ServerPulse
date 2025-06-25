<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;
use App\Models\Server;

// Get the first server
$server = Server::first();
if (!$server) {
    echo "No server found. Please create a server first.\n";
    exit(1);
}

// Clear existing alerts
Alert::query()->delete();

// Create realistic test alerts with different severity levels
$alerts = [
    // Critical alerts (should be red)
    [
        'metric_value' => 98.5,
        'alert_type' => 'cpu',
        'alert_message' => 'CPU usage critically high: 98.5% (threshold: 95.0%)',
        'expected_severity' => 'critical'
    ],
    [
        'metric_value' => 95.0,
        'alert_type' => 'disk',
        'alert_message' => 'Disk usage critically high: 95.0% (threshold: 90.0%)',
        'expected_severity' => 'critical'
    ],
    [
        'metric_value' => 92.0,
        'alert_type' => 'memory',
        'alert_message' => 'Memory usage critically high: 92.0% (threshold: 90.0%)',
        'expected_severity' => 'critical'
    ],
    
    // High alerts (should be orange)
    [
        'metric_value' => 88.2,
        'alert_type' => 'cpu',
        'alert_message' => 'CPU usage high: 88.2% (threshold: 85.0%)',
        'expected_severity' => 'high'
    ],
    [
        'metric_value' => 87.0,
        'alert_type' => 'disk',
        'alert_message' => 'Disk usage high: 87.0% (threshold: 85.0%)',
        'expected_severity' => 'high'
    ],
    [
        'metric_value' => 82.5,
        'alert_type' => 'memory',
        'alert_message' => 'Memory usage high: 82.5% (threshold: 80.0%)',
        'expected_severity' => 'high'
    ],
    
    // Medium alerts (should be yellow)
    [
        'metric_value' => 75.0,
        'alert_type' => 'cpu',
        'alert_message' => 'CPU usage moderate: 75.0% (threshold: 70.0%)',
        'expected_severity' => 'medium'
    ],
    [
        'metric_value' => 72.0,
        'alert_type' => 'disk',
        'alert_message' => 'Disk usage moderate: 72.0% (threshold: 70.0%)',
        'expected_severity' => 'medium'
    ],
    
    // Low alerts (should be blue)
    [
        'metric_value' => 65.0,
        'alert_type' => 'memory',
        'alert_message' => 'Memory usage elevated: 65.0% (threshold: 60.0%)',
        'expected_severity' => 'medium'
    ]
];

echo "Creating test alerts...\n";

foreach ($alerts as $index => $alertData) {
    $alert = Alert::create([
        'server_id' => $server->id,
        'metric_value' => $alertData['metric_value'],
        'status' => 'triggered',
        'alert_type' => $alertData['alert_type'],
        'alert_message' => $alertData['alert_message'],
        'alert_time' => now()->subMinutes($index * 5),
    ]);
    
    $actualSeverity = $alert->severity;
    $expected = $alertData['expected_severity'];
    $match = $actualSeverity === $expected ? '✓' : '✗';
    
    echo sprintf(
        "%s Alert ID %d: %s %.1f%% -> Expected: %s, Actual: %s %s\n",
        $match,
        $alert->id,
        ucfirst($alertData['alert_type']),
        $alertData['metric_value'],
        $expected,
        $actualSeverity,
        $match === '✗' ? '(MISMATCH!)' : ''
    );
}

echo "\nTest alerts created successfully!\n";
echo "Visit http://serverpulse.test/alerts to see the results.\n";
