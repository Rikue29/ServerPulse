<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Server;
use App\Services\ServerMonitoringService;

class TestLogDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:downtime-tracking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test downtime tracking functionality';

    private $monitoringService;

    public function __construct(ServerMonitoringService $monitoringService)
    {
        parent::__construct();
        $this->monitoringService = $monitoringService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $servers = Server::all();
        
        if ($servers->isEmpty()) {
            $this->error('No servers found. Please add a server first.');
            return;
        }

        $this->info('Testing downtime tracking for ' . $servers->count() . ' servers...');
        
        foreach ($servers as $server) {
            $this->info("\n=== Server: {$server->name} ({$server->ip_address}) ===");
            $this->info("Current Status: " . ($server->status ?? 'unknown'));
            $this->info("Running Since: " . ($server->running_since ? $server->running_since->format('Y-m-d H:i:s') : 'null'));
            $this->info("Last Down At: " . ($server->last_down_at ? $server->last_down_at->format('Y-m-d H:i:s') : 'null'));
            $this->info("Total Uptime: " . $this->formatSeconds($server->total_uptime_seconds));
            $this->info("Total Downtime: " . $this->formatSeconds($server->total_downtime_seconds));
            
            // Get current metrics
            $metrics = $this->monitoringService->getMetrics($server);
            $this->info("Current Metrics Status: " . ($metrics['status'] ?? 'unknown'));
            $this->info("Current Uptime: " . (isset($metrics['current_uptime']) ? $this->formatSeconds($metrics['current_uptime']) : 'null'));
            $this->info("Current Downtime: " . (isset($metrics['current_downtime']) ? $this->formatSeconds($metrics['current_downtime']) : 'null'));
            
            // Refresh server data from database
            $server->refresh();
            $this->info("After Update - Status: " . ($server->status ?? 'unknown'));
            $this->info("After Update - Running Since: " . ($server->running_since ? $server->running_since->format('Y-m-d H:i:s') : 'null'));
            $this->info("After Update - Last Down At: " . ($server->last_down_at ? $server->last_down_at->format('Y-m-d H:i:s') : 'null'));
        }
        
        $this->info("\nDowntime tracking test completed!");
    }
    
    private function formatSeconds($seconds)
    {
        if (!$seconds) return '0s';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        $parts = [];
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'm';
        if ($secs > 0 || empty($parts)) $parts[] = $secs . 's';
        
        return implode(' ', $parts);
    }
}
