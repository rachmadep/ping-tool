<?php

namespace App\Events;

use App\Libraries\PingMapper;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PingFailed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ping;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PingMapper $ping)
    {
        $this->ping = $ping;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['hostname-'.$this->ping->getHost()];
    }

    public function broadcastWith()
    {
        return [
            'status' => false,
            'date' => $this->ping->getTime(),
        ];
    }
}
