<?php

namespace App\Listeners\PassingPhase;

use App\Events\PassingPhase\HumanPassInputEvent;
use App\Services\GameService;

class HumanPassInputListener
{
    protected $gameService;

    /**
     * Create the event listener.
     */
    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
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
        $cardHands = $hand->cardHands()->whereNull('from_hand_id')->with('card')->get();
        $gameId = $game->id;
        $playerId = $player->id;
        $roundId = $round->id;
        $handId = $hand->id;
        $trickId = null;

        $hasRoundChanged = false;
        $previousRound = $_SESSION["gameState"]["roundId"] ?? null;
        if ($round->roundNumber() > 1)
            $hasRoundChanged = $previousRound != $roundId;

        $cardHands = $cardHands->sortBy(function ($cardHand) {
            $suitOrder = ['clubs', 'diamonds', 'spades', 'hearts'];
            $suit = $cardHand->card->suit;
            $suitIndex = array_search($suit, $suitOrder) + 1;
            $value = $cardHand->card->value;
            return ($suitIndex * 53 + $value);
        })->values();

        $gameState = [
            'gameId' => $gameId,
            'userId' => $player->player()->first()->id,
            'playerId' => $playerId,
            'roundId' => $roundId,
            'handId' => $handId,
            'trickId' => $trickId,
        ];

        $_SESSION["gameState"] = $gameState;


        $players = $game->gamePlayers()->with('player')->get();
        $scores = $this->gameService->calculateGameScores($game)->toArray();
        $playersData = $players->map(function ($gamePlayer) use ($scores) {
            $score = $scores[$gamePlayer->id];
                return [
                    'id' => $gamePlayer->id,
                    'name' => $gamePlayer->player->name,
                    'isHuman' => $gamePlayer->is_human ? true : false,
                    'discarded' => null,
                    'handCount' => 13,
                    'score' => $score
                ];
            })->toArray();

        $_SESSION["state"] = "passing";
        $_SESSION["data"] = [
            'game' => $game,
            'round' => $round,
            'player' => $player,
            'hand' => $hand,
            'cardHands' => $cardHands->toArray(),
            'playersData' => $playersData,
            'roundChanged' => $hasRoundChanged ? true : false,
            'roundData' => [$previousRound, $roundId]
        ];
    }
}
