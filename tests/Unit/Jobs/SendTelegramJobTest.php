<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendTelegramJob;
use Tests\TestCase;

class SendTelegramJobTest extends TestCase
{
    public function test_job_can_be_instantiated()
    {
        $job = new SendTelegramJob(12345, 'Hello!');

        $this->assertNotNull($job);
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(10, $job->timeout);
    }

    public function test_job_has_handle_method()
    {
        $this->assertTrue(method_exists(SendTelegramJob::class, 'handle'));
    }

    public function test_job_uses_correct_queue()
    {
        $this->assertEquals('send_telegram', (new SendTelegramJob(1, 'test'))->queue);
    }
}
