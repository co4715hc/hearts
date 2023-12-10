<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardHand extends Model
{

    protected $table = "cardhands";

    protected $fillable = ['hand_id', 'card_id', 'from_hand_id'];

    public function hand()
    {
        return $this->belongsTo(Hand::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function discard()
    {
        return $this->hasOne(Discard::class, 'cardhand_id');
    }

    public function gamePlayer()
    {
        return $this->hasOneThrough(
            GamePlayer::class,
            Hand::class,
            'id',
            'id',
            'hand_id',
            'gameplayer_id'
        );
    }
}
