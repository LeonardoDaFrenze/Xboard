<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\MailTemplate;
use App\Services\MailService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailServiceTest extends TestCase
{
    use RefreshDatabase;

    private MailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MailService::class);
    }

    public function test_mail_service_methods_exist()
    {
        $this->assertTrue(method_exists($service = new MailService(), 'getTotalUsersNeedRemind'));
        $this->assertTrue(method_exists($service, 'processUsersInChunks'));
        $this->assertTrue(method_exists($service, 'remindTraffic'));
        $this->assertTrue(method_exists($service, 'remindExpire'));
    }

    public function test_get_total_users_need_remind_returns_zero_when_none()
    {
        User::factory()->create([
            'remind_expire' => false,
            'remind_traffic' => false,
            'banned' => false,
        ]);

        $count = $this->service->getTotalUsersNeedRemind();

        $this->assertEquals(0, $count);
    }

    public function test_get_total_users_need_remind_counts_eligible()
    {
        User::factory()->create([
            'remind_expire' => true,
            'remind_traffic' => true,
            'banned' => false,
            'email' => 'test@example.com',
        ]);

        $count = $this->service->getTotalUsersNeedRemind();

        $this->assertEquals(1, $count);
    }

    public function test_remind_expire_returns_when_user_has_no_expiry()
    {
        $user = User::factory()->create(['expired_at' => null]);

        $result = $this->service->remindExpire($user);

        $this->assertNull($result);
    }

    public function test_remind_traffic_skips_when_under_80_percent()
    {
        $user = User::factory()->create([
            'u' => 10,
            'd' => 10,
            'transfer_enable' => 1000,
            'remind_traffic' => true,
        ]);

        $result = $this->service->remindTraffic($user);

        $this->assertNull($result);
    }

    public function test_send_email_static_method_exists()
    {
        $this->assertTrue(method_exists(MailService::class, 'sendEmail'));
    }
}
