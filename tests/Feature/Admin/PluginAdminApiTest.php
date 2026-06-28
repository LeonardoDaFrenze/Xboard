<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PluginAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_get_plugins()
    {
        $this->json('GET', $this->getAdminUri('plugin/getPlugins'))
            ->assertStatus(200);
    }

    public function test_get_plugin_config_needs_code()
    {
        $this->json('GET', $this->getAdminUri('plugin/config'))
            ->assertStatus(422);
    }

    public function test_update_plugin_config_needs_code()
    {
        $this->json('POST', $this->getAdminUri('plugin/config'), [
            'config' => ['enabled' => true],
        ])->assertStatus(422);
    }
}
