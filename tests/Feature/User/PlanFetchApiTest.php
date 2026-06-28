<?php

namespace Tests\Feature\User;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanFetchApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 0]));
    }

    public function test_fetch_available_plans()
    {
        Plan::factory()->create(['show' => 1, 'sell' => 1]);

        $this->json('GET', '/api/v1/user/plan/fetch')
            ->assertStatus(200);
    }

    public function test_fetch_plans_only_shows_available()
    {
        Plan::factory()->create(['show' => 1, 'sell' => 1, 'name' => 'Visible']);
        Plan::factory()->create(['show' => 0, 'sell' => 1, 'name' => 'Hidden']);

        $this->json('GET', '/api/v1/user/plan/fetch')
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Visible'])
            ->assertJsonMissing(['name' => 'Hidden']);
    }
}
