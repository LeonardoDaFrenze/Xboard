<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\GiftCardTemplate;
use App\Models\GiftCardCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GiftCardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_redeem_gift_card()
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);

        $template = GiftCardTemplate::create([
            'name' => '50 Balance Card',
            'type' => GiftCardTemplate::TYPE_GENERAL,
            'status' => 1,
            'rewards' => [
                'balance' => 5000,
            ],
            'admin_id' => 1,
        ]);
        
        $giftCard = GiftCardCode::create([
            'template_id' => $template->id,
            'code' => 'GIFTCARD5000', // Must be alphanumeric capitalized, min length 8
            'status' => GiftCardCode::STATUS_UNUSED,
            'max_usage' => 1,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/user/gift-card/redeem', [
            'code' => 'GIFTCARD5000'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('v2_user', [
            'id' => $user->id,
            'balance' => 5000,
        ]);

        $this->assertDatabaseHas('v2_gift_card_code', [
            'id' => $giftCard->id,
            'status' => GiftCardCode::STATUS_USED,
        ]);
    }

    public function test_user_cannot_redeem_used_gift_card()
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);
        
        $template = GiftCardTemplate::create([
            'name' => '50 Balance Card',
            'type' => GiftCardTemplate::TYPE_GENERAL,
            'status' => 1,
            'rewards' => [
                'balance' => 5000,
            ],
            'admin_id' => 1,
        ]);

        $giftCard = GiftCardCode::create([
            'template_id' => $template->id,
            'code' => 'GIFTCARDUSED', // Must be alphanumeric capitalized, min length 8
            'status' => GiftCardCode::STATUS_USED,
            'max_usage' => 1,
            'usage_count' => 1,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/user/gift-card/redeem', [
            'code' => 'GIFTCARDUSED'
        ]);

        $response->assertStatus(400);
        
        $this->assertDatabaseHas('v2_user', [
            'id' => $user->id,
            'balance' => 0,
        ]);
    }
}
