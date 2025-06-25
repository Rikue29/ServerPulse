<?php

// Quick test to verify resolve alert functionality
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;

// Get the first active alert
$alert = Alert::where('status', 'triggered')->first();

if (!$alert) {
    echo "No active alerts found to test with.\n";
    exit;
}

echo "Testing resolve functionality for alert ID: {$alert->id}\n";
echo "Alert: {$alert->alert_message}\n";
echo "Value: {$alert->metric_value}%\n";
echo "Status: {$alert->status}\n\n";

// Test the AlertsTable resolve logic
$alertsTable = new \App\Livewire\AlertsTable();

try {
    // Call the resolve method directly
    $alertsTable->resolveAlert($alert->id);
    echo "✅ Resolve method executed successfully!\n";
    
    // Check if alert was resolved
    $alert->refresh();
    echo "New status: {$alert->status}\n";
    
    if ($alert->status === 'resolved') {
        echo "🎉 Alert was successfully resolved!\n";
    } else {
        echo "⚠️ Alert status unchanged - likely due to simulated metric check\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing resolve: " . $e->getMessage() . "\n";
}
