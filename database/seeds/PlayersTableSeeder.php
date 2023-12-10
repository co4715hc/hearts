<?php

use App\Models\Player;
use Illuminate\Database\Seeder;

class PlayersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $players = ['Pauline', 'Michele', 'Ben', 'Computer', 'Human'];
        foreach ($players as $player) {
            Player::create([
                'name' => $player,
            ]);
        }
    }
}
