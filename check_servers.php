<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->boot();

use App\Models\Server;

echo "=== SERVERS IN DATABASE ===\n";

$servers = Server::all(['id', 'name', 'ip_address', 'status']);

if ($servers->count() === 0) {
    echo "No servers found in database.\n";
} else {
    foreach ($servers as $server) {
        echo "ID: {$server->id}, Name: {$server->name}, IP: {$server->ip_address}, Status: {$server->status}\n";
    }
}

echo "\n=== TOTAL SERVERS: {$servers->count()} ===\n";
