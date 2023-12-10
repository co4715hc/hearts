<?php

namespace Listeners;

use App\Events\GameLifecycle\StartPassingEvent;
use App\Events\PassingPhase\ComputerPassInputEvent;
use App\Events\PassingPhase\PassingTurnEvent;
use App\Events\PassingPhase\PlayerPassInputtedEvent;
use App\Events\PassingPhase\PlayerPassTurnEvent;
use App\Events\TrickPhase\ComputerTrickInputEvent;
use App\Events\TrickPhase\EndTrickEvent;
use App\Events\TrickPhase\PlayerTrickTurnEvent;
use App\Events\TrickPhase\StartTrickEvent;
use App\Events\TrickPhase\TrickTurnEvent;
use App\Listeners\GameLifecycle\StartPassingListener;
use App\Listeners\PassingPhase\ComputerPassInputListener;
use App\Listeners\PassingPhase\PassingTurnListener;
use App\Listeners\PassingPhase\PlayerPassInputtedListener;
use App\Listeners\PassingPhase\PlayerPassTurnListener;
use App\Listeners\TrickPhase\ComputerTrickInputListener;
use App\Listeners\TrickPhase\EndTrickListener;
use App\Listeners\TrickPhase\PlayerTrickTurnListener;
use App\Listeners\TrickPhase\StartTrickListener;
use App\Listeners\TrickPhase\TrickTurnListener;
use App\Models\GamePlayer;
use App\Models\Round;
use App\Models\Trick;
use App\Services\GameOrchestrationService;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class TrickPhaseListenersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->orchestrator = Mockery::mock(GameOrchestrationService::class);
    }

    public function testStartTrickListener()
    {
        $round = Mockery::mock(Round::class);
        $this->orchestrator->shouldReceive('startTrickSequence')->once()->with($round);
        $listener = new StartTrickListener($this->orchestrator);
        $event = new StartTrickEvent($round);
        $listener->handle($event);
        $this->assertEquals($round, $event->round);
    }

    public function testTrickTurnListener()
    {
        $trick = Mockery::mock(Trick::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startTrickTurnSequence')->once()->with($trick);
        $listener = new TrickTurnListener($orchestrator);
        $event = new TrickTurnEvent($trick);
        $listener->handle($event);
        $this->assertEquals($trick, $event->trick);
    }

    public function testPlayerTrickTurnListener()
    {
        $trick = Mockery::mock(Trick::class);
        $player = Mockery::mock(GamePlayer::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startPlayerTrickTurnSequence')->once()->with($trick, $player);
        $listener = new PlayerTrickTurnListener($orchestrator);
        $event = new PlayerTrickTurnEvent($trick, $player);
        $listener->handle($event);
        $this->assertEquals($trick, $event->trick);
        $this->assertEquals($player, $event->player);
    }

    public function testComputerTrickInputListener()
    {
        $trick = Mockery::mock(Trick::class);
        $player = Mockery::mock(GamePlayer::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startComputerTrickInputSequence')
            ->once()
            ->with($trick, $player);
        $listener = new ComputerTrickInputListener($orchestrator);
        $event = new ComputerTrickInputEvent($trick, $player);
        $listener->handle($event);
        $this->assertEquals($trick, $event->trick);
        $this->assertEquals($player, $event->player);
    }

    public function testEndTrickListener()
    {
        $trick = Mockery::mock(Trick::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startEndTrickSequence')->once()->with($trick);
        $listener = new EndTrickListener($orchestrator);
        $event = new EndTrickEvent($trick);
        $listener->handle($event);
        $this->assertEquals($trick, $event->trick);
    }


}
