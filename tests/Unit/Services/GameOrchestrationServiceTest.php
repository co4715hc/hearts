<?php

namespace Services;

use App\Events\GameLifecycle\EndGameEvent;
use App\Events\GameLifecycle\EndPassingEvent;
use App\Events\GameLifecycle\EndRoundEvent;
use App\Events\GameLifecycle\EndTrickPhaseEvent;
use App\Events\GameLifecycle\StartRoundEvent;
use App\Events\GameLifecycle\StartTrickPhaseEvent;
use App\Events\PassingPhase\ComputerPassInputEvent;
use App\Events\PassingPhase\HumanPassInputEvent;
use App\Events\PassingPhase\PassingTurnEvent;
use App\Events\PassingPhase\PlayerPassInputtedEvent;
use App\Events\PassingPhase\PlayerPassTurnEvent;
use App\Events\TrickPhase\ComputerTrickInputEvent;
use App\Events\TrickPhase\EndTrickEvent;
use App\Events\TrickPhase\HumanTrickInputEvent;
use App\Events\TrickPhase\PlayerTrickInputtedEvent;
use App\Events\TrickPhase\PlayerTrickTurnEvent;
use App\Events\TrickPhase\StartTrickEvent;
use App\Events\TrickPhase\TrickTurnEvent;
use App\Models\CardHand;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Round;
use App\Models\Trick;
use App\Services\GameOrchestrationService;
use App\Services\GameService;
use App\Services\PlayerService;
use App\Services\RoundService;
use App\Services\TrickService;
use EmptyTrickSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class GameOrchestrationServiceTest extends TestCase
{
    use RefreshDatabase;
    public function setUp(): void
    {
        parent::setUp();
        $this->player = Mockery::mock(GamePlayer::class);
        $this->round = Mockery::mock(Round::class);
        $this->game = Mockery::mock(Game::class);
        $this->trick = Mockery::mock(Trick::class);
        $this->roundService = Mockery::mock(RoundService::class);
        $this->gameService = Mockery::mock(GameService::class);
        $this->playerService = Mockery::mock(PlayerService::class);
        $this->trickService = Mockery::mock(TrickService::class);
    }

    public function testStartGameSequence(): void
    {
        $playerId = 1;
        $this->gameService
            ->shouldReceive('createGame')
            ->once()
            ->andReturn($this->game);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->startGameSequence($playerId);
        Event::assertDispatched(StartRoundEvent::class, function ($event) {
            return $event->game == $this->game;
        });
    }

    public function testPassingSequenceNoPass(): void
    {
        $this->roundService
            ->shouldReceive('getPassingDirection')
            ->once()
            ->with($this->round)
            ->andReturn('none');
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->startPassingSequence($this->round);
        Event::assertDispatched(EndPassingEvent::class, function ($event) {
            return $event->round == $this->round;
        });
    }

    public static function directionProvider(): array { return [['left'], ['right'], ['across']]; }
    /**
     * @dataProvider directionProvider
     */
    public function testPassingSequenceYesPass($direction): void
    {
        $this->roundService
            ->shouldReceive('getPassingDirection')
            ->once()
            ->with($this->round)
            ->andReturn($direction);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->startPassingSequence($this->round);
        Event::assertDispatched(PassingTurnEvent::class, function ($event) {
            return $event->round == $this->round;
        });
    }

    public function testPassingTurnSequence(): void
    {
        $this->roundService
            ->shouldReceive('getNextPlayerToPass')
            ->once()
            ->with($this->round)
            ->andReturn($this->player);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->startPassingTurnSequence($this->round);
        Event::assertDispatched(PlayerPassTurnEvent::class, function ($event) {
            return $event->round == $this->round && $event->player == $this->player;
        });
    }

    public function testPassingTurnSequenceNull(): void
    {
        $this->roundService
            ->shouldReceive('getNextPlayerToPass')
            ->once()
            ->with($this->round)
            ->andReturn(null);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->startPassingTurnSequence($this->round);
        Event::assertDispatched(EndPassingEvent::class, function ($event) {
            return $event->round == $this->round;
        });
    }

    public function testPlayerPassTurnSequenceHuman(): void
    {
        $this->player
            ->shouldReceive('getAttribute')
            ->once()
            ->with('is_human')
            ->andReturn(true);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->startPlayerPassTurnSequence($this->round, $this->player);
        Event::assertDispatched(HumanPassInputEvent::class, function ($event) {
            return $event->round == $this->round && $event->player == $this->player;
        });
    }

    public function testPlayerPassTurnSequenceComputer(): void
    {
        $this->player
            ->shouldReceive('getAttribute')
            ->once()
            ->with('is_human')
            ->andReturn(false);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->startPlayerPassTurnSequence($this->round, $this->player);
        Event::assertDispatched(ComputerPassInputEvent::class, function ($event) {
            return $event->round == $this->round && $event->player == $this->player;
        });
    }

    public function testStartComputerPassInputSequence()
    {
        $cards = Mockery::mock(Collection::class);
        $this->playerService->shouldReceive('getCardsToPass')
            ->once()
            ->with($this->round, $this->player)
            ->andReturn($cards);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->startComputerPassInputSequence($this->round, $this->player);
        Event::assertDispatched(PlayerPassInputtedEvent::class, function ($event) {
            return $event->round == $this->round && $event->player == $this->player;
        });
    }

    public function testPlayerPassInputtedSequence(): void
    {
        $cards = Mockery::mock(Collection::class);
        $fromHand = Mockery::mock(Hand::class);
        $toHand = Mockery::mock(Hand::class);
        $this->player
            ->shouldReceive('getHandForRound')
            ->once()
            ->with($this->round)
            ->andReturn($fromHand);
        $this->roundService
            ->shouldReceive('isValidPass')
            ->once()
            ->with($fromHand, $cards)
            ->andReturn(true);
        $this->roundService
            ->shouldReceive('getHandToPassTo')
            ->once()
            ->with($this->round, $this->player)
            ->andReturn($toHand);
        $this->roundService
            ->shouldReceive('passCards')
            ->once()
            ->with($fromHand, $toHand, $cards);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->startPlayerPassInputtedSequence($this->round, $this->player, $cards);
        Event::assertDispatched(PassingTurnEvent::class, function ($event) {
            return $event->round == $this->round;
        });
    }

    public function testEndPassingSequence()
    {
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->endPassingSequence($this->round);
        Event::assertDispatched(StartTrickPhaseEvent::class, function ($event) {
            return $event->round == $this->round;
        });
    }

    public function testStartTrickPhaseSequence()
    {
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        Event::fake();
        $orchestrator->startTrickPhaseSequence($this->round);
        Event::assertDispatched(StartTrickEvent::class, function ($event) {
            return $event->round == $this->round;
        });
    }

    public function testStartTrickSequence()
    {
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                     $this->trickService);
        $this->trickService
            ->shouldReceive('createTrick')
            ->once()
            ->with($this->round)
            ->andReturn($this->trick);
        Event::Fake();
        $orchestrator->startTrickSequence($this->round);
        Event::assertDispatched(TrickTurnEvent::class, function ($event) {
            return $event->trick == $this->trick;
        });
    }

    public function testStartTrickTurnSequence()
    {
        $this->trickService
            ->shouldReceive('getNextPlayer')
            ->once()
            ->with($this->trick)
            ->andReturn($this->player);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
            $this->trickService);
        Event::Fake();
        $orchestrator->startTrickTurnSequence($this->trick);
        Event::assertDispatched(PlayerTrickTurnEvent::class, function ($event) {
            return $event->trick == $this->trick && $event->player == $this->player;
        });
    }

    public function testStartTrickTurnSequenceEnd()
    {
        $this->trickService
            ->shouldReceive('getNextPlayer')
            ->once()
            ->with($this->trick)
            ->andReturn(null);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
            $this->trickService);
        Event::Fake();
        $orchestrator->startTrickTurnSequence($this->trick);
        Event::assertDispatched(EndTrickEvent::class, function ($event) {
            return $event->trick == $this->trick;
        });
    }

    public function testStartPlayerTrickTurnSequenceHuman(): void
    {
        $this->player
            ->shouldReceive('getAttribute')
            ->once()
            ->with('is_human')
            ->andReturn(true);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService, $this->trickService);
        Event::Fake();
        $orchestrator->startPlayerTrickTurnSequence($this->trick, $this->player);
        Event::assertDispatched(HumanTrickInputEvent::class, function ($event) {
            return $event->trick == $this->trick && $event->player == $this->player;
        });
    }

    public function testStartPlayerTrickTurnSequenceComputer(): void
    {
        $this->player
            ->shouldReceive('getAttribute')
            ->once()
            ->with('is_human')
            ->andReturn(false);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService, $this->trickService);
        Event::Fake();
        $orchestrator->startPlayerTrickTurnSequence($this->trick, $this->player);
        Event::assertDispatched(ComputerTrickInputEvent::class, function ($event) {
            return $event->trick == $this->trick && $event->player == $this->player;
        });
    }

    public function testStartComputerTrickInputSequence()
    {
        $cardHand = Mockery::mock(CardHand::class);
        $this->playerService
            ->shouldReceive('getCardToPlay')
            ->once()
            ->with($this->trick, $this->player)
            ->andReturn($cardHand);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                    $this->trickService);
        Event::Fake();
        $orchestrator->startComputerTrickInputSequence($this->trick, $this->player);
        Event::assertDispatched(PlayerTrickInputtedEvent::class, function ($event) {
            return $event->trick == $this->trick && $event->player == $this->player;
        });
    }

    public function testStartPlayerTrickInputtedSequence()
    {
        $cardhand = Mockery::mock(CardHand::class);
        $this->playerService
            ->shouldReceive('isValidCard')
            ->once()
            ->with($this->trick, $this->player, $cardhand)
            ->andReturn(true);
        $this->trickService
            ->shouldReceive('discardCard')
            ->once()
            ->with($this->trick, $cardhand);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                    $this->trickService);
        Event::Fake();
        $orchestrator->startPlayerTrickInputtedSequence($this->trick, $this->player, $cardhand);
        Event::assertDispatched(TrickTurnEvent::class, function ($event) {
            return $event->trick == $this->trick;
        });
    }

    public function testStartEndTrickSequence13()
    {
        $this->seed(EmptyTrickSeeder::class);
        $round = Round::first();
        for ($i = 0; $i < 12; $i++) {
            $round->tricks()->create();
        }
        $this->assertEquals(13, $round->tricks->count());
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                    $this->trickService);
        Event::Fake();
        $trick = $round->tricks->last();
        $orchestrator->startEndTrickSequence($trick);
        Event::assertDispatched(EndTrickPhaseEvent::class, function ($event) use ($trick){
            return $event->trick->id == $trick->id;
        });
    }

    public function testStartEndTrickSequence12()
    {
        $this->seed(EmptyTrickSeeder::class);
        $round = Round::with('tricks')->first();
        for ($i = 0; $i < 11; $i++) {
            $round->tricks()->create();
        }
        $this->assertEquals(12, $round->tricks()->count());
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService,
                                                    $this->trickService);
        Event::Fake();
        $trick = $round->tricks->last();
        $orchestrator->startEndTrickSequence($trick);
        Event::assertDispatched(StartTrickEvent::class, function ($event) use ($round){
            return $event->round->id == $round->id;
        });
    }

    public function testStartEndTrickPhaseSequence()
    {
        $trick = Mockery::mock(Trick::class);
        $round = Mockery::mock(Round::class);
        $trick->shouldReceive('getAttribute')
            ->once()
            ->with('round')
            ->andReturn($round);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService, $this->trickService);
        Event::Fake();
        $orchestrator->startEndTrickPhaseSequence($trick);
        Event::assertDispatched(EndRoundEvent::class, function ($event) use ($round){
            return $event->round == $round;
        });
    }

    public function testStartEndRoundSequenceContinueGame(): void
    {
        $round = Mockery::mock(Round::class);
        $game = Mockery::mock(Game::class);
        $scores = Mockery::mock(Collection::class);

        $round->shouldReceive('getAttribute')
            ->once()
            ->with('game')
            ->andReturn($game);

        $this->gameService
            ->shouldReceive('calculateGameScores')
            ->once()
            ->with($game)
            ->andReturn($scores);
        $this->gameService
            ->shouldReceive('isGameOver')
            ->once()
            ->with($scores)
            ->andReturn(false);

        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService, $this->trickService);
        Event::Fake();
        $orchestrator->startEndRoundSequence($round);
        Event::assertDispatched(StartRoundEvent::class, function ($event) use ($game){
            return $event->game == $game;
        });
    }

    public function testStartEndRoundSequenceEndGame(): void
    {
        $round = Mockery::mock(Round::class);
        $game = Mockery::mock(Game::class);
        $scores = Mockery::mock(Collection::class);

        $round->shouldReceive('getAttribute')
            ->once()
            ->with('game')
            ->andReturn($game);

        $this->gameService
            ->shouldReceive('calculateGameScores')
            ->once()
            ->with($game)
            ->andReturn($scores);
        $this->gameService
            ->shouldReceive('isGameOver')
            ->once()
            ->with($scores)
            ->andReturn(true);

        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService, $this->trickService);
        Event::Fake();
        $orchestrator->startEndRoundSequence($round);
        Event::assertDispatched(EndGameEvent::class, function ($event) use ($game, $scores){
            return $event->game == $game && $event->scores == $scores;
        });
    }

    public function testStartEndGameSequence()
    {
        $scores = Mockery::mock(Collection::class);
        $scores->shouldReceive('jsonSerialize')
            ->once()
            ->andReturn([]);
        $orchestrator = new GameOrchestrationService($this->gameService, $this->roundService, $this->playerService, $this->trickService);
        $orchestrator->startEndGameSequence($this->game, $scores);
        $this->assertTrue(true);
    }
}
