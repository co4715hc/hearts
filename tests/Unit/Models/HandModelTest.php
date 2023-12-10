<?php

namespace Tests\Unit\Models;

use App\Models\Card;
use App\Models\CardHand;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Round;
use App\Models\Trick;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandModelTest extends TestCase
{
    use RefreshDatabase;

    public function testCreation()
    {
        $hand = factory(Hand::class)->create();
        $this->assertInstanceOf(Hand::class, $hand);
        $this->assertDatabaseHas('hands', ['id' => $hand->id]);
    }

    public function testAttributes()
    {
        $hand = factory(Hand::class)->create();
        $this->assertIsInt($hand->id);
        $this->assertIsInt($hand->round_id);
        $this->assertIsInt($hand->gameplayer_id);
    }

    public function testBelongsToRound()
    {
        $hand = factory(Hand::class)->create();
        $this->assertInstanceOf(Hand::class, $hand);
        $this->assertInstanceOf(Round::class, $hand->round);
        $this->assertEquals($hand->round_id, $hand->round->id);
    }

    public function testBelongsToGamePlayer()
    {
        $hand = factory(Hand::class)->create();
        $this->assertInstanceOf(Hand::class, $hand);
        $this->assertInstanceOf(GamePlayer::class, $hand->gamePlayer);
        $this->assertEquals($hand->gameplayer_id, $hand->gamePlayer->id);
    }

    public function testHasManyCardHands()
    {
        $hand = factory(Hand::class)->state('withCardHands')->create();
        $this->assertInstanceOf(Hand::class, $hand);
        $this->assertCount(13, $hand->cardHands);
        $this->assertDatabaseHas('cardhands', ['hand_id' => $hand->id]);
        $this->assertInstanceOf(CardHand::class, $hand->cardHands[0]);
    }

    public function testHasManyCards()
    {
        $hand = factory(Hand::class)->state('withCardHands')->create();
        $this->assertInstanceOf(Hand::class, $hand);
        $this->assertCount(13, $hand->cards);
        $this->assertDatabaseHas('cardhands', ['hand_id' => $hand->id]);
        $this->assertInstanceOf(Card::class, $hand->cards[0]);
    }

    public function testHasPassedNoPass()
    {
        $hand = factory(Hand::class)->state('withCardHands')->create();
        $this->assertFalse($hand->hasPassed());
    }

    public function testHasPassedWithPass()
    {
        $hand = factory(Hand::class)->state('with10CardHands')->create();
        $this->assertTrue($hand->hasPassed());
    }

    public function testHasPassedWithPassAndReceive()
    {
        $hand = factory(Hand::class)->state('withCardHands')->create();
        $hand->cardHands[0]->update(['from_hand_id' => 1]);
        $hand->cardHands[1]->update(['from_hand_id' => 1]);
        $hand->cardHands[2]->update(['from_hand_id' => 1]);
        $this->assertTrue($hand->hasPassed());
    }

    public function testHasPassedWithReceive()
    {
        $hand = factory(Hand::class)->state('with16CardHands')->create();
        $this->assertFalse($hand->hasPassed());
    }

    public function testGetNonDiscardedCards()
    {
        $trick = factory(Trick::class)->create();
        $hand = factory(Hand::class)->create();
        $card1 = factory(Card::class)->create([
            'rank' => '2',
            'suit' => 'hearts',
            'value' => 2
        ]);
        $card2 = factory(Card::class)->create([
            'rank' => '3',
            'suit' => 'hearts',
            'value' => 3
        ]);
        $card3 = factory(Card::class)->create([
            'rank' => '4',
            'suit' => 'spades',
            'value' => 4
        ]);
        $card4 = factory(Card::class)->create([
            'rank' => '5',
            'suit' => 'clubs',
            'value' => 5
        ]);
        $cardHand1 = factory(CardHand::class)->create([
            'hand_id' => $hand->id,
            'card_id' => $card1->id
        ]);
        $cardHand2 = factory(CardHand::class)->create([
            'hand_id' => $hand->id,
            'card_id' => $card2->id
        ]);
        $cardHand3 = factory(CardHand::class)->create([
            'hand_id' => $hand->id,
            'card_id' => $card3->id
        ]);
        $cardHand4 = factory(CardHand::class)->create([
            'hand_id' => $hand->id,
            'card_id' => $card4->id
        ]);
        $trick->discards()->create([
            'cardhand_id' => $cardHand4->id
        ]);
        $nonDiscardedCards = $hand->getNonDiscardedCards();
        $this->assertCount(3, $nonDiscardedCards);
        $this->assertEquals($cardHand1->id, $nonDiscardedCards[0]->id);
        $this->assertEquals($cardHand2->id, $nonDiscardedCards[1]->id);
        $this->assertEquals($cardHand3->id, $nonDiscardedCards[2]->id);
    }


    public function testHasSuit()
    {
        $trick = factory(Trick::class)->create();
        $hand = factory(Hand::class)->create();
        $card1 = factory(Card::class)->create([
            'rank' => '2',
            'suit' => 'hearts',
            'value' => 2
        ]);
        $card2 = factory(Card::class)->create([
            'rank' => '3',
            'suit' => 'hearts',
            'value' => 3
        ]);
        $card3 = factory(Card::class)->create([
            'rank' => '4',
            'suit' => 'spades',
            'value' => 4
        ]);
        $card4 = factory(Card::class)->create([
            'rank' => '5',
            'suit' => 'clubs',
            'value' => 5
        ]);
        $cardHand1 = factory(CardHand::class)->create([
            'hand_id' => $hand->id,
            'card_id' => $card1->id
        ]);
        $cardHand2 = factory(CardHand::class)->create([
            'hand_id' => $hand->id,
            'card_id' => $card2->id
        ]);
        $cardHand3 = factory(CardHand::class)->create([
            'hand_id' => $hand->id,
            'card_id' => $card3->id
        ]);
        $cardHand4 = factory(CardHand::class)->create([
            'hand_id' => $hand->id,
            'card_id' => $card4->id
        ]);
        $trick->discards()->create([
            'cardhand_id' => $cardHand4->id
        ]);
        $this->assertTrue($hand->hasSuit('hearts'));
        $this->assertTrue($hand->hasSuit('spades'));
        $this->assertFalse($hand->hasSuit('diamonds'));
        $this->assertFalse($hand->hasSuit('clubs'));
    }
}
