<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{

    protected $table = 'players';
    protected $fillable = ['name'];

    public function gamePlayers()
    {
        return $this->hasMany(GamePlayer::class);
    }
}
