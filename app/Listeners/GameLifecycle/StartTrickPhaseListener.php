<?php

namespace App\Listeners\GameLifecycle;

use App\Events\GameLifecycle\StartTrickPhaseEvent;
use App\Services\GameOrchestrationService;

class StartTrickPhaseListener
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
    public function handle(StartTrickPhaseEvent $event): void
    {
        $round = $event->round;
        $this->orchestrator->startTrickPhaseSequence($round);
    }
}
