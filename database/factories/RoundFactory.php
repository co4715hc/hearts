<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Hand;
use App\Models\Round;

/** @var \Illuminate\Database\Eloquent\Factory $factory */


$factory->define(Round::class, function () {
    return [
        'game_id' => factory(Game::class)->create()->id
    ];
});

$factory->afterCreatingState(Round::class, 'withHands', function (Round $round) {
    $game = factory(Game::class)->state("withGamePlayers")->create();
    $round->game_id = $game->id;
    $round->save();

    foreach ($game->gamePlayers as $gamePlayer) {
        factory(Hand::class)->create([
            'round_id' => $round->id,
            'gameplayer_id' => $gamePlayer->id
        ]);
    }
});
