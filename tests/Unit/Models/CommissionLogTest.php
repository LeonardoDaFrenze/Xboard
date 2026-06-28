<?php

namespace Tests\Unit\Models;

use App\Models\CommissionLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a commission log can be created successfully.
     *
     * @return void
     */
    public function test_commission_log_creation_is_successful(): void
    {
        $inviter = User::factory()->create();
        $invitee = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $invitee->id]);

        $log = CommissionLog::factory()->create([
            'invite_user_id' => $inviter->id,
            'user_id' => $invitee->id,
            'trade_no' => $order->trade_no,
            'order_amount' => 1500,
            'get_amount' => 150,
        ]);

        $this->assertDatabaseHas('v2_commission_log', [
            'id' => $log->id,
            'invite_user_id' => $inviter->id,
            'user_id' => $invitee->id,
            'trade_no' => $order->trade_no,
            'order_amount' => 1500,
            'get_amount' => 150,
        ]);

        $this->assertInstanceOf(CommissionLog::class, $log);
    }

    /**
     * Test that a commission log can be updated.
     *
     * @return void
     */
    public function test_commission_log_can_be_updated(): void
    {
        $log = CommissionLog::factory()->create([
            'order_amount' => 1000,
        ]);

        $log->update([
            'order_amount' => 2000,
        ]);

        $this->assertDatabaseHas('v2_commission_log', [
            'id' => $log->id,
            'order_amount' => 2000,
        ]);
    }

    /**
     * Test that a commission log can be deleted.
     *
     * @return void
     */
    public function test_commission_log_can_be_deleted(): void
    {
        $log = CommissionLog::factory()->create();
        $logId = $log->id;

        $log->delete();

        $this->assertDatabaseMissing('v2_commission_log', [
            'id' => $logId,
        ]);
    }
}
