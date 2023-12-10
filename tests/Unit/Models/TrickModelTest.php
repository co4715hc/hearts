<?php

namespace Tests\Unit\Models;

use App\Models\Card;
use App\Models\CardHand;
use App\Models\Discard;
use App\Models\Round;
use App\Models\Trick;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrickModelTest extends TestCase
{
    use RefreshDatabase;

    public function testCreation()
    {
        $trick = factory(Trick::class)->create();
        $this->assertInstanceOf(Trick::class, $trick);
    }

    public function testAttributes()
    {
        $trick = factory(Trick::class)->create();
        $this->assertIsInt($trick->id);
        $this->assertIsInt($trick->round_id);
        $this->assertDatabaseHas('tricks', [
            'id' => $trick->id,
            'round_id' => $trick->round_id
        ]);
    }

    public function testBelongsToRound()
    {
        $trick = factory(Trick::class)->create();
        $this->assertInstanceOf(Trick::class, $trick);
        $this->assertInstanceOf(Round::class, $trick->round);
        $this->assertEquals($trick->round_id, $trick->round->id);
    }

    public function testHasManyDiscards()
    {
        $trick = factory(Trick::class)->state('withFourDiscards')->create();
        $discards = $trick->discards;
        $this->assertNotNull($discards);
        $this->assertEquals(4, $discards->count());
        for ($i = 0; $i < 4; $i++) {
            $this->assertEquals($trick->id, $discards[$i]->trick_id);
        }
    }

    public function testHasManyCardHands()
    {
        $trick = factory(Trick::class)->state('withFourDiscards')->create();
        $discards = $trick->discards;
        $cardHands = $trick->cardHands;
        $this->assertNotNull($cardHands);
        $this->assertEquals(4, $cardHands->count());
        for ($i = 0; $i < 4; $i++) {
            $this->assertEquals($discards[$i]->id, $cardHands[$i]->id);
        }
    }

    public function testGetCards()
    {
        $trick = factory(Trick::class)->state('withFourDiscards')->create();
        $cards = $trick->getCards();
        $this->assertNotNull($cards);
        $this->assertEquals(4, $cards->count());
        $this->assertInstanceOf(Card::class, $cards[0]);
    }

    public function testPreviousTrick()
    {
        $round = factory(Round::class)->create();
        $tricks = factory(Trick::class, 2)->create([
            'round_id' => $round->id
        ]);
        $trick1 = $tricks[0];
        $trick2 = $tricks[1];
        $this->assertEquals($trick1->id, $trick2->previousTrick()->id);
    }

    public function testPreviousTrickNull()
    {
        $round = factory(Round::class)->create();
        $trick = factory(Trick::class)->create([
            'round_id' => $round->id
        ]);
        $this->assertNull($trick->previousTrick());
    }

    public function testLeadingSuit()
    {
        $trick = factory(Trick::class)->create();
        $card = factory(Card::class)->create([
            'suit' => 'clubs',
            'rank' => 'ace'
        ]);
        $cardHand = factory(CardHand::class)->create([
            'card_id' => $card->id
        ]);
        $discard = factory(Discard::class)->create([
            'cardhand_id' => $cardHand->id,
            'trick_id' => $trick->id
        ]);
        $this->assertEquals('clubs', $trick->leadingSuit());
    }

    public function testLeadingSuitNull()
    {
        $trick = factory(Trick::class)->create();
        $this->assertNull($trick->leadingSuit());
    }

    public function testWinningGamePlayer()
    {
        $trick = factory(Trick::class)->create();
        $card1 = factory(Card::class)->create([
            'suit' => 'clubs',
            'rank' => 'queen',
            'value' => 12
        ]);
        $card2 = factory(Card::class)->create([
            'suit' => 'clubs',
            'rank' => 'ace',
            'value' => 13
        ]);
        $card3 = factory(Card::class)->create([
            'suit' => 'spades',
            'rank' => 'queen',
            'value' => 12
        ]);
        $card4 = factory(Card::class)->create([
            'suit' => 'clubs',
            'rank' => 'jack',
            'value' => 11
        ]);
        $cardHand1 = factory(CardHand::class)->create([
            'card_id' => $card1->id
        ]);
        $cardHand2 = factory(CardHand::class)->create([
            'card_id' => $card2->id
        ]);
        $cardHand3 = factory(CardHand::class)->create([
            'card_id' => $card3->id
        ]);
        $cardHand4 = factory(CardHand::class)->create([
            'card_id' => $card4->id
        ]);
        $discard1 = factory(Discard::class)->create([
            'cardhand_id' => $cardHand1->id,
            'trick_id' => $trick->id
        ]);
        $discard2 = factory(Discard::class)->create([
            'cardhand_id' => $cardHand2->id,
            'trick_id' => $trick->id
        ]);
        $discard3 = factory(Discard::class)->create([
            'cardhand_id' => $cardHand3->id,
            'trick_id' => $trick->id
        ]);
        $discard4 = factory(Discard::class)->create([
            'cardhand_id' => $cardHand4->id,
            'trick_id' => $trick->id
        ]);
        $this->assertEquals($cardHand2->gamePlayer->id, $trick->winningGamePlayer()->id);
    }

    public function testGetTrickPoints13()
    {
        $trick = factory(Trick::class)->create();
        $card1 = factory(Card::class)->create(['suit' => 'clubs', 'rank' => 'queen', 'value' => 12]);
        $card2 = factory(Card::class)->create(['suit' => 'clubs', 'rank' => 'ace', 'value' => 13]);
        $card3 = factory(Card::class)->create(['suit' => 'spades', 'rank' => 'queen', 'value' => 12]);
        $card4 = factory(Card::class)->create(['suit' => 'clubs', 'rank' => 'jack','value' => 11 ]);

        $cardHand1 = factory(CardHand::class)->create(['card_id' => $card1->id]);
        $cardHand2 = factory(CardHand::class)->create(['card_id' => $card2->id]);
        $cardHand3 = factory(CardHand::class)->create(['card_id' => $card3->id]);
        $cardHand4 = factory(CardHand::class)->create(['card_id' => $card4->id]);

        $discard1 = factory(Discard::class)->create(['cardhand_id' => $cardHand1->id, 'trick_id' => $trick->id]);
        $discard2 = factory(Discard::class)->create(['cardhand_id' => $cardHand2->id, 'trick_id' => $trick->id]);
        $discard3 = factory(Discard::class)->create(['cardhand_id' => $cardHand3->id, 'trick_id' => $trick->id]);
        $discard4 = factory(Discard::class)->create(['cardhand_id' => $cardHand4->id, 'trick_id' => $trick->id]);

        $this->assertEquals(13, $trick->getTrickPoints());
    }

    public function testGetTrickPoints0()
    {
        $trick = factory(Trick::class)->create();
        $card1 = factory(Card::class)->create(['suit' => 'clubs', 'rank' => 'queen', 'value' => 12]);
        $card2 = factory(Card::class)->create(['suit' => 'clubs', 'rank' => 'ace', 'value' => 13]);
        $card3 = factory(Card::class)->create(['suit' => 'diamonds', 'rank' => 'queen', 'value' => 12]);
        $card4 = factory(Card::class)->create(['suit' => 'clubs', 'rank' => 'jack','value' => 11 ]);

        $cardHand1 = factory(CardHand::class)->create(['card_id' => $card1->id]);
        $cardHand2 = factory(CardHand::class)->create(['card_id' => $card2->id]);
        $cardHand3 = factory(CardHand::class)->create(['card_id' => $card3->id]);
        $cardHand4 = factory(CardHand::class)->create(['card_id' => $card4->id]);

        $discard1 = factory(Discard::class)->create(['cardhand_id' => $cardHand1->id, 'trick_id' => $trick->id]);
        $discard2 = factory(Discard::class)->create(['cardhand_id' => $cardHand2->id, 'trick_id' => $trick->id]);
        $discard3 = factory(Discard::class)->create(['cardhand_id' => $cardHand3->id, 'trick_id' => $trick->id]);
        $discard4 = factory(Discard::class)->create(['cardhand_id' => $cardHand4->id, 'trick_id' => $trick->id]);

        $this->assertEquals(0, $trick->getTrickPoints());
    }

    public function testGetTrickPoints3()
    {
        $trick = factory(Trick::class)->create();
        $card1 = factory(Card::class)->create(['suit' => 'hearts', 'rank' => 'queen', 'value' => 12]);
        $card2 = factory(Card::class)->create(['suit' => 'hearts', 'rank' => 'ace', 'value' => 13]);
        $card3 = factory(Card::class)->create(['suit' => 'diamonds', 'rank' => 'queen', 'value' => 12]);
        $card4 = factory(Card::class)->create(['suit' => 'hearts', 'rank' => 'jack','value' => 11 ]);

        $cardHand1 = factory(CardHand::class)->create(['card_id' => $card1->id]);
        $cardHand2 = factory(CardHand::class)->create(['card_id' => $card2->id]);
        $cardHand3 = factory(CardHand::class)->create(['card_id' => $card3->id]);
        $cardHand4 = factory(CardHand::class)->create(['card_id' => $card4->id]);

        $discard1 = factory(Discard::class)->create(['cardhand_id' => $cardHand1->id, 'trick_id' => $trick->id]);
        $discard2 = factory(Discard::class)->create(['cardhand_id' => $cardHand2->id, 'trick_id' => $trick->id]);
        $discard3 = factory(Discard::class)->create(['cardhand_id' => $cardHand3->id, 'trick_id' => $trick->id]);
        $discard4 = factory(Discard::class)->create(['cardhand_id' => $cardHand4->id, 'trick_id' => $trick->id]);

        $this->assertEquals(3, $trick->getTrickPoints());
    }

    public function testGetTrickPoints16()
    {
        $trick = factory(Trick::class)->create();
        $card1 = factory(Card::class)->create(['suit' => 'hearts', 'rank' => 'queen', 'value' => 12]);
        $card2 = factory(Card::class)->create(['suit' => 'hearts', 'rank' => 'ace', 'value' => 13]);
        $card3 = factory(Card::class)->create(['suit' => 'spades', 'rank' => 'queen', 'value' => 12]);
        $card4 = factory(Card::class)->create(['suit' => 'hearts', 'rank' => 'jack','value' => 11 ]);

        $cardHand1 = factory(CardHand::class)->create(['card_id' => $card1->id]);
        $cardHand2 = factory(CardHand::class)->create(['card_id' => $card2->id]);
        $cardHand3 = factory(CardHand::class)->create(['card_id' => $card3->id]);
        $cardHand4 = factory(CardHand::class)->create(['card_id' => $card4->id]);

        $discard1 = factory(Discard::class)->create(['cardhand_id' => $cardHand1->id, 'trick_id' => $trick->id]);
        $discard2 = factory(Discard::class)->create(['cardhand_id' => $cardHand2->id, 'trick_id' => $trick->id]);
        $discard3 = factory(Discard::class)->create(['cardhand_id' => $cardHand3->id, 'trick_id' => $trick->id]);
        $discard4 = factory(Discard::class)->create(['cardhand_id' => $cardHand4->id, 'trick_id' => $trick->id]);

        $this->assertEquals(16, $trick->getTrickPoints());
    }
}
