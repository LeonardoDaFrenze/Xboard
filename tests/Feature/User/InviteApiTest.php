<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_invite_code_requires_auth()
    {
        $this->json('GET', '/api/v1/user/invite/save')
            ->assertStatus(403);
    }

    public function test_fetch_invite_codes_requires_auth()
    {
        $this->json('GET', '/api/v1/user/invite/fetch')
            ->assertStatus(403);
    }

    public function test_save_invite_as_authenticated_user()
    {
        $this->actingAs(User::factory()->create(['is_admin' => 0]));

        $this->json('GET', '/api/v1/user/invite/save')
            ->assertStatus(200);
    }
}
