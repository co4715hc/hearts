<?php

use App\Models\Card;
use Illuminate\Database\Seeder;

class CardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        $ranks = ['ace', 'king', 'queen', 'jack', '10', '9', '8', '7', '6', '5', '4', '3', '2'];
        $values = [14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2];
        foreach ($suits as $suit)
        {
            foreach ($ranks as $rank)
            {
                Card::create([
                    'suit' => $suit,
                    'rank' => $rank,
                    'value' => $values[array_search($rank, $ranks)]
                ]);
            }
        }
    }
}
