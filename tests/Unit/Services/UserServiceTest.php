<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserService::class);
    }

    public function test_is_available_returns_true_for_active_user(): void
    {
        $user = User::factory()->create([
            'banned' => 0,
            'transfer_enable' => 1073741824,
            'expired_at' => time() + 86400,
        ]);

        $this->assertTrue($this->service->isAvailable($user));
    }

    public function test_is_available_returns_false_for_banned_user(): void
    {
        $user = User::factory()->create([
            'banned' => 1,
            'transfer_enable' => 1073741824,
            'expired_at' => time() + 86400,
        ]);

        $this->assertFalse($this->service->isAvailable($user));
    }

    public function test_is_available_returns_false_for_expired_user(): void
    {
        $user = User::factory()->create([
            'banned' => 0,
            'transfer_enable' => 1073741824,
            'expired_at' => time() - 86400,
        ]);

        $this->assertFalse($this->service->isAvailable($user));
    }

    public function test_is_not_complete_order_returns_true_when_pending(): void
    {
        $user = User::factory()->create();
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 0,
        ]);

        $this->assertTrue($this->service->isNotCompleteOrderByUserId($user->id));
    }

    public function test_is_not_complete_order_returns_false_when_none(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->service->isNotCompleteOrderByUserId($user->id));
    }

    public function test_add_balance_increases_balance(): void
    {
        $user = User::factory()->create(['balance' => 100]);

        $result = $this->service->addBalance($user->id, 500);

        $this->assertTrue($result);
        $this->assertEquals(600, $user->fresh()->balance);
    }

    public function test_add_balance_returns_false_when_negative(): void
    {
        $user = User::factory()->create(['balance' => 100]);

        $result = $this->service->addBalance($user->id, -200);

        $this->assertFalse($result);
    }

    public function test_get_reset_day_returns_int_or_null(): void
    {
        $user = User::factory()->create();

        $days = $this->service->getResetDay($user);

        $this->assertTrue($days === null || is_int($days));
    }

    public function test_get_available_users_returns_only_available(): void
    {
        User::factory()->create([
            'banned' => 0,
            'transfer_enable' => 1073741824,
            'expired_at' => time() + 86400,
            'u' => 0,
            'd' => 0,
        ]);

        $users = $this->service->getAvailableUsers();

        $this->assertCount(1, $users);
    }

    public function test_create_user_creates_with_defaults(): void
    {
        $user = $this->service->createUser([
            'email' => 'newuser@example.com',
            'expired_at' => time() + 86400 * 30,
        ]);

        $this->assertEquals('newuser@example.com', $user->email);
        $this->assertNotNull($user->uuid);
        $this->assertNotNull($user->token);
    }

    public function test_assign_plan_assigns_plan_to_user(): void
    {
        $user = User::factory()->create(['plan_id' => null]);
        $plan = Plan::factory()->create([
            'transfer_enable' => 10,
            'speed_limit' => 100,
            'device_limit' => 3,
        ]);

        $result = $this->service->assignPlan($user, $plan, 30);

        $this->assertEquals($plan->id, $result->plan_id);
        $this->assertEquals($plan->group_id, $result->group_id);
    }

    public function test_extend_subscription_extends_expiry(): void
    {
        $user = User::factory()->create(['expired_at' => time()]);

        $result = $this->service->extendSubscription($user, 30);

        $this->assertGreaterThan(time(), $result->expired_at);
    }
}
