<?php

namespace Tests\Feature\Admin;

use App\Models\ServerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerGroupAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_fetch_server_groups()
    {
        ServerGroup::factory()->count(3)->create();

        $this->json('GET', $this->getAdminUri('server/group/fetch'))
            ->assertStatus(200);
    }

    public function test_save_server_group()
    {
        $this->json('POST', $this->getAdminUri('server/group/save'), [
            'name' => 'Premium Group',
        ])->assertStatus(200);

        $this->assertDatabaseHas('v2_server_group', [
            'name' => 'Premium Group',
        ]);
    }

    public function test_drop_server_group()
    {
        $group = ServerGroup::factory()->create();

        $this->json('POST', $this->getAdminUri('server/group/drop'), [
            'id' => $group->id,
        ])->assertStatus(200);

        $this->assertDatabaseMissing('v2_server_group', ['id' => $group->id]);
    }
}
