<?php

namespace Services;

use App\Models\Card;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Round;
use App\Models\Trick;
use App\Services\CardService;
use App\Services\RoundService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RoundServiceTest extends TestCase
{
    use RefreshDatabase;
    private $cardService;
    private $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $mockCards = array_map(function ($i) {
            $mockCard = Mockery::mock(Card::class)->makePartial();
            $mockCard->id = $i;
            return $mockCard;
        }, range(1, 13));
        $this->cardService = Mockery::mock(CardService::class);
        $this->cardService->shouldReceive('drawCards')
                ->with(13)
                ->andReturn($mockCards);
        $this->game = factory(Game::class)->state('withGamePlayers')->create();
    }

    public function testConstructor(): void
    {
        $roundService = new RoundService($this->cardService);
        $this->assertTrue(true);
    }

    public function testCreateRound(): void
    {
        $roundService = new RoundService($this->cardService);
        $round = $roundService->createRound($this->game);
        $this->assertInstanceOf(Round::class, $round);
    }

    public function testCreateRoundHands(): void
    {
        $roundService = new RoundService($this->cardService);
        $round = $roundService->createRound($this->game);
        $this->assertEquals(4, $round->hands()->count());
        foreach ($round->hands as $hand)
            $this->assertEquals(13, $hand->cardHands()->count());
    }

    public function testGetPassingDirection(): void
    {
        $round = Mockery::mock(Round::class);
        $round->shouldReceive('roundNumber')
                ->andReturn(1, 2, 3, 4);
        $roundService = new RoundService($this->cardService);
        $this->assertEquals('left', $roundService->getPassingDirection($round));
        $this->assertEquals('right', $roundService->getPassingDirection($round));
        $this->assertEquals('across', $roundService->getPassingDirection($round));
        $this->assertEquals('none', $roundService->getPassingDirection($round));
    }

    public function testGetNextPlayerToPass(): void
    {
        $roundService = new RoundService($this->cardService);
        $round = $roundService->createRound($this->game);
        $nextPlayer = $roundService->getNextPlayerToPass($round);
        $this->assertEquals(1, $nextPlayer->seat_number);

        $nextPlayer->hands()->first()->cardHands()->first()->update(['from_hand_id' => 4]);
        $nextPlayer = $roundService->getNextPlayerToPass($round);
        $this->assertEquals(2, $nextPlayer->seat_number);

        $nextPlayer->hands()->first()->cardHands()->first()->update(['from_hand_id' => 1]);
        $nextPlayer = $roundService->getNextPlayerToPass($round);
        $this->assertEquals(3, $nextPlayer->seat_number);

        $nextPlayer->hands()->first()->cardHands()->first()->update(['from_hand_id' => 2]);
        $nextPlayer = $roundService->getNextPlayerToPass($round);
        $this->assertEquals(4, $nextPlayer->seat_number);

        $nextPlayer->hands()->first()->cardHands()->first()->update(['from_hand_id' => 3]);
        $nextPlayer = $roundService->getNextPlayerToPass($round);
        $this->assertNull($nextPlayer);
    }

    public function testIsValidPassSuccess(): void
    {
        $roundService = new RoundService($this->cardService);
        $round = $roundService->createRound($this->game);
        $hand = $round->hands->first();
        $cardsToPass = $hand->cardHands->take(3);
        $this->assertTrue($roundService->isValidPass($hand, $cardsToPass));
    }

    public function testIsValidPassFailTooMany(): void
    {
        $roundService = new RoundService($this->cardService);
        $round = $roundService->createRound($this->game);
        $hand = $round->hands->first();
        $cardsToPass = $hand->cardHands->take(4);
        $this->assertFalse($roundService->isValidPass($hand, $cardsToPass));
    }

    public function testIsValidPassFailWrongCards(): void
    {
        $roundService = new RoundService($this->cardService);
        $round = $roundService->createRound($this->game);
        $hand = $round->hands->first();
        $otherHand = $round->hands->where('gameplayer_id', '!=', $hand->gameplayer_id)->first();
        $cardsToPass = $otherHand->cardHands->take(3);
        $this->assertFalse($roundService->isValidPass($hand, $cardsToPass));
    }

    public function testIsValidPassFailAlreadyPassed(): void
    {
        $roundService = new RoundService($this->cardService);
        $round = $roundService->createRound($this->game);
        $hand = $round->hands->first();
        $cardsToPass = $hand->cardHands->take(3);
        $hand->cardHands()->update(['from_hand_id' => 1]);
        $this->assertFalse($roundService->isValidPass($hand, $cardsToPass));
    }

    public static function directionAndSeatProvider(): array
    {
        return [['left', 1, 4], ['right', 4, 1], ['right', 2, 3], ['across', 2, 4], ['across', 3, 1]];
    }

    /**
     * @dataProvider directionAndSeatProvider
     */
    public function testGetHandToPassTo($direction, $fromSeat, $expectedToSeat)
    {
        $roundService = $this->getMockBuilder(RoundService::class)
            ->setConstructorArgs([$this->cardService])
            ->onlyMethods(['getNextPlayerToPass', 'getPassingDirection'])
            ->getMock();
        $roundService->method('getPassingDirection')
            ->willReturn($direction);
        $round = $roundService->createRound($this->game);
        $player = $round->game->gamePlayers->where('seat_number', $fromSeat)->first();
        $hand = $roundService->getHandToPassTo($round, $player);
        $this->assertEquals($expectedToSeat, $hand->gamePlayer->seat_number);
        $this->assertEquals($round->id, $hand->round_id);
    }

    public function testPassCards(): void
    {
        $roundService = new RoundService($this->cardService);
        $round = $roundService->createRound($this->game);
        $hand = $round->hands->first();
        $handToPassTo = $round->hands->where('gameplayer_id', '!=', $hand->gameplayer_id)->first();
        $cardsToPass = $hand->cardHands->take(3);
        $roundService->passCards($hand, $handToPassTo, $cardsToPass);
        $this->assertEquals(10, $hand->cardHands()->count());
        $this->assertEquals(16, $handToPassTo->cardHands()->count());
        foreach ($cardsToPass as $cardToPass) {
            $this->assertEquals($handToPassTo->id, $cardToPass->hand_id);
            $this->assertEquals($hand->id, $cardToPass->from_hand_id);
        }
    }

    public function testCalculateScoresMoonShot(): void
    {
        $roundService = new RoundService($this->cardService);

        $player1 = (object) ['id' => 1];
        $player2 = (object) ['id' => 2];
        $player3 = (object) ['id' => 3];
        $player4 = (object) ['id' => 4];
        $players = collect([$player1, $player2, $player3, $player4]);

        $game = Mockery::mock(Game::class)->shouldReceive('getAttribute')->with('gamePlayers')->andReturn($players)->getMock();
        $round = Mockery::mock(Round::class)->shouldReceive('getAttribute')->with('game')->andReturn($game)->getMock();

        $tricks = collect();
        for ($i = 0; $i < 12; $i++) {
            $trick = Mockery::mock(Trick::class);
            $trick->shouldReceive('getTrickWinner')->andReturn($player3);
            $trick->shouldReceive('getTrickPoints')->andReturn(1);
            $tricks->push($trick);
        }
        $trick = Mockery::mock(Trick::class);
        $trick->shouldReceive('getTrickWinner')->andReturn($player3);
        $trick->shouldReceive('getTrickPoints')->andReturn(14);
        $tricks->push($trick);

        $round->shouldReceive('getAttribute')->with('tricks')->andReturn($tricks)->getMock();

        $scores = $roundService->calculateRoundScores($round);
        $this->assertCount(4, $scores);
        $this->assertEquals(26, $scores[1]);
        $this->assertEquals(26, $scores[2]);
        $this->assertEquals(0, $scores[3]);
        $this->assertEquals(26, $scores[4]);
    }



    public function testCalculateScores(): void
    {
        $roundService = new RoundService($this->cardService);

        $player1 = (object) ['id' => 1];
        $player2 = (object) ['id' => 2];
        $player3 = (object) ['id' => 3];
        $player4 = (object) ['id' => 4];
        $players = collect([$player1, $player2, $player3, $player4]);

        $game = Mockery::mock(Game::class)->shouldReceive('getAttribute')->with('gamePlayers')->andReturn($players)->getMock();
        $round = Mockery::mock(Round::class)->shouldReceive('getAttribute')->with('game')->andReturn($game)->getMock();

        $tricks = collect();
        for ($i = 0; $i < 5; $i++) {
            $trick = Mockery::mock(Trick::class);
            $trick->shouldReceive('getTrickWinner')->andReturn($players->random());
            $trick->shouldReceive('getTrickPoints')->andReturn(0);
            $tricks->push($trick);
        }
        for ($i = 0; $i < 5; $i++) {
            $trick = Mockery::mock(Trick::class);
            $trick->shouldReceive('getTrickWinner')->andReturn($player3);
            $trick->shouldReceive('getTrickPoints')->andReturn(2);
            $tricks->push($trick);
        }
        $trick = Mockery::mock(Trick::class);
        $trick->shouldReceive('getTrickWinner')->andReturn($player4);
        $trick->shouldReceive('getTrickPoints')->andReturn(14);
        $tricks->push($trick);

        $trick = Mockery::mock(Trick::class);
        $trick->shouldReceive('getTrickWinner')->andReturn($player3);
        $trick->shouldReceive('getTrickPoints')->andReturn(1);
        $tricks->push($trick);

        $trick = Mockery::mock(Trick::class);
        $trick->shouldReceive('getTrickWinner')->andReturn($player1);
        $trick->shouldReceive('getTrickPoints')->andReturn(1);
        $tricks->push($trick);

        $round->shouldReceive('getAttribute')->with('tricks')->andReturn($tricks)->getMock();

        $scores = $roundService->calculateRoundScores($round);
        $this->assertCount(4, $scores);
        $this->assertEquals(1, $scores[1]);
        $this->assertEquals(0, $scores[2]);
        $this->assertEquals(11, $scores[3]);
        $this->assertEquals(14, $scores[4]);
    }
}
