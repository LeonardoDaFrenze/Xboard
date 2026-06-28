<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServerManageAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_create_server()
    {
        $response = $this->json('POST', $this->getAdminUri('server/manage/save'), [
            'type' => 'shadowsocks',
            'name' => 'Test Server',
            'group_ids' => [1],
            'route_ids' => [],
            'parent_id' => null,
            'host' => '127.0.0.1',
            'port' => 443,
            'server_port' => 443,
            'tags' => ['Test'],
            'rate' => 1.0,
            'show' => 1,
            'protocol_settings' => ['cipher' => 'aes-256-gcm'],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('v2_server', [
            'name' => 'Test Server',
            'host' => '127.0.0.1',
            'type' => 'shadowsocks',
        ]);
    }

    public function test_fetch_servers()
    {
        Server::factory()->count(3)->create();

        $this->json('GET', $this->getAdminUri('server/manage/getNodes'))
            ->assertStatus(200);
    }

    public function test_update_server()
    {
        $server = Server::factory()->create(['name' => 'Old Name']);

        $this->json('POST', $this->getAdminUri('server/manage/update'), [
            'id' => $server->id,
            'name' => 'New Name',
        ])->assertStatus(200);
    }

    public function test_drop_server()
    {
        $server = Server::factory()->create();

        $this->json('POST', $this->getAdminUri('server/manage/drop'), [
            'id' => $server->id,
        ])->assertStatus(200);

        $this->assertDatabaseMissing('v2_server', ['id' => $server->id]);
    }

    public function test_sort_servers()
    {
        $server1 = Server::factory()->create(['sort' => 1]);
        $server2 = Server::factory()->create(['sort' => 2]);

        $this->json('POST', $this->getAdminUri('server/manage/sort'), [
            'server_ids' => [$server2->id, $server1->id],
        ])->assertStatus(200);
    }
}
