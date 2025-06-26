<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\ServerStatusUpdated;
use App\Models\Server;
use App\Services\ServerMonitoringService;
use Carbon\Carbon;

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
            
            // 1. Get raw metrics from our stateless service with response time measurement
            $startTime = microtime(true);
            $metrics = $this->monitoringService->getMetrics($server);
            $responseTime = (microtime(true) - $startTime) * 1000; // Calculate response time in milliseconds
            
            // Ensure all metrics are proper integers
            // Force all network and IO metrics to be integers to avoid type issues
            $metrics['network_rx'] = isset($metrics['network_rx']) ? intval($metrics['network_rx']) : 0;
            $metrics['network_tx'] = isset($metrics['network_tx']) ? intval($metrics['network_tx']) : 0;
            $metrics['disk_io_read'] = isset($metrics['disk_io_read']) ? intval($metrics['disk_io_read']) : 0;
            $metrics['disk_io_write'] = isset($metrics['disk_io_write']) ? intval($metrics['disk_io_write']) : 0;
            
            // Log actual values for debugging
            $this->info("Server {$server->name}: network_rx = {$metrics['network_rx']} (".gettype($metrics['network_rx'])."), network_tx = {$metrics['network_tx']} (".gettype($metrics['network_tx']).")");
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

            // Create a performance log for every check
            \App\Models\PerformanceLog::create([
                'server_id' => $server->id,
                'cpu_usage' => $metrics['cpu_usage'] ?? 0,
                'ram_usage' => $metrics['ram_usage'] ?? 0,
                'disk_usage' => $metrics['disk_usage'] ?? 0,
                'network_rx' => $metrics['network_rx'] ?? 0,
                'network_tx' => $metrics['network_tx'] ?? 0,
                'disk_io_read' => $metrics['disk_io_read'] ?? 0,
                'disk_io_write' => $metrics['disk_io_write'] ?? 0,
                'response_time' => $responseTime, // Store the response time in the performance log
            ]);

            // 3. Check for threshold breaches
            $this->monitoringService->checkAndLogThresholds($server, $metrics);

            // 4. Broadcast the updated status
            // Prepare the broadcast payload with strict typecasting for network metrics
            $payload = [
                'server_id' => intval($server->id),
                'name' => $server->name,
                'ip_address' => $server->ip_address,
                'cpu_usage' => floatval($server->cpu_usage),
                'ram_usage' => floatval($server->ram_usage),
                'disk_usage' => floatval($server->disk_usage),
                'status' => $metrics['status'], // Use metrics status directly
                'system_uptime' => $server->system_uptime,
                'last_down_at' => $server->last_down_at?->toDateTimeString(),
                'current_uptime' => $server->running_since ? intval($server->running_since->diffInSeconds(now())) : null,
                'current_downtime' => $server->last_down_at ? intval($server->last_down_at->diffInSeconds(now())) : 0,
                // Force all network and IO values to be integers with strict typecasting
                'network_rx' => intval($server->network_rx ?? 0),
                'network_tx' => intval($server->network_tx ?? 0), 
                'disk_io_read' => intval($server->disk_io_read ?? 0),
                'disk_io_write' => intval($server->disk_io_write ?? 0),
                'response_time' => floatval($responseTime) // Ensure response time is a float
            ];
            // Enhanced debug logging for broadcast payload
            $this->info('Broadcast payload: ' . json_encode($payload));
            
            // Log detailed information about network data types and values
            $this->info("Broadcasting server status for {$server->name} (ID: {$server->id}):");
            $this->info("Status = {$payload['status']} (from metrics)");
            $this->info("network_rx = {$payload['network_rx']} (type: " . gettype($payload['network_rx']) . ")");
            $this->info("network_tx = {$payload['network_tx']} (type: " . gettype($payload['network_tx']) . ")");
            $this->info("disk_io_read = {$payload['disk_io_read']} (type: " . gettype($payload['disk_io_read']) . ")");
            $this->info("disk_io_write = {$payload['disk_io_write']} (type: " . gettype($payload['disk_io_write']) . ")");
            
            // Finally, broadcast the event
            broadcast(new ServerStatusUpdated($payload));
        }
    }
}
