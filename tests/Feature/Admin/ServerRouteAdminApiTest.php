<?php

namespace Tests\Feature\Admin;

use App\Models\ServerRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerRouteAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_fetch_server_routes()
    {
        $this->json('GET', $this->getAdminUri('server/route/fetch'))
            ->assertStatus(200);
    }

    public function test_save_server_route()
    {
        $this->json('POST', $this->getAdminUri('server/route/save'), [
            'remarks' => 'Test route',
            'match' => ['domain:example.com'],
            'action' => 'direct',
        ])->assertStatus(200);
    }

    public function test_drop_server_route()
    {
        $route = ServerRoute::create([
            'name' => 'Test Route',
            'remarks' => 'Test',
            'match' => ['domain:test.com'],
            'action' => 'proxy',
        ]);

        $this->json('POST', $this->getAdminUri('server/route/drop'), [
            'id' => $route->id,
        ])->assertStatus(200);

        $this->assertDatabaseMissing('v2_server_route', ['id' => $route->id]);
    }
}
