<?php

namespace Tests\Unit\Models;

use App\Models\GiftCardCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardCodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a gift card code can be created successfully.
     *
     * @return void
     */
    public function test_gift_card_code_creation_is_successful(): void
    {
        $giftCard = GiftCardCode::factory()->create([
            'code' => 'GIFT-2026-XYZ',
            'status' => 0,
        ]);

        $this->assertDatabaseHas('v2_gift_card_code', [
            'id' => $giftCard->id,
            'code' => 'GIFT-2026-XYZ',
            'status' => 0,
        ]);

        $this->assertInstanceOf(GiftCardCode::class, $giftCard);
    }

    /**
     * Test that a gift card code can be updated to used status.
     *
     * @return void
     */
    public function test_gift_card_code_can_be_marked_as_used(): void
    {
        $giftCard = GiftCardCode::factory()->create([
            'status' => 0,
        ]);

        $giftCard->update([
            'status' => 1,
            'used_at' => time(),
        ]);

        $this->assertDatabaseHas('v2_gift_card_code', [
            'id' => $giftCard->id,
            'status' => 1,
        ]);
        
        $this->assertNotNull($giftCard->fresh()->used_at);
    }

    /**
     * Test that a gift card code can be deleted.
     *
     * @return void
     */
    public function test_gift_card_code_can_be_deleted(): void
    {
        $giftCard = GiftCardCode::factory()->create();
        $cardId = $giftCard->id;

        $giftCard->delete();

        $this->assertDatabaseMissing('v2_gift_card_code', [
            'id' => $cardId,
        ]);
    }
}
