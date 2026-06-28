<?php

namespace Tests\Unit\Models;

use App\Models\StatServer;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatServerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that server statistics can be created.
     *
     * @return void
     */
    public function test_stat_server_creation_is_successful(): void
    {
        $server = Server::factory()->create();

        $stat = new StatServer();
        $stat->server_id = $server->id;
        $stat->server_type = 'v2ray';
        $stat->u = 102400;
        $stat->d = 204800;
        $stat->record_type = 'd';
        $stat->record_at = time();
        $stat->save();

        $this->assertModelExists($stat);

        $retrieved = StatServer::find($stat->id);
        $this->assertEquals($server->id, $retrieved->server_id);
        $this->assertEquals('v2ray', $retrieved->server_type);
        $this->assertEquals(102400, $retrieved->u);
        $this->assertEquals(204800, $retrieved->d);
    }

    /**
     * Test updating a stat server record.
     *
     * @return void
     */
    public function test_stat_server_can_be_updated(): void
    {
        $server = Server::factory()->create();

        $stat = new StatServer();
        $stat->server_id = $server->id;
        $stat->server_type = 'trojan';
        $stat->u = 100;
        $stat->d = 100;
        $stat->record_type = 'm';
        $stat->record_at = time();
        $stat->save();

        $stat->u = 500;
        $stat->save();

        $this->assertEquals(500, $stat->fresh()->u);
    }

    /**
     * Test deleting a stat server record.
     *
     * @return void
     */
    public function test_stat_server_can_be_deleted(): void
    {
        $server = Server::factory()->create();

        $stat = new StatServer();
        $stat->server_id = $server->id;
        $stat->server_type = 'shadowsocks';
        $stat->u = 0;
        $stat->d = 0;
        $stat->record_type = 'd';
        $stat->record_at = time();
        $stat->save();

        $stat->delete();

        $this->assertModelMissing($stat);
    }
}
