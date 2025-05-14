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
}
