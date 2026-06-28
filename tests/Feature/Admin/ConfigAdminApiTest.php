<?php

namespace Tests\Feature\Admin;

use App\Models\SubscribeTemplate;
use App\Models\User;
use App\Services\MailService;
use App\Services\TelegramService;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ConfigAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    public function test_get_email_template_returns_files()
    {
        $this->json('GET', $this->getAdminUri('config/getEmailTemplate'))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function test_get_theme_template_returns_files()
    {
        $this->json('GET', $this->getAdminUri('config/getThemeTemplate'))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function test_test_send_mail_dispatches_email()
    {
        $mailMock = Mockery::mock('alias:' . MailService::class);
        $mailMock->shouldReceive('sendEmail')
            ->once()
            ->andReturn(['success' => true]);

        $this->json('POST', $this->getAdminUri('config/testSendMail'))
            ->assertStatus(200)
            ->assertJson(['data' => ['success' => true]]);
    }

    public function test_set_telegram_webhook_updates_settings()
    {
        $telegramMock = Mockery::mock('overload:' . TelegramService::class);
        $telegramMock->shouldReceive('getMe')->once();
        $telegramMock->shouldReceive('setWebhook')->once();
        $telegramMock->shouldReceive('registerBotCommands')->once();

        $this->json('POST', $this->getAdminUri('config/setTelegramWebhook'), [
            'telegram_bot_token' => '12345:test_token'
        ])->assertStatus(200)
          ->assertJsonPath('data.success', true);
    }

    public function test_fetch_config_returns_all_mappings()
    {
        $this->json('GET', $this->getAdminUri('config/fetch'))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['invite', 'site', 'subscribe', 'frontend', 'server', 'email', 'telegram', 'app', 'safe']]);
    }

    public function test_save_config_updates_settings()
    {
        // Mock ThemeService for theme switch
        $themeMock = Mockery::mock(ThemeService::class);
        $themeMock->shouldReceive('switch')->once()->with('default');
        $this->instance(ThemeService::class, $themeMock);

        $this->json('POST', $this->getAdminUri('config/save'), [
            'app_name' => 'NewName',
            'frontend_theme' => 'default'
        ])->assertStatus(200);

        $this->assertEquals('NewName', admin_setting('app_name'));
    }
}
