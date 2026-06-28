<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Jobs\SendEmailJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

class SendRemindMailCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_remind_mail_queues_email_for_expiring_users()
    {
        Queue::fake();

        // Enable mail reminder feature
        admin_setting(['remind_mail_enable' => 1]);

        $plan = Plan::factory()->create();
        $user = User::factory()->create([
            'plan_id' => $plan->id,
            'remind_expire' => 1,
            'expired_at' => time() + 86400 / 2, // Expiring in 12 hours (within the 24h threshold)
            'banned' => 0,
        ]);

        $exitCode = Artisan::call('send:remindMail', ['--force' => true]);

        $this->assertEquals(0, $exitCode);

        // Verify the job was pushed
        Queue::assertPushed(SendEmailJob::class, function ($job) use ($user) {
            $reflection = new \ReflectionClass($job);
            $property = $reflection->getProperty('params');
            $property->setAccessible(true);
            $params = $property->getValue($job);

            return $params['email'] === $user->email &&
                   $params['template_name'] === 'remindExpire';
        });
    }
}
