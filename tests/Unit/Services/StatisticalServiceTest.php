<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StatisticalService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatisticalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistical_service_methods_exist()
    {
        $service = new StatisticalService();
        
        $this->assertTrue(method_exists($service, 'statUser'));
        $this->assertTrue(method_exists($service, 'statServer'));
        $this->assertTrue(method_exists($service, 'getStatUser'));
        $this->assertTrue(method_exists($service, 'getStatServer'));
    }
}
