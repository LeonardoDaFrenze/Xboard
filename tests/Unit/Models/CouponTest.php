<?php

namespace Tests\Unit\Models;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test basic coupon creation.
     *
     * @return void
     */
    public function test_coupon_creation_is_successful(): void
    {
        $coupon = Coupon::factory()->create([
            'name' => 'Black Friday',
            'code' => 'BF2026',
            'type' => 1,
            'value' => 500,
        ]);

        $this->assertDatabaseHas('v2_coupon', [
            'id' => $coupon->id,
            'code' => 'BF2026',
            'type' => 1,
            'value' => 500,
        ]);

        $this->assertInstanceOf(Coupon::class, $coupon);
    }

    /**
     * Test coupon factory states for percentage and expired.
     *
     * @return void
     */
    public function test_coupon_factory_states(): void
    {
        $percentageCoupon = Coupon::factory()->percentage()->create();
        $this->assertEquals(2, $percentageCoupon->type);
        $this->assertEquals(20, $percentageCoupon->value);

        $expiredCoupon = Coupon::factory()->expired()->create();
        $this->assertLessThan(time(), $expiredCoupon->ended_at);
    }

    /**
     * Test coupon casts are correctly applied.
     *
     * @return void
     */
    public function test_coupon_casts(): void
    {
        $coupon = Coupon::factory()->create([
            'limit_plan_ids' => [10, 11, 12],
            'show' => 1,
        ]);

        $this->assertIsArray($coupon->limit_plan_ids);
        $this->assertEquals([10, 11, 12], $coupon->limit_plan_ids);
        $this->assertIsBool($coupon->show);
        $this->assertTrue($coupon->show);
    }

    /**
     * Test empty limit period accessor resolves to empty array safely.
     *
     * @return void
     */
    public function test_coupon_empty_limit_period_accessor(): void
    {
        $coupon = Coupon::factory()->create([
            'limit_period' => null,
        ]);

        $this->assertIsArray($coupon->limit_period);
        $this->assertEmpty($coupon->limit_period);
    }
}
