<?php

namespace Tests\Unit\Services;

use App\Services\DeviceStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class DeviceStateServiceTest extends TestCase
{
    use RefreshDatabase;

    private DeviceStateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Redis::shouldReceive('keys')
            ->andReturn([]);

        Redis::shouldReceive('hgetall')
            ->andReturn([]);

        Redis::shouldReceive('hset')
            ->andReturn(true);

        Redis::shouldReceive('hdel')
            ->andReturn(true);

        Redis::shouldReceive('sadd')
            ->andReturn(true);

        Redis::shouldReceive('expire')
            ->andReturn(true);

        Redis::shouldReceive('srem')
            ->andReturn(true);

        $this->service = app(DeviceStateService::class);
    }

    public function test_get_node_devices_returns_empty_array_when_no_devices(): void
    {
        $devices = $this->service->getNodeDevices(999);

        $this->assertIsArray($devices);
        $this->assertEmpty($devices);
    }

    public function test_notify_update_does_not_throw(): void
    {
        $this->service->notifyUpdate(42);
        $this->assertTrue(true);
    }
}
