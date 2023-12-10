<?php

namespace App\Services;

use App\Models\CardHand;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Round;
use App\Models\Trick;
use Illuminate\Support\Collection;

class RoundService
{
    protected $cardService;

    public function __construct(CardService $card)
    {
        $this->cardService = $card;
    }

    /**
     * Creates a new round for the game.
     *
     * @param Game $game
     * @return Round
     */
    public function createRound(Game $game): Round
    {
        /** @var Round $round */
        $round = $game->rounds()->create();
        $this->createHands($round);
        return $round;
    }

    /**
     * Create and assign hands for each player in the round consisting of 13 cards each.
     *
     * @param Round $round
     * @return void
     */
    private function createHands(Round $round): void
    {
        $gamePlayers = $round->game->gamePlayers;
        foreach ($gamePlayers as $gamePlayer)
        {
            $hand = $round->hands()->create(['gameplayer_id' => $gamePlayer->id]);
            $cards = $this->cardService->drawCards(13);
            foreach ($cards as $card)
                $hand->cardHands()->create(['card_id' => $card->id]);
        }
    }

    /**
     * Returns the direction of passing for the round.
     *
     * @param Round $round
     * @return string 'left', 'right', 'across', or 'none'
     */
    public function getPassingDirection(Round $round): string
    {
        $roundNumber = ($round->roundNumber() + 0) % 4;
        switch ($roundNumber)
        {
            case 1:
                return 'left';
            case 2:
                return 'right';
            case 3:
                return 'across';
            default:
                return 'none';
        }
    }

    /**
     * Get the next player to pass if there is one, otherwise return null.
     *
     * @param Round $round
     * @return GamePlayer|null
     */
    public function getNextPlayerToPass(Round $round): ?GamePlayer
    {
        $hands = $round->hands()->get();
        foreach ($hands as $hand)
        {
            $hasPassed = $hand->hasPassed();
            if (!$hasPassed)
                return $hand->gamePlayer;
        }
        return null;
    }

    /**
     * Returns true if the given cardsToPass are valid for the given hand and
     * the player has not already passed.
     *
     * @param Hand $hand
     * @param array|Collection $cardsToPass
     * @return bool
     */
    public function isValidPass(Hand $hand, $cardsToPass): bool
    {
        if ($hand->hasPassed()) // already passed
            return false;
        $cardIds = $cardsToPass->pluck('id')->toArray();
        return $hand->cardHands()->whereIn('id', $cardIds)->count() == 3;
    }

    /**
     * Returns the hand that the given player should pass to this round.
     *
     * @param Round $round
     * @param GamePlayer $player
     * @return Hand
     */
    public function getHandToPassTo(Round $round, GamePlayer $player): Hand
    {
        $passingDirection = $this->getPassingDirection($round);
        $fromSeatNumber = $player->seat_number;
        $toSeatNumber = $this->getNextSeat($fromSeatNumber, $passingDirection);
        return $this->getHandForSeat($round, $toSeatNumber);
    }

    /**
     * Returns seat number in the given direction from the given seat number.
     *
     * @param int $fromSeatNumber
     * @param string $direction
     * @return int
     */
    private function getNextSeat(int $fromSeatNumber, string $direction): int
    {
        switch ($direction)
        {
            case 'left':
                return $fromSeatNumber == 1 ? 4 : $fromSeatNumber - 1;
            case 'right':
                return $fromSeatNumber == 4 ? 1 : $fromSeatNumber + 1;
            case 'across':
                return $fromSeatNumber == 1 || $fromSeatNumber == 2 ? $fromSeatNumber + 2 : $fromSeatNumber - 2;
            default:
                return 0;
        }
    }

    /**
     * Returns the hand for the player in the given seat.
     *
     * @param Round $round
     * @param int $seatNumber
     * @return Hand
     */
    private function getHandForSeat(Round $round, int $seatNumber): Hand
    {
        $gamePlayer = $round->game->gamePlayers->where('seat_number', $seatNumber)->first();
        return $gamePlayer->getHandForRound($round);
    }

    /**
     * @param Hand $hand
     * @param Hand $handToPassTo
     * @param Collection|CardHand[] $cardsToPass
     * @return void
     */
    public function passCards(Hand $hand, Hand $handToPassTo, $cardsToPass): void
    {
        foreach ($cardsToPass as $cardToPass)
            $cardToPass->update(['from_hand_id' => $hand->id, 'hand_id' => $handToPassTo->id]);
    }

    /**
     * Returns the score as a collection of player ids mapped to their score.
     *
     * @param Round $round
     * @return Collection
     */
    public function calculateRoundScores(Round $round): Collection
    {
        $players = $round->game->gamePlayers;
        $playerScores = $players->pluck('id')->flip()->map(function ($id) {
            return 0;
        });
        if ($round->tricks()->count() < 13 || $round->tricks()->latest()->first()->discards()->count() < 4)
            return $playerScores;
        foreach ($round->tricks as $trick)
        {
            $trickWinner = $trick->winningGamePlayer();
            $playerScores[$trickWinner->id] += $trick->getTrickPoints();
        }
        $moonHasBeenShot = $playerScores->contains(26);
        if ($moonHasBeenShot)
            $playerScores = $playerScores->map(function ($score) {
                return $score == 26 ? 0 : 26;
            });
        return $playerScores;
    }
}
