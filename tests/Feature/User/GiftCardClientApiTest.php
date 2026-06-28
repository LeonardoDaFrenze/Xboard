<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardClientApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 0]));
    }

    public function test_check_gift_card_needs_code()
    {
        $this->json('POST', '/api/v1/user/gift-card/check', [
            'code' => '',
        ])->assertStatus(500);
    }

    public function test_fetch_gift_card_types()
    {
        $this->json('GET', '/api/v1/user/gift-card/types')
            ->assertStatus(200);
    }

    public function test_gift_card_history()
    {
        $this->json('GET', '/api/v1/user/gift-card/history')
            ->assertStatus(200);
    }
}
