<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;
use App\Models\Server;
use App\Models\AlertThreshold;
use Carbon\Carbon;

echo "Creating test alerts using Eloquent...\n";

$server = Server::first();
$threshold = AlertThreshold::first();

if (!$server || !$threshold) {
    echo "No server or threshold found.\n";
    exit;
}

// Clear existing
Alert::where('status', 'triggered')->delete();

echo "Creating alerts one by one...\n";

try {
    // Alert 1
    $alert1 = new Alert();
    $alert1->server_id = $server->id;
    $alert1->threshold_id = $threshold->id;
    $alert1->alert_type = 'performance';
    $alert1->alert_message = 'Critical CPU usage detected';
    $alert1->metric_value = 95.5;
    $alert1->alert_time = Carbon::now();
    $alert1->status = 'triggered';
    $alert1->save();
    echo "✅ Critical CPU alert created\n";

    // Alert 2
    $alert2 = new Alert();
    $alert2->server_id = $server->id;
    $alert2->threshold_id = $threshold->id;
    $alert2->alert_type = 'memory';
    $alert2->alert_message = 'High memory usage';
    $alert2->metric_value = 85.2;
    $alert2->alert_time = Carbon::now()->subMinutes(10);
    $alert2->status = 'triggered';
    $alert2->save();
    echo "✅ High memory alert created\n";

    // Alert 3
    $alert3 = new Alert();
    $alert3->server_id = $server->id;
    $alert3->threshold_id = $threshold->id;
    $alert3->alert_type = 'system';
    $alert3->alert_message = 'Disk usage elevated';
    $alert3->metric_value = 78.3;
    $alert3->alert_time = Carbon::now()->subMinutes(20);
    $alert3->status = 'triggered';
    $alert3->save();
    echo "✅ Medium disk alert created\n";

    // Alert 4 - Resolved
    $alert4 = new Alert();
    $alert4->server_id = $server->id;
    $alert4->threshold_id = $threshold->id;
    $alert4->alert_type = 'performance';
    $alert4->alert_message = 'CPU was elevated but resolved';
    $alert4->metric_value = 88.0;
    $alert4->alert_time = Carbon::now()->subHour();
    $alert4->status = 'resolved';
    $alert4->resolved_at = Carbon::now()->subMinutes(30);
    $alert4->resolved_by = 1;
    $alert4->save();
    echo "✅ Resolved CPU alert created\n";

    echo "\n🎉 Successfully created 4 test alerts!\n";
    echo "Visit: http://serverpulse.test/alerts\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
