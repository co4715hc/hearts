<?php

namespace App\Listeners\TrickPhase;

use App\Events\TrickPhase\PlayerTrickTurnEvent;
use App\Services\GameOrchestrationService;

class PlayerTrickTurnListener
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
    public function handle(PlayerTrickTurnEvent $event): void
    {
        $trick = $event->trick;
        $player = $event->player;
        $this->orchestrator->startPlayerTrickTurnSequence($trick, $player);
    }
}
