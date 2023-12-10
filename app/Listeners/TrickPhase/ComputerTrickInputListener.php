<?php

namespace App\Listeners\TrickPhase;

use App\Events\TrickPhase\ComputerTrickInputEvent;
use App\Services\GameOrchestrationService;

class ComputerTrickInputListener
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
    public function handle(ComputerTrickInputEvent $event): void
    {
        $trick = $event->trick;
        $player = $event->player;
        $this->orchestrator->startComputerTrickInputSequence($trick, $player);
    }
}
