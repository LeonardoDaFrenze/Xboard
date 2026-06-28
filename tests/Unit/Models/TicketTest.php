<?php

namespace Tests\Unit\Models;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a ticket can be created successfully.
     *
     * @return void
     */
    public function test_ticket_creation_is_successful(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Network Connectivity Issue',
            'level' => 1,
            'status' => 0,
            'reply_status' => 0,
        ]);

        $this->assertDatabaseHas('v2_ticket', [
            'id' => $ticket->id,
            'user_id' => $user->id,
            'subject' => 'Network Connectivity Issue',
            'level' => 1,
            'status' => 0,
        ]);

        $this->assertInstanceOf(Ticket::class, $ticket);
    }

    /**
     * Test that a ticket is properly associated with a user.
     *
     * @return void
     */
    public function test_ticket_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Xray-core Routing Failure',
        ]);

        $this->assertEquals($user->id, $ticket->user_id);
    }

    /**
     * Test that a ticket can be updated.
     *
     * @return void
     */
    public function test_ticket_can_be_updated(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => 0,
        ]);

        $ticket->update([
            'status' => 1,
        ]);

        $this->assertDatabaseHas('v2_ticket', [
            'id' => $ticket->id,
            'status' => 1,
        ]);
    }

    /**
     * Test that a ticket can be deleted.
     *
     * @return void
     */
    public function test_ticket_can_be_deleted(): void
    {
        $ticket = Ticket::factory()->create();

        $ticketId = $ticket->id;

        $ticket->delete();

        $this->assertDatabaseMissing('v2_ticket', [
            'id' => $ticketId,
        ]);
    }
}
