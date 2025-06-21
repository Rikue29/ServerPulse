<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Server;
use App\Models\PerformanceLog;
use App\Models\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$server = Server::find(12);

if ($server) {
    echo "=== Server Status After Agent Test ===\n";
    echo "Server Name: {$server->name}\n";
    echo "Server IP: {$server->ip_address}\n";
    echo "Server Status: {$server->status}\n";
    echo "Agent Enabled: " . ($server->agent_enabled ? 'Yes' : 'No') . "\n";
    echo "Agent Status: {$server->agent_status}\n";
    echo "Agent Version: {$server->agent_version}\n";
    echo "CPU Usage: {$server->cpu_usage}%\n";
    echo "RAM Usage: {$server->ram_usage}%\n";
    echo "Disk Usage: {$server->disk_usage}%\n";
    echo "System Uptime: {$server->system_uptime} seconds\n";
    echo "Last Checked: {$server->last_checked_at}\n";
    echo "Agent Last Heartbeat: {$server->agent_last_heartbeat}\n";
    
    if ($server->last_metrics) {
        echo "\n=== Last Metrics from Agent ===\n";
        $metrics = $server->last_metrics;
        echo "Timestamp: {$metrics['timestamp']}\n";
        echo "Received at: {$metrics['received_at']}\n";
        
        if (isset($metrics['services'])) {
            echo "\nServices:\n";
            foreach ($metrics['services'] as $service) {
                echo "  - {$service['name']}: {$service['status']}\n";            }
        }
    }
    
    // Check performance logs
    $perfLog = PerformanceLog::where('server_id', $server->id)->latest()->first();
    if ($perfLog) {
        echo "\n=== Performance Log Entry ===\n";
        echo "CPU: {$perfLog->cpu_usage}%\n";
        echo "RAM: {$perfLog->ram_usage}%\n";
        echo "Disk: {$perfLog->disk_usage}%\n";
        echo "Created: {$perfLog->created_at}\n";
    }
    
    // Check recent logs
    $recentLogs = Log::where('server_id', $server->id)->where('source', 'agent')->latest()->take(3)->get();
    if ($recentLogs->count() > 0) {
        echo "\n=== Recent Agent Logs ===\n";
        foreach ($recentLogs as $log) {
            echo "[{$log->level}] {$log->message} ({$log->created_at})\n";
        }
    }
} else {
    echo "Server not found!\n";
}
