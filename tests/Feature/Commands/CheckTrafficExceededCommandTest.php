<?php

namespace Tests\Feature\Commands;

use App\Models\Server;
use App\Models\User;
use App\Services\NodeSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class CheckTrafficExceededCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    public function test_check_traffic_exceeded_notifies_nodes_for_exceeded_users()
    {
        // 1. Setup Data
        $exceededUser = User::factory()->create([
            'u' => 100,
            'd' => 100,
            'transfer_enable' => 150,
            'group_id' => 1,
            'banned' => 0
        ]);

        $compliantUser = User::factory()->create([
            'u' => 10,
            'd' => 10,
            'transfer_enable' => 150,
            'group_id' => 1,
            'banned' => 0
        ]);

        $server = Server::factory()->create([
            'group_ids' => ['1']
        ]);

        // Mark the node as online via Cache
        \Illuminate\Support\Facades\Cache::put("node_ws_alive:{$server->id}", true);

        // 2. Mock Redis
        Redis::shouldReceive('scard')
            ->with('traffic:pending_check')
            ->andReturn(2);

        Redis::shouldReceive('spop')
            ->with('traffic:pending_check', 2)
            ->andReturn([$exceededUser->id, $compliantUser->id]);

        Redis::shouldReceive('publish')
            ->once()
            ->with('node:push', Mockery::on(function ($argument) use ($server, $exceededUser) {
                $decoded = json_decode($argument, true);
                return isset($decoded['node_id']) && $decoded['node_id'] === $server->id &&
                       isset($decoded['event']) && $decoded['event'] === 'sync.user.delta' &&
                       isset($decoded['data']['action']) && $decoded['data']['action'] === 'remove' &&
                       isset($decoded['data']['users']) && $decoded['data']['users'] === [['id' => $exceededUser->id]];
            }));

        // 4. Run command
        $this->artisan('check:traffic-exceeded')
            ->expectsOutputToContain('Checked 2 users, notified 1 nodes for 1 exceeded users.')
            ->assertExitCode(0);
    }

    public function test_check_traffic_exceeded_skips_offline_nodes()
    {
        $exceededUser = User::factory()->create([
            'u' => 100,
            'd' => 100,
            'transfer_enable' => 50,
            'group_id' => 1,
            'banned' => 0
        ]);

        $server = Server::factory()->create(['group_ids' => ['1']]);

        // Node is offline since we don't set cache

        Redis::shouldReceive('scard')->andReturn(1);
        Redis::shouldReceive('spop')->andReturn([$exceededUser->id]);

        $this->artisan('check:traffic-exceeded')
            ->assertExitCode(0);
    }
}
