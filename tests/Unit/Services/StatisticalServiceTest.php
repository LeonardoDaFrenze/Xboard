<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Order;
use App\Models\User;
use App\Services\StatisticalService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatisticalServiceTest extends TestCase
{
    use RefreshDatabase;

    private StatisticalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StatisticalService::class);
    }

    public function test_generate_stat_data_returns_correct_structure()
    {
        $this->service->setStartAt(strtotime('-1 day'));
        $this->service->setEndAt(time());

        $data = $this->service->generateStatData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('order_count', $data);
        $this->assertArrayHasKey('order_total', $data);
        $this->assertArrayHasKey('paid_count', $data);
        $this->assertArrayHasKey('paid_total', $data);
        $this->assertArrayHasKey('commission_count', $data);
        $this->assertArrayHasKey('commission_total', $data);
        $this->assertArrayHasKey('register_count', $data);
        $this->assertArrayHasKey('invite_count', $data);
        $this->assertArrayHasKey('transfer_used_total', $data);
    }

    public function test_generate_stat_data_counts_orders()
    {
        $now = time();
        $this->service->setStartAt(strtotime('-1 day', $now));
        $this->service->setEndAt($now);

        Order::factory()->create([
            'created_at' => $now - 3600,
            'total_amount' => 1000,
        ]);

        $data = $this->service->generateStatData();

        $this->assertEquals(1, $data['order_count']);
        $this->assertEquals(1000, $data['order_total']);
    }

    public function test_generate_stat_counts_registered_users()
    {
        $now = time();
        $this->service->setStartAt(strtotime('-1 day', $now));
        $this->service->setEndAt($now);

        User::factory()->create(['created_at' => $now - 3600]);

        $data = $this->service->generateStatData();

        $this->assertEquals(1, $data['register_count']);
    }

    public function test_stat_server_and_stat_user_methods_exist()
    {
        $this->assertTrue(method_exists($this->service, 'statServer'));
        $this->assertTrue(method_exists($this->service, 'statUser'));
        $this->assertTrue(method_exists($this->service, 'getStatUser'));
        $this->assertTrue(method_exists($this->service, 'getStatServer'));
        $this->assertTrue(method_exists($this->service, 'getRanking'));
        $this->assertTrue(method_exists($this->service, 'getStatRecord'));
    }
}
