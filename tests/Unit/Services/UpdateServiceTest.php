<?php

namespace Tests\Unit\Services;

use App\Services\UpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateServiceTest extends TestCase
{
    use RefreshDatabase;

    private UpdateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UpdateService::class);
    }

    public function test_get_current_version_returns_string(): void
    {
        $version = $this->service->getCurrentVersion();

        $this->assertIsString($version);
        $this->assertNotEmpty($version);
    }

    public function test_get_cached_update_info_returns_array(): void
    {
        $info = $this->service->getCachedUpdateInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('has_update', $info);
        $this->assertArrayHasKey('latest_version', $info);
        $this->assertArrayHasKey('current_version', $info);
    }

    public function test_get_last_check_time_returns_null_initially(): void
    {
        $time = $this->service->getLastCheckTime();

        $this->assertNull($time);
    }

    public function test_update_version_cache_does_not_throw(): void
    {
        $this->service->updateVersionCache();

        $this->assertTrue(true);
    }

    public function test_format_commit_hash_returns_7_chars(): void
    {
        $formatted = $this->invokeMethod($this->service, 'formatCommitHash', ['abcdef1234567890']);

        $this->assertEquals('abcdef1', $formatted);
    }

    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
