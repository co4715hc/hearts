<?php

namespace App\Listeners\GameLifecycle;

use App\Events\GameLifecycle\EndGameEvent;
use App\Services\GameOrchestrationService;
use App\Services\GameService;

class EndGameListener
{

    protected $orchestrator;
    protected $gameService;

    /**
     * Create the event listener.
     */
    public function __construct(GameOrchestrationService $orchestrator, GameService $gameService)
    {
        $this->orchestrator = $orchestrator;
        $this->gameService = $gameService;
    }

    /**
     * Handle the event.
     */
    public function handle(EndGameEvent $event): void
    {
        $game = $event->game;
        $scores = $event->scores;
        $this->orchestrator->startEndGameSequence($game, $scores);

        $_SESSION["state"] = "end";

        $players = $game->gamePlayers()->with('player')->get();
        $scores = $this->gameService->calculateGameScores($game)->toArray();
        $playersData = $players->map(function ($gamePlayer) use ($scores) {
            $score = $scores[$gamePlayer->id];
                return [
                    'id' => $gamePlayer->id,
                    'name' => $gamePlayer->player->name,
                    'isHuman' => $gamePlayer->is_human ? true : false,
                    'discarded' => null,
                    'handCount' => 0,
                    'score' => $score
                ];
            })->toArray();
        $_SESSION["data"] = [
            'gameOver' => true,
            'playersData' => $playersData,
            'cardHands' => []
        ];
    }
}
