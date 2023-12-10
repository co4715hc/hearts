<?php

namespace Events;

use App\Events\GameLifecycle\StartPassingEvent;
use App\Events\PassingPhase\ComputerPassInputEvent;
use App\Events\PassingPhase\PassingTurnEvent;
use App\Events\PassingPhase\PlayerPassInputtedEvent;
use App\Events\PassingPhase\PlayerPassTurnEvent;
use App\Models\GamePlayer;
use App\Models\Round;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PassingPhaseEventsTest extends TestCase
{
    public function testPassingTurnEvent()
    {
        $round = $this->createMock(Round::class);
        $event = new PassingTurnEvent($round);
        $this->assertEquals($round, $event->round);
    }

    public function testPlayerPassTurnEvent()
    {
        $round = $this->createMock(Round::class);
        $player = $this->createMock(GamePlayer::class);
        $event = new PlayerPassTurnEvent($round, $player);
        $this->assertEquals($round, $event->round);
        $this->assertEquals($player, $event->player);
    }

    public function testComputerPassInputEvent()
    {
        $round = $this->createMock(Round::class);
        $player = $this->createMock(GamePlayer::class);
        $event = new ComputerPassInputEvent($round, $player);
        $this->assertEquals($round, $event->round);
        $this->assertEquals($player, $event->player);
    }

    public function testPlayerPassInputtedEvent()
    {
        $round = $this->createMock(Round::class);
        $player = $this->createMock(GamePlayer::class);
        $cards = $this->createMock(Collection::class);
        $event = new PlayerPassInputtedEvent($round, $player, $cards);
        $this->assertEquals($round, $event->round);
        $this->assertEquals($player, $event->player);
    }
}
