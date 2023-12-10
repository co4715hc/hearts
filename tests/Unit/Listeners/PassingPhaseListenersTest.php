<?php

namespace Listeners;

use App\Events\GameLifecycle\StartPassingEvent;
use App\Events\PassingPhase\ComputerPassInputEvent;
use App\Events\PassingPhase\PassingTurnEvent;
use App\Events\PassingPhase\PlayerPassInputtedEvent;
use App\Events\PassingPhase\PlayerPassTurnEvent;
use App\Listeners\GameLifecycle\StartPassingListener;
use App\Listeners\PassingPhase\ComputerPassInputListener;
use App\Listeners\PassingPhase\PassingTurnListener;
use App\Listeners\PassingPhase\PlayerPassInputtedListener;
use App\Listeners\PassingPhase\PlayerPassTurnListener;
use App\Models\GamePlayer;
use App\Models\Round;
use App\Services\GameOrchestrationService;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class PassingPhaseListenersTest extends TestCase
{

    public function testPassingTurnListener()
    {
        $round = Mockery::mock(Round::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startPassingTurnSequence')->once()->with($round);
        $listener = new PassingTurnListener($orchestrator);
        $event = new PassingTurnEvent($round);
        $listener->handle($event);
        $this->assertEquals($round, $event->round);
    }

    public function testPlayerPassTurnListener()
    {
        $round = Mockery::mock(Round::class);
        $player = Mockery::mock(GamePlayer::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startPlayerPassTurnSequence')->once()->with($round, $player);
        $listener = new PlayerPassTurnListener($orchestrator);
        $event = new PlayerPassTurnEvent($round, $player);
        $listener->handle($event);
        $this->assertEquals($round, $event->round);
        $this->assertEquals($player, $event->player);
    }

    public function testComputerPassInputListener()
    {
        $round = Mockery::mock(Round::class);
        $player = Mockery::mock(GamePlayer::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startComputerPassInputSequence')->once()->with($round, $player);
        $listener = new ComputerPassInputListener($orchestrator);
        $event = new ComputerPassInputEvent($round, $player);
        $listener->handle($event);
        $this->assertEquals($round, $event->round);
    }

    public function testPlayerPassInputtedListener()
    {
        $round = Mockery::mock(Round::class);
        $player = Mockery::mock(GamePlayer::class);
        $cards = Mockery::mock(Collection::class);
        $orchestrator = Mockery::mock(GameOrchestrationService::class);
        $orchestrator->shouldReceive('startPlayerPassInputtedSequence')->once()->with($round, $player, $cards);
        $listener = new PlayerPassInputtedListener($orchestrator);
        $event = new PlayerPassInputtedEvent($round, $player, $cards);
        $listener->handle($event);
        $this->assertEquals($round, $event->round);
        $this->assertEquals($player, $event->player);
    }
}
