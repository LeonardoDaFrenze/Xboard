<?php

namespace Tests\Unit\Models;

use App\Models\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatTest extends TestCase
{
    use RefreshDatabase;

    public function test_stat_creation(): void
    {
        $stat = Stat::factory()->create([
            'record_at' => time(),
            'order_count' => 10,
            'paid_count' => 8,
            'paid_total' => 50000,
            'register_count' => 15,
            'commission_count' => 3,
            'commission_total' => 1500,
            'invite_count' => 5,
            'transfer_used_total' => 1073741824,
        ]);

        $this->assertDatabaseHas('v2_stat', [
            'id' => $stat->id,
            'order_count' => 10,
            'paid_count' => 8,
            'register_count' => 15,
        ]);
    }

    public function test_stat_can_be_updated(): void
    {
        $stat = Stat::factory()->create([
            'record_at' => time(),
            'order_count' => 5,
        ]);

        $stat->update(['order_count' => 20]);

        $this->assertEquals(20, $stat->fresh()->order_count);
    }

    public function test_stat_can_be_deleted(): void
    {
        $stat = Stat::factory()->create(['record_at' => time()]);
        $statId = $stat->id;

        $stat->delete();

        $this->assertDatabaseMissing('v2_stat', [
            'id' => $statId,
        ]);
    }

    public function test_stat_has_timestamps(): void
    {
        $stat = Stat::factory()->create(['record_at' => time()]);

        $this->assertNotNull($stat->created_at);
        $this->assertNotNull($stat->updated_at);
    }
}
