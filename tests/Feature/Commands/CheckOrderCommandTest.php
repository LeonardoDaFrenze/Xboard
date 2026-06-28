<?php

namespace Tests\Feature\Commands;

use App\Jobs\OrderHandleJob;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CheckOrderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_order_dispatches_jobs_for_pending_and_processing_orders()
    {
        Bus::fake();

        // Create a pending order
        $order1 = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'created_at' => now()->subMinutes(10),
        ]);

        // Create a processing order
        $order2 = Order::factory()->create([
            'status' => Order::STATUS_PROCESSING,
            'created_at' => now()->subMinutes(5),
        ]);

        // Create a completed order (should NOT be processed)
        $order3 = Order::factory()->create([
            'status' => Order::STATUS_COMPLETED,
        ]);

        $this->artisan('check:order')->assertExitCode(0);

        Bus::assertDispatched(OrderHandleJob::class, function ($job) use ($order1) {
            $reflection = new \ReflectionProperty($job, 'tradeNo');
            $reflection->setAccessible(true);
            return $reflection->getValue($job) === $order1->trade_no;
        });

        Bus::assertDispatched(OrderHandleJob::class, function ($job) use ($order2) {
            $reflection = new \ReflectionProperty($job, 'tradeNo');
            $reflection->setAccessible(true);
            return $reflection->getValue($job) === $order2->trade_no;
        });

        Bus::assertNotDispatched(OrderHandleJob::class, function ($job) use ($order3) {
            $reflection = new \ReflectionProperty($job, 'tradeNo');
            $reflection->setAccessible(true);
            return $reflection->getValue($job) === $order3->trade_no;
        });
    }
}
