<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_clear_user_deletes_users_matching_criteria()
    {
        $past = time() - 86400; // 1 day ago
        $future = time() + 86400; // 1 day in the future

        // Target user 1 (should be deleted)
        $user1 = User::factory()->create([
            'plan_id' => null,
            'transfer_enable' => 0,
            'expired_at' => $past,
            'last_login_at' => null,
        ]);

        // Target user 2 (should be deleted)
        $user2 = User::factory()->create([
            'plan_id' => null,
            'transfer_enable' => 0,
            'expired_at' => $past - 3600,
            'last_login_at' => null,
        ]);

        // User with a plan (should stay)
        $user3 = User::factory()->create([
            'plan_id' => 1,
            'transfer_enable' => 0,
            'expired_at' => $past,
            'last_login_at' => null,
        ]);

        // User with transfer_enable > 0 (should stay)
        $user4 = User::factory()->create([
            'plan_id' => null,
            'transfer_enable' => 1024,
            'expired_at' => $past,
            'last_login_at' => null,
        ]);

        // User not expired (should stay)
        $user5 = User::factory()->create([
            'plan_id' => null,
            'transfer_enable' => 0,
            'expired_at' => $future,
            'last_login_at' => null,
        ]);

        // User logged in before (should stay)
        $user6 = User::factory()->create([
            'plan_id' => null,
            'transfer_enable' => 0,
            'expired_at' => $past,
            'last_login_at' => $past,
        ]);

        // User with expired_at 0 (should stay)
        $user7 = User::factory()->create([
            'plan_id' => null,
            'transfer_enable' => 0,
            'expired_at' => 0,
            'last_login_at' => null,
        ]);
        
        // User with expired_at null (should stay)
        $user8 = User::factory()->create([
            'plan_id' => null,
            'transfer_enable' => 0,
            'expired_at' => null,
            'last_login_at' => null,
        ]);

        $this->artisan('clear:user')
            ->expectsOutputToContain('已删除2位没有任何数据的用户')
            ->assertExitCode(0);

        $this->assertNull(User::find($user1->id));
        $this->assertNull(User::find($user2->id));
        
        $this->assertNotNull(User::find($user3->id));
        $this->assertNotNull(User::find($user4->id));
        $this->assertNotNull(User::find($user5->id));
        $this->assertNotNull(User::find($user6->id));
        $this->assertNotNull(User::find($user7->id));
        $this->assertNotNull(User::find($user8->id));
    }
}
