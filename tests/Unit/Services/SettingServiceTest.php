<?php

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingServiceTest extends TestCase
{
    use RefreshDatabase;

    private SettingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SettingService::class);
    }

    public function test_get_returns_default_when_not_found(): void
    {
        $value = $this->service->get('nonexistent', 'default');

        $this->assertEquals('default', $value);
    }

    public function test_get_returns_stored_value(): void
    {
        Setting::create(['name' => 'test_key', 'value' => 'test_value']);

        $value = $this->service->get('test_key');

        $this->assertEquals('test_value', $value);
    }

    public function test_get_all_returns_settings(): void
    {
        Setting::create(['name' => 'key1', 'value' => 'val1']);
        Setting::create(['name' => 'key2', 'value' => 'val2']);

        $all = $this->service->getAll();

        $this->assertArrayHasKey('key1', $all);
        $this->assertArrayHasKey('key2', $all);
        $this->assertEquals('val1', $all['key1']);
        $this->assertEquals('val2', $all['key2']);
    }

    public function test_get_all_returns_array(): void
    {
        $all = $this->service->getAll();

        $this->assertIsArray($all);
    }
}
