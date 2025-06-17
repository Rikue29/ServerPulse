<?php

namespace App\Livewire;

use App\Models\Server;
use Livewire\Component;

class ServerMonitoring extends Component
{
    public $servers;
    
    protected $listeners = ['echo:server-status,ServerStatusUpdated' => 'handleServerStatusUpdate'];

    public function mount()
    {
        $this->servers = Server::all();
    }

    public function handleServerStatusUpdate($event)
    {
        // Update the server metrics in real-time
        $serverId = $event['serverId'];
        $metrics = $event['metrics'];
        
        // Find and update the server in the collection
        $this->servers = $this->servers->map(function ($server) use ($serverId, $metrics) {
            if ($server->id === $serverId) {
                $server->cpu_usage = $metrics['cpu_usage'] ?? null;
                $server->memory_usage = $metrics['memory_usage'] ?? null;
                $server->disk_usage = $metrics['disk_usage'] ?? null;
                $server->status = 'online';
                $server->last_checked_at = now();
            }
            return $server;
        });
    }

    public function render()
    {
        return view('livewire.server-monitoring');
    }
}
