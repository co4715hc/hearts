<?php

namespace Database\Factories;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Card;
use App\Models\CardHand;
use App\Models\Hand;
use Faker\Generator as Faker;

$factory -> define(CardHand::class, function (Faker $faker) {
    return [
        'hand_id' => factory(Hand::class)->create()->id,
        'card_id' => factory(Card::class)->create()->id,
        'from_hand_id' => null
    ];
});
