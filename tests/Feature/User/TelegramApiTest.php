<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 0]));
    }

    public function test_get_bot_info_requires_token()
    {
        $this->json('GET', '/api/v1/user/telegram/getBotInfo')
            ->assertStatus(400);
    }
}
