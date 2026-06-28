<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendEmailJob;
use Tests\TestCase;

class SendEmailJobTest extends TestCase
{
    public function test_job_can_be_instantiated()
    {
        $job = new SendEmailJob(['email' => 'test@example.com', 'subject' => 'Test', 'content' => 'Body']);

        $this->assertNotNull($job);
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(10, $job->timeout);
    }

    public function test_job_has_handle_method()
    {
        $this->assertTrue(method_exists(SendEmailJob::class, 'handle'));
    }

    public function test_job_uses_correct_queue()
    {
        $this->assertEquals('send_email', (new SendEmailJob([]))->queue);
    }
}
