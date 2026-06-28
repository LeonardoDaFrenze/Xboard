<?php

namespace Tests\Unit\Models;

use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a server can be created successfully.
     *
     * @return void
     */
    public function test_server_creation_is_successful(): void
    {
        $server = Server::factory()->create([
            'name' => 'US-East-1',
            'group_ids' => [1, 2],
            'host' => 'us-east.example.com',
            'port' => 443,
            'server_port' => 443,
            'tags' => ['vip', 'streaming'],
            'rate' => 1.5,
            'show' => 1,
        ]);

        $this->assertDatabaseHas('v2_server', [
            'id' => $server->id,
            'name' => 'US-East-1',
            'host' => 'us-east.example.com',
        ]);

        $this->assertInstanceOf(Server::class, $server);
    }

    /**
     * Test that array casts on the server model work correctly.
     *
     * @return void
     */
    public function test_server_array_casts_are_applied(): void
    {
        $server = Server::factory()->create([
            'group_ids' => [10, 20],
            'tags' => ['fast', 'stable'],
        ]);

        $this->assertIsArray($server->group_ids);
        $this->assertContains(10, $server->group_ids);
        $this->assertContains(20, $server->group_ids);
        
        $this->assertIsArray($server->tags);
        $this->assertContains('fast', $server->tags);
    }

    /**
     * Test that a server can be updated.
     *
     * @return void
     */
    public function test_server_can_be_updated(): void
    {
        $server = Server::factory()->create([
            'show' => 1,
        ]);

        $server->update([
            'show' => 0,
            'name' => 'Updated Server Name',
        ]);

        $this->assertDatabaseHas('v2_server', [
            'id' => $server->id,
            'show' => 0,
            'name' => 'Updated Server Name',
        ]);
    }

    /**
     * Test that a server can be deleted.
     *
     * @return void
     */
    public function test_server_can_be_deleted(): void
    {
        $server = Server::factory()->create();
        $serverId = $server->id;

        $server->delete();

        $this->assertDatabaseMissing('v2_server', [
            'id' => $serverId,
        ]);
    }
}
