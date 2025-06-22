<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\ServerStatusUpdated;
use App\Models\Server;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class UpdateOfflineServers extends Command
{
    protected $signature = 'monitor:offline-servers';
    protected $description = 'Update and broadcast downtime for offline servers every few seconds';

    public function handle()
    {
        $offlineServers = Server::where('status', 'offline')->get();
        
        $this->info("Found {$offlineServers->count()} offline servers");
        
        foreach ($offlineServers as $server) {
            if (!$server->last_down_at) {
                $this->warn("Server {$server->name} is marked offline but has no last_down_at timestamp - fixing");
                $server->last_down_at = now();
                $server->save();
            }

            $currentDowntime = $server->last_down_at->diffInSeconds(now());
            $formattedDowntime = CarbonInterval::seconds($currentDowntime)->cascade()->forHumans(['short' => true]);
            
            $this->info("Server {$server->name} is OFFLINE - Current downtime: {$formattedDowntime} ({$currentDowntime} seconds)");
            
            // Create a payload with fresh downtime and zeroed metrics
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
                'timestamp' => now()->timestamp
            ];
            
            // Broadcast the downtime update
            broadcast(new ServerStatusUpdated($payload));
            $this->info("Broadcasted updated downtime for {$server->name}");
        }
    }
}
