<?php

namespace Tests\Unit\Services;

use App\Models\Server;
use App\Services\NodeSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NodeSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_node_online_checks_cache(): void
    {
        $server = Server::factory()->create(['type' => 'shadowsocks']);

        $online = NodeSyncService::isNodeOnline($server->id);

        $this->assertIsBool($online);
    }

    public function test_notify_user_changed_does_not_throw(): void
    {
        $user = \App\Models\User::factory()->create();

        NodeSyncService::notifyUserChanged($user);

        $this->assertTrue(true);
    }
}
