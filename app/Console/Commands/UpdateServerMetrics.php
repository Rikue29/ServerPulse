<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Server;
use App\Models\PerformanceLog;
use App\Services\ServerMonitoringService;
use Carbon\Carbon;

class UpdateServerMetrics extends Command
{
    protected $signature = 'servers:update-metrics';
    protected $description = 'Update metrics for all servers';

    private $monitoringService;

    public function __construct(ServerMonitoringService $monitoringService)
    {
        parent::__construct();
        $this->monitoringService = $monitoringService;
    }

    public function handle()
    {
        $servers = Server::all();
        $this->info('Updating metrics for ' . $servers->count() . ' servers...');

        foreach ($servers as $server) {
            $this->info("Checking server: {$server->name} ({$server->ip_address})");
            
            try {
                $metrics = $this->monitoringService->getMetrics($server);
                
                // Update basic metrics
                $updateData = [
                    'cpu_usage' => $metrics['cpu_usage'],
                    'ram_usage' => $metrics['ram_usage'],
                    'disk_usage' => $metrics['disk_usage'],
                    'status' => $metrics['status'],
                    'network_rx' => $metrics['network_rx'] ?? 0,
                    'network_tx' => $metrics['network_tx'] ?? 0,
                    'network_speed' => $metrics['network_speed'] ?? 0,
                    'disk_io_read' => $metrics['disk_io_read'] ?? 0,
                    'disk_io_write' => $metrics['disk_io_write'] ?? 0,
                ];
                
                // Preserve downtime tracking fields that were set by the monitoring service
                if (isset($metrics['running_since'])) {
                    $updateData['running_since'] = $metrics['running_since'];
                }
                if (isset($metrics['last_down_at'])) {
                    $updateData['last_down_at'] = $metrics['last_down_at'];
                }
                if (isset($metrics['total_uptime_seconds'])) {
                    $updateData['total_uptime_seconds'] = $metrics['total_uptime_seconds'];
                }
                if (isset($metrics['total_downtime_seconds'])) {
                    $updateData['total_downtime_seconds'] = $metrics['total_downtime_seconds'];
                }
                
                $server->update($updateData);

                // Log performance data for analytics with explicit timezone
                $this->info("Creating performance log for server {$server->id}...");
                $currentTime = Carbon::now('Asia/Kuala_Lumpur')->utc();
                $performanceLog = PerformanceLog::create([
                    'server_id' => $server->id,
                    'cpu_usage' => $metrics['cpu_usage'],
                    'ram_usage' => $metrics['ram_usage'],
                    'disk_usage' => $metrics['disk_usage'],
                    'network_rx' => $metrics['network_rx'] ?? 0,
                    'network_tx' => $metrics['network_tx'] ?? 0,
                    'disk_io_read' => $metrics['disk_io_read'] ?? 0,
                    'disk_io_write' => $metrics['disk_io_write'] ?? 0,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,
                ]);
                
                $this->info("Performance log created with timestamp: " . $performanceLog->created_at->format('Y-m-d H:i:s'));

                $this->info("✓ Updated metrics and logged performance data for {$server->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to update metrics for {$server->name}: " . $e->getMessage());
            }
        }

        $this->info('Done updating server metrics.');
    }
} 