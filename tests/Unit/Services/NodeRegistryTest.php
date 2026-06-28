<?php

namespace Tests\Unit\Services;

use App\Models\Server;
use App\Services\NodeRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NodeRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_count_returns_zero_initially(): void
    {
        $count = NodeRegistry::count();

        $this->assertEquals(0, $count);
    }

    public function test_machine_count_returns_zero_initially(): void
    {
        $count = NodeRegistry::machineCount();

        $this->assertEquals(0, $count);
    }

    public function test_get_connected_node_ids_returns_array(): void
    {
        $ids = NodeRegistry::getConnectedNodeIds();

        $this->assertIsArray($ids);
    }

    public function test_is_online_returns_false_for_unknown(): void
    {
        $online = NodeRegistry::isOnline(999);

        $this->assertFalse($online);
    }
}
