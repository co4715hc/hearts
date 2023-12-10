<?php

namespace App\Services;

use App\Models\CardHand;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Round;
use App\Models\Trick;
use Illuminate\Support\Collection;

class TrickService
{

    public function __construct()
    {
    }

    /**
     * Creates a new trick for the round.
     *
     * @param Round $round
     * @return Trick
     */
    public function createTrick(Round $round): Trick
    {
        /** @var Trick $trick */
        $trick = $round->tricks()->create();
        return $trick;
    }

    /**
     * Gets next player to play a card in the trick. If it's the end of the trick, returns null.
     *
     * @param Trick $trick
     * @return null|GamePlayer
     */
    public function getNextPlayer(Trick $trick)
    {
        $trick->refresh();
        if ($trick->discards->count() == 0)
            return $this->getFirstPlayer($trick);
        else if ($trick->discards->count() == 4)
            return null;
        else
            return $this->getNextPlayerBySeat($trick);
    }

    private function getFirstPlayer(Trick $trick)
    {
        if ($previousTrick = $trick->previousTrick())
            return $previousTrick->winningGamePlayer();
        else
            return $this->getPlayerWithTwoOfClubs($trick);
    }

    private function getPlayerWithTwoOfClubs(Trick $trick)
    {
        $hands = $trick->round->hands;
        foreach ($hands as $hand)
            foreach ($hand->cardHands as $cardHand)
                if ($cardHand->card->isTwoOfClubs())
                    return $cardHand->gamePlayer;
        throw new \Exception('Two of clubs not found');
    }

    private function getNextPlayerBySeat(Trick $trick)
    {
        $lastDiscard = $trick->discards->sortByDesc('id')->first();
        $lastSeat = $lastDiscard->cardhand->gamePlayer->seat_number;
        $nextSeat = ($lastSeat % 4) + 1;
        $gamePlayers = $trick->round->game->gamePlayers;
        return $gamePlayers->where('seat_number', $nextSeat)->first();
    }

    /**
     * Discards a card from a player's hand into the given trick
     *
     * @param Trick $trick
     * @param CardHand $cardhand
     * @return void
     */
    public function discardCard(Trick $trick, CardHand $cardhand)
    {
        $trick->discards()->create(['cardhand_id' => $cardhand->id]);
    }
}
