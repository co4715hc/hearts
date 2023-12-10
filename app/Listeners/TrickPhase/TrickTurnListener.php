<?php

namespace App\Listeners\TrickPhase;

use App\Events\TrickPhase\TrickTurnEvent;
use App\Services\GameOrchestrationService;

class TrickTurnListener
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
    public function handle(TrickTurnEvent $event): void
    {
        $trick = $event->trick;
        $this->orchestrator->startTrickTurnSequence($trick);
    }
}
