<?php

namespace App\Listeners\PassingPhase;

use App\Events\PassingPhase\ComputerPassInputEvent;
use App\Services\GameOrchestrationService;

class ComputerPassInputListener
{
    protected $orchestrator;
    /**
     * Create the event listener.
     */
    public function __construct(GameORchestrationService $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    /**
     * Handle the event.
     */
    public function handle(ComputerPassInputEvent $event): void
    {
        $round = $event->round;
        $player = $event->player;
        $this->orchestrator->startComputerPassInputSequence($round, $player);
    }
}
