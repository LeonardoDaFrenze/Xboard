<?php

namespace Tests\Unit\Models;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test basic plan creation and attribute casts.
     *
     * @return void
     */
    public function test_plan_creation_and_casts(): void
    {
        $plan = Plan::factory()->create([
            'name' => 'Premium Plan',
            'tags' => ['VIP', 'High Speed'],
            'show' => 1,
            'renew' => 0,
        ]);

        $this->assertDatabaseHas('v2_plan', [
            'id' => $plan->id,
            'name' => 'Premium Plan',
        ]);

        $this->assertIsArray($plan->tags);
        $this->assertContains('VIP', $plan->tags);
        $this->assertTrue($plan->show);
        $this->assertFalse($plan->renew);
        $this->assertIsArray($plan->prices);
    }

    /**
     * Test that static methods return correct configuration data.
     *
     * @return void
     */
    public function test_static_methods_return_correct_data(): void
    {
        $resetMethods = Plan::getResetTrafficMethods();
        $this->assertIsArray($resetMethods);
        $this->assertArrayHasKey(Plan::RESET_TRAFFIC_MONTHLY, $resetMethods);

        $availablePeriods = Plan::getAvailablePeriods();
        $this->assertIsArray($availablePeriods);
        $this->assertArrayHasKey(Plan::PERIOD_MONTHLY, $availablePeriods);

        $this->assertEquals(30, Plan::getPeriodDays(Plan::PERIOD_MONTHLY));
        $this->assertEquals(365, Plan::getPeriodDays(Plan::PERIOD_YEARLY));

        $this->assertTrue(Plan::isValidPeriod(Plan::PERIOD_HALF_YEARLY));
        $this->assertFalse(Plan::isValidPeriod('invalid_period'));
    }

    /**
     * Test plan price manipulation methods.
     *
     * @return void
     */
    public function test_price_manipulation(): void
    {
        $plan = Plan::factory()->create(['prices' => []]);

        $plan->setPeriodPrice(Plan::PERIOD_MONTHLY, 1500);
        $this->assertEquals(1500, $plan->getPriceByPeriod(Plan::PERIOD_MONTHLY));

        $plan->setPeriodPrice(Plan::PERIOD_YEARLY, 15000);
        $activePeriods = $plan->getActivePeriods();
        
        $this->assertArrayHasKey(Plan::PERIOD_MONTHLY, $activePeriods);
        $this->assertArrayHasKey(Plan::PERIOD_YEARLY, $activePeriods);
        $this->assertArrayNotHasKey(Plan::PERIOD_QUARTERLY, $activePeriods);

        $priceList = $plan->getPriceList();
        $this->assertArrayHasKey(Plan::PERIOD_YEARLY, $priceList);
        $this->assertEquals(15000, $priceList[Plan::PERIOD_YEARLY]['price']);

        $plan->removePeriodPrice(Plan::PERIOD_MONTHLY);
        $this->assertNull($plan->getPriceByPeriod(Plan::PERIOD_MONTHLY));
    }

    /**
     * Test that setting an invalid period price throws an exception.
     *
     * @return void
     */
    public function test_invalid_period_throws_exception(): void
    {
        $plan = Plan::factory()->create();
        
        $this->expectException(InvalidArgumentException::class);
        $plan->setPeriodPrice('invalid_period', 100);
    }

    /**
     * Test that getting days for an invalid period throws an exception.
     *
     * @return void
     */
    public function test_get_invalid_period_days_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Plan::getPeriodDays('invalid_period');
    }

    /**
     * Test traffic reset logic and methods.
     *
     * @return void
     */
    public function test_traffic_reset_methods(): void
    {
        $plan = Plan::factory()->create([
            'reset_traffic_method' => Plan::RESET_TRAFFIC_NEVER,
            'prices' => [Plan::PRICE_TYPE_RESET_TRAFFIC => 0]
        ]);

        $this->assertFalse($plan->canResetTraffic());

        $plan->setResetTrafficMethod(Plan::RESET_TRAFFIC_MONTHLY);
        $plan->setResetTrafficPrice(500);

        $this->assertTrue($plan->canResetTraffic());
        $this->assertEquals(500, $plan->getResetTrafficPrice());
        $this->assertEquals(Plan::RESET_TRAFFIC_MONTHLY, $plan->reset_traffic_method);
    }

    /**
     * Test that setting an invalid traffic reset method throws an exception.
     *
     * @return void
     */
    public function test_invalid_traffic_reset_method_throws_exception(): void
    {
        $plan = Plan::factory()->create();
        
        $this->expectException(InvalidArgumentException::class);
        $plan->setResetTrafficMethod(999);
    }
}
