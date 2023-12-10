<?php

namespace App\Listeners\GameLifecycle;

use App\Events\GameLifecycle\StartRoundEvent;
use App\Services\GameOrchestrationService;

class StartRoundListener
{
    protected $orchestrator;

    /**
     * Create the event listener.
     */
    public function __construct(GameOrchestrationService $orchestrator)
    {
        $this->orchestrator= $orchestrator;
    }

    /**
     * Handle the event.
     */
    public function handle(StartRoundEvent $event): void
    {
        $game = $event->game;
        $this->orchestrator->startRoundSequence($game);
    }
}
