<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class CheckTrafficExceededCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifies_users_when_traffic_threshold_exceeded()
    {
        $plan = Plan::factory()->create();
        
        $user = User::factory()->create([
            'plan_id' => $plan->id,
            'group_id' => 1,
            'transfer_enable' => 100 * 1024 * 1024 * 1024, // 100 GB
            'u' => 50 * 1024 * 1024 * 1024, // 50 GB
            'd' => 55 * 1024 * 1024 * 1024, // 55 GB (Total 105 GB, which is >= transfer_enable)
            'remind_traffic' => 1,
            'banned' => 0,
        ]);

        $server = Server::factory()->create([
            'group_ids' => ['1'],
        ]);

        // Mock NodeSyncService isNodeOnline check
        Cache::put("node_ws_alive:{$server->id}", true);

        // Mock Redis calls inside the command CheckTrafficExceeded
        Redis::shouldReceive('scard')
            ->once()
            ->with('traffic:pending_check')
            ->andReturn(1);

        Redis::shouldReceive('spop')
            ->once()
            ->with('traffic:pending_check', 1)
            ->andReturn([$user->id]);

        Redis::shouldReceive('publish')
            ->once()
            ->with('node:push', \Mockery::on(function ($argument) use ($user, $server) {
                $decoded = json_decode($argument, true);
                return $decoded['node_id'] === $server->id &&
                       $decoded['event'] === 'sync.user.delta' &&
                       $decoded['data']['action'] === 'remove' &&
                       $decoded['data']['users'][0]['id'] === $user->id;
            }))
            ->andReturn(1);

        $exitCode = Artisan::call('check:traffic-exceeded');

        $this->assertEquals(0, $exitCode);
    }
}
