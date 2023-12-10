<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Round extends Model
{

    protected $table = "rounds";

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function hands()
    {
        return $this->hasMany(Hand::class);
    }

    public function tricks()
    {
        return $this->hasMany(Trick::class);
    }

    public function roundNumber(): int
    {
        return $this->game->rounds()->where('id', '<=', $this->id)->count();
    }

    public function isHeartsBroken(): bool
    {
        if ($this->tricks()->count() == 1)
            return false;
        $heartsPlayed = false;
        foreach ($this->tricks as $trick) {
            foreach ($trick->getCards() as $card) {
                if ($card->suit === 'hearts' ||
                    ($card->suit === 'spades' && $card->rank === "queen")) {
                    $heartsPlayed = true;
                    break;
                }
            }
        }
        return $heartsPlayed;
    }
}
