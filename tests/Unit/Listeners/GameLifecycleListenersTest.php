<?php

namespace Listeners;

use App\Events\GameLifecycle\EndGameEvent;
use App\Events\GameLifecycle\EndPassingEvent;
use App\Events\GameLifecycle\EndRoundEvent;
use App\Events\GameLifecycle\EndTrickPhaseEvent;
use App\Events\GameLifecycle\StartGameEvent;
use App\Events\GameLifecycle\StartPassingEvent;
use App\Events\GameLifecycle\StartRoundEvent;
use App\Events\GameLifecycle\StartTrickPhaseEvent;
use App\Listeners\GameLifecycle\EndGameListener;
use App\Listeners\GameLifecycle\EndPassingListener;
use App\Listeners\GameLifecycle\EndRoundListener;
use App\Listeners\GameLifecycle\EndTrickPhaseListener;
use App\Listeners\GameLifecycle\StartGameListener;
use App\Listeners\GameLifecycle\StartPassingListener;
use App\Listeners\GameLifecycle\StartRoundListener;
use App\Listeners\GameLifecycle\StartTrickPhaseListener;
use App\Models\Game;
use App\Models\Round;
use App\Models\Trick;
use App\Services\GameOrchestrationService;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class GameLifecycleListenersTest extends TestCase
{
    public function testStartGameListener()
    {
        $playerId = 1;
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startGameSequence')->once()->with($playerId);
        $listener = new StartGameListener($orchestrator);
        $event = new StartGameEvent($playerId);
        $listener->handle($event);
        $this->assertEquals($playerId, $event->playerId);
    }

    public function testStartRoundListener()
    {
        $game = Mockery::mock(Game::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startRoundSequence')->once()->with($game);
        $listener = new StartRoundListener($orchestrator);
        $event = new StartRoundEvent($game);
        $listener->handle($event);
        $this->assertEquals($game, $event->game);
    }

    public function testStartPassingEventListener()
    {
        $round = Mockery::mock(Round::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startPassingSequence')->once()->with($round);
        $listener = new StartPassingListener($orchestrator);
        $event = new StartPassingEvent($round);
        $listener->handle($event);
        $this->assertEquals($round, $event->round);
    }

    public function testEndPassingEvent()
    {
        $round = Mockery::mock(Round::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('endPassingSequence')->once()->with($round);
        $listener = new EndPassingListener($orchestrator);
        $event = new EndPassingEvent($round);
        $listener->handle($event);
        $this->assertEquals($round, $event->round);
    }

    public function testStartTrickPhaseListener()
    {
        $round = Mockery::mock(Round::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startTrickPhaseSequence')->once()->with($round);
        $listener = new StartTrickPhaseListener($orchestrator);
        $event = new StartTrickPhaseEvent($round);
        $listener->handle($event);
        $this->assertEquals($round, $event->round);
    }

    public function testEndTrickPhaseListener()
    {
        $trick = Mockery::mock(Trick::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startEndTrickPhaseSequence')->once()->with($trick);
        $listener = new EndTrickPhaseListener($orchestrator);
        $event = new EndTrickPhaseEvent($trick);
        $listener->handle($event);
        $this->assertEquals($trick, $event->trick);
    }

    public function testEndRoundListener()
    {
        $round = Mockery::mock(Round::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startEndRoundSequence')->once()->with($round);
        $listener = new EndRoundListener($orchestrator);
        $event = new EndRoundEvent($round);
        $listener->handle($event);
        $this->assertEquals($round, $event->round);
    }

    public function testEndGameListener()
    {
        $game = Mockery::mock(Game::class);
        $scores = Mockery::mock(Collection::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startEndGameSequence')->once()->with($game, $scores);
        $listener = new EndGameListener($orchestrator);
        $event = new EndGameEvent($game, $scores);
        $listener->handle($event);
        $this->assertEquals($game, $event->game);
        $this->assertEquals($scores, $event->scores);
    }
}
