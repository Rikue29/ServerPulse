<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\ServerStatusUpdated;
use App\Models\Server;
use App\Services\ServerMonitoringService;
use Carbon\Carbon;
use Carbon\CarbonInterval;

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

            // Debug: Log the metrics being returned
            $this->info("Server: {$server->name} - Status: {$metrics['status']} - Disk Usage: {$metrics['disk_usage']}");

            // 2. Update server state directly in this command
            $server->cpu_usage = $metrics['cpu_usage'] ?? 0;
            $server->ram_usage = $metrics['ram_usage'] ?? 0;
            $server->disk_usage = $metrics['disk_usage'] ?? 0;
            $server->system_uptime = $metrics['system_uptime'] ?? null;
            $server->response_time = $metrics['response_time'] ?? 0;
            $server->last_checked_at = now();
            $server->status = $metrics['status'] ?? 'offline';
            $server->network_rx = $metrics['network_rx'] ?? 0;
            $server->network_tx = $metrics['network_tx'] ?? 0;
            $server->network_speed = $metrics['network_speed'] ?? 0;
            $server->disk_io_read = $metrics['disk_io_read'] ?? 0;
            $server->disk_io_write = $metrics['disk_io_write'] ?? 0;

            // Always update running_since to match Docker uptime if online
            if ($server->status === 'online' && isset($metrics['system_uptime'])) {
                // Parse uptime string (e.g., '55h 40m 12s') to seconds
                $uptimeStr = $metrics['system_uptime'];
                $uptimeSeconds = 0;
                if (preg_match_all('/(\d+)d/', $uptimeStr, $d)) $uptimeSeconds += $d[1][0] * 86400;
                if (preg_match_all('/(\d+)h/', $uptimeStr, $h)) $uptimeSeconds += $h[1][0] * 3600;
                if (preg_match_all('/(\d+)m/', $uptimeStr, $m)) $uptimeSeconds += $m[1][0] * 60;
                if (preg_match_all('/(\d+)s/', $uptimeStr, $s)) $uptimeSeconds += $s[1][0];
                $server->running_since = now()->subSeconds($uptimeSeconds);
            }

            if ($isOnline && !$wasOnline) {
                // Server just came online
                $server->last_down_at = null;
            } elseif (!$isOnline && $wasOnline) {
                // Server just went offline
                $server->last_down_at = now();
            }
            
            $server->save();

            // Debug: Log what was saved
            $this->info("Saved disk_usage: {$server->disk_usage} for server {$server->name}");

            // Create a performance log for every check with correct timezone
            $currentTime = Carbon::now('Asia/Kuala_Lumpur')->utc();
            \App\Models\PerformanceLog::create([
                'server_id' => $server->id,
                'cpu_usage' => $metrics['cpu_usage'] ?? 0,
                'ram_usage' => $metrics['ram_usage'] ?? 0,
                'disk_usage' => $metrics['disk_usage'] ?? 0,
                'network_rx' => $metrics['network_rx'] ?? 0,
                'network_tx' => $metrics['network_tx'] ?? 0,
                'disk_io_read' => $metrics['disk_io_read'] ?? 0,
                'disk_io_write' => $metrics['disk_io_write'] ?? 0,
                'response_time' => $metrics['response_time'] ?? 0,
                'created_at' => $currentTime,
                'updated_at' => $currentTime,
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
                'system_uptime' => $server->status === 'online' && $server->running_since 
                    ? CarbonInterval::seconds(now()->diffInSeconds($server->running_since))->cascade()->forHumans(['short' => true])
                    : '0s',
                'response_time' => $server->response_time,
                'network_rx' => $server->network_rx,
                'network_tx' => $server->network_tx,
                'last_down_at' => $server->last_down_at?->toDateTimeString(),
                'current_uptime' => $server->running_since ? $server->running_since->diffInSeconds(now()) : null,
                'current_downtime' => $server->last_down_at ? $server->last_down_at->diffInSeconds(now()) : 0,
            ];
            
            broadcast(new ServerStatusUpdated($payload));
            $this->info("Broadcasted status for {$server->name} ({$server->ip_address})");
        }
    }
}
