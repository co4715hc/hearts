<?php

namespace Database\Factories;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Card;
use Faker\Generator as Faker;
use Exception;


$factory->define(Card::class, function (Faker $faker) {
    if (Card::count() >= 52) {
        throw new Exception('Too many cards');
    }

    $suits = ['clubs', 'diamonds', 'hearts', 'spades'];
    $ranks = ['2','3','4','5','6','7','8','9','10','jack','queen','king','ace'];
    $values = [2,3,4,5,6,7,8,9,10,11,12,13,14];

    do {
        $suit = $faker->randomElement($suits);
        $rank = $faker->randomElement($ranks);
        $value = $values[array_search($rank, $ranks)];
    } while (Card::where('suit', $suit)->where('rank', $rank)->exists());

    return [
        'suit' => $suit,
        'rank' => $rank,
        'value' => $value
    ];
});
