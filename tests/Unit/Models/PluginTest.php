<?php

namespace Tests\Unit\Models;

use App\Models\Plugin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PluginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a plugin configuration can be created.
     *
     * @return void
     */
    public function test_plugin_creation_is_successful(): void
    {
        $plugin = new Plugin();
        $plugin->name = 'AlipayF2f';
        $plugin->code = 'alipay_f2f';
        $plugin->version = '1.0.0';
        $plugin->is_enabled = true;
        $plugin->save();

        $this->assertModelExists($plugin);

        $retrieved = Plugin::where('name', 'AlipayF2f')->first();
        $this->assertNotNull($retrieved);
        $this->assertTrue($retrieved->is_enabled);
    }
}
