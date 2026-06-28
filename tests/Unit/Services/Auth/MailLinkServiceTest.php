<?php

namespace Tests\Unit\Services\Auth;

use App\Models\User;
use App\Services\Auth\MailLinkService;
use App\Utils\CacheKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MailLinkServiceTest extends TestCase
{
    use RefreshDatabase;

    private MailLinkService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = app(MailLinkService::class);
    }

    public function test_handle_mail_link_returns_404_when_disabled(): void
    {
        admin_setting(['login_with_mail_link_enable' => 0]);

        [$success, $result] = $this->service->handleMailLink('test@example.com');

        $this->assertFalse($success);
        $this->assertEquals([404, null], $result);
    }

    public function test_handle_mail_link_returns_true_when_user_not_found(): void
    {
        admin_setting(['login_with_mail_link_enable' => 1]);

        [$success, $result] = $this->service->handleMailLink('nonexistent@example.com');

        $this->assertTrue($success);
        $this->assertTrue($result);
    }

    public function test_handle_token_login_with_invalid_token_returns_null(): void
    {
        $result = $this->service->handleTokenLogin('invalid_token');

        $this->assertNull($result);
    }

    public function test_handle_token_login_with_valid_token_returns_user_id(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'valid_token_12345';

        Cache::put(CacheKey::get('TEMP_TOKEN', $token), $user->id, 300);

        $result = $this->service->handleTokenLogin($token);

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result);
    }

    public function test_handle_token_login_with_expired_token_returns_null(): void
    {
        User::factory()->create(['email' => 'test@example.com']);
        $token = 'expired_token';

        Cache::put(CacheKey::get('TEMP_TOKEN', $token), 1, 0);

        $result = $this->service->handleTokenLogin($token);

        $this->assertNull($result);
    }
}
