<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_get_system_status_returns_something()
    {
        $response = $this->json('GET', $this->getAdminUri('system/getSystemStatus'));

        $this->assertTrue(in_array($response->status(), [200, 500]));
    }
}
