<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trick extends Model
{
    protected $table = "tricks";

    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    public function discards()
    {
        return $this->hasMany(Discard::class);
    }

    public function cardHands()
    {
        return $this->hasManyThrough(
        CardHand::class,
        Discard::class,
        'trick_id', // Foreign key on the Discards table
        'id',       // Foreign key on the CardHands table
        'id',       // Local key on the Tricks table
        'cardhand_id'   // Local key on the Discards table
    );
    }


    public function getCards()
    {
        return $this->cardHands->map(function ($cardHand) {
            return $cardHand->card;
        });
    }

    public function previousTrick()
    {
        return $this->round->tricks()->where('id', '<', $this->id)->orderBy('id', 'desc')->first();
    }

    public function leadingSuit()
    {
        $discards = $this->discards;
        if ($discards->count() === 0)
            return null;
        else
            return $this->discards()->first()->card->suit;
    }

    public function winningGamePlayer()
    {
        $discards = $this->discards;
        if ($discards->count() !== 4)
            return null;
        $leadingSuit = $this->leadingSuit();
        $winningDiscard = $discards->filter(function ($discard) use ($leadingSuit) {
            return $discard->cardhand->card->suit === $leadingSuit;
        })->sortByDesc(function ($discard) {
            return $discard->cardhand->card->value;
        })->first();
        return $winningDiscard->cardhand->gamePlayer;
    }

    public function getTrickPoints()
    {
        $points = 0;
        foreach ($this->discards as $discard) {
            if ($discard->cardhand->card->suit === 'hearts')
                $points++;
            if ($discard->cardhand->card->suit === 'spades' && $discard->cardhand->card->rank === 'queen')
                $points += 13;
        }
        return $points;
    }
}
