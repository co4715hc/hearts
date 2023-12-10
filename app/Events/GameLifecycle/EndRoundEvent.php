<?php

namespace App\Events\GameLifecycle;

use App\Models\Round;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndRoundEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $round;


    /**
     * Create a new event instance.
     */
    public function __construct(Round $round)
    {
        $this->round = $round;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
