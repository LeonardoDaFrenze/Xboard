<?php

namespace Tests\Feature\Commands;

use App\Models\Stat;
use App\Services\StatisticalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class XboardStatisticsCommandTest extends TestCase
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

    public function test_xboard_statistics_generates_data()
    {
        // 1. Setup Mock
        $statData = [
            'order_count' => 10,
            'order_total' => 1000,
            'commission_count' => 1,
            'commission_total' => 100,
            'paid_count' => 5,
            'paid_total' => 500,
            'register_count' => 2,
            'invite_count' => 1,
            'transfer_used_total' => '0'
        ];

        $statServiceMock = Mockery::mock(StatisticalService::class);
        $statServiceMock->shouldReceive('setStartAt')->once();
        $statServiceMock->shouldReceive('setEndAt')->once();
        $statServiceMock->shouldReceive('generateStatData')
            ->once()
            ->andReturn($statData);

        $this->instance(StatisticalService::class, $statServiceMock);

        // 2. Execute Command
        $this->artisan('xboard:statistics')->assertExitCode(0);

        // 3. Assertions
        $this->assertDatabaseHas('v2_stat', [
            'order_count' => 10,
            'record_type' => 'd'
        ]);
    }

    public function test_xboard_statistics_updates_existing_record()
    {
        // Create existing stat
        $existing = Stat::factory()->create([
            'record_at' => strtotime('-1 day', strtotime(date('Y-m-d'))),
            'record_type' => 'd',
            'order_count' => 5
        ]);

        $statData = [
            'order_count' => 20,
            'order_total' => 2000,
            'commission_count' => 2,
            'commission_total' => 200,
            'paid_count' => 10,
            'paid_total' => 1000,
            'register_count' => 4,
            'invite_count' => 2,
            'transfer_used_total' => '0'
        ];

        $statServiceMock = Mockery::mock(StatisticalService::class);
        $statServiceMock->shouldReceive('setStartAt')->once();
        $statServiceMock->shouldReceive('setEndAt')->once();
        $statServiceMock->shouldReceive('generateStatData')
            ->once()
            ->andReturn($statData); // New values

        $this->instance(StatisticalService::class, $statServiceMock);

        $this->artisan('xboard:statistics')->assertExitCode(0);

        $this->assertEquals(20, $existing->fresh()->order_count);
    }
}
