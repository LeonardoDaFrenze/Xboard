<?php

namespace Tests\Feature\Commands;

use App\Services\Plugin\PluginManager;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

class XboardUpdateCommandTest extends TestCase
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

    public function test_xboard_update_executes_all_steps()
    {
        // Mock PluginManager via container
        $pluginManagerMock = Mockery::mock(PluginManager::class);
        $pluginManagerMock->shouldReceive('installDefaultPlugins')->once();
        $this->instance(PluginManager::class, $pluginManagerMock);

        // Mock ThemeService via container
        $themeServiceMock = Mockery::mock(ThemeService::class);
        $themeServiceMock->shouldReceive('refreshCurrentTheme')->once();
        $this->instance(ThemeService::class, $themeServiceMock);

        // Use sync queue so horizon:terminate is skipped (no failure risk)
        Config::set('queue.default', 'sync');

        $this->artisan('xboard:update')
            ->expectsOutputToContain('正在导入数据库请稍等...')
            ->expectsOutputToContain('正在检查并安装默认插件...')
            ->expectsOutputToContain('默认插件检查完成')
            ->expectsOutputToContain('更新完毕，队列服务已重启，你无需进行任何操作。')
            ->assertExitCode(0);
    }
}
