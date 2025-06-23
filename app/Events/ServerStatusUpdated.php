<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $status;

    public function __construct($status)
    {
        $this->status = $status;
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

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Ensure network_rx and network_tx are always present in the payload as integers
        $payload = $this->status;
        
        // Force integer conversion for network_rx
        if (!isset($payload['network_rx'])) {
            $payload['network_rx'] = 0;
        } else {
            $payload['network_rx'] = intval($payload['network_rx']);
        }
        
        // Force integer conversion for network_tx
        if (!isset($payload['network_tx'])) {
            $payload['network_tx'] = 0;
        } else {
            $payload['network_tx'] = intval($payload['network_tx']);
        }
        
        // Force integer conversion for disk_io_read and disk_io_write
        if (!isset($payload['disk_io_read'])) {
            $payload['disk_io_read'] = 0;
        } else {
            $payload['disk_io_read'] = intval($payload['disk_io_read']);
        }
        
        if (!isset($payload['disk_io_write'])) {
            $payload['disk_io_write'] = 0;
        } else {
            $payload['disk_io_write'] = intval($payload['disk_io_write']);
        }
        return ['data' => $payload];
    }
}
