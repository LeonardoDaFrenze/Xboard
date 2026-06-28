<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user can be created.
     *
     * @return void
     */
    public function test_user_creation_is_successful(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('secret123'),
            'balance' => 0,
            'transfer_enable' => 107374182400, // 100GB
            'u' => 0,
            'd' => 0,
        ]);

        $this->assertDatabaseHas('v2_user', [
            'id' => $user->id,
            'email' => 'testuser@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * Test user active subscription plan relation.
     *
     * @return void
     */
    public function test_user_belongs_to_plan(): void
    {
        $plan = Plan::factory()->create([
            'name' => 'Basic Plan',
        ]);

        $user = User::factory()->create([
            'plan_id' => $plan->id,
        ]);

        $this->assertEquals($plan->id, $user->plan_id);
    }

    /**
     * Test user attribute casting.
     *
     * @return void
     */
    public function test_user_casts_are_applied(): void
    {
        $user = User::factory()->create([
            'is_admin' => 1,
            'is_staff' => 0,
            'banned' => 0,
        ]);

        $this->assertIsBool($user->is_admin);
        $this->assertTrue($user->is_admin);
        $this->assertIsBool($user->is_staff);
        $this->assertFalse($user->is_staff);
        $this->assertIsBool($user->banned);
        $this->assertFalse($user->banned);
    }

    /**
     * Test user traffic logic.
     *
     * @return void
     */
    public function test_user_traffic_calculation(): void
    {
        $user = User::factory()->create([
            'u' => 1024,
            'd' => 2048,
        ]);

        $totalTraffic = $user->u + $user->d;
        $this->assertEquals(3072, $totalTraffic);
    }
}
