<?php

namespace Tests\Unit\Models;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketMessageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a ticket message can be created successfully.
     *
     * @return void
     */
    public function test_ticket_message_creation_is_successful(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $message = TicketMessage::factory()->create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'message' => 'I am still facing issues with the routing.',
        ]);

        $this->assertDatabaseHas('v2_ticket_message', [
            'id' => $message->id,
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'message' => 'I am still facing issues with the routing.',
        ]);

        $this->assertInstanceOf(TicketMessage::class, $message);
    }

    /**
     * Test that a ticket message correctly relates to a ticket.
     *
     * @return void
     */
    public function test_ticket_message_belongs_to_ticket(): void
    {
        $ticket = Ticket::factory()->create();
        
        $message = TicketMessage::factory()->create([
            'ticket_id' => $ticket->id,
        ]);

        $this->assertEquals($ticket->id, $message->ticket_id);
    }

    /**
     * Test that a ticket message can be deleted.
     *
     * @return void
     */
    public function test_ticket_message_can_be_deleted(): void
    {
        $message = TicketMessage::factory()->create();
        $messageId = $message->id;

        $message->delete();

        $this->assertDatabaseMissing('v2_ticket_message', [
            'id' => $messageId,
        ]);
    }
}
