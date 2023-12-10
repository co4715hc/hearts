<?php

namespace App\Services;

use App\Models\Card;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class CardService
{
    private $rankValues = [
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 6,
        '7' => 7,
        '8' => 8,
        '9' => 9,
        '10' => 10,
        'jack' => 11,
        'queen' => 12,
        'king' => 13,
        'ace' => 14,
    ];
    protected $deck = [];

    public function __construct()
    {
        $this->shuffleDeck();
    }

    private function shuffleDeck(): void
    {
        $this->deck = range(1, 52);
        shuffle($this->deck);
    }

    /**
     * Returns an array of cards
     *
     * @param int $numberOfCards
     * @return Collection[Card]
     */
    public function drawCards(int $numberOfCards): array
    {
        $numberOfCards = min($numberOfCards, count($this->deck));
        $cardIds = array_splice($this->deck, 0, $numberOfCards);
        return Card::findMany($cardIds)->all();
    }

    public function getCardValue(Card $card)
    {
        return $this->rankValues[$card->rank];
    }

    public function getCardPointValue(Card $card)
    {
        if ($card->suit == 'hearts')
            return 1;
        else if ($card->suit == 'spades' && $card->rank == 'queen')
            return 13;
        else
            return 0;
    }
}
