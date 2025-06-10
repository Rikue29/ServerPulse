<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\ServerStatusUpdated;
use App\Models\Server;
use App\Services\ServerMonitoringService;

class MonitorServer extends Command
{
    private $monitoringService;

    public function __construct(ServerMonitoringService $monitoringService)
    {
        parent::__construct();
        $this->monitoringService = $monitoringService;
    }
    protected $signature = 'monitor:server';
    protected $description = 'Monitor remote server and broadcast status';

    public function handle()
    {
        $servers = Server::all();
        foreach ($servers as $server) {
            $metrics = $this->monitoringService->getMetrics($server);
            $this->monitoringService->checkAndLogThresholds($server, $metrics);
            $payload = [
                'server_id' => $server->id,
                'name' => $server->name,
                'ip_address' => $server->ip_address,
                'cpu_usage' => $metrics['cpu_usage'] ?? null,
                'ram_usage' => $metrics['ram_usage'] ?? null,
                'disk_usage' => $metrics['disk_usage'] ?? null,
                'status' => $metrics['status'] ?? 'offline',
                'last_checked_at' => now()->toDateTimeString(),
            ];
            broadcast(new ServerStatusUpdated($payload));
            $this->info("Broadcasted status for {$server->name} ({$server->ip_address})");
        }
    }
}
