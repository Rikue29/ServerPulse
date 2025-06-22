<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Server;
use App\Events\ServerStatusUpdated;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class UpdateOfflineServerDowntime extends Command
{
    protected $signature = 'server:update-offline-downtime';
    protected $description = 'Update downtime for offline servers and broadcast real-time updates';

    public function handle()
    {
        $this->info('Starting offline server downtime update...');
        
        $offlineServers = Server::where('status', 'offline')->get();
        
        if ($offlineServers->isEmpty()) {
            $this->info('No offline servers found.');
            return;
        }
        
        $this->info('Found ' . $offlineServers->count() . ' offline servers. Updating downtime...');
        
        foreach ($offlineServers as $server) {
            if (!$server->last_down_at) {
                $this->warn("Server {$server->name} is offline but doesn't have last_down_at timestamp. Setting it now.");
                $server->last_down_at = now();
                $server->save();
            }
            
            // Calculate current downtime in seconds
            $downtimeSeconds = $server->last_down_at->diffInSeconds(now());
            $formattedDowntime = CarbonInterval::seconds($downtimeSeconds)->cascade()->forHumans(['short' => true]);
            
            $this->info("Server: {$server->name} - Current downtime: {$formattedDowntime}");
            
            // Prepare payload with all metrics set to zero for offline server
            $payload = [
                'server_id' => $server->id,
                'name' => $server->name,
                'ip_address' => $server->ip_address,
                'cpu_usage' => 0,
                'ram_usage' => 0,
                'disk_usage' => 0, 
                'status' => 'offline',
                'system_uptime' => '0s',
                'response_time' => 0,
                'network_rx' => 0,
                'network_tx' => 0,
                'last_down_at' => $server->last_down_at->toDateTimeString(),
                'current_downtime' => $downtimeSeconds,
                'formatted_downtime' => $formattedDowntime,
                'disk_io_read' => 0,
                'disk_io_write' => 0,
            ];
            
            // Broadcast the updated downtime
            broadcast(new ServerStatusUpdated($payload));
            $this->info("Broadcasted updated downtime for server {$server->name}: {$formattedDowntime}");
        }
        
        $this->info('Completed offline server downtime update.');
    }
}
