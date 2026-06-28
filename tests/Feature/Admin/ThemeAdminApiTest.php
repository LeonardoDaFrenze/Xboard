<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_get_themes()
    {
        $this->json('GET', $this->getAdminUri('theme/getThemes'))
            ->assertStatus(200);
    }

    public function test_get_theme_config_needs_name()
    {
        $this->json('POST', $this->getAdminUri('theme/getThemeConfig'))
            ->assertStatus(422);
    }

    public function test_save_theme_config_needs_theme()
    {
        $this->json('POST', $this->getAdminUri('theme/saveThemeConfig'))
            ->assertStatus(422);
    }
}
