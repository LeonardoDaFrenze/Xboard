<?php

namespace Tests\Unit\Jobs;

use App\Jobs\StatUserJob;
use App\Models\Server;
use App\Models\StatUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatUserJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_can_be_instantiated()
    {
        $server = Server::factory()->make(['id' => 1, 'rate' => 1.0])->toArray();
        $job = new StatUserJob($server, [1 => [100, 200]], 'shadowsocks', 'd');

        $this->assertNotNull($job);
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->timeout);
    }

    public function test_job_handle_creates_stat_record()
    {
        $server = Server::factory()->make(['id' => 1, 'rate' => 2.0])->toArray();
        $job = new StatUserJob($server, [1 => [100, 200]], 'shadowsocks', 'd');

        $job->handle();

        $this->assertDatabaseHas('v2_stat_user', [
            'user_id' => 1,
            'server_rate' => 2.0,
            'record_type' => 'd',
            'u' => 200,
            'd' => 400,
        ]);
    }

    public function test_job_uses_correct_queue()
    {
        $server = Server::factory()->make(['id' => 1, 'rate' => 1.0])->toArray();
        $this->assertEquals('stat', (new StatUserJob($server, [], 'test'))->queue);
    }
}
