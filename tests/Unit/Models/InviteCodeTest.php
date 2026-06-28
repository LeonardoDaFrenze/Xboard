<?php

namespace Tests\Unit\Models;

use App\Models\InviteCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteCodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an invite code can be created successfully.
     *
     * @return void
     */
    public function test_invite_code_creation_is_successful(): void
    {
        $user = User::factory()->create();

        $inviteCode = InviteCode::factory()->create([
            'user_id' => $user->id,
            'code' => 'XYZ123ABC',
            'status' => 0,
        ]);

        $this->assertDatabaseHas('v2_invite_code', [
            'id' => $inviteCode->id,
            'user_id' => $user->id,
            'code' => 'XYZ123ABC',
            'status' => 0,
        ]);

        $this->assertInstanceOf(InviteCode::class, $inviteCode);
    }

    /**
     * Test that an invite code belongs to a user.
     *
     * @return void
     */
    public function test_invite_code_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        
        $inviteCode = InviteCode::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $inviteCode->user_id);
    }

    /**
     * Test that an invite code can be updated to used status.
     *
     * @return void
     */
    public function test_invite_code_can_be_marked_as_used(): void
    {
        $inviteCode = InviteCode::factory()->create([
            'status' => 0,
        ]);

        $inviteCode->update([
            'status' => 1,
        ]);

        $this->assertDatabaseHas('v2_invite_code', [
            'id' => $inviteCode->id,
            'status' => 1,
        ]);
    }

    /**
     * Test that an invite code can be deleted.
     *
     * @return void
     */
    public function test_invite_code_can_be_deleted(): void
    {
        $inviteCode = InviteCode::factory()->create();
        $codeId = $inviteCode->id;

        $inviteCode->delete();

        $this->assertDatabaseMissing('v2_invite_code', [
            'id' => $codeId,
        ]);
    }
}
