<?php

namespace App\Services;

use App\Models\CardHand;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Round;
use App\Models\Trick;
use Illuminate\Support\Collection;

class PlayerService
{
    /**
     *  Chooses 3 random cards TODO: implement AI
     *
     * @param Round $round
     * @param GamePlayer $player
     * @return Collection|CardHand[]
     */
    public function getCardsToPass(Round $round, GamePlayer $player)
    {
        $hand = $player->getHandForRound($round);
        return $hand->cardHands()->whereNull('from_hand_id')->inRandomOrder()->take(3)->get();
    }

    /**
     * Chooses random playable card TODO: implement AI
     *
     * @param Trick $trick
     * @param GamePlayer $player
     * @return CardHand
     */
    public function getCardToPlay(Trick $trick, GamePlayer $player): CardHand
    {
        $playableCards = $this->getPlayableCards($trick, $player);
        try {
        return $playableCards->random();
        } catch (\Exception $e) {
            // All cards:
            $leadingSuit = $trick->leadingSuit();
            $cards = [];
            $cardHands = $player->getHandForRound($trick->round)->cardHands;
            // all discards this round
            foreach ($trick->round->tricks as $trick) {
                foreach ($trick->discards as $discard) {
                    $cards[] = $discard->card;
                }
            }


        // Log the error for better debugging

        // Fetch discarded cards by the player
            $playerCardhands = $player->getHandForRound($trick->round)->cardHands;
        $discardedCards = $playerCardhands->filter(function ($cardHand) {
            return $cardHand->discard !== null;
        });

        // Log discarded cards

        // Fetch remaining cards in the player's hand
        $remainingCards = $playerCardhands->reject(function ($cardHand) {
            return $cardHand->discard !== null;
        });

        // Log remaining cards

        // Use dd to dump the detailed information
        dd([
            'leadingSuit' => $leadingSuit,
            'playableCards' => $playableCards->pluck('card')->toArray(),
            'heartsBroken' => $trick->round->isHeartsBroken() ? 'true' : 'false',
            'discardedCards' => $discardedCards->pluck('card')->toArray(),
            'remainingCards' => $remainingCards->pluck('card')->toArray(),
        ]);

            $count = count($cards);
            $round = $trick->round;
            $round->refresh();
            $broken = $round->isHeartsBroken() ? 'true' : 'false';
            dd("Hearts broken: {$broken} \nLeading suit: $leadingSuit\n, No playable cards for player (total: $count) " . $player->id . "\n in trick " . $trick->id . " with cards: \n" . implode(', ', $cards));
        }
    }

    /**
     * Returns the valid cards a player can play in the trick as a collection of CardHand.
     *
     * @param Trick $trick
     * @param GamePlayer $player
     * @return Collection|CardHand[]
     */
    public function getPlayableCards(Trick $trick, GamePlayer $player): Collection
    {
        $hand = $player->getHandForRound($trick->round);
        $isHeartsBroken = $trick->round->isHeartsBroken();
        $leadingSuit = $trick->leadingSuit();
        $hasLeadingSuit = $leadingSuit ? $hand->hasSuit($leadingSuit) : false;
        $hasNonHearts = $hand->cardHands()
                ->whereDoesntHave('discard')
                ->whereHas('card', function ($query) {
                    $query->where(function ($query) {
                        $query->where('suit', '!=', 'hearts')
                          ->where(function ($query) {
                              $query->where('suit', '!=', 'spades')
                                         ->orWhere('rank', '!=', 'queen');
                          });
                    });
                })->count() > 0;

        $isFirstTrick = $trick->round->tricks->count() === 1;

        // First discard of round, must play 2 of clubs
        if ($isFirstTrick && $trick->discards->count() === 0)
            return $this->getTwoOfClubs($hand);

        // There is a leading suit and player has it, must play leading suit
        if ($hasLeadingSuit)
            return $this->filterHandBySuit($hand, $leadingSuit);

        // There is no leading suit and hearts isn't broken and player has non-hearts, must play non-hearts
        if ((!$leadingSuit && !$isHeartsBroken && $hasNonHearts) || ($isFirstTrick && $hasNonHearts))
            return $this->filterHandByNotHeartsOrQueenOfSpades($hand);

        // Else, player can play any card
        return $hand->getNonDiscardedCards();
    }

    private function filterHandBySuit(Hand $hand, string $suit): Collection
    {
        return $hand->getNonDiscardedCards()->filter(function ($cardHand) use ($suit) {
            return $cardHand->card->suit === $suit;
        });
    }

    private function filterHandByNotHeartsOrQueenOfSpades(Hand $hand): Collection
    {
        return $hand->getNonDiscardedCards()->reject(function ($cardHand) {
            return $cardHand->card->suit === 'hearts' || ($cardHand->card->suit === 'spades' && $cardHand->card->rank === 'queen');
        });
    }

    private function getTwoOfClubs(Hand $hand): Collection
    {
        return $hand->cardHands()->whereHas('card', function ($query) {
            $query->where('suit', 'clubs')->where('rank', '2');
        })->get();
    }

    public function isValidCard(Trick $trick, GamePlayer $player, CardHand $cardhand)
    {
        $playableCards = $this->getPlayableCards($trick, $player);
        return $playableCards->contains($cardhand);
    }
}
