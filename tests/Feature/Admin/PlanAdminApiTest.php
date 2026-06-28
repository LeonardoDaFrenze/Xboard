<?php

namespace Tests\Feature\Admin;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_fetch_plans()
    {
        Plan::factory()->count(3)->create();

        $this->json('GET', $this->getAdminUri('plan/fetch'))
            ->assertStatus(200);
    }

    public function test_save_plan()
    {
        $this->json('POST', $this->getAdminUri('plan/save'), [
            'name' => 'Test Plan',
            'transfer_enable' => 100,
            'device_limit' => 3,
            'speed_limit' => 500,
            'show' => 1,
            'sell' => 1,
            'sort' => 0,
        ])->assertStatus(200);

        $this->assertDatabaseHas('v2_plan', [
            'name' => 'Test Plan',
        ]);
    }

    public function test_update_plan()
    {
        $plan = Plan::factory()->create();

        $this->json('POST', $this->getAdminUri('plan/update'), [
            'id' => $plan->id,
            'name' => 'Updated Plan',
        ])->assertStatus(200);
    }

    public function test_drop_plan()
    {
        $plan = Plan::factory()->create();

        $this->json('POST', $this->getAdminUri('plan/drop'), [
            'id' => $plan->id,
        ])->assertStatus(200);

        $this->assertDatabaseMissing('v2_plan', ['id' => $plan->id]);
    }

    public function test_sort_plans()
    {
        $plan1 = Plan::factory()->create(['sort' => 1]);
        $plan2 = Plan::factory()->create(['sort' => 2]);

        $this->json('POST', $this->getAdminUri('plan/sort'), [
            'ids' => [$plan2->id, $plan1->id],
        ])->assertStatus(200);
    }
}
