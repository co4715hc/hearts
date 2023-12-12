<?php

namespace App\Listeners\PassingPhase;

use App\Events\PassingPhase\HumanPassInputEvent;
use App\Services\GameService;
use App\Services\HumanService;

class HumanPassInputListener
{
    protected $gameService;
    protected $humanService;

    /**
     * Create the event listener.
     */
    public function __construct(GameService $gameService, HumanService $humanService)
    {
        $this->gameService = $gameService;
        $this->humanService = $humanService;
    }

    /**
     * Handle the event.
     */
    public function handle(HumanPassInputEvent $event): void
    {
        $round = $event->round;
        $player = $event->player;

        $game = $round->game;
        $hand = $player->hands()->where('round_id', $round->id)->first();

        $roundId = $round->id;
        $previousRound = $_SESSION["gameState"]["roundId"] ?? null;

        $hasRoundChanged = $this->humanService->hasRoundChanged($round);
        $cardHands = $this->humanService->getPlayerCardHandsPass($hand);

        $this->humanService->updateGameStatePass($round, $player, $hand);
        $playersData = $this->humanService->getPlayerDataPass($game);

        $history = $this->humanService->getRecentDiscardsPass($round);

        $_SESSION["state"] = "passing";
        $_SESSION["data"] = [
            'game' => $game,
            'round' => $round,
            'player' => $player,
            'hand' => $hand,
            'cardHands' => $cardHands->toArray(),
            'playersData' => $playersData,
            'roundChanged' => $hasRoundChanged ? true : false,
            'roundData' => [$previousRound, $roundId],
            'history' => $history
        ];
    }
}
