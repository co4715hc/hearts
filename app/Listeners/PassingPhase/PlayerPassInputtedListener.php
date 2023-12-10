<?php

namespace App\Listeners\PassingPhase;

use App\Events\PassingPhase\PlayerPassInputtedEvent;
use App\Services\GameOrchestrationService;

class PlayerPassInputtedListener
{
    public $orchestrator;
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
    public function handle(PlayerPassInputtedEvent $event): void
    {
        $round = $event->round;
        $player = $event->player;
        $cardsToPass = $event->cardsToPass;
        $this->orchestrator->startPlayerPassInputtedSequence($round, $player, $cardsToPass);
    }
}
