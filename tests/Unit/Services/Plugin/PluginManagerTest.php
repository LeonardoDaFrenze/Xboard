<?php

namespace Tests\Unit\Services\Plugin;

use Tests\TestCase;
use App\Services\Plugin\PluginManager;
use Illuminate\Support\Facades\Event;

class PluginManagerTest extends TestCase
{
    public function test_plugin_manager_can_be_instantiated()
    {
        $manager = new PluginManager();
        $this->assertInstanceOf(PluginManager::class, $manager);
    }

    public function test_plugin_manager_loads_plugins_from_directory()
    {
        $manager = new PluginManager();
        
        // Ensure the core plugins directory exists before testing
        $corePath = base_path('plugins-core');
        if (is_dir($corePath)) {
            $plugins = $manager->getPluginPaths();
            $this->assertIsArray($plugins);
        } else {
            $this->markTestSkipped('plugins-core directory not found.');
        }
    }
}
