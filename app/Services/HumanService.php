<?php

namespace App\Services;

use App\Models\Hand;
use App\Models\Trick;

class HumanService
{
    protected $playerService;
    protected $gameService;
    protected $roundService;

    public function __construct(PlayerService $playerService, GameService $gameService, RoundService $roundService)
    {
        $this->playerService = $playerService;
        $this->gameService = $gameService;
        $this->roundService = $roundService;
    }

    public function getPlayerCardHands($player, $trick, $hand)
    {
        $round = $trick->round;
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
        return $cardHands;
    }

    public function getPlayerCardHandsPass($hand)
    {
        $cardHands = $hand->cardHands()->whereNull('from_hand_id')->with('card')->get();
        $cardHands = $cardHands->sortBy(function ($cardHand) {
            $suitOrder = ['clubs', 'diamonds', 'spades', 'hearts'];
            $suit = $cardHand->card->suit;
            $suitIndex = array_search($suit, $suitOrder) + 1;
            $value = $cardHand->card->value;
            return ($suitIndex * 53 + $value);
        })->values();
        return $cardHands;

    }

    public function hasRoundChanged($round)
    {
        $hasRoundChanged = false;
        $previousRound = $_SESSION["gameState"]["roundId"] ?? null;
        if ($round->roundNumber() > 1)
            $hasRoundChanged = $previousRound != $round->id;
        return $hasRoundChanged;
    }

    public function updateGameState($trick, $player, $hand)
    {
        $round = $trick->round;
        $game = $round->game;
        $gameId = $game->id;
        $playerId = $player->id;
        $roundId = $round->id;
        $handId = $hand->id;
        $trickId = $trick->id;

        $gameState = [
            'gameId' => $gameId,
            'userId' => $player->player()->first()->id,
            'playerId' => $playerId,
            'roundId' => $roundId,
            'handId' => $handId,
            'trickId' => $trickId,
        ];

        $_SESSION["gameState"] = $gameState;
    }

    public function updateGameStatePass($round, $player, $hand)
    {
        $game = $round->game;
        $gameId = $game->id;
        $playerId = $player->id;
        $roundId = $round->id;
        $handId = $hand->id;
        $trickId = null;

        $gameState = [
            'gameId' => $gameId,
            'userId' => $player->player()->first()->id,
            'playerId' => $playerId,
            'roundId' => $roundId,
            'handId' => $handId,
            'trickId' => $trickId,
        ];

        $_SESSION["gameState"] = $gameState;

    }

    public function getPlayerData($game, $round, $trick)
    {
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
        return $playersData;
    }

    public function getPlayerDataPass($game)
    {
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
        return $playersData;
    }

    public function getRecentDiscards($trick)
    {
        $isFirstTrickOfRound = $trick->round->tricks->count() == 1;
        if (!$isFirstTrickOfRound) {
            $previousTrick = Trick::where('round_id', $trick->round->id)
                ->where('id', '<', $trick->id)
                ->orderBy('id', 'desc')
                ->first();
            $previousDiscards = $previousTrick->discards;
            $previousDiscardsDetailed = [];
            foreach ($previousDiscards as $discard) {
                $cardHand = $discard->cardHand;
                $cardHand->load('hand', 'card', 'gamePlayer');
                $previousDiscardsDetailed[] = $cardHand;
            }
            $previousWinner = $previousTrick->winningGamePlayer();
        } else {
            if ($this->roundService->getPassingDirection($trick->round) == 'none') {
                $previousRound = $trick->round->game->rounds()->where('id', '<', $trick->round->id)->orderBy('id', 'desc')->first();
                $previousTrick = Trick::where('round_id', $previousRound->id)
                        ->orderBy('id', 'desc')
                        ->first();
                $previousDiscards = $previousTrick->discards;
                $previousDiscardsDetailed = [];
                foreach ($previousDiscards as $discard) {
                    $cardHand = $discard->cardHand;
                    $cardHand->load('hand', 'card', 'gamePlayer');
                    $previousDiscardsDetailed[] = $cardHand;
                }
                $previousWinner = $previousTrick->winningGamePlayer();
            } else {
                $previousDiscardsDetailed = [];
                $previousWinner = null;
            }
        }

        $currentlyDiscarded = $trick->discards;
        $currentlyDiscardedDetailed = [];
        foreach ($currentlyDiscarded as $discard2) {
            $cardHand = $discard2->cardHand;
            $cardHand->load('hand', 'card', 'gamePlayer');
            $currentlyDiscardedDetailed[] = $cardHand;
        }
        return [
            'previousDiscards' => $previousDiscardsDetailed,
            'previousWinner' => $previousWinner,
            'currentlyDiscarded' => $currentlyDiscardedDetailed
        ];
    }

    public function getRecentDiscardsPass($round)
    {
        if ($round->roundNumber() == 1)
            return [
                'previousDiscards' => [],
                'previousWinner' => null,
                'currentlyDiscarded' => []
            ];
        $previousRound = $round->game->rounds()->where('id', '<', $round->id)->orderBy('id', 'desc')->first();
        $previousTrick = Trick::where('round_id', $previousRound->id)
                ->orderBy('id', 'desc')
                ->first();
        $previousDiscards = $previousTrick->discards;
        $previousDiscardsDetailed = [];
        foreach ($previousDiscards as $discard) {
            $cardHand = $discard->cardHand;
            $cardHand->load('hand', 'card', 'gamePlayer');
            $previousDiscardsDetailed[] = $cardHand;
        }
        $previousWinner = $previousTrick->winningGamePlayer();
        return [
            'previousDiscards' => $previousDiscardsDetailed,
            'previousWinner' => $previousWinner,
            'currentlyDiscarded' => []
        ];
    }
}
