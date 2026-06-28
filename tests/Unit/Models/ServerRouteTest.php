<?php

namespace Tests\Unit\Models;

use App\Models\ServerRoute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a server route can be created.
     *
     * @return void
     */
    public function test_server_route_creation_is_successful(): void
    {
        $route = new ServerRoute();
        $route->remarks = 'Main Routing';
        $route->match = json_encode(['domain' => ['geosite:google']]);
        $route->action = 'block';
        $route->action_value = '';
        $route->save();

        $this->assertModelExists($route);

        $retrieved = ServerRoute::find($route->id);
        $this->assertEquals('Main Routing', $retrieved->remarks);
        $this->assertEquals('block', $retrieved->action);
    }

    /**
     * Test that a server route can be deleted.
     *
     * @return void
     */
    public function test_server_route_can_be_deleted(): void
    {
        $route = new ServerRoute();
        $route->remarks = 'Temp Route';
        $route->match = json_encode(['domain' => ['geosite:google']]);
        $route->action = 'block';
        $route->action_value = '';
        $route->save();

        $route->delete();

        $this->assertModelMissing($route);
    }
}
