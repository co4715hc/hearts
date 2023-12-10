<?php

namespace Services;

use App\Models\Card;
use App\Services\CardService;
use CardsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;


class CardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CardsTableSeeder::class);
    }

    public function testConstructor(): void
    {
        new CardService();
        $this->assertTrue(true);
    }

    public function testDrawCards(): void
    {
        $cardService = new CardService();
        $cards1 = $cardService->drawCards(13);
        $this->assertCount(13, $cards1);
        $cards0 = $cardService->drawCards(0);
        $this->assertCount(0, $cards0);
        $cards2 = $cardService->drawCards(14);
        $this->assertCount(14, $cards2);
        $cards3 = $cardService->drawCards(12);
        $this->assertCount(12, $cards3);
        $cards4 = $cardService->drawCards(20);
        $this->assertCount(13, $cards4);

        $allCards = array_merge(
            $cards1,
            $cards2,
            $cards3,
            $cards4);
        $uniqueCards = array_unique($allCards);
        $this->assertCount(52, $uniqueCards);
        $this->assertCount(52, $allCards);
    }

    public static function cardValueProvider(): array
    {
        return [
            ['2', 2],
            ['3', 3],
            ['4', 4],
            ['5', 5],
            ['6', 6],
            ['7', 7],
            ['8', 8],
            ['9', 9],
            ['10', 10],
            ['jack', 11],
            ['queen', 12],
            ['king', 13],
            ['ace', 14],
        ];
    }

    /**
     * @dataProvider cardValueProvider
     */
    public function testGetCardValue(string $rank, int $expectedValue): void
    {
        $cardService = new CardService();
        $card = Mockery::mock(Card::class);
        $card->shouldReceive('getAttribute')
            ->with('rank')
            ->andReturn($rank);
        $this->assertEquals($expectedValue, $cardService->getCardValue($card));
    }

    public static function cardPointValueProvider(): array
    {
        return [
            ['2', 'clubs', 0],
            ['ace', 'diamonds', 0],
            ['queen', 'diamonds', 0],
            ['queen', 'hearts', 1],
            ['2', 'hearts', 1],
            ['queen', 'spades', 13],
            ['ace', 'spades', 0],
            ['jack', 'diamonds', 0],
            ['jack', 'hearts', 1]
        ];
    }

    /**
     * @dataProvider cardPointValueProvider
     */
    public function testGetCardPointValue(string $rank, string $suit, int $expectedValue): void
    {
        $cardService = new CardService();

        $card = Mockery::mock(Card::class);
        $card->shouldReceive('getAttribute')
            ->with('rank')
            ->andReturn($rank);
        $card->shouldReceive('getAttribute')
            ->with('suit')
            ->andReturn($suit);

        $this->assertEquals($expectedValue, $cardService->getCardPointValue($card));
    }
}
