<?php

namespace Tests\Unit\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\CouponService;
use App\Exceptions\ApiException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_service_can_be_instantiated(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'TEST2024']);

        $service = new CouponService('TEST2024');

        $this->assertNotNull($service->getCoupon());
        $this->assertEquals($coupon->id, $service->getId());
    }

    public function test_coupon_service_returns_null_for_invalid_code(): void
    {
        $service = new CouponService('INVALID_CODE');

        $this->assertNull($service->getCoupon());
    }

    public function test_check_throws_for_invisible_coupon(): void
    {
        Coupon::factory()->create([
            'code' => 'HIDDEN',
            'show' => 0,
        ]);

        $this->expectException(ApiException::class);

        $service = new CouponService('HIDDEN');
        $service->check();
    }

    public function test_check_throws_for_expired_coupon(): void
    {
        Coupon::factory()->create([
            'code' => 'EXPIRED',
            'show' => 1,
            'started_at' => time() - 86400,
            'ended_at' => time() - 3600,
        ]);

        $this->expectException(ApiException::class);

        $service = new CouponService('EXPIRED');
        $service->check();
    }

    public function test_check_throws_for_not_started_coupon(): void
    {
        Coupon::factory()->create([
            'code' => 'FUTURE',
            'show' => 1,
            'started_at' => time() + 86400,
            'ended_at' => time() + 172800,
        ]);

        $this->expectException(ApiException::class);

        $service = new CouponService('FUTURE');
        $service->check();
    }

    public function test_use_applies_fixed_discount(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'FIXED50',
            'type' => 1,
            'value' => 500,
            'show' => 1,
            'started_at' => time() - 86400,
            'ended_at' => time() + 86400,
        ]);

        $plan = Plan::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $user->id,
            'total_amount' => 2000,
            'discount_amount' => 0,
            'period' => 'month_price',
        ]);

        $service = new CouponService('FIXED50');
        $service->setPlanId($plan->id);
        $service->setUserId($user->id);
        $service->setPeriod($order->period);

        $result = $service->use($order);

        $this->assertTrue($result);
        $this->assertEquals(500, $order->discount_amount);
    }

    public function test_use_applies_percentage_discount(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'PCT20',
            'type' => 2,
            'value' => 20,
            'show' => 1,
            'started_at' => time() - 86400,
            'ended_at' => time() + 86400,
        ]);

        $plan = Plan::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $user->id,
            'total_amount' => 2000,
            'discount_amount' => 0,
            'period' => 'month_price',
        ]);

        $service = new CouponService('PCT20');
        $service->setPlanId($plan->id);
        $service->setUserId($user->id);
        $service->setPeriod($order->period);

        $result = $service->use($order);

        $this->assertTrue($result);
        $this->assertEquals(400, $order->discount_amount);
    }

    public function test_discount_does_not_exceed_total(): void
    {
        Coupon::factory()->create([
            'code' => 'BIG100',
            'type' => 1,
            'value' => 9999,
            'show' => 1,
            'started_at' => time() - 86400,
            'ended_at' => time() + 86400,
        ]);

        $plan = Plan::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $user->id,
            'total_amount' => 1500,
            'discount_amount' => 0,
            'period' => 'month_price',
        ]);

        $service = new CouponService('BIG100');
        $service->setPlanId($plan->id);
        $service->setUserId($user->id);
        $service->setPeriod($order->period);

        $service->use($order);

        $this->assertEquals(1500, $order->discount_amount);
    }
}
