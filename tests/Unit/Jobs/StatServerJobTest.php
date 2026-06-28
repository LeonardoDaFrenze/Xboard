<?php

namespace Tests\Unit\Jobs;

use App\Jobs\StatServerJob;
use App\Models\Server;
use App\Models\StatServer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatServerJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_can_be_instantiated()
    {
        $server = Server::factory()->make(['id' => 1])->toArray();
        $job = new StatServerJob($server, [[100, 200]], 'shadowsocks', 'd');

        $this->assertNotNull($job);
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->timeout);
    }

    public function test_job_handle_creates_stat_record()
    {
        $server = Server::factory()->create(['u' => 0, 'd' => 0]);
        $job = new StatServerJob($server->toArray(), [[100, 200]], 'shadowsocks', 'd');

        $job->handle();

        $this->assertDatabaseHas('v2_stat_server', [
            'server_id' => $server->id,
            'server_type' => 'shadowsocks',
            'record_type' => 'd',
            'u' => 100,
            'd' => 200,
        ]);
    }

    public function test_job_handle_updates_existing_stat_record()
    {
        $server = Server::factory()->create();
        $recordAt = strtotime(date('Y-m-d'));

        StatServer::create([
            'record_at' => $recordAt,
            'server_id' => $server->id,
            'server_type' => 'shadowsocks',
            'record_type' => 'd',
            'u' => 50,
            'd' => 100,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $job = new StatServerJob($server->toArray(), [[30, 40]], 'shadowsocks', 'd');
        $job->handle();

        $this->assertDatabaseHas('v2_stat_server', [
            'server_id' => $server->id,
            'server_type' => 'shadowsocks',
            'record_type' => 'd',
            'u' => 80,
            'd' => 140,
        ]);
    }

    public function test_job_handle_updates_server_traffic()
    {
        $server = Server::factory()->create(['u' => 100, 'd' => 200]);
        $job = new StatServerJob($server->toArray(), [[50, 75]], 'shadowsocks', 'd');

        $job->handle();

        $server->refresh();
        $this->assertEquals(150, $server->u);
        $this->assertEquals(275, $server->d);
    }

    public function test_job_uses_correct_queue()
    {
        $server = Server::factory()->make(['id' => 1])->toArray();
        $this->assertEquals('stat', (new StatServerJob($server, [], 'test'))->queue);
    }
}
