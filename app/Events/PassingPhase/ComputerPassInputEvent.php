<?php

namespace App\Events\PassingPhase;

use App\Models\GamePlayer;
use App\Models\Round;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComputerPassInputEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $round;
    public $player;

    /**
     * Create a new event instance.
     */
    public function __construct(Round $round, GamePlayer $gamePlayer)
    {
        $this->round = $round;
        $this->player = $gamePlayer;
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
