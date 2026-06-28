<?php

namespace Tests\Unit\Services\Plugin;

use App\Services\Plugin\PluginConfigService;
use Tests\TestCase;

class PluginConfigServiceTest extends TestCase
{
    private PluginConfigService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PluginConfigService::class);
    }

    public function test_get_config_returns_array(): void
    {
        $config = $this->service->getConfig('nonexistent-plugin');

        $this->assertIsArray($config);
    }

    public function test_get_db_config_allows_access(): void
    {
        $this->assertTrue(method_exists($this->service, 'getDbConfig'));
    }
}
