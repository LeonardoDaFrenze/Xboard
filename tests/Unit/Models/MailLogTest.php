<?php

namespace Tests\Unit\Models;

use App\Models\MailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a mail log can be created successfully.
     *
     * @return void
     */
    public function test_mail_log_creation_is_successful(): void
    {
        $log = MailLog::factory()->create([
            'email' => 'user@example.com',
            'subject' => 'Welcome to our service',
            'template_name' => 'welcome',
            'error' => null,
        ]);

        $this->assertDatabaseHas('v2_mail_log', [
            'id' => $log->id,
            'email' => 'user@example.com',
            'template_name' => 'welcome',
        ]);

        $this->assertInstanceOf(MailLog::class, $log);
    }

    /**
     * Test that a mail log can be deleted.
     *
     * @return void
     */
    public function test_mail_log_can_be_deleted(): void
    {
        $log = MailLog::factory()->create();
        $logId = $log->id;

        $log->delete();

        $this->assertDatabaseMissing('v2_mail_log', [
            'id' => $logId,
        ]);
    }
}
