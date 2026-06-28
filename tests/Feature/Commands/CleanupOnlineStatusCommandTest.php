<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CleanupOnlineStatusCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_online_status_resets_stale_users()
    {
        Carbon::setTestNow(now());

        // Stale user 1: online_count > 0, last_online_at > 10 mins ago
        $staleUser1 = User::factory()->create([
            'online_count' => 5,
            'last_online_at' => now()->subMinutes(15),
        ]);

        // Stale user 2: online_count > 0, last_online_at is null
        $staleUser2 = User::factory()->create([
            'online_count' => 3,
            'last_online_at' => null,
        ]);

        // Active user: online_count > 0, last_online_at < 10 mins ago
        $activeUser = User::factory()->create([
            'online_count' => 2,
            'last_online_at' => now()->subMinutes(5),
        ]);

        // Unaffected user: online_count already 0
        $unaffectedUser = User::factory()->create([
            'online_count' => 0,
            'last_online_at' => now()->subMinutes(20),
        ]);

        $this->artisan('cleanup:online-status')
            ->expectsOutputToContain('Reset online_count for 2 stale users.')
            ->assertExitCode(0);

        $this->assertEquals(0, $staleUser1->fresh()->online_count);
        $this->assertEquals(0, $staleUser2->fresh()->online_count);
        $this->assertEquals(2, $activeUser->fresh()->online_count);
        $this->assertEquals(0, $unaffectedUser->fresh()->online_count);
    }

    public function test_cleanup_online_status_with_no_stale_users_produces_no_output()
    {
        Carbon::setTestNow(now());

        User::factory()->create([
            'online_count' => 1,
            'last_online_at' => now()->subMinutes(2),
        ]);

        $this->artisan('cleanup:online-status')
            ->doesntExpectOutputToContain('Reset online_count for')
            ->assertExitCode(0);
    }
}
