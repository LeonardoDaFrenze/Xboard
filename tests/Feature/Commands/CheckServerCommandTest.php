<?php

namespace Tests\Feature\Commands;

use App\Models\Server;
use App\Utils\CacheKey;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;
use Mockery;

class CheckServerCommandTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_check_server_processes_offline_nodes()
    {
        $server1 = new Server();
        $server1->id = 1;
        $server1->name = 'Online Node';
        $server1->host = '1.1.1.1';
        $server1->type = 'v2ray';
        $server1->parent_id = null;

        $server2 = new Server();
        $server2->id = 2;
        $server2->name = 'Offline Node';
        $server2->host = '2.2.2.2';
        $server2->type = 'trojan';
        $server2->parent_id = null;

        $server3 = new Server();
        $server3->id = 3;
        $server3->name = 'Child Node';
        $server3->host = '3.3.3.3';
        $server3->type = 'v2ray';
        $server3->parent_id = 1;

        Cache::put(CacheKey::get('SERVER_VMESS_LAST_CHECK_AT', 1), time() - 100);
        Cache::put(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', 2), time() - 2000);
        Cache::put(CacheKey::get('SERVER_VMESS_LAST_CHECK_AT', 3), time() - 2000);

        Mockery::mock('alias:App\Services\ServerService')
            ->shouldReceive('getAllServers')
            ->andReturn([$server1, $server2, $server3]);

        $telegramMock = Mockery::mock('overload:App\Services\TelegramService');
        $telegramMock->shouldReceive('sendMessageWithAdmin')
            ->once()
            ->withArgs(function ($message) {
                return strpos($message, 'Offline Node') !== false;
            });

        $this->artisan('check:server')->assertExitCode(0);

        $this->assertNull(Cache::get(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', 2)));
        $this->assertNotNull(Cache::get(CacheKey::get('SERVER_VMESS_LAST_CHECK_AT', 1)));
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
