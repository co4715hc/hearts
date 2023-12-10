<?php

namespace App\Listeners\GameLifecycle;

use App\Events\GameLifecycle\EndTrickPhaseEvent;
use App\Services\GameOrchestrationService;

class EndTrickPhaseListener
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
    public function handle(EndTrickPhaseEvent $event): void
    {
        $trick = $event->trick;
        $this->orchestrator->startEndTrickPhaseSequence($trick);
    }
}
