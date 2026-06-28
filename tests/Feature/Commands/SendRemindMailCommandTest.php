<?php

namespace Tests\Feature\Commands;

use App\Services\MailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class SendRemindMailCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        admin_setting(['remind_mail_enable' => 1]);
    }

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_send_remind_mail_processes_users()
    {
        // overload: intercepts `new MailService()` inside the command
        $mailServiceMock = Mockery::mock('overload:' . MailService::class);

        $mailServiceMock->shouldReceive('getTotalUsersNeedRemind')
            ->once()
            ->andReturn(100);

        $mailServiceMock->shouldReceive('processUsersInChunks')
            ->once()
            ->andReturnUsing(function($chunkSize, $callback) {
                $callback();
                return [
                    'processed_users' => 100,
                    'expire_emails' => 50,
                    'traffic_emails' => 50,
                    'skipped' => 0,
                    'errors' => 0,
                ];
            });

        $this->artisan('send:remindMail', ['--force' => true])
            ->expectsOutputToContain('Reminder emails sent.')
            ->assertExitCode(0);
    }
}
