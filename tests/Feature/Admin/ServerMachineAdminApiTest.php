<?php

namespace Tests\Feature\Admin;

use App\Models\ServerMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerMachineAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_fetch_machines()
    {
        ServerMachine::factory()->count(3)->create();

        $this->json('GET', $this->getAdminUri('server/machine/fetch'))
            ->assertStatus(200);
    }

    public function test_save_machine()
    {
        $this->json('POST', $this->getAdminUri('server/machine/save'), [
            'name' => 'Main Machine',
            'host' => '192.168.1.1',
        ])->assertStatus(200);

        $this->assertDatabaseHas('v2_server_machine', [
            'name' => 'Main Machine',
        ]);
    }

    public function test_drop_machine()
    {
        $machine = ServerMachine::factory()->create();

        $this->json('POST', $this->getAdminUri('server/machine/drop'), [
            'id' => $machine->id,
        ])->assertStatus(200);

        $this->assertDatabaseMissing('v2_server_machine', ['id' => $machine->id]);
    }
}
