<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an order can be created successfully.
     *
     * @return void
     */
    public function test_order_creation_is_successful(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'period' => 'month_price',
            'trade_no' => 'TRD' . time() . rand(1000, 9999),
            'total_amount' => 1500,
            'status' => 0,
        ]);

        $this->assertDatabaseHas('v2_order', [
            'id' => $order->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'total_amount' => 1500,
            'status' => 0,
        ]);

        $this->assertInstanceOf(Order::class, $order);
    }

    /**
     * Test that an order belongs to a user.
     *
     * @return void
     */
    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $order->user_id);
    }

    /**
     * Test that an order can be updated to paid status.
     *
     * @return void
     */
    public function test_order_can_be_marked_as_paid(): void
    {
        $order = Order::factory()->create([
            'status' => 0,
        ]);

        $order->update([
            'status' => 1,
            'paid_at' => time(),
        ]);

        $this->assertDatabaseHas('v2_order', [
            'id' => $order->id,
            'status' => 1,
        ]);
        
        $this->assertNotNull($order->fresh()->paid_at);
    }

    /**
     * Test that an order can be deleted.
     *
     * @return void
     */
    public function test_order_can_be_deleted(): void
    {
        $order = Order::factory()->create();

        $orderId = $order->id;

        $order->delete();

        $this->assertDatabaseMissing('v2_order', [
            'id' => $orderId,
        ]);
    }
}
