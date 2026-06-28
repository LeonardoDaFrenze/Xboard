<?php

namespace Tests\Unit\Models;

use App\Models\ServerMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerMachineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a server machine can be created.
     *
     * @return void
     */
    public function test_server_machine_creation_is_successful(): void
    {
        $machine = new ServerMachine();
        $machine->name = 'Node-01';
        $machine->token = ServerMachine::generateToken();
        $machine->notes = 'Some test server notes';
        $machine->is_active = true;
        $machine->load_status = ['cpu' => 12.5, 'mem' => 45];
        $machine->save();

        $this->assertModelExists($machine);

        $retrieved = ServerMachine::find($machine->id);
        $this->assertEquals('Node-01', $retrieved->name);
        $this->assertEquals('Some test server notes', $retrieved->notes);
        $this->assertTrue($retrieved->is_active);
        $this->assertEquals(['cpu' => 12.5, 'mem' => 45], $retrieved->load_status);
    }

    /**
     * Test that a server machine can be updated.
     *
     * @return void
     */
    public function test_server_machine_can_be_updated(): void
    {
        $machine = new ServerMachine();
        $machine->name = 'Node-Old';
        $machine->token = ServerMachine::generateToken();
        $machine->is_active = true;
        $machine->save();

        $machine->name = 'Node-New';
        $machine->is_active = false;
        $machine->save();

        $this->assertEquals('Node-New', $machine->fresh()->name);
        $this->assertFalse($machine->fresh()->is_active);
    }

    /**
     * Test that a server machine can be deleted.
     *
     * @return void
     */
    public function test_server_machine_can_be_deleted(): void
    {
        $machine = new ServerMachine();
        $machine->name = 'Node-Temp';
        $machine->token = ServerMachine::generateToken();
        $machine->save();

        $machine->delete();

        $this->assertModelMissing($machine);
    }
}

