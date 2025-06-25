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

// Create a few more varied alerts
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'mem', // Shortened to avoid column length issue
    'alert_message' => 'High memory usage - 85%',
    'metric_value' => 85.2,
    'alert_time' => Carbon::now(),
    'status' => 'triggered'
]);

Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'disk', // Shortened
    'alert_message' => 'Disk usage 78%',
    'metric_value' => 78.3,
    'alert_time' => Carbon::now(),
    'status' => 'triggered'
]);

Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $threshold->id,
    'alert_type' => 'net', // Shortened
    'alert_message' => 'Network latency high',
    'metric_value' => 65.0,
    'alert_time' => Carbon::now(),
    'status' => 'triggered'
]);

echo "Additional alerts created!\n";
