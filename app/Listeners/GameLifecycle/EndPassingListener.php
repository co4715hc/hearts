<?php

namespace App\Listeners\GameLifecycle;

use App\Events\GameLifecycle\EndPassingEvent;
use App\Services\GameOrchestrationService;

class EndPassingListener
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
    public function handle(EndPassingEvent $event): void
    {
        $round = $event->round;
        $this->orchestrator->endPassingSequence($round);
    }
}
