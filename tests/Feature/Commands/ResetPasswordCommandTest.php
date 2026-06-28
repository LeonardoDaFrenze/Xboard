<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetPasswordCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_with_provided_password()
    {
        $user = User::factory()->create(['email' => 'test@test.com']);

        $this->artisan('reset:password', ['email' => 'test@test.com', 'password' => 'newsecret'])
            ->expectsOutput('!!!重置成功!!!')
            ->expectsOutput('新密码为：newsecret，请尽快修改密码。')
            ->assertExitCode(0);

        $this->assertTrue(password_verify('newsecret', $user->fresh()->password));
    }

    public function test_reset_password_with_generated_password()
    {
        $user = User::factory()->create(['email' => 'test@test.com']);

        $this->artisan('reset:password', ['email' => 'test@test.com'])
            ->expectsOutput('!!!重置成功!!!')
            ->assertExitCode(0);

        $this->assertNotNull($user->fresh()->password);
    }

    public function test_reset_password_fails_for_nonexistent_user()
    {
        $this->withoutExceptionHandling();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('邮箱不存在');

        $this->artisan('reset:password', ['email' => 'nonexistent@test.com']);
    }
}
