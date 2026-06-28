<?php

namespace Tests\Unit\Models;

use App\Models\ServerMachine;
use App\Models\ServerMachineLoadHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerMachineLoadHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_load_history_creation(): void
    {
        $machine = ServerMachine::factory()->create();

        $history = ServerMachineLoadHistory::create([
            'machine_id' => $machine->id,
            'cpu' => 45.5,
            'mem_total' => 8388608,
            'mem_used' => 4194304,
            'disk_total' => 107374182400,
            'disk_used' => 53687091200,
            'net_in_speed' => 1024.5,
            'net_out_speed' => 512.3,
            'recorded_at' => time(),
        ]);

        $this->assertDatabaseHas('v2_server_machine_load_history', [
            'id' => $history->id,
            'machine_id' => $machine->id,
            'cpu' => 45.5,
        ]);
    }

    public function test_load_history_belongs_to_machine(): void
    {
        $machine = ServerMachine::factory()->create();
        $history = ServerMachineLoadHistory::create([
            'machine_id' => $machine->id,
            'cpu' => 30.0,
            'mem_total' => 8388608,
            'mem_used' => 2097152,
            'disk_total' => 107374182400,
            'disk_used' => 26843545600,
            'net_in_speed' => 0,
            'net_out_speed' => 0,
            'recorded_at' => time(),
        ]);

        $this->assertEquals($machine->id, $history->machine->id);
    }

    public function test_load_history_casts(): void
    {
        $machine = ServerMachine::factory()->create();
        $history = ServerMachineLoadHistory::create([
            'machine_id' => $machine->id,
            'cpu' => 75.8,
            'mem_total' => 16777216,
            'mem_used' => 8388608,
            'disk_total' => 214748364800,
            'disk_used' => 107374182400,
            'net_in_speed' => 2048.0,
            'net_out_speed' => 1024.0,
            'recorded_at' => time(),
        ]);

        $this->assertIsFloat($history->cpu);
        $this->assertIsInt($history->mem_total);
        $this->assertIsInt($history->mem_used);
        $this->assertIsInt($history->disk_total);
        $this->assertIsInt($history->disk_used);
        $this->assertIsFloat($history->net_in_speed);
        $this->assertIsFloat($history->net_out_speed);
    }

    public function test_load_history_can_be_deleted(): void
    {
        $machine = ServerMachine::factory()->create();
        $history = ServerMachineLoadHistory::create([
            'machine_id' => $machine->id,
            'cpu' => 10.0,
            'mem_total' => 8388608,
            'mem_used' => 1048576,
            'disk_total' => 107374182400,
            'disk_used' => 10737418240,
            'net_in_speed' => 0,
            'net_out_speed' => 0,
            'recorded_at' => time(),
        ]);

        $historyId = $history->id;
        $history->delete();

        $this->assertDatabaseMissing('v2_server_machine_load_history', [
            'id' => $historyId,
        ]);
    }
}
