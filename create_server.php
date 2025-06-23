<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Server;

// Create server record for Linux VM
$server = Server::create([
    'name' => 'Linux VM Server',
    'ip_address' => '192.168.159.128',
    'server_type' => 'linux',
    'port' => 22,
    'status' => 'online',
    'description' => 'Ubuntu VM for ServerPulse Agent Testing'
]);

echo "✅ Server created successfully!\n";
echo "Server ID: " . $server->id . "\n";
echo "Server Name: " . $server->name . "\n";
echo "IP Address: " . $server->ip_address . "\n";
