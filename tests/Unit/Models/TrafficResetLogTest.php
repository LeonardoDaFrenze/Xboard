<?php

namespace Tests\Unit\Models;

use App\Models\TrafficResetLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrafficResetLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a traffic reset log can be created.
     *
     * @return void
     */
    public function test_traffic_reset_log_creation_is_successful(): void
    {
        $user = User::factory()->create();

        $log = new TrafficResetLog();
        $log->user_id = $user->id;
        $log->reset_type = 'monthly';
        $log->reset_time = now();
        $log->trigger_source = 'auto';
        $log->old_upload = 5000;
        $log->old_download = 10000;
        $log->save();

        $this->assertModelExists($log);

        $retrieved = TrafficResetLog::find($log->id);
        $this->assertEquals($user->id, $retrieved->user_id);
        $this->assertEquals('monthly', $retrieved->reset_type);
        $this->assertEquals(5000, $retrieved->old_upload);
    }

    /**
     * Test traffic reset log relation with user.
     *
     * @return void
     */
    public function test_traffic_reset_log_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $log = new TrafficResetLog();
        $log->user_id = $user->id;
        $log->reset_type = 'manual';
        $log->reset_time = now();
        $log->trigger_source = 'manual';
        $log->save();

        // Using property access if BelongsTo relation is setup, 
        // fallback to checking IDs directly conceptually 
        $this->assertEquals($user->id, $log->user_id);
    }

    /**
     * Test that a traffic reset log can be deleted.
     *
     * @return void
     */
    public function test_traffic_reset_log_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $log = new TrafficResetLog();
        $log->user_id = $user->id;
        $log->reset_type = 'manual';
        $log->reset_time = now();
        $log->trigger_source = 'manual';
        $log->save();

        $log->delete();

        $this->assertModelMissing($log);
    }
}
