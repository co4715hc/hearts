<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Player;
use App\Models\Round;
use Faker\Generator;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(GamePlayer::class, function (Generator $faker) {
    return [
            'game_id' => factory(Game::class)->create()->id,
            'player_id' => factory(Player::class)->create()->id,
            'seat_number' => $faker->numberBetween(1, 4),
            'is_human' => false
    ];
});

$factory->afterCreatingState(GamePlayer::class, 'withHands', function (GamePlayer $gamePlayer) {
    $round = factory(Round::class)->create(['game_id' => $gamePlayer->game_id]);
    for ($i = 0; $i < 4; $i++) {
        factory(Hand::class)->create([
            'gameplayer_id' => $gamePlayer->id,
            'round_id' => $round->id
        ]);
    }
});
