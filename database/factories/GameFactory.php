<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Player;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Game::class, function () {
    return [
        //
    ];
});


$factory->afterCreatingState(Game::class, 'withGamePlayers', function (Game $game) {
    $players = factory(Player::class, 4)->create();
    $i = 1;
    foreach ($players as $player) {
        factory(GamePlayer::class)->create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'seat_number' => $i++
        ]);
    }
});

$factory->afterCreatingState(Game::class, 'withThreeRounds', function (Game $game) {
    for ($i = 0; $i < 3; $i++) {
        $game->rounds()->create();
    }
});

$factory->afterCreatingState(Game::class, 'withOneRounds', function (Game $game) {

        $game->rounds()->create();
});
