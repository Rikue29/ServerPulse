<?php

namespace App\Console\Commands;

use App\Models\Log;
use App\Models\Server;
use Illuminate\Console\Command;

class GenerateThresholdLogs extends Command
{
    protected $signature = 'logs:generate-threshold-violations';
    protected $description = 'Generate sample logs with threshold violations for testing';

    public function handle()
    {
        $servers = Server::all();
        
        if ($servers->isEmpty()) {
            $this->error('No servers found. Please create servers first.');
            return 1;
        }

        $this->info('Generating threshold violation logs...');

        // Critical CPU Spike
        $this->createLog($servers->first(), 'error', 'Critical CPU usage detected - immediate action required', [
            'cpu_usage' => 92,
            'memory_usage' => 78,
            'disk_usage' => 45,
            'load_average' => 5.2,
            'network_io' => 85,
            'process_count' => 245,
            'alert_type' => 'cpu_spike',
            'duration' => '15 minutes',
            'trigger_threshold' => 85
        ]);

        // Memory Warning
        $this->createLog($servers->first(), 'warning', 'Memory usage approaching critical threshold', [
            'cpu_usage' => 45,
            'memory_usage' => 88,
            'disk_usage' => 67,
            'load_average' => 2.1,
            'network_io' => 23,
            'swap_usage' => 45,
            'alert_type' => 'memory_warning',
            'duration' => '8 minutes',
            'trigger_threshold' => 75
        ]);

        // Disk Space Critical
        $this->createLog($servers->first(), 'error', 'Disk space critically low - service degradation imminent', [
            'cpu_usage' => 23,
            'memory_usage' => 56,
            'disk_usage' => 97,
            'load_average' => 1.2,
            'network_io' => 12,
            'available_space' => '1.2GB',
            'alert_type' => 'disk_critical',
            'duration' => '30 minutes',
            'trigger_threshold' => 95
        ]);

        // Load Average Spike
        $this->createLog($servers->first(), 'warning', 'High system load detected - performance impact expected', [
            'cpu_usage' => 78,
            'memory_usage' => 82,
            'disk_usage' => 34,
            'load_average' => 4.8,
            'network_io' => 67,
            'active_connections' => 1250,
            'alert_type' => 'load_spike',
            'duration' => '12 minutes',
            'trigger_threshold' => 4.0
        ]);

        // Network Congestion
        $this->createLog($servers->first(), 'warning', 'Network I/O congestion affecting application response times', [
            'cpu_usage' => 34,
            'memory_usage' => 67,
            'disk_usage' => 23,
            'load_average' => 1.8,
            'network_io' => 94,
            'bandwidth_usage' => '850 Mbps',
            'alert_type' => 'network_congestion',
            'duration' => '5 minutes',
            'trigger_threshold' => 80
        ]);

        // Multiple Threshold Violations (Critical)
        $this->createLog($servers->first(), 'error', 'Multiple system resources critically overloaded - emergency intervention required', [
            'cpu_usage' => 96,
            'memory_usage' => 94,
            'disk_usage' => 89,
            'load_average' => 8.2,
            'network_io' => 88,
            'swap_usage' => 78,
            'alert_type' => 'system_overload',
            'duration' => '25 minutes',
            'violated_thresholds' => ['cpu', 'memory', 'load']
        ]);

        $this->info('✅ Generated 6 threshold violation logs successfully!');
        $this->info('You can now test the log details page with enhanced infrastructure analysis.');
        
        return 0;
    }

    private function createLog($server, $level, $message, $context)
    {
        Log::create([
            'server_id' => $server->id,
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
            'source' => 'ServerPulse Monitor',
            'created_at' => now()->subMinutes(rand(1, 120)),
        ]);
    }
}
