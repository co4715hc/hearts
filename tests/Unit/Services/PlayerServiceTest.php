<?php

namespace Services;

use App\Models\Card;
use App\Models\CardHand;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Round;
use App\Models\Trick;
use App\Services\PlayerService;
use EmptyTrickSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Mime\Encoder\QpContentEncoder;
use Tests\TestCase;

class PlayerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testGetCardsToPass()
    {
        $playerService = new PlayerService();
        $round = factory(Round::class)->create();
        $gamePlayer = factory(GamePlayer::class)->create();
        $hand = factory(Hand::class)->create(['gameplayer_id' => $gamePlayer->id, 'round_id' => $round->id]);
        factory(CardHand::class, 5)->create(['hand_id' => $hand->id, 'from_hand_id' => null]);
        factory(CardHand::class, 5)->create(['hand_id' => $hand->id, 'from_hand_id' => -1]);
        $cardsToPass = $playerService->getCardsToPass($round, $gamePlayer);
        $this->assertEquals(3, $cardsToPass->count());
        foreach ($cardsToPass as $cardToPass)
            $this->assertNull($cardToPass->from_hand_id);
    }

    public function testGetCardToPlayTwoOfClubs()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();
        $hand = $round->hands->first();
        $player = $hand->gamePlayer;
        $hand->cardHands()->delete();
        $twoOfHearts = Card::where('suit', 'hearts')->where('rank', '2')->first();
        $queenOfSpades = Card::where('suit', 'spades')->where('rank', 'queen')->first();
        $twoOfClubs = Card::where('suit', 'clubs')->where('rank', '2')->first();

        $cardHand1 = $hand->cardHands()->create(['card_id' => $twoOfHearts->id]);
        $cardHand2 = $hand->cardHands()->create(['card_id' => $queenOfSpades->id]);
        $cardHand3 = $hand->cardHands()->create(['card_id' => $twoOfClubs->id]);

        $cardHandToPlay = $playerService->getCardToPlay($trick, $player);
        $this->assertInstanceOf(CardHand::class, $cardHandToPlay);
        $this->assertEquals($twoOfClubs->id, $cardHandToPlay->card_id);
    }

    public function testGetCardToPlayHasLeadingSuit()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();

        // Queen of spades as first card
        $hand1 = $round->hands->first();
        $hand1->cardHands()->delete();
        $card = Card::where('suit', 'spades')->where('rank', 'queen')->first();
        $cardhand1 = $hand1->cardHands()->create(['card_id' => $card->id]);
        $trick->discards()->create(['cardhand_id' => $cardhand1->id]);

        $hand2 = $round->hands->get(1);
        $hand2->cardHands()->delete();

        $queenOfHearts = Card::where('suit', 'hearts')->where('rank', 'queen')->first();
        $kingOfSpades = Card::where('suit', 'spades')->where('rank', 'king')->first();
        $twoOfSpades = Card::where('suit', 'spades')->where('rank', '2')->first();

        $cardhand1 = $hand2->cardHands()->create(['card_id' => $queenOfHearts->id]);
        $cardhand2 = $hand2->cardHands()->create(['card_id' => $kingOfSpades->id]);
        $cardhand3 = $hand2->cardHands()->create(['card_id' => $twoOfSpades->id]);

        $expectedCardIds = [$twoOfSpades->id, $kingOfSpades->id];

        $cardHandToPlay = $playerService->getCardToPlay($trick, $hand2->gamePlayer);
        $this->assertInstanceOf(CardHand::class, $cardHandToPlay);
        $this->assertContains($cardHandToPlay->card_id, $expectedCardIds);
    }



    public function testGetCardToPlayDoesntHaveLeadingSuitHeartsNotBroken()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();

        // ace of spades as first card
        $hand1 = $round->hands->first();
        $hand1->cardHands()->delete();
        $card = Card::where('suit', 'spades')->where('rank', 'ace')->first();
        $cardhand1 = $hand1->cardHands()->create(['card_id' => $card->id]);
        $trick->discards()->create(['cardhand_id' => $cardhand1->id]);

        $hand2 = $round->hands->get(1);
        $hand2->cardHands()->delete();

        $queenOfHearts = Card::where('suit', 'hearts')->where('rank', 'queen')->first();
        $kingOfClubs = Card::where('suit', 'clubs')->where('rank', 'king')->first();
        $twoOfDiamonds = Card::where('suit', 'diamonds')->where('rank', '2')->first();

        $cardhand1 = $hand2->cardHands()->create(['card_id' => $queenOfHearts->id]);
        $cardhand2 = $hand2->cardHands()->create(['card_id' => $kingOfClubs->id]);
        $cardhand3 = $hand2->cardHands()->create(['card_id' => $twoOfDiamonds->id]);

        $expectedCardIds = [$twoOfDiamonds->id, $kingOfClubs->id];

        $cardHandToPlay = $playerService->getCardToPlay($trick, $hand2->gamePlayer);
        $this->assertInstanceOf(CardHand::class, $cardHandToPlay);
        $this->assertContains($cardHandToPlay->card_id, $expectedCardIds);
    }

    public function testGetPlayableCardsTwoOfClubs()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();
        $hand = $round->hands->first();
        $player = $hand->gamePlayer;
        $hand->cardHands()->delete();
        $twoOfHearts = Card::where('suit', 'hearts')->where('rank', '2')->first();
        $queenOfSpades = Card::where('suit', 'spades')->where('rank', 'queen')->first();
        $twoOfClubs = Card::where('suit', 'clubs')->where('rank', '2')->first();

        $cardHand1 = $hand->cardHands()->create(['card_id' => $twoOfHearts->id]);
        $cardHand2 = $hand->cardHands()->create(['card_id' => $queenOfSpades->id]);
        $cardHand3 = $hand->cardHands()->create(['card_id' => $twoOfClubs->id]);

        $playableCards = $playerService->getPlayableCards($trick, $player);
        $this->assertCount(1, $playableCards);
        $this->assertEquals($twoOfClubs->id, $playableCards->first()->card_id);
    }

    public function testGetPlayableCardsHasLeadingSuit()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();

        // Queen of spades as first card
        $hand1 = $round->hands->first();
        $hand1->cardHands()->delete();
        $card = Card::where('suit', 'spades')->where('rank', 'queen')->first();
        $cardhand1 = $hand1->cardHands()->create(['card_id' => $card->id]);
        $trick->discards()->create(['cardhand_id' => $cardhand1->id]);

        $hand2 = $round->hands->get(1);
        $hand2->cardHands()->delete();

        $queenOfHearts = Card::where('suit', 'hearts')->where('rank', 'queen')->first();
        $kingOfSpades = Card::where('suit', 'spades')->where('rank', 'king')->first();
        $twoOfSpades = Card::where('suit', 'spades')->where('rank', '2')->first();

        $cardhand1 = $hand2->cardHands()->create(['card_id' => $queenOfHearts->id]);
        $cardhand2 = $hand2->cardHands()->create(['card_id' => $kingOfSpades->id]);
        $cardhand3 = $hand2->cardHands()->create(['card_id' => $twoOfSpades->id]);

        $expectedCardIds = [$twoOfSpades->id, $kingOfSpades->id];

        $playableCards = $playerService->getPlayableCards($trick, $hand2->gamePlayer);
        $this->assertCount(2, $playableCards);
        foreach ($playableCards as $playableCard)
            $this->assertContains($playableCard->card_id, $expectedCardIds);
    }

    public function testGetPlayableCardsNoLeadingSuitHeartsBroken()
    {

        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();
        $hand = $round->hands->first();
        $player = $hand->gamePlayer;
        $hand->cardHands()->delete();

        // Break hearts in first trick
        $twoOfHearts = Card::where('suit', 'hearts')->where('rank', '2')->first();
        $threeOfHearts = Card::where('suit', 'hearts')->where('rank', '3')->first();
        $queenOfSpades = Card::where('suit', 'spades')->where('rank', 'queen')->first();
        $twoOfClubs = Card::where('suit', 'clubs')->where('rank', '2')->first();
        $cardHand1 = $hand->cardHands()->create(['card_id' => $twoOfHearts->id]);
        $cardHand2 = $hand->cardHands()->create(['card_id' => $threeOfHearts->id]);
        $cardHand3 = $hand->cardHands()->create(['card_id' => $queenOfSpades->id]);
        $cardHand4 = $hand->cardHands()->create(['card_id' => $twoOfClubs->id]);
        $trick->discards()->create(['cardhand_id' => $cardHand1->id]);

        $trick2 = $round->tricks()->create();
        $expectedCardIds = [$threeOfHearts->id, $queenOfSpades->id, $twoOfClubs->id];
        $playableCards = $playerService->getPlayableCards($trick2, $player);
        $this->assertCount(3, $playableCards);
        foreach ($playableCards as $playableCard)
            $this->assertContains($playableCard->card_id, $expectedCardIds);
    }


    public function testGetPlayableCardsNoLeadingSuitHeartsNotBroken()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks()->create();
        $hand = $round->hands->first();
        $player = $hand->gamePlayer;
        $hand->cardHands()->delete();

        $twoOfDiamonds = Card::where('suit', 'diamonds')->where('rank', '2')->first();
        $queenOfSpades = Card::where('suit', 'spades')->where('rank', 'queen')->first();
        $twoOfClubs = Card::where('suit', 'clubs')->where('rank', '2')->first();
        $threeOfHearts = Card::where('suit', 'hearts')->where('rank', '3')->first();

        $cardHand1 = $hand->cardHands()->create(['card_id' => $twoOfDiamonds->id]);
        $cardHand2 = $hand->cardHands()->create(['card_id' => $queenOfSpades->id]);
        $cardHand3 = $hand->cardHands()->create(['card_id' => $twoOfClubs->id]);
        $cardHand4 = $hand->cardHands()->create(['card_id' => $threeOfHearts->id]);

        $expectedCardIds = [$twoOfClubs->id, $twoOfDiamonds->id];

        $playableCards = $playerService->getPlayableCards($trick, $player);

        $this->assertCount(2, $playableCards);
        foreach ($playableCards as $playableCard) {
            $this->assertContains($playableCard->card_id, $expectedCardIds);
        }
    }

    public function testGetPlayableCardsDoesntHaveLeadingSuitHeartsNotBroken()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();

        // ace of spades as first card
        $hand1 = $round->hands->first();
        $hand1->cardHands()->delete();
        $card = Card::where('suit', 'spades')->where('rank', 'ace')->first();
        $cardhand1 = $hand1->cardHands()->create(['card_id' => $card->id]);
        $trick->discards()->create(['cardhand_id' => $cardhand1->id]);

        $hand2 = $round->hands->get(1);
        $hand2->cardHands()->delete();

        $queenOfHearts = Card::where('suit', 'hearts')->where('rank', 'queen')->first();
        $kingOfClubs = Card::where('suit', 'clubs')->where('rank', 'king')->first();
        $twoOfDiamonds = Card::where('suit', 'diamonds')->where('rank', '2')->first();

        $cardhand1 = $hand2->cardHands()->create(['card_id' => $queenOfHearts->id]);
        $cardhand2 = $hand2->cardHands()->create(['card_id' => $kingOfClubs->id]);
        $cardhand3 = $hand2->cardHands()->create(['card_id' => $twoOfDiamonds->id]);

        $expectedCardIds = [$twoOfDiamonds->id, $kingOfClubs->id];
        $trick2 = $round->tricks()->create();
        $playableCards = $playerService->getPlayableCards($trick2, $hand2->gamePlayer);
        $this->assertCount(2, $playableCards);
        foreach ($playableCards as $playableCard)
            $this->assertContains($playableCard->card_id, $expectedCardIds);
    }

    public function testGetPlayableCardsDoesntHaveLeadingSuitFirstTrick()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();

        $hand1 = $round->hands->first();
        $hand1->cardHands()->delete();
        $card = Card::where('suit', 'spades')->where('rank', 'ace')->first();
        $cardhand1 = $hand1->cardHands()->create(['card_id' => $card->id]);
        $trick->discards()->create(['cardhand_id' => $cardhand1->id]);

        $hand2 = $round->hands->get(1);
        $hand2->cardHands()->delete();

        $queenOfHearts = Card::where('suit', 'hearts')->where('rank', 'queen')->first();
        $kingOfClubs = Card::where('suit', 'clubs')->where('rank', 'king')->first();
        $twoOfDiamonds = Card::where('suit', 'diamonds')->where('rank', '2')->first();

        $cardhand1 = $hand2->cardHands()->create(['card_id' => $queenOfHearts->id]);
        $cardhand2 = $hand2->cardHands()->create(['card_id' => $kingOfClubs->id]);
        $cardhand3 = $hand2->cardHands()->create(['card_id' => $twoOfDiamonds->id]);

        $expectedCardIds = [$kingOfClubs->id, $twoOfDiamonds->id];

        $playableCards = $playerService->getPlayableCards($trick, $hand2->gamePlayer);
        $this->assertCount(2, $playableCards);
        foreach ($playableCards as $playableCard)
            $this->assertContains($playableCard->card_id, $expectedCardIds);
    }

    public function testGetPlayableCardsDoesntHaveLeadingSuitHeartsNotBrokenOnlyHasHeartsAndQueenOfSpades()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();

        // ace of spades as first card
        $hand1 = $round->hands->first();
        $hand1->cardHands()->delete();
        $card = Card::where('suit', 'clubs')->where('rank', 'ace')->first();
        $cardhand1 = $hand1->cardHands()->create(['card_id' => $card->id]);
        $trick->discards()->create(['cardhand_id' => $cardhand1->id]);

        $hand2 = $round->hands->get(1);
        $hand2->cardHands()->delete();

        $queenOfHearts = Card::where('suit', 'spades')->where('rank', 'queen')->first();
        $kingOfHearts = Card::where('suit', 'hearts')->where('rank', 'king')->first();
        $twoOfHearts = Card::where('suit', 'hearts')->where('rank', '2')->first();

        $cardhand1 = $hand2->cardHands()->create(['card_id' => $queenOfHearts->id]);
        $cardhand2 = $hand2->cardHands()->create(['card_id' => $kingOfHearts->id]);
        $cardhand3 = $hand2->cardHands()->create(['card_id' => $twoOfHearts->id]);

        $expectedCardIds = [$twoOfHearts->id, $kingOfHearts->id, $queenOfHearts->id];

        $playableCards = $playerService->getPlayableCards($trick, $hand2->gamePlayer);
        $this->assertCount(3, $playableCards);
        foreach ($playableCards as $playableCard)
            $this->assertContains($playableCard->card_id, $expectedCardIds);
    }



    public function testGetPlayableCardsDoesntHaveLeadingSuitHeartsNotBrokenOnlyHasHearts()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();

        // ace of spades as first card
        $hand1 = $round->hands->first();
        $hand1->cardHands()->delete();
        $card = Card::where('suit', 'clubs')->where('rank', 'ace')->first();
        $cardhand1 = $hand1->cardHands()->create(['card_id' => $card->id]);
        $trick->discards()->create(['cardhand_id' => $cardhand1->id]);

        $hand2 = $hand1;

        $queenOfHearts = Card::where('suit', 'hearts')->where('rank', 'queen')->first();
        $kingOfHearts = Card::where('suit', 'hearts')->where('rank', 'king')->first();
        $twoOfHearts = Card::where('suit', 'hearts')->where('rank', '2')->first();

        $cardhand1 = $hand2->cardHands()->create(['card_id' => $queenOfHearts->id]);
        $cardhand2 = $hand2->cardHands()->create(['card_id' => $kingOfHearts->id]);
        $cardhand3 = $hand2->cardHands()->create(['card_id' => $twoOfHearts->id]);

        $expectedCardIds = [$twoOfHearts->id, $kingOfHearts->id, $queenOfHearts->id];

        $playableCards = $playerService->getPlayableCards($trick, $hand2->gamePlayer);
        $this->assertCount(3, $playableCards);
        foreach ($playableCards as $playableCard)
            $this->assertContains($playableCard->card_id, $expectedCardIds);
    }


    public function testIsValidCard()
    {
        $this->seed(EmptyTrickSeeder::class);
        $playerService = new PlayerService();
        $round = Round::first();
        $trick = $round->tricks->first();
        $hand = $round->hands->first();
        $player = $hand->gamePlayer;
        $hand->cardHands()->delete();

        // Break hearts in first trick
        $twoOfHearts = Card::where('suit', 'hearts')->where('rank', '2')->first();
        $threeOfHearts = Card::where('suit', 'hearts')->where('rank', '3')->first();
        $queenOfSpades = Card::where('suit', 'spades')->where('rank', 'queen')->first();
        $twoOfClubs = Card::where('suit', 'clubs')->where('rank', '2')->first();
        $cardHand1 = $hand->cardHands()->create(['card_id' => $twoOfHearts->id]);
        $cardHand2 = $hand->cardHands()->create(['card_id' => $threeOfHearts->id]);
        $cardHand3 = $hand->cardHands()->create(['card_id' => $queenOfSpades->id]);
        $cardHand4 = $hand->cardHands()->create(['card_id' => $twoOfClubs->id]);
        $trick->discards()->create(['cardhand_id' => $cardHand1->id]);

        $hand2 = $round->hands->get(1);
        $fiveOfDiamonds = Card::where('suit', 'diamonds')->where('rank', '5')->first();
        $cardHand5 = $hand2->cardHands()->create(['card_id' => $fiveOfDiamonds->id]);

        $trick2 = $round->tricks()->create();
        $expectedCardIds = [$threeOfHearts->id, $queenOfSpades->id, $twoOfClubs->id];
        $this->assertFalse($playerService->isValidCard($trick2, $player, $cardHand1));
        $this->assertTrue($playerService->isValidCard($trick2, $player, $cardHand2));
        $this->assertTrue($playerService->isValidCard($trick2, $player, $cardHand3));
        $this->assertTrue($playerService->isValidCard($trick2, $player, $cardHand4));
        $this->assertFalse($playerService->isValidCard($trick2, $player, $cardHand5));

    }
}
