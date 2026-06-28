<?php

namespace Tests\Feature\Admin;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_fetch_tickets()
    {
        Ticket::factory()->count(3)->create();

        $this->json('GET', $this->getAdminUri('ticket/fetch'))
            ->assertStatus(200);
    }

    public function test_reply_to_ticket()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        $admin = User::factory()->create(['is_admin' => 1]);

        $this->actingAs($admin)
            ->json('POST', $this->getAdminUri('ticket/reply'), [
                'id' => $ticket->id,
                'message' => 'Admin reply message',
            ])->assertStatus(200);
    }

    public function test_close_ticket()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'status' => Ticket::STATUS_OPENING,
        ]);

        $this->json('POST', $this->getAdminUri('ticket/close'), [
            'id' => $ticket->id,
        ])->assertStatus(200);

        $this->assertEquals(Ticket::STATUS_CLOSED, $ticket->fresh()->status);
    }
}
