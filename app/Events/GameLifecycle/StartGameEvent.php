<?php

namespace App\Events\GameLifecycle;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StartGameEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $playerId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $playerId)
    {
        $this->playerId = $playerId;
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
