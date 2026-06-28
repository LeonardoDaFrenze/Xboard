<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use App\Models\StatServer;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_get_override()
    {
        $this->json('GET', $this->getAdminUri('stat/getOverride'))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_get_override_has_keys()
    {
        $this->json('GET', $this->getAdminUri('stat/getOverride'))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'month_income',
                'month_register_total',
                'ticket_pending_total',
                'commission_pending_total',
                'day_income',
            ]]);
    }
}
