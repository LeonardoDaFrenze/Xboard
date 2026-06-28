<?php

namespace Tests\Feature\Commands;

use App\WebSocket\NodeWorker;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

class NodeWebSocketServerCommandTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_ws_server_starts_with_default_options()
    {
        $workerMock = Mockery::mock('overload:' . NodeWorker::class);
        $workerMock->shouldReceive('__construct')
            ->with('0.0.0.0', '8076')
            ->once();
        $workerMock->shouldReceive('run')->once();

        $this->artisan('ws-server', ['action' => 'start'])
            ->assertExitCode(0);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_ws_server_starts_with_custom_options()
    {
        $workerMock = Mockery::mock('overload:' . NodeWorker::class);
        $workerMock->shouldReceive('__construct')
            ->with('127.0.0.1', '9000')
            ->once();
        $workerMock->shouldReceive('run')->once();

        $this->artisan('ws-server', [
            'action' => 'start',
            '--host' => '127.0.0.1',
            '--port' => '9000'
        ])->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }
}
