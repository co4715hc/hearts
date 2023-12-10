<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamePlayer extends Model
{
    protected $table = 'game_player';
    protected $fillable = ['game_id', 'player_id', 'seat_number', 'is_human'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function hands()
    {
        return $this->hasMany(Hand::class, 'gameplayer_id');
    }

    public function getHandForRound(Round $round)
    {
        return $this->hands()->where('round_id', $round->id)->first();
    }
}
