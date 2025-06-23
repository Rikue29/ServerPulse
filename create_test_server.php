<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Server;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Get the first user or create one
    $user = User::first();
    if (!$user) {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        echo "Created test user: {$user->email}\n";
    } else {
        echo "Using existing user: {$user->email}\n";
    }

    // Check if test server already exists
    $existingServer = Server::where('ip_address', '192.168.1.100')->first();
    if ($existingServer) {
        echo "Test server already exists with ID: {$existingServer->id}\n";
        echo "Server IP: {$existingServer->ip_address}\n";
    } else {
        // Create a test server
        $server = Server::create([
            'name' => 'Test Linux Server',
            'ip_address' => '192.168.1.100',
            'environment' => 'dev',
            'location' => 'Test Lab',
            'created_by' => $user->id,
            'status' => 'offline',
            'monitoring_type' => 'online',
            'cpu_usage' => 0,
            'ram_usage' => 0,
            'disk_usage' => 0
        ]);

        echo "Test server created with ID: {$server->id}\n";
        echo "Server IP: {$server->ip_address}\n";
    }

    echo "Test server setup complete!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
