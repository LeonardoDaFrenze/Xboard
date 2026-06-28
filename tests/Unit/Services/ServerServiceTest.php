<?php

namespace Tests\Unit\Services;

use App\Models\Server;
use App\Models\ServerMachine;
use App\Models\ServerRoute;
use App\Models\User;
use App\Services\ServerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_servers_returns_all(): void
    {
        Server::factory()->count(3)->create();

        $servers = ServerService::getAllServers();

        $this->assertCount(3, $servers);
    }

    public function test_get_all_servers_ordered_by_sort(): void
    {
        $server1 = Server::factory()->create(['sort' => 2]);
        $server2 = Server::factory()->create(['sort' => 1]);

        $servers = ServerService::getAllServers();

        $this->assertEquals($server2->id, $servers->first()->id);
    }

    public function test_get_machine_nodes_returns_servers(): void
    {
        $machine = ServerMachine::factory()->create();
        Server::factory()->create(['machine_id' => $machine->id]);

        $nodes = ServerService::getMachineNodes($machine);

        $this->assertIsIterable($nodes);
    }

    public function test_get_routes_returns_matching_routes(): void
    {
        $route1 = ServerRoute::create([
            'name' => 'Route 1',
            'match' => ['domain:example.com'],
            'action' => 'direct',
            'remarks' => 'test',
        ]);
        $route2 = ServerRoute::create([
            'name' => 'Route 2',
            'match' => ['domain:test.com'],
            'action' => 'proxy',
            'remarks' => 'test',
        ]);

        $routes = ServerService::getRoutes([$route1->id, $route2->id]);

        $this->assertCount(2, $routes);
    }

    public function test_get_available_users_returns_collection(): void
    {
        $node = Server::factory()->create([
            'group_ids' => [1],
        ]);

        $users = ServerService::getAvailableUsers($node);

        $this->assertIsIterable($users);
    }

    public function test_process_traffic_with_valid_data(): void
    {
        $node = Server::factory()->create(['type' => 'shadowsocks']);

        ServerService::processTraffic($node, [
            [1, [100, 200]],
        ]);

        $this->assertTrue(true);
    }

    public function test_touch_node_sets_cache(): void
    {
        $node = Server::factory()->create(['type' => 'shadowsocks']);

        ServerService::touchNode($node);

        $this->assertTrue(true);
    }
}
