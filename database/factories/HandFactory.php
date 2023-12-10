<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\CardHand;
use App\Models\Game;
use App\Models\Hand;
use App\Models\Round;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Hand::class, function () {
    $game = factory(Game::class)->state('withGamePlayers')->create();
    $round = factory(Round::class)->create([
        'game_id' => $game->id
    ]);
    $gamePlayer = $game->gamePlayers()->first();
    return [
        'round_id' => $round->id,
        'gameplayer_id' => $gamePlayer->id,
    ];
});

$factory->afterCreatingState(Hand::class, 'withCardHands', function (Hand $hand) {
    for ($i = 0; $i < 13; $i++) {
        factory(CardHand::class)->create([
            'hand_id' => $hand->id
        ]);
    }
});

$factory->afterCreatingState(Hand::class, 'with10CardHands', function (Hand $hand) {
    for ($i = 0; $i < 10; $i++) {
        factory(CardHand::class)->create([
            'hand_id' => $hand->id
        ]);
    }
});

$factory->afterCreatingState(Hand::class, 'with16CardHands', function (Hand $hand) {
    for ($i = 0; $i < 16; $i++) {
        factory(CardHand::class)->create([
            'hand_id' => $hand->id
        ]);
    }
});
