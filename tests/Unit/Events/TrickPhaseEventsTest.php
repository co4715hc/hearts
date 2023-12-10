<?php

namespace Events;

use App\Events\TrickPhase\ComputerTrickInputEvent;
use App\Events\TrickPhase\EndTrickEvent;
use App\Events\TrickPhase\PlayerTrickInputtedEvent;
use App\Events\TrickPhase\PlayerTrickTurnEvent;
use App\Events\TrickPhase\StartTrickEvent;
use App\Events\TrickPhase\TrickTurnEvent;
use App\Models\CardHand;
use App\Models\GamePlayer;
use App\Models\Round;
use App\Models\Trick;
use Mockery;
use Tests\TestCase;

class TrickPhaseEventsTest extends TestCase
{
    public function testStartTrickEvent()
    {
        $round = $this->createMock(Round::class);
        $event = new StartTrickEvent($round);
        $this->assertEquals($round, $event->round);
    }

    public function testTrickTurnEvent()
    {
        $trick = Mockery::mock(Trick::class);
        $event = new TrickTurnEvent($trick);
        $this->assertEquals($trick, $event->trick);
    }

    public function testPlayerTrickTurnEvent()
    {
        $trick = Mockery::mock(Trick::class);
        $player = Mockery::mock(GamePlayer::class);
        $event = new PlayerTrickTurnEvent($trick, $player);
        $this->assertEquals($trick, $event->trick);
        $this->assertEquals($player, $event->player);
    }

    public function testComputerTrickInputEvent()
    {
        $trick = Mockery::mock(Trick::class);
        $player = Mockery::mock(GamePlayer::class);
        $event = new ComputerTrickInputEvent($trick, $player);
        $this->assertEquals($trick, $event->trick);
        $this->assertEquals($player, $event->player);
    }

    public function testPlayerTrickInputtedEvent()
    {
        $trick = Mockery::mock(Trick::class);
        $player = Mockery::mock(GamePlayer::class);
        $cardhand = Mockery::mock(CardHand::class);
        $event = new PlayerTrickInputtedEvent($trick, $player, $cardhand);
        $this->assertEquals($trick, $event->trick);
        $this->assertEquals($player, $event->player);
        $this->assertEquals($cardhand, $event->cardhand);
    }

    public function testEndTrickEvent()
    {
        $trick = Mockery::mock(Trick::class);
        $event = new EndTrickEvent($trick);
        $this->assertEquals($trick, $event->trick);
    }
}
