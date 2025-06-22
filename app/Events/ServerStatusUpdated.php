<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ServerStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $status;

    public function __construct($status)
    {
        $this->status = $status;
        
        // Ensure offline servers have zero metrics with extra validation
        if (isset($this->status['status']) && $this->status['status'] === 'offline') {
            // Always force these numeric metrics to exactly zero
            $zeroMetrics = [
                'cpu_usage', 'ram_usage', 'disk_usage', 'response_time',
                'network_rx', 'network_tx', 'network_activity', 'network_speed',
                'disk_io_read', 'disk_io_write', 'load_average'
            ];
            
            foreach ($zeroMetrics as $metric) {
                $this->status[$metric] = 0;
            }
            
            // Always force uptime to zero string
            $this->status['system_uptime'] = '0s';
            
            // Double check downtime calculation is present and valid
            if (!isset($this->status['current_downtime']) || $this->status['current_downtime'] === null) {
                if (isset($this->status['last_down_at']) && $this->status['last_down_at']) {
                    // Calculate current downtime in seconds from last_down_at
                    $lastDownAt = new \DateTime($this->status['last_down_at']);
                    $now = new \DateTime();
                    $this->status['current_downtime'] = $now->getTimestamp() - $lastDownAt->getTimestamp();
                    
                    // Format downtime for human display
                    $interval = $lastDownAt->diff($now);
                    $parts = [];
                    if ($interval->d > 0) $parts[] = $interval->d . 'd';
                    if ($interval->h > 0) $parts[] = $interval->h . 'h';
                    if ($interval->i > 0) $parts[] = $interval->i . 'm';
                    if ($interval->s > 0 || count($parts) === 0) $parts[] = $interval->s . 's';
                    
                    $this->status['formatted_downtime'] = implode(' ', $parts);
                } else {
                    // No last_down_at, set sensible defaults
                    $this->status['current_downtime'] = 0;
                    $this->status['formatted_downtime'] = '0s';
                }
            }
        }
    }

    public function broadcastOn()
    {
        return new Channel('server-status');
    }

    /**
     * Get the data to broadcast.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'server.status.updated';
    }
}
