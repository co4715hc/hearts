<?php

namespace App\Listeners\PassingPhase;

use App\Events\PassingPhase\ComputerPassInputEvent;
use App\Events\PassingPhase\HumanPassInputEvent;
use App\Events\PassingPhase\PlayerPassTurnEvent;
use App\Services\GameOrchestrationService;

class PlayerPassTurnListener
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
    public function handle(PlayerPassTurnEvent $event): void
    {
        $round = $event->round;
        $player = $event->player;
        $this->orchestrator->startPlayerPassTurnSequence($round, $player);
    }
}
