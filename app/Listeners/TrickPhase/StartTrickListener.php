<?php

namespace App\Listeners\TrickPhase;

use App\Events\TrickPhase\StartTrickEvent;
use App\Services\GameOrchestrationService;
use Illuminate\Support\Facades\Log;

class StartTrickListener
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
    public function handle(StartTrickEvent $event): void
    {
        $round = $event->round;
        $this->orchestrator->startTrickSequence($round);
    }
}
