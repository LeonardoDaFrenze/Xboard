<?php

namespace Tests\Feature\User;

use App\Models\Server;
use App\Models\ServerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerListApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetch_servers_requires_auth()
    {
        $this->json('GET', '/api/v1/user/server/fetch')
            ->assertStatus(403);
    }

    public function test_fetch_servers_as_user()
    {
        $user = User::factory()->create(['is_admin' => 0, 'group_id' => 1]);

        $this->actingAs($user)
            ->json('GET', '/api/v1/user/server/fetch')
            ->assertStatus(200);
    }
}
