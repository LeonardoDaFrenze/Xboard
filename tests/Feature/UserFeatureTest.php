<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_traffic_reset_feature()
    {
        $plan = Plan::factory()->create([
            'reset_traffic_method' => Plan::RESET_TRAFFIC_MONTHLY,
        ]);

        $user = User::factory()->create([
            'plan_id' => $plan->id,
            'u' => 5000,
            'd' => 10000,
            'transfer_enable' => 100000,
            'banned' => 0,
            'expired_at' => time() + 86400,
            'reset_count' => 0,
        ]);

        $user->next_reset_at = time() - 100;
        $user->save();

        $this->assertTrue($user->shouldResetTraffic());

        $user->update([
            'u' => 0,
            'd' => 0,
            'last_reset_at' => time(),
            'next_reset_at' => time() + (30 * 24 * 60 * 60),
            'reset_count' => $user->reset_count + 1,
        ]);

        $this->assertDatabaseHas('v2_user', [
            'id' => $user->id,
            'u' => 0,
            'd' => 0,
            'reset_count' => 1,
        ]);
        
        $this->assertEquals(1, $user->fresh()->reset_count);
        $this->assertFalse($user->fresh()->shouldResetTraffic());
    }

    public function test_user_banned_cannot_use_service()
    {
        $plan = Plan::factory()->create();

        $user = User::factory()->create([
            'plan_id' => $plan->id,
            'u' => 0,
            'd' => 0,
            'transfer_enable' => 100000,
            'banned' => 1,
            'expired_at' => time() + 86400,
        ]);

        $this->assertFalse($user->isActive());
        $this->assertFalse($user->isAvailable());
        $this->assertFalse($user->shouldResetTraffic());
    }
}
