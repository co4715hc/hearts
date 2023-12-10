<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hand extends Model
{

    protected $table = "hands";

    protected $fillable = ['gameplayer_id'];

    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    public function gamePlayer()
    {
        return $this->belongsTo(GamePlayer::class, 'gameplayer_id');
    }

    public function cardHands()
    {
        return $this->hasMany(CardHand::class);
    }

    public function cards()
    {
        return $this->hasManyThrough(
                Card::class,
                CardHand::class,
                'hand_id',
                'id',
                'id',
                'card_id');
    }

    public function hasPassed(): bool
    {
        $totalCards = $this->cardHands()->count();
        $receivedCards = $this->cardHands()->whereNotNull('from_hand_id')->count();
        if ($totalCards < 13)
            return true;
        else if ($totalCards == 13 && $receivedCards > 0)
            return true;
        else
            return false;
    }

    public function getNonDiscardedCards()
    {
        return $this->cardHands()->whereDoesntHave('discard')->get();
    }

    public function hasSuit(string $suit): bool
    {
        return $this->getNonDiscardedCards()->contains(function ($cardHand) use ($suit) {
            return $cardHand->card->suit === $suit;
        });
    }
}
