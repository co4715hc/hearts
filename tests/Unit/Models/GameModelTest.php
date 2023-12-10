<?php

namespace Tests\Unit\Models;

use App\Models\Game;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GameModelTest extends TestCase
{
    use RefreshDatabase;

    public function testGameCreation()
    {
        $game = factory(Game::class)->create();
        $this->assertInstanceOf(Game::class, $game);
        $this->assertDatabaseHas('games', ['id' => $game->id]);
    }

    public function testGameWithPlayersCreation()
    {
        $game = factory(Game::class)->state('withGamePlayers')->create();
        $this->assertInstanceOf(Game::class, $game);
        $player = $game->gamePlayers[0]->player;
        $this->assertInstanceOf(Player::class, $player);
    }

    public function testHasManyGamePlayers()
    {
        $game = factory(Game::class)->state('withGamePlayers')->create();
        $players = $game->gamePlayers;
        $this->assertCount(4, $players);
        $this->assertDatabaseHas('game_player', ['game_id' => $game->id]);
        $count = DB::table('game_player')->count();
        $this->assertEquals(4, $count);
    }


    public function testAttributes()
    {
        $game = factory(Game::class)->create();
        $this->assertIsInt($game->id);
    }

    public function testWithRound()
    {
        $game = factory(Game::class)->state('withOneRounds')->create();
        $this->assertInstanceOf(Game::class, $game);
        $this->assertCount(1, $game->rounds);
        $this->assertInstanceOf(Round::class, $game->rounds[0]);
        $this->assertDatabaseHas('rounds', ['game_id' => $game->id]);
    }

    public function testWithManyRounds()
    {
        $game = factory(Game::class)->state("withThreeRounds")->create();
        $this->assertInstanceOf(Game::class, $game);
        $this->assertCount(3, $game->rounds);
        $this->assertInstanceOf(Round::class, $game->rounds[2]);
        $count = DB::table('rounds')->count();
        $this->assertEquals(3, $count);
    }
}
