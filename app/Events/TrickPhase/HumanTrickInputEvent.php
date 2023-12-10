<?php

namespace App\Events\TrickPhase;

use App\Models\GamePlayer;
use App\Models\Trick;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HumanTrickInputEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trick;
    public $player;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Trick $trick, GamePlayer $player)
    {
        $this->trick = $trick;
        $this->player = $player;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
