<?php

namespace Tests\Unit\Events;

use App\Events\GameLifecycle\EndGameEvent;
use App\Events\GameLifecycle\EndPassingEvent;
use App\Events\GameLifecycle\EndRoundEvent;
use App\Events\GameLifecycle\EndTrickPhaseEvent;
use App\Events\GameLifecycle\StartGameEvent;
use App\Events\GameLifecycle\StartPassingEvent;
use App\Events\GameLifecycle\StartRoundEvent;
use App\Events\GameLifecycle\StartTrickPhaseEvent;
use App\Models\Game;
use App\Models\Round;
use App\Models\Trick;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class GameLifecycleEventsTest extends TestCase
{
    public function testStartGameEvent()
    {
        $playerId = 1;
        $event = new StartGameEvent($playerId);
        $this->assertEquals($playerId, $event->playerId);
    }

    public function testStartRoundEvent()
    {
        $game = $this->createMock(Game::class);
        $event = new StartRoundEvent($game);
        $this->assertEquals($game, $event->game);
    }

    public function testStartPassingEvent()
    {
        $round = $this->createMock(Round::class);
        $event = new StartPassingEvent($round);
        $this->assertEquals($round, $event->round);
    }

    public function testEndPassingEvent()
    {
        $round = $this->createMock(Round::class);
        $event = new EndPassingEvent($round);
        $this->assertEquals($round, $event->round);
    }

    public function testStartTrickPhaseEvent()
    {
        $round = $this->createMock(Round::class);
        $event = new StartTrickPhaseEvent($round);
        $this->assertEquals($round, $event->round);
    }

    public function testEndTrickPhaseEvent()
    {
        $trick = $this->createMock(Trick::class);
        $event = new EndTrickPhaseEvent($trick);
        $this->assertEquals($trick, $event->trick);
    }

    public function testEndRoundEvent()
    {
        $round = $this->createMock(Round::class);
        $event = new EndRoundEvent($round);
        $this->assertEquals($round, $event->round);
    }

    public function testEndGameEvent()
    {
        $game = $this->createMock(Game::class);
        $scores = $this->createMock(Collection::class);
        $event = new EndGameEvent($game, $scores);
        $this->assertEquals($game, $event->game);
        $this->assertEquals($scores, $event->scores);
    }
}
