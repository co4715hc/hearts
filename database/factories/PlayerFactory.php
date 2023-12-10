<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Player;
use Faker\Generator;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Player::class, function (Generator $faker) {
    return [
        'name' => $this->faker->name()
    ];
});

$factory->afterCreatingState(Player::class, 'withGamePlayers', function (Player $player) {
    $game = factory(Game::class)->create();
    factory(GamePlayer::class)->create([
        'game_id' => $game->id,
        'player_id' => $player->id,
        'seat_number' => 1
    ]);
});
