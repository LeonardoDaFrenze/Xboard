<?php

namespace Tests\Feature\Commands;

use App\Models\Order;
use App\Models\User;
use App\Models\CommissionLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckCommissionCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_commission_processes_eligible_orders()
    {
        // 1. Setup Data
        $inviter = User::factory()->create(['commission_balance' => 0]);
        $user = User::factory()->create(['invite_user_id' => $inviter->id]);

        // Order 1: Eligible for commission (status 3, updated > 3 days ago)
        $order1 = Order::factory()->create([
            'status' => 3,
            'invite_user_id' => $inviter->id,
            'user_id' => $user->id,
            'commission_status' => 0,
            'updated_at' => strtotime('-4 day'),
            'commission_balance' => 1000,
            'total_amount' => 1000
        ]);

        // Order 2: Not eligible (updated < 3 days ago)
        $order2 = Order::factory()->create([
            'status' => 3,
            'invite_user_id' => $inviter->id,
            'user_id' => $user->id,
            'commission_status' => 0,
            'updated_at' => strtotime('-1 day'),
            'commission_balance' => 500,
            'total_amount' => 500
        ]);

        // 2. Execute Command
        $this->artisan('check:commission')->assertExitCode(0);

        // 3. Assertions
        
        // Order 1 should be processed (status 2 = paid)
        $this->assertEquals(2, $order1->fresh()->commission_status);
        
        // Order 2 should remain untouched (status 0)
        $this->assertEquals(0, $order2->fresh()->commission_status);

        // Verify commission log and balance for Order 1
        $this->assertDatabaseHas('v2_commission_log', [
            'invite_user_id' => $inviter->id,
            'trade_no' => $order1->trade_no,
            'get_amount' => 1000 // Default behavior (100% share)
        ]);

        $this->assertEquals(1000, $inviter->fresh()->commission_balance);
    }
}
