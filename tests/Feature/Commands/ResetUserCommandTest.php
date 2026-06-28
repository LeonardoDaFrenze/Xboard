<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_user_resets_tokens_and_uuids()
    {
        // Create multiple users
        $user1 = User::factory()->create([
            'token' => 'old_token_1',
            'uuid' => 'old_uuid_1'
        ]);
        
        $user2 = User::factory()->create([
            'token' => 'old_token_2',
            'uuid' => 'old_uuid_2'
        ]);

        $this->artisan('reset:user')
            ->expectsConfirmation('确定要重置所有用户安全信息吗？', 'yes')
            ->expectsOutput("已重置用户{$user1->email}的安全信息")
            ->expectsOutput("已重置用户{$user2->email}的安全信息")
            ->assertExitCode(0);

        $freshUser1 = $user1->fresh();
        $freshUser2 = $user2->fresh();

        $this->assertNotEquals('old_token_1', $freshUser1->token);
        $this->assertNotEquals('old_uuid_1', $freshUser1->uuid);
        $this->assertNotEquals('old_token_2', $freshUser2->token);
        $this->assertNotEquals('old_uuid_2', $freshUser2->uuid);
    }

    public function test_reset_user_does_nothing_if_confirmed_no()
    {
        $user = User::factory()->create([
            'token' => 'old_token',
            'uuid' => 'old_uuid'
        ]);

        $this->artisan('reset:user')
            ->expectsConfirmation('确定要重置所有用户安全信息吗？', 'no')
            ->assertExitCode(0);

        $freshUser = $user->fresh();
        $this->assertEquals('old_token', $freshUser->token);
        $this->assertEquals('old_uuid', $freshUser->uuid);
    }
}
