<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TelegramServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_service_can_be_instantiated()
    {
        admin_setting(['telegram_bot_token' => 'test:token']);

        $service = new TelegramService();

        $this->assertNotNull($service);
    }

    public function test_send_message_with_admin_does_not_throw_when_no_admins()
    {
        admin_setting(['telegram_bot_token' => 'test:token']);

        $service = new TelegramService();

        $service->sendMessageWithAdmin('test message');

        $this->assertTrue(true);
    }

    public function test_service_methods_exist()
    {
        $this->assertTrue(method_exists(TelegramService::class, 'sendMessage'));
        $this->assertTrue(method_exists(TelegramService::class, 'getMe'));
        $this->assertTrue(method_exists(TelegramService::class, 'setWebhook'));
        $this->assertTrue(method_exists(TelegramService::class, 'sendMessageWithAdmin'));
        $this->assertTrue(method_exists(TelegramService::class, 'registerBotCommands'));
        $this->assertTrue(method_exists(TelegramService::class, 'approveChatJoinRequest'));
        $this->assertTrue(method_exists(TelegramService::class, 'declineChatJoinRequest'));
    }
}
