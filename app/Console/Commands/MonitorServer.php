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
        
        // First, separately update any offline servers with fresh downtime info
        $offlineServers = $servers->filter(function ($server) {
            return $server->status === 'offline';
        });
        
        if ($offlineServers->count() > 0) {
            $this->info("Found {$offlineServers->count()} offline servers - updating downtime");
            
            foreach ($offlineServers as $offlineServer) {
                $this->updateOfflineServerDowntime($offlineServer);
            }
        }

        foreach ($servers as $server) {
            $wasOnline = $server->status === 'online';
            $this->info("Monitoring server: {$server->name} ({$server->ip_address}) - Current status: {$server->status}");
            
            // Check for agent-enabled servers that haven't sent a heartbeat in over a minute
            if ($server->agent_enabled && $server->agent_last_heartbeat) {
                $lastHeartbeat = $server->agent_last_heartbeat;
                $heartbeatTimeout = now()->subMinute();
                
                if ($lastHeartbeat < $heartbeatTimeout && $server->status === 'online') {
                    $this->info("Agent last heartbeat was over a minute ago for server {$server->name} - marking as offline");
                    $server->status = 'offline';
                    $server->last_down_at = now();
                    $server->running_since = null;
                    $server->save();
                    
                    // We need to collect metrics but we already know it's offline due to agent not reporting
                    $wasOnline = false; // Update this for below code to avoid toggling status back
                }
            }
            
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
                $this->info("Server {$server->name} has come back ONLINE");
                $server->last_down_at = null;
                $server->running_since = now();
            } elseif (!$isOnline && $wasOnline) {
                // Server just went offline
                $this->error("Server {$server->name} has gone OFFLINE");
                $server->last_down_at = now();
                $server->running_since = null;
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
            // Enhanced payload with better downtime/uptime handling
            $payload = [
                'server_id' => $server->id,
                'name' => $server->name,
                'ip_address' => $server->ip_address,
                'cpu_usage' => $server->status === 'online' ? $server->cpu_usage : 0,
                'ram_usage' => $server->status === 'online' ? $server->ram_usage : 0,
                'disk_usage' => $server->status === 'online' ? $server->disk_usage : 0,
                'status' => $server->status,
                'system_uptime' => $server->status === 'online' && $server->running_since 
                    ? CarbonInterval::seconds(now()->diffInSeconds($server->running_since))->cascade()->forHumans(['short' => true])
                    : '0s',
                'response_time' => $server->status === 'online' ? $server->response_time : 0,
                'network_rx' => $server->status === 'online' ? $server->network_rx : 0,
                'network_tx' => $server->status === 'online' ? $server->network_tx : 0,
                'last_down_at' => $server->last_down_at?->toDateTimeString(),
                'current_uptime' => $server->status === 'online' && $server->running_since ? $server->running_since->diffInSeconds(now()) : null,
                'current_downtime' => $server->status === 'offline' && $server->last_down_at ? $server->last_down_at->diffInSeconds(now()) : 0,
                // Add formatted downtime for offline servers
                'formatted_downtime' => $server->status === 'offline' && $server->last_down_at ? 
                    CarbonInterval::seconds($server->last_down_at->diffInSeconds(now()))->cascade()->forHumans(['short' => true]) : null
            ];
            
            broadcast(new ServerStatusUpdated($payload));
            
            if ($server->status === 'online') {
                $this->info("Broadcasted ONLINE status for {$server->name} ({$server->ip_address})");
            } else {
                $this->warn("Broadcasted OFFLINE status for {$server->name} ({$server->ip_address})");
                if ($server->last_down_at) {
                    $downtime = $server->last_down_at->diffInSeconds(now());
                    $this->warn("Current downtime: {$payload['formatted_downtime']} ({$downtime} seconds)");
                }
            }
        }
    }
    
    /**
     * Update and broadcast the downtime for an offline server
     */
    protected function updateOfflineServerDowntime($server)
    {
        if ($server->status !== 'offline' || !$server->last_down_at) {
            return; // Only process servers that are offline and have a last_down_at timestamp
        }
        
        // Calculate current downtime
        $currentDowntime = $server->last_down_at->diffInSeconds(now());
        $formattedDowntime = CarbonInterval::seconds($currentDowntime)->cascade()->forHumans(['short' => true]);
        
        $this->info("Server {$server->name} is OFFLINE - Current downtime: {$formattedDowntime} ({$currentDowntime} seconds)");
        
        // Create a payload focused on accurate downtime
        $payload = [
            'server_id' => $server->id,
            'name' => $server->name,
            'ip_address' => $server->ip_address,
            'status' => 'offline',
            'cpu_usage' => 0,
            'ram_usage' => 0,
            'disk_usage' => 0,
            'response_time' => 0,
            'network_rx' => 0,
            'network_tx' => 0,
            'system_uptime' => '0s',
            'last_down_at' => $server->last_down_at->toDateTimeString(),
            'current_downtime' => $currentDowntime,
            'formatted_downtime' => $formattedDowntime,
            'last_checked_at' => now()->toDateTimeString(),
        ];
        
        // Broadcast the downtime update
        broadcast(new ServerStatusUpdated($payload));
        $this->info("Broadcasted updated downtime for {$server->name}");
    }
}
