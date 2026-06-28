<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use App\Models\TrafficResetLog;
use App\Services\TrafficResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ResetTrafficCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    public function test_reset_traffic_standard_mode()
    {
        User::withoutEvents(function () {
            User::factory()->activePlan()->create([
                'next_reset_at' => time() - 3600,
                'banned' => 0,
            ]);
        });

        $serviceMock = Mockery::mock(TrafficResetService::class);
        $serviceMock->shouldReceive('checkAndReset')
            ->once()
            ->with(Mockery::type(User::class), TrafficResetLog::SOURCE_CRON)
            ->andReturn(true);

        $this->instance(TrafficResetService::class, $serviceMock);

        $this->artisan('reset:traffic')
            ->expectsOutputToContain('重置用户数量: 1')
            ->assertExitCode(0);
    }

    public function test_reset_traffic_fix_null_mode()
    {
        $user = User::withoutEvents(function () {
            return User::factory()->activePlan()->create([
                'next_reset_at' => null,
                'banned' => 0,
            ]);
        });

        $serviceMock = Mockery::mock(TrafficResetService::class);
        $serviceMock->shouldReceive('calculateNextResetTime')
            ->once()
            ->with(Mockery::type(User::class))
            ->andReturn(now()->addMonth());

        $this->instance(TrafficResetService::class, $serviceMock);

        $this->artisan('reset:traffic --fix-null')
            ->expectsOutputToContain('成功修正数量: 1')
            ->assertExitCode(0);

        $this->assertNotNull($user->fresh()->next_reset_at);
    }

    public function test_reset_traffic_force_mode()
    {
        User::withoutEvents(function () {
            User::factory()->activePlan()->create([
                'next_reset_at' => time() + 3600,
                'banned' => 0,
            ]);
        });

        $serviceMock = Mockery::mock(TrafficResetService::class);
        $serviceMock->shouldReceive('calculateNextResetTime')
            ->once()
            ->with(Mockery::type(User::class))
            ->andReturn(now()->addMonth());

        $this->instance(TrafficResetService::class, $serviceMock);

        $this->artisan('reset:traffic --force')
            ->expectsOutputToContain('成功修正数量: 1')
            ->assertExitCode(0);
    }
}
