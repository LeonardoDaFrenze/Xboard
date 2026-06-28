<?php

namespace Tests\Unit\Services;

use App\Models\Plan;
use App\Models\User;
use App\Services\PlanService;
use App\Exceptions\ApiException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_service_can_be_instantiated(): void
    {
        $plan = Plan::factory()->create();
        $service = new PlanService($plan);

        $this->assertNotNull($service);
    }

    public function test_get_available_plans_returns_visible_sellable(): void
    {
        Plan::factory()->create(['show' => 1, 'sell' => 1, 'capacity_limit' => null]);
        Plan::factory()->create(['show' => 0, 'sell' => 1]);

        $plan = Plan::factory()->create(['show' => 1, 'sell' => 0]);
        $service = new PlanService($plan);

        $available = $service->getAvailablePlans();

        $this->assertCount(1, $available);
    }

    public function test_has_capacity_returns_true_when_no_limit(): void
    {
        $plan = Plan::factory()->create(['capacity_limit' => null]);
        $service = new PlanService($plan);

        $this->assertTrue($service->hasCapacity($plan));
    }

    public function test_has_capacity_returns_false_when_full(): void
    {
        $plan = Plan::factory()->create(['capacity_limit' => 1]);
        User::factory()->create([
            'plan_id' => $plan->id,
            'expired_at' => time() + 86400,
        ]);

        $service = new PlanService($plan);

        $this->assertFalse($service->hasCapacity($plan));
    }

    public function test_get_period_key_converts_legacy_format(): void
    {
        $key = PlanService::getPeriodKey('month_price');

        $this->assertEquals(Plan::PERIOD_MONTHLY, $key);
    }

    public function test_get_period_key_preserves_new_format(): void
    {
        $key = PlanService::getPeriodKey(Plan::PERIOD_YEARLY);

        $this->assertEquals(Plan::PERIOD_YEARLY, $key);
    }

    public function test_validate_purchase_throws_for_nonexistent_period(): void
    {
        $plan = Plan::factory()->create(['prices' => []]);
        $user = User::factory()->create();

        $service = new PlanService($plan);

        $this->expectException(ApiException::class);
        $service->validatePurchase($user, 'invalid_period');
    }

    public function test_can_reset_traffic(): void
    {
        $plan = Plan::factory()->create([
            'reset_traffic_method' => Plan::RESET_TRAFFIC_MONTHLY,
            'prices' => [Plan::PRICE_TYPE_RESET_TRAFFIC => 500],
        ]);

        $service = new PlanService($plan);

        $this->assertTrue($service->canResetTraffic($plan));
    }

    public function test_is_plan_available_for_user(): void
    {
        $plan = Plan::factory()->create(['show' => 1, 'sell' => 1, 'renew' => 1]);
        $user = User::factory()->create();
        $service = new PlanService($plan);

        $this->assertTrue($service->isPlanAvailableForUser($plan, $user));
    }
}
