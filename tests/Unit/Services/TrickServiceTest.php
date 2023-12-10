<?php

namespace Services;

use App\Models\Card;
use App\Models\CardHand;
use App\Models\Discard;
use App\Models\Game;
use App\Models\Round;
use App\Models\Trick;
use App\Services\TrickService;
use EmptyTrickSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TrickServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $trickService;

    public function setUp(): void
    {
        parent::setUp();
        $this->trickService = new TrickService();
    }

    public function testConstructor()
    {
        $this->assertNotNull($this->trickService);
    }

    public function testCreateTrick()
    {
        $round = Mockery::mock(Round::class);
        $trick = Mockery::mock(Trick::class);
        $round->shouldReceive('tricks->create')->once()->andReturn($trick);
        $trick = $this->trickService->createTrick($round);
        $this->assertNotNull($trick);
    }

    public function testGetNextPlayerTrickDone()
    {
        $trick = factory(Trick::class)->state('withFourDiscards')->create();
        $nextPlayer = $this->trickService->getNextPlayer($trick);
        $this->assertNull($nextPlayer);
    }

    public function testGetNextPlayerFirstTrickFirstDiscard()
    {
        $this->seed(EmptyTrickSeeder::class);
        $game = Game::first();
        $trick = $game->rounds->first()->tricks->first();
        $card = Card::where('suit', 'clubs')->where('rank', '2')->first();
        $cardHand = CardHand::where('card_id', $card->id)->first();
        $player = $cardHand->gamePlayer;
        $actualPlayer = $this->trickService->getNextPlayer($trick);
        $this->assertEquals($player->seat_number, $actualPlayer->seat_number);
    }

    public function testGetNextPlayerSecondTrickFirstDiscard()
    {
        $this->seed(EmptyTrickSeeder::class);
        $game = Game::first();
        $round = $game->rounds->first();
        $trick = $round->tricks->first();

        $card1 = Card::where('suit', 'clubs')->where('rank', '3')->first();
        $cardHand1 = CardHand::create(['hand_id' => 1, 'card_id' => $card1->id]);
        $card2 = Card::where('suit', 'clubs')->where('rank', '2')->first();
        $cardHand2 = CardHand::create(['hand_id' => 2, 'card_id' => $card2->id]);
        $card3 = Card::where('suit', 'clubs')->where('rank', '8')->first();
        $cardHand3 = CardHand::create(['hand_id' => 3, 'card_id' => $card3->id]);
        $card4 = Card::where('suit', 'spades')->where('rank', '9')->first();
        $cardHand4 = CardHand::create(['hand_id' => 4, 'card_id' => $card4->id]);

        $discard1 = $trick->discards()->create(['cardhand_id' => $cardHand1->id]);
        $discard2 = $trick->discards()->create(['cardhand_id' => $cardHand2->id]);
        $discard3 = $trick->discards()->create(['cardhand_id' => $cardHand3->id]);
        $discard4 = $trick->discards()->create(['cardhand_id' => $cardHand4->id]);

        $trick2 = $round->tricks()->create();

        $winningPlayer = $cardHand3->gamePlayer;
        $actualPlayer = $this->trickService->getNextPlayer($trick2);
        $this->assertEquals($winningPlayer->seat_number, $actualPlayer->seat_number);
    }

    public function testGetNextPlayerThirdDiscard()
    {
        $round = factory(Round::class)->state("withHands")->create();
        $trick = factory(Trick::class)->create(['round_id' => $round->id]);

        $hand1 = $round->hands->get(2);
        $card1 = factory(Card::class)->create(['suit' => 'clubs', 'rank' => 'five']);
        $cardHand1 = factory(CardHand::class)->create(['hand_id' => $hand1->id, 'card_id' => $card1->id]);
        $discard1 = factory(Discard::class)->create(['trick_id' => $trick->id, 'cardhand_id' => $cardHand1->id]);

        $hand2 = $round->hands->get(3);
        $card2 = factory(Card::class)->create(['suit' => 'clubs', 'rank' => 'six']);
        $cardHand2 = factory(CardHand::class)->create(['hand_id' => $hand2->id, 'card_id' => $card2->id]);
        $discard2 = factory(Discard::class)->create(['trick_id' => $trick->id, 'cardhand_id' => $cardHand2->id]);
        $lastPlayerSeat = $hand2->gamePlayer->seat_number;

        $expectedSeat = ($lastPlayerSeat % 4) + 1;

        $actualPlayer = $this->trickService->getNextPlayer($trick);

        $this->assertEquals($expectedSeat, $actualPlayer->seat_number);
    }

    public function testDiscardCard()
    {
        $trick = factory(Trick::class)->create();
        $cardHand = factory(CardHand::class)->create();
        $discards = $trick->discards;
        $this->assertEmpty($discards);
        $this->trickService->discardCard($trick, $cardHand);
        $trick->refresh();
        $discards = $trick->discards;
        $this->assertCount(1, $discards);
        $this->assertEquals($cardHand->id, $discards->first()->cardhand_id);
    }
}
