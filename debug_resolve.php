<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;
use App\Livewire\AlertsTable;
use Livewire\Livewire;

try {
    // Get a triggered alert
    $alert = Alert::where('status', 'triggered')->first();
    
    if (!$alert) {
        echo "No triggered alerts found\n";
        exit;
    }
    
    echo "Found alert ID: " . $alert->id . "\n";
    echo "Alert message: " . $alert->alert_message . "\n";
    echo "Current status: " . $alert->status . "\n";
    
    // Try to resolve directly via model
    $alert->update([
        'status' => 'resolved',
        'resolved_at' => now(),
        'resolved_by' => 1, // Assuming user ID 1 exists
    ]);
    
    echo "Alert resolved successfully via model\n";
    
    // Reset for Livewire test
    $alert->update([
        'status' => 'triggered',
        'resolved_at' => null,
        'resolved_by' => null,
    ]);
    
    echo "Alert reset to triggered status\n";
    
    // Now try via Livewire component
    Auth::loginUsingId(1); // Login as user 1
    
    $component = Livewire::test(AlertsTable::class);
    $response = $component->call('resolveAlert', $alert->id);
    
    echo "Livewire component response:\n";
    var_dump($response);
    
    // Check final status
    $alert->refresh();
    echo "Final alert status: " . $alert->status . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
