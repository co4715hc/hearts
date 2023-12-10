<?php


use App\Events\GameLifecycle\EndPassingEvent;
use App\Events\GameLifecycle\StartGameEvent;
use App\Events\GameLifecycle\StartRoundEvent;
use App\Events\PassingPhase\ComputerPassInputEvent;
use App\Events\PassingPhase\PassingTurnEvent;
use App\Events\PassingPhase\PlayerPassInputtedEvent;
use App\Events\PassingPhase\PlayerPassTurnEvent;
use App\Models\Game;
use App\Models\Round;
use App\Services\GameOrchestrationService;
use App\Services\RoundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PassingPhaseTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(CardsTableSeeder::class);
        $this->seed(PlayersTableSeeder::class);
    }

    public function testPassingPhase(): void
    {
        event(new StartGameEvent(4));

        $game = Game::first();
        $round = $game->rounds()->first();
        $hand = $round->hands()->first();
        $this->assertDatabaseHas('rounds', ['id' => $round->id]);
        $this->assertDatabaseHas('hands', ['id' => $hand->id]);
    }
}
