<?php

namespace Tests\Feature\Commands;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckTicketCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_ticket_closes_abandoned_tickets()
    {
        // Ticket 1: Open, updated > 24h ago, admin replied -> SHOULD CLOSE
        $ticket1 = Ticket::factory()->create([
            'status' => 0,
            'reply_status' => Ticket::REPLY_STATUS_REPLIED,
            'user_id' => 1,
            'last_reply_user_id' => 99, // Represents an admin
            'updated_at' => time() - 86500, // 24 hours + 100 seconds
        ]);

        // Ticket 2: Open, updated > 24h ago, user replied -> SHOULD REMAIN OPEN
        $ticket2 = Ticket::factory()->create([
            'status' => 0,
            'reply_status' => Ticket::REPLY_STATUS_REPLIED,
            'user_id' => 1,
            'last_reply_user_id' => 1, // Replied by the user
            'updated_at' => time() - 86500,
        ]);

        // Ticket 3: Open, updated < 24h ago, admin replied -> SHOULD REMAIN OPEN
        $ticket3 = Ticket::factory()->create([
            'status' => 0,
            'reply_status' => Ticket::REPLY_STATUS_REPLIED,
            'user_id' => 1,
            'last_reply_user_id' => 99,
            'updated_at' => time() - 1000, // Recent
        ]);

        // Ticket 4: Not replied status -> SHOULD REMAIN OPEN
        $ticket4 = Ticket::factory()->create([
            'status' => 0,
            'reply_status' => 0, // Not replied
            'user_id' => 1,
            'last_reply_user_id' => 99,
            'updated_at' => time() - 86500,
        ]);

        $this->artisan('check:ticket')->assertExitCode(0);

        $this->assertEquals(Ticket::STATUS_CLOSED, $ticket1->fresh()->status);
        $this->assertEquals(0, $ticket2->fresh()->status);
        $this->assertEquals(0, $ticket3->fresh()->status);
        $this->assertEquals(0, $ticket4->fresh()->status);
    }
}
