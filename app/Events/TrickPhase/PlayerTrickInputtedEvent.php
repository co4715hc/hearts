<?php

namespace App\Events\TrickPhase;

use App\Models\CardHand;
use App\Models\GamePlayer;
use App\Models\Trick;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerTrickInputtedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trick;
    public $player;
    public $cardhand;

    /**
     * Create a new event instance.
     */
    public function __construct(Trick $trick, GamePlayer $player, CardHand $cardhand)
    {
        $this->trick = $trick;
        $this->player = $player;
        $this->cardhand = $cardhand;
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
