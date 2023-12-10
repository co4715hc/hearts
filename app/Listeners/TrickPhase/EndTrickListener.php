<?php

namespace App\Listeners\TrickPhase;

use App\Events\TrickPhase\EndTrickEvent;
use App\Services\GameOrchestrationService;

class EndTrickListener
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
    public function handle(EndTrickEvent $event): void
    {
        $trick = $event->trick;
        $this->orchestrator->startEndTrickSequence($trick);
    }
}
