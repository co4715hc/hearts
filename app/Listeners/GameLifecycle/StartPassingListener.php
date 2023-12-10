<?php

namespace App\Listeners\GameLifecycle;

use App\Events\GameLifecycle\StartPassingEvent;
use App\Services\GameOrchestrationService;

class StartPassingListener
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
    public function handle(StartPassingEvent $event): void
    {
        $round = $event->round;
        $this->orchestrator->startPassingSequence($round);
    }
}
