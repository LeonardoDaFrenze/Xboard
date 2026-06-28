<?php

namespace Tests\Feature\User;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderClientApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 0]));
    }

    public function test_fetch_user_orders()
    {
        $this->json('GET', '/api/v1/user/order/fetch')
            ->assertStatus(200);
    }

    public function test_create_order_fails_without_plan_id()
    {
        $this->json('POST', '/api/v1/user/order/save', [])
            ->assertStatus(422);
    }

    public function test_get_payment_method()
    {
        $this->json('GET', '/api/v1/user/order/getPaymentMethod')
            ->assertStatus(200);
    }
}
