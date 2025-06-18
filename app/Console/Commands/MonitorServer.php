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
            $wasOnline = $server->status === 'online';
            
            // 1. Get raw metrics from our stateless service
            $metrics = $this->monitoringService->getMetrics($server);
            $isOnline = ($metrics['status'] ?? 'offline') === 'online';

            // 2. Update server state directly in this command
            $server->cpu_usage = $metrics['cpu_usage'] ?? 0;
            $server->ram_usage = $metrics['ram_usage'] ?? 0;
            $server->disk_usage = $metrics['disk_usage'] ?? 0;
            $server->system_uptime = $metrics['system_uptime'] ?? null;
            $server->last_checked_at = now();
            $server->status = $metrics['status'] ?? 'offline';
            $server->network_rx = $metrics['network_rx'] ?? 0;
            $server->network_tx = $metrics['network_tx'] ?? 0;
            $server->network_speed = $metrics['network_speed'] ?? 0;
            $server->disk_io_read = $metrics['disk_io_read'] ?? 0;
            $server->disk_io_write = $metrics['disk_io_write'] ?? 0;

            if ($isOnline && !$wasOnline) {
                // Server just came online
                $server->last_down_at = null;
            } elseif (!$isOnline && $wasOnline) {
                // Server just went offline
                $server->last_down_at = now();
            }
            
            $server->save();

            // Create a performance log for every check
            \App\Models\Log::create([
                'server_id' => $server->id,
                'level' => 'info',
                'log_level' => 'INFO',
                'source' => 'performance_log',
                'message' => 'Server metrics collected.',
                'context' => [
                    'all_metrics' => $metrics
                ],
            ]);

            // 3. Check for threshold breaches
            $this->monitoringService->checkAndLogThresholds($server, $metrics);

            // 4. Broadcast the updated status
            $payload = [
                'server_id' => $server->id,
                'name' => $server->name,
                'ip_address' => $server->ip_address,
                'cpu_usage' => $server->cpu_usage,
                'ram_usage' => $server->ram_usage,
                'disk_usage' => $server->disk_usage,
                'status' => $server->status,
                'system_uptime' => $server->system_uptime,
                'last_down_at' => $server->last_down_at?->toDateTimeString(),
                'current_uptime' => $server->running_since ? $server->running_since->diffInSeconds(now()) : null,
                'current_downtime' => $server->last_down_at ? $server->last_down_at->diffInSeconds(now()) : 0,
            ];
            
            broadcast(new ServerStatusUpdated($payload));
            $this->info("Broadcasted status for {$server->name} ({$server->ip_address})");
        }
    }
}
