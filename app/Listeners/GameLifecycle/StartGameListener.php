<?php

namespace App\Listeners\GameLifecycle;

use App\Events\GameLifecycle\StartGameEvent;
use App\Services\GameOrchestrationService;

class StartGameListener
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
    public function handle(StartGameEvent $event): void
    {
        $playerId = $event->playerId;
        $this->orchestrator->startGameSequence($playerId);

    }
}
