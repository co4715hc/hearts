<?php

namespace App\Listeners\TrickPhase;

use App\Events\TrickPhase\PlayerTrickInputtedEvent;
use App\Services\GameOrchestrationService;

class PlayerTrickInputtedListener
{
    protected $orchestrator;

    /**
     * Create the event listener.
     */
    public function __construct(GameOrchestrationService $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    /**
     * Handle the event.
     */
    public function handle(PlayerTrickInputtedEvent $event): void
    {
        $trick = $event->trick;
        $player = $event->player;
        $cardhand = $event->cardhand;
        $this->orchestrator->startPlayerTrickInputtedSequence($trick, $player, $cardhand);
    }
}
