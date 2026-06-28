<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\CommissionLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommissionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_commission_logs()
    {
        $inviter = User::factory()->create();
        $invitee = User::factory()->create();

        CommissionLog::factory()->create([
            'invite_user_id' => $inviter->id,
            'user_id' => $invitee->id,
            'order_amount' => 2000,
            'get_amount' => 200,
        ]);

        $response = $this->actingAs($inviter)->getJson('/api/v1/user/invite/details');

        $response->assertStatus(200)
                 ->assertJsonFragment(['get_amount' => 200]);
    }
}
