<?php

namespace Services;

use App\Models\Game;
use App\Models\Round;
use App\Services\GameService;
use App\Services\RoundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GameServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateGame(): void
    {
        $this->seed();
        $lastGame = Game::latest()->first();
        $expectedGameId = $lastGame ? $lastGame->id + 1 : 1;
        $playerId = 4;
        $roundService = Mockery::mock(RoundService::class);
        $gameService = new GameService($roundService);
        $game = $gameService->createGame($playerId);

        $this->assertEquals($expectedGameId, $game->id);
        $this->assertEquals(4, $game->gamePlayers()->count());
        $this->assertEquals(1, $game->gamePlayers()->first()->id);
        $this->assertEquals(2, $game->gamePlayers()->get()[1]->id);
        $this->assertEquals(3, $game->gamePlayers()->get()[2]->id);
        $this->assertEquals(4, $game->gamePlayers()->get()[3]->id);
    }

    public function testCreateGamePlayersSeatsUnique(): void
    {
        $this->seed();
        $playerId = 4;
        $roundService = Mockery::mock(RoundService::class);
        $gameService = new GameService($roundService);
        $game = $gameService->createGame($playerId);

        $seatNumbers = $game->gamePlayers()->get()->pluck('seat_number')->toArray();
        $uniqueSeatNumbers = array_unique($seatNumbers);
        $this->assertEquals($seatNumbers, $uniqueSeatNumbers);
        $this->assertContains(1, $seatNumbers);
        $this->assertContains(2, $seatNumbers);
        $this->assertContains(3, $seatNumbers);
        $this->assertContains(4, $seatNumbers);
    }

    // TODO: Currently no one is human!
    public function testCreateGamePlayersIsHuman(): void
    {
        $this->seed();
        $playerId = 4;
        $roundService = Mockery::mock(RoundService::class);
        $gameService = new GameService($roundService);
        $game = $gameService->createGame($playerId);
        $game->gamePlayers()->get()->each(function ($gamePlayer) {
            $this->assertFalse((bool) $gamePlayer->is_human);
        });
    }

    public function testCalculateGameScores(): void
    {
        $player1 = (object) ['id' => 1];
        $player2 = (object) ['id' => 2];
        $player3 = (object) ['id' => 3];
        $player4 = (object) ['id' => 4];
        $players = collect([$player1, $player2, $player3, $player4]);

        $roundService = Mockery::mock(RoundService::class);
        $round1 = Mockery::mock(Round::class);
        $round2 = Mockery::mock(Round::class);
        $round3 = Mockery::mock(Round::class);
        $rounds = collect([$round1, $round2, $round3]);

        $roundService->shouldReceive('calculateRoundScores')
                ->with($round1)
                ->andReturn(collect([1 => 10, 2 => 0, 3 => 10, 4 => 6]));
        $roundService->shouldReceive('calculateRoundScores')
                ->with($round2)
                ->andReturn(collect([1 => 6, 2 => 10, 3 => 5, 4 => 5]));
        $roundService->shouldReceive('calculateRoundScores')
                ->with($round3)
                ->andReturn(collect([1 => 0, 2 => 6, 3 => 10, 4 => 10]));

        $game = Mockery::mock(Game::class);
        $game->shouldReceive('getAttribute')->with('rounds')->andReturn($rounds);
        $game->shouldReceive('getAttribute')->with('gamePlayers')->andReturn($players);

        $gameService = new GameService($roundService);

        $scores = $gameService->calculateGameScores($game);

        $this->assertCount(4, $scores);
        $this->assertEquals(16, $scores[1]);
        $this->assertEquals(16, $scores[2]);
        $this->assertEquals(25, $scores[3]);
        $this->assertEquals(21, $scores[4]);
    }



    public function testCalculateGameScores100(): void
    {
        $player1 = (object) ['id' => 1];
        $player2 = (object) ['id' => 2];
        $player3 = (object) ['id' => 3];
        $player4 = (object) ['id' => 4];
        $players = collect([$player1, $player2, $player3, $player4]);

        $roundService = Mockery::mock(RoundService::class);
        $round1 = Mockery::mock(Round::class);
        $round2 = Mockery::mock(Round::class);
        $round3 = Mockery::mock(Round::class);
        $rounds = collect([$round1, $round2, $round3]);

        $roundService->shouldReceive('calculateRoundScores')
                ->with($round1)
                ->andReturn(collect([1 => 26, 2 => 0, 3 => 10, 4 => 6]));
        $roundService->shouldReceive('calculateRoundScores')
                ->with($round2)
                ->andReturn(collect([1 => 26, 2 => 10, 3 => 5, 4 => 5]));
        $roundService->shouldReceive('calculateRoundScores')
                ->with($round3)
                ->andReturn(collect([1 => 50, 2 => 6, 3 => 10, 4 => 10]));

        $game = Mockery::mock(Game::class);
        $game->shouldReceive('getAttribute')->with('rounds')->andReturn($rounds);
        $game->shouldReceive('getAttribute')->with('gamePlayers')->andReturn($players);

        $gameService = new GameService($roundService);

        $scores = $gameService->calculateGameScores($game);

        $this->assertCount(4, $scores);
        $this->assertEquals(102, $scores[1]);
        $this->assertEquals(16, $scores[2]);
        $this->assertEquals(25, $scores[3]);
        $this->assertEquals(21, $scores[4]);
    }

    public function testIsGameOverYes()
    {

        $player1 = (object) ['id' => 1];
        $player2 = (object) ['id' => 2];
        $player3 = (object) ['id' => 3];
        $player4 = (object) ['id' => 4];
        $players = collect([$player1, $player2, $player3, $player4]);

        $roundService = Mockery::mock(RoundService::class);
        $round1 = Mockery::mock(Round::class);
        $round2 = Mockery::mock(Round::class);
        $round3 = Mockery::mock(Round::class);
        $rounds = collect([$round1, $round2, $round3]);

        $roundService->shouldReceive('calculateRoundScores')
                ->with($round1)
                ->andReturn(collect([1 => 26, 2 => 0, 3 => 10, 4 => 6]));
        $roundService->shouldReceive('calculateRoundScores')
                ->with($round2)
                ->andReturn(collect([1 => 26, 2 => 10, 3 => 5, 4 => 5]));
        $roundService->shouldReceive('calculateRoundScores')
                ->with($round3)
                ->andReturn(collect([1 => 50, 2 => 6, 3 => 10, 4 => 10]));

        $game = Mockery::mock(Game::class);
        $game->shouldReceive('getAttribute')->with('rounds')->andReturn($rounds);
        $game->shouldReceive('getAttribute')->with('gamePlayers')->andReturn($players);

        $gameService = new GameService($roundService);

        $scores = $gameService->calculateGameScores($game);

        $this->assertTrue($gameService->isGameOver($scores));
    }

    public function testIsGameOverNo()
    {
        $player1 = (object) ['id' => 1];
        $player2 = (object) ['id' => 2];
        $player3 = (object) ['id' => 3];
        $player4 = (object) ['id' => 4];
        $players = collect([$player1, $player2, $player3, $player4]);

        $roundService = Mockery::mock(RoundService::class);
        $round1 = Mockery::mock(Round::class);
        $round2 = Mockery::mock(Round::class);
        $round3 = Mockery::mock(Round::class);
        $rounds = collect([$round1, $round2, $round3]);

        $roundService->shouldReceive('calculateRoundScores')
                ->with($round1)
                ->andReturn(collect([1 => 10, 2 => 0, 3 => 10, 4 => 6]));
        $roundService->shouldReceive('calculateRoundScores')
                ->with($round2)
                ->andReturn(collect([1 => 6, 2 => 10, 3 => 5, 4 => 5]));
        $roundService->shouldReceive('calculateRoundScores')
                ->with($round3)
                ->andReturn(collect([1 => 0, 2 => 6, 3 => 10, 4 => 10]));

        $game = Mockery::mock(Game::class);
        $game->shouldReceive('getAttribute')->with('rounds')->andReturn($rounds);
        $game->shouldReceive('getAttribute')->with('gamePlayers')->andReturn($players);

        $gameService = new GameService($roundService);

        $scores = $gameService->calculateGameScores($game);

        $this->assertFalse($gameService->isGameOver($scores));
    }
}
