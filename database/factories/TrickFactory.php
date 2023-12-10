<?php

namespace Database\Factories;

use App\Models\Discard;
use App\Models\Game;
use App\Models\Round;
use App\Models\Trick;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Trick::class, function () {
    return [
        'round_id' => factory(Round::class)->create()->id
    ];
});

$factory->afterCreatingState(Trick::class, 'withOneDiscard', function ($trick) {
    $discard = factory(Discard::class)->create([
        'trick_id' => $trick->id
    ]);
    $trick->discards()->save($discard);
});

$factory->afterCreatingState(Trick::class, 'withFourDiscards', function ($trick) {
    factory(Discard::class, 4)->create([
        'trick_id' => $trick->id
    ]);
});
