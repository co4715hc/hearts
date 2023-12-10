<?php

namespace App\Events\PassingPhase;

use App\Models\GamePlayer;
use App\Models\Round;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PlayerPassInputtedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $round;
    public $player;
    public $cardsToPass;

    /**
     * Create a new event instance.
     */
    public function __construct(Round $round, GamePlayer $player, Collection $cardsToPass)
    {
        $this->round = $round;
        $this->player = $player;
        $this->cardsToPass = $cardsToPass;
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
