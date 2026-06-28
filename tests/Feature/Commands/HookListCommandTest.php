<?php

namespace Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HookListCommandTest extends TestCase
{
    protected string $testFile;

    protected function setUp(): void
    {
        parent::setUp();
        
        $pluginDir = base_path('plugins');
        if (!File::exists($pluginDir)) {
            File::makeDirectory($pluginDir, 0755, true);
        }
        
        $this->testFile = $pluginDir . '/TempTestHookFile.php';
        
        $content = <<<PHP
<?php
// Dummy file to test hook scanning
HookManager::call('test.hook.dummy.call');
HookManager::filter("test_hook_dummy_filter");
HookManager::register('test.hook.dummy.register');
HookManager::registerFilter("test.hook.dummy.registerFilter");
PHP;
        File::put($this->testFile, $content);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testFile)) {
            File::delete($this->testFile);
        }
        parent::tearDown();
    }

    public function test_hook_list_scans_and_displays_hooks()
    {
        $this->artisan('hook:list')
            ->expectsOutputToContain('All Supported Hooks:')
            ->expectsOutputToContain('  test.hook.dummy.call')
            ->expectsOutputToContain('  test.hook.dummy.registerFilter')
            ->expectsOutputToContain('  test.hook.dummy.register')
            ->expectsOutputToContain('  test_hook_dummy_filter')
            ->assertExitCode(0);
    }
}
