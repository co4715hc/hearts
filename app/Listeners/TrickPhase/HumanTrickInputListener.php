<?php

namespace App\Listeners\TrickPhase;

use App\Events\TrickPhase\HumanTrickInputEvent;
use App\Services\GameOrchestrationService;
use App\Services\GameService;
use App\Services\PlayerService;

class HumanTrickInputListener
{
    protected $playerService;
    protected $gameService;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(PlayerService $playerService, GameService $gameService)
    {
        $this->playerService = $playerService;
        $this->gameService = $gameService;
    }

    /**
     * Handle the event.
     *
     * @param  HumanTrickInputEvent  $event
     * @return void
     */
    public function handle(HumanTrickInputEvent $event)
    {
        $trick= $event->trick;
        $player = $event->player;
        $round = $trick->round;
        $game = $round->game;
        $hand = $player->hands()->where('round_id', $round->id)->first();
        $allCardHands = $hand->cardHands()->whereDoesntHave("discard")->with('card')->get();
        $playableCardHands = $this->playerService->getPlayableCards($trick, $player);
        $playableCardHandIds = $playableCardHands->pluck('id');
        $cardHands = $allCardHands->transform(function ($cardHand) use ($playableCardHandIds) {
            $cardHand->isPlayable = $playableCardHandIds->contains($cardHand->id);
            return $cardHand;
        });
        $cardHands = $cardHands->sortBy(function ($cardHand) {
            $suitOrder = ['clubs', 'diamonds', 'spades', 'hearts'];
            $suit = $cardHand->card->suit;
            $suitIndex = array_search($suit, $suitOrder) + 1;
            $value = $cardHand->card->value;
            return ($suitIndex * 53 + $value);
        })->values();


        $gameId = $game->id;
        $playerId = $player->id;
        $roundId = $round->id;
        $handId = $hand->id;
        $trickId = $trick->id;

        $hasRoundChanged = false;
        $previousRound = $_SESSION["gameState"]["roundId"] ?? null;
        if ($round->roundNumber() > 1)
            $hasRoundChanged = $previousRound != $roundId;

        $gameState = [
            'gameId' => $gameId,
            'userId' => $player->player()->first()->id,
            'playerId' => $playerId,
            'roundId' => $roundId,
            'handId' => $handId,
            'trickId' => $trickId,
        ];

        $_SESSION["gameState"] = $gameState;

        $_SESSION["state"] = "trick";

        $players = $game->gamePlayers()->with('player')->get();
        $scores = $this->gameService->calculateGameScores($game)->toArray();
        $playersData = $players->map(function ($gamePlayer) use ($round, $trick, $scores) {
            $hand = $gamePlayer->getHandForRound($round);
            $handCount = $hand->cardHands()->whereDoesntHave('discard')->count();
            $discards = $hand->cardHands()->whereHas('discard')->with('discard')->with('card')->get();
            $score = $scores[$gamePlayer->id];

            $currentDiscard = $discards->filter(function ($cardHand) use ($trick) {
                return $cardHand->discard->trick_id == $trick->id;
            })->first();
                return [
                    'id' => $gamePlayer->id,
                    'name' => $gamePlayer->player->name,
                    'isHuman' => $gamePlayer->is_human ? true : false,
                    'discarded' => $currentDiscard->card ?? null,
                    'handCount' => $handCount,
                    'score' => $score
                ];
            })->toArray();

        $_SESSION["data"] = [
            'game' => $game,
            'round' => $round,
            'player' => $player,
            'hand' => $hand,
            'cardHands' => $cardHands->toArray(),
            'playersData' => $playersData,
            'roundChanged' => $hasRoundChanged ? true : false,
        ];
    }
}
