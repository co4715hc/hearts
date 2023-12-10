<?php

namespace App\Events\TrickPhase;

use App\Models\Trick;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrickTurnEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trick;

    /**
     * Create a new event instance.
     */
    public function __construct(Trick $trick)
    {
        $this->trick = $trick;
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
