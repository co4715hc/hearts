<?php

namespace App\Events\TrickPhase;

use App\Models\Round;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StartTrickEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $round;

    /**
     * Create a new event instance.
     */
    public function __construct(Round $round)
    {
        $this->round = $round;
        $dog =$round;
        $dog->refresh();
        Log::Info("Why is this the end??", $round->toArray());
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
