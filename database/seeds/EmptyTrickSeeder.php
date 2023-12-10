<?php

use App\Models\Card;
use App\Models\Game;
use App\Models\Player;
use Illuminate\Database\Seeder;

class EmptyTrickSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DatabaseSeeder::class);
        $game = Game::create();
        $playersIds = Player::all()->pluck('id')->toArray();
        $i = 1;
        foreach ($playersIds as $playerId) {
            $game->gamePlayers()->create([
                'player_id' => $playerId,
                'seat_number' => $i++
            ]);
        }
        $round = $game->rounds()->create();
        $gamePlayers = $game->gamePlayers;
        $hands = [];
        foreach ($gamePlayers as $gamePlayer) {
            $hands[] = $gamePlayer->hands()->create([
                'round_id' => $round->id
            ]);
        }
        $cards = Card::all();
        $i = 0;
        foreach ($cards as $card) {
            $hands[$i % 4]->cardHands()->create([
                'card_id' => $card->id
            ]);
            $i++;
        }
        $trick = $round->tricks()->create();
    }
}
