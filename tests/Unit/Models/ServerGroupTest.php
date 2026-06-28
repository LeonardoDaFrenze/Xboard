<?php

namespace Tests\Unit\Models;

use App\Models\ServerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerGroupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a server group can be created successfully.
     *
     * @return void
     */
    public function test_server_group_creation_is_successful(): void
    {
        $group = ServerGroup::factory()->create([
            'name' => 'Premium VIP Servers',
        ]);

        $this->assertDatabaseHas('v2_server_group', [
            'id' => $group->id,
            'name' => 'Premium VIP Servers',
        ]);

        $this->assertInstanceOf(ServerGroup::class, $group);
    }

    /**
     * Test that a server group can be updated.
     *
     * @return void
     */
    public function test_server_group_can_be_updated(): void
    {
        $group = ServerGroup::factory()->create([
            'name' => 'Old Group Name',
        ]);

        $group->update([
            'name' => 'New Group Name',
        ]);

        $this->assertDatabaseHas('v2_server_group', [
            'id' => $group->id,
            'name' => 'New Group Name',
        ]);
    }

    /**
     * Test that a server group can be deleted.
     *
     * @return void
     */
    public function test_server_group_can_be_deleted(): void
    {
        $group = ServerGroup::factory()->create();
        $groupId = $group->id;

        $group->delete();

        $this->assertDatabaseMissing('v2_server_group', [
            'id' => $groupId,
        ]);
    }
}
