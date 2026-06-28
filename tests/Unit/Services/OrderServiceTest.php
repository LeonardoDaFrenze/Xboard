<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_service_can_be_instantiated(): void
    {
        $order = Order::factory()->create();

        $service = new OrderService($order);

        $this->assertNotNull($service);
        $this->assertEquals($order->id, $service->order->id);
    }

    public function test_order_can_be_cancelled(): void
    {
        $order = Order::factory()->create(['status' => 0]);
        $service = new OrderService($order);

        $service->cancel();

        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_order_can_be_set_to_paid(): void
    {
        $order = Order::factory()->create(['status' => 0]);
        $service = new OrderService($order);

        $result = $service->paid('callback_123');

        $this->assertIsBool($result);
    }

    public function test_create_from_request_creates_order(): void
    {
        $user = User::factory()->create(['balance' => 0]);
        $plan = Plan::factory()->create([
            'prices' => [Plan::PERIOD_MONTHLY => 1000],
        ]);

        $order = OrderService::createFromRequest($user, $plan, Plan::PERIOD_MONTHLY);

        $this->assertDatabaseHas('v2_order', [
            'id' => $order->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);
        $this->assertEquals(100000, $order->total_amount);
    }

    public function test_order_assigns_user_service(): void
    {
        $user = User::factory()->create(['balance' => 99999]);
        $plan = Plan::factory()->create([
            'prices' => [Plan::PERIOD_MONTHLY => 500],
        ]);

        $order = OrderService::createFromRequest($user, $plan, Plan::PERIOD_MONTHLY);

        $this->assertNotNull($order);
    }
}
