<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Collection;

class GameService
{
    protected $roundService;

    public function __construct(RoundService $roundService)
    {
        $this->roundService = $roundService;
    }

    public function createGame(int $playerId): Game
    {
        $game = Game::create();
        $playerIds = [1, 2, 3, $playerId];
        $seatNumber = 1;

        foreach ($playerIds as $id)
        {
            $isHuman = $playerId === $id;
            $game->gamePlayers()->create([
                'player_id' => $id,
                'seat_number' => $seatNumber++,
                'is_human' => $isHuman
            ]);
        }
        return $game;
    }

    public function calculateGameScores(Game $game): Collection
    {
        $players = $game->gamePlayers;
        $playerScores = $players->pluck('id')->flip()->map(function ($id) {
            return 0;
        });
        foreach ($game->rounds as $round)
        {
            $roundScores = $this->roundService->calculateRoundScores($round);
            foreach ($roundScores as $playerId => $score)
            {
                $playerScores[$playerId] += $score;
            }
        }
        return $playerScores;
    }

    public function isGameOver(Collection $scores): bool
    {
        foreach ($scores as $score)
        {
            if ($score >= 10)
                return true;
        }
        return false;
    }
}
