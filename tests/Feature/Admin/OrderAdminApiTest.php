<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_order_detail()
    {
        $order = Order::factory()->create();
        $this->json('POST', $this->getAdminUri('order/detail'), ['id' => $order->id])
             ->assertStatus(200)
             ->assertJsonPath('data.id', $order->id);
    }

    public function test_fetch_orders()
    {
        Order::factory()->count(5)->create();
        $this->json('GET', $this->getAdminUri('order/fetch'), ['current' => 1, 'pageSize' => 5])
             ->assertStatus(200)
             ->assertJsonPath('current_page', 1);
    }

    public function test_order_paid()
    {
        $order = Order::factory()->create(['status' => 0]);
        $this->json('POST', $this->getAdminUri('order/paid'), ['trade_no' => $order->trade_no])
             ->assertStatus(200);

        $this->assertEquals(Order::STATUS_COMPLETED, $order->fresh()->status);
    }

    public function test_order_cancel()
    {
        $order = Order::factory()->create(['status' => 0]);
        $this->json('POST', $this->getAdminUri('order/cancel'), ['trade_no' => $order->trade_no])
             ->assertStatus(200);

        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_order_update()
    {
        $order = Order::factory()->create();
        $this->json('POST', $this->getAdminUri('order/update'), [
            'trade_no' => $order->trade_no,
            'commission_status' => 1
        ])->assertStatus(200);

        $this->assertEquals(1, $order->fresh()->commission_status);
    }

    public function test_order_assign()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        $this->json('POST', $this->getAdminUri('order/assign'), [
            'email' => $user->email,
            'plan_id' => $plan->id,
            'period' => 'month_price',
            'total_amount' => 100
        ])->assertStatus(200);

        $this->assertDatabaseHas('v2_order', [
            'user_id' => $user->id,
            'plan_id' => $plan->id
        ]);
    }
}
