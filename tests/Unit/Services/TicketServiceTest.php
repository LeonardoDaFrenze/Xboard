<?php

namespace Tests\Unit\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    private TicketService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TicketService::class);
    }

    public function test_create_ticket_creates_ticket_and_message(): void
    {
        $user = User::factory()->create();

        $ticket = $this->service->createTicket($user->id, 'Test Subject', 'low', 'Test message content');

        $this->assertDatabaseHas('v2_ticket', [
            'id' => $ticket->id,
            'user_id' => $user->id,
            'subject' => 'Test Subject',
        ]);
        $this->assertDatabaseHas('v2_ticket_message', [
            'ticket_id' => $ticket->id,
            'message' => 'Test message content',
        ]);
    }

    public function test_reply_adds_message(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $message = $this->service->reply($ticket, 'Reply content', $user->id);

        $this->assertNotFalse($message);
        $this->assertEquals('Reply content', $message->message);
    }

    public function test_reply_updates_ticket_status_for_admin_reply(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'reply_status' => Ticket::REPLY_STATUS_WAITING,
        ]);

        $this->service->reply($ticket, 'Admin reply', $admin->id);

        $this->assertEquals(Ticket::REPLY_STATUS_REPLIED, $ticket->fresh()->reply_status);
        $this->assertEquals($admin->id, $ticket->fresh()->last_reply_user_id);
    }

    public function test_reply_updates_ticket_status_for_user_reply(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'reply_status' => Ticket::REPLY_STATUS_REPLIED,
        ]);

        $this->service->reply($ticket, 'User reply', $user->id);

        $this->assertEquals(Ticket::REPLY_STATUS_WAITING, $ticket->fresh()->reply_status);
    }
}
