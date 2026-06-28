<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_auth_data_returns_token(): void
    {
        $user = User::factory()->create();

        $service = new AuthService($user);
        $result = $service->generateAuthData();

        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('auth_data', $result);
        $this->assertArrayHasKey('is_admin', $result);
    }

    public function test_generate_auth_data_returns_correct_is_admin(): void
    {
        $user = User::factory()->create(['is_admin' => 1]);

        $service = new AuthService($user);
        $result = $service->generateAuthData();

        $this->assertEquals(1, $result['is_admin']);
    }

    public function test_get_sessions_returns_array(): void
    {
        $user = User::factory()->create();

        $service = new AuthService($user);
        $sessions = $service->getSessions();

        $this->assertIsArray($sessions);
    }

    public function test_remove_session(): void
    {
        $user = User::factory()->create();

        $service = new AuthService($user);
        $result = $service->removeSession('0');

        $this->assertTrue($result);
    }

    public function test_remove_all_sessions(): void
    {
        $user = User::factory()->create();

        $service = new AuthService($user);
        $result = $service->removeAllSessions();

        $this->assertTrue($result);
    }
}
