<?php

namespace Tests\Unit\Services;

use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeServiceTest extends TestCase
{
    use RefreshDatabase;

    private ThemeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ThemeService::class);
    }

    public function test_exists_returns_true_for_system_theme(): void
    {
        $exists = $this->service->exists('Xxxboard');

        $this->assertTrue($exists);
    }

    public function test_exists_returns_false_for_nonexistent(): void
    {
        $exists = $this->service->exists('nonexistent_theme');

        $this->assertFalse($exists);
    }

    public function test_get_list_returns_array(): void
    {
        $themes = $this->service->getList();

        $this->assertIsArray($themes);
    }

    public function test_get_theme_path_returns_null_for_nonexistent(): void
    {
        $path = $this->service->getThemePath('nonexistent');

        $this->assertNull($path);
    }

    public function test_get_theme_view_path_returns_null_for_nonexistent(): void
    {
        $path = $this->service->getThemeViewPath('nonexistent');

        $this->assertNull($path);
    }

    public function test_get_config_returns_null_for_nonexistent(): void
    {
        $config = $this->service->getConfig('nonexistent_theme');

        $this->assertNull($config);
    }
}
