<?php

namespace Tests\Unit\Models;

use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CardModelTest extends TestCase
{
    use RefreshDatabase;

    public function testCardCreation()
    {
        $card = factory(Card::class)->create();
        $this->assertInstanceOf(Card::class, $card);
        $this->assertDatabaseHas('cards', ['id' => $card->id]);
        $this->assertDatabaseHas('cards', ['suit' => $card->suit]);
        $this->assertDatabaseHas('cards', ['rank' => $card->rank]);
        $count = DB::table('cards')->count();
        $this->assertEquals(1, $count);
    }

    public function testCreateManyCards()
    {
        $cards = [];
        for ($i = 0; $i < 52; $i++) {
            $card = factory(Card::class)->create();
            $cards[] = $card;
        }
        $this->assertInstanceOf(Card::class, $cards[9]);
        $this->assertDatabaseHas('cards', ['id' => $cards[9]->id]);
        $this->assertDatabaseHas('cards', ['suit' => $cards[9]->suit]);
        $this->assertDatabaseHas('cards', ['rank' => $cards[9]->rank]);
        $count = DB::table('cards')->count();
        $this->assertEquals(52, $count);
        $cardArray = array_map(function ($card) {
            return $card['suit'] . $card['rank'];
        }, $cards);
        $this->assertCount(52, array_unique($cardArray));
    }

    public function testCreateTooManyCards()
    {
        $this->expectException(\Exception::class);
        for ($i = 0; $i < 53; $i++) {
            $card = factory(Card::class)->create();
        }
    }

    public function testCardAttributes()
    {
        $card = factory(Card::class)->create();
        $this->assertIsInt($card->id);
        $this->assertIsString($card->suit);
        $this->assertIsString($card->rank);
        $this->assertIsInt($card->value);
    }

    public function testCardToString()
    {
        $card = factory(Card::class)->create(['suit' => 'hearts', 'rank' => 'ace']);
        $expected = "Card(suit=hearts, rank=ace)";
        $this->assertEquals($expected, $card->__toString());
    }

    public function testIsTwoOfClubs()
    {
        $card = factory(Card::class)->create(['suit' => 'clubs', 'rank' => '2']);
        $this->assertTrue($card->isTwoOfClubs());
    }

    public function testIsNotTwoOfClubs()
    {
        $card = factory(Card::class)->create(['suit' => 'clubs', 'rank' => '3']);
        $this->assertFalse($card->isTwoOfClubs());
    }
}
