<?php

namespace App\Listeners\PassingPhase;

use App\Events\GameLifecycle\EndPassingEvent;
use App\Events\PassingPhase\PassingTurnEvent;
use App\Events\PassingPhase\PlayerPassTurnEvent;
use App\Services\GameOrchestrationService;
use Illuminate\Support\Facades\Log;

class PassingTurnListener
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
    public function handle(PassingTurnEvent $event): void
    {
        $round = $event->round;
        $this->orchestrator->startPassingTurnSequence($round);
    }
}

