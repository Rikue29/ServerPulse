<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;

// Check what alerts we have
$alerts = Alert::with(['server', 'threshold'])->where('status', 'triggered')->get();

echo "Active Alerts:\n";
echo "==============\n";

foreach ($alerts as $alert) {
    echo "ID: {$alert->id}\n";
    echo "Type: {$alert->alert_type}\n";
    echo "Message: {$alert->alert_message}\n";
    echo "Value: {$alert->metric_value}%\n";
    echo "Severity: {$alert->severity}\n";
    echo "Color: {$alert->severity_color}\n";
    echo "Row Style: {$alert->row_style}\n";
    echo "Status: {$alert->status}\n";
    echo "---\n";
}

echo "\nTotal active alerts: " . $alerts->count() . "\n";
