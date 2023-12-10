<?php

namespace App\Listeners\GameLifecycle;

use App\Events\GameLifecycle\EndRoundEvent;
use App\Services\GameOrchestrationService;

class EndRoundListener
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
    public function handle(EndRoundEvent $event): void
    {
        $round = $event->round;
        $this->orchestrator->startEndRoundSequence($round);
    }
}
