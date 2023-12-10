<?php

namespace Database\Factories;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CardHand;
use App\Models\Discard;
use App\Models\Trick;
use Faker\Generator as Faker;

$factory -> define(Discard::class, function (Faker $faker) {
    return [
        'cardhand_id' => factory(CardHand::class)->create()->id,
        'trick_id' => factory(Trick::class)->create()->id
    ];
});
