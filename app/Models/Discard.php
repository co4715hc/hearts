<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Discard extends Model
{

    protected $table = "discards";

    protected $fillable = ['trick_id', 'cardhand_id'];

    public function cardhand()
    {
        return $this->belongsTo(CardHand::class);
    }

    public function card()
    {
        return $this->hasOneThrough(
            Card::class,
            CardHand::class,
            'id',
            'id',
            'cardhand_id',
            'card_id');
    }


    public function trick()
    {
        return $this->belongsTo(Trick::class);
    }
}
