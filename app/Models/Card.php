<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{

    public $timestamps = false;
    protected $table = "cards";

    protected $fillable = ['suit', 'rank', 'value'];

    public function __toString(): string
    {
        return sprintf("Card(suit=%s, rank=%s)", $this->suit, $this->rank);
    }

    public function isTwoOfClubs()
    {
        return $this->suit == 'clubs' && $this->rank == '2';
    }
}
