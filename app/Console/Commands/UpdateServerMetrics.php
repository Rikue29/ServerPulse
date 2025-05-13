<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Server;
use App\Services\ServerMonitoringService;

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
                $server->update([
                    'cpu_usage' => $metrics['cpu_usage'],
                    'ram_usage' => $metrics['ram_usage'],
                    'disk_usage' => $metrics['disk_usage'],
                    'status' => $metrics['status'],
                    'last_checked_at' => now(),
                ]);

                $this->info("✓ Updated metrics for {$server->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to update metrics for {$server->name}: " . $e->getMessage());
            }
        }

        $this->info('Done updating server metrics.');
    }
} 