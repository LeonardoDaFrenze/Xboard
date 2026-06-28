<?php

namespace Tests\Unit\Models;

use App\Models\Server;
use App\Models\StatServer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerStatTest extends TestCase
{
    use RefreshDatabase;

    public function test_stat_server_creation(): void
    {
        $server = Server::factory()->create();

        $stat = StatServer::create([
            'server_id' => $server->id,
            'server_type' => 'shadowsocks',
            'u' => 1024000,
            'd' => 2048000,
            'record_at' => time(),
            'record_type' => 'd',
        ]);

        $this->assertDatabaseHas('v2_stat_server', [
            'id' => $stat->id,
            'server_id' => $server->id,
            'u' => 1024000,
            'd' => 2048000,
        ]);
    }

    public function test_stat_server_updates(): void
    {
        $server = Server::factory()->create();
        $stat = StatServer::create([
            'server_id' => $server->id,
            'server_type' => 'shadowsocks',
            'u' => 500,
            'd' => 500,
            'record_at' => time(),
            'record_type' => 'd',
        ]);

        $stat->update(['u' => 1500]);

        $this->assertEquals(1500, $stat->fresh()->u);
    }

    public function test_stat_server_can_be_deleted(): void
    {
        $server = Server::factory()->create();
        $stat = StatServer::create([
            'server_id' => $server->id,
            'server_type' => 'shadowsocks',
            'u' => 0,
            'd' => 0,
            'record_at' => time(),
            'record_type' => 'd',
        ]);

        $statId = $stat->id;
        $stat->delete();

        $this->assertDatabaseMissing('v2_stat_server', [
            'id' => $statId,
        ]);
    }
}
