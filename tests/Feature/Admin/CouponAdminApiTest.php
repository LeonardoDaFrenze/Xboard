<?php

namespace Tests\Feature\Admin;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_fetch_coupons_returns_paginated_data()
    {
        Coupon::factory()->count(15)->create();

        $this->json('GET', $this->getAdminUri('coupon/fetch'), ['current' => 1, 'pageSize' => 10])
            ->assertStatus(200)
            ->assertJsonPath('current_page', 1)
            ->assertJsonCount(10, 'data');
    }

    public function test_update_coupon_success()
    {
        $coupon = Coupon::factory()->create(['show' => 0]);

        $this->json('POST', $this->getAdminUri('coupon/update'), [
            'id' => $coupon->id,
            'show' => 1
        ])->assertStatus(200);

        $this->assertEquals(1, $coupon->fresh()->show);
    }

    public function test_show_toggles_status()
    {
        $coupon = Coupon::factory()->create(['show' => 0]);

        $this->json('POST', $this->getAdminUri('coupon/show'), ['id' => $coupon->id])
            ->assertStatus(200);

        $this->assertEquals(1, $coupon->fresh()->show);
    }

    public function test_generate_single_coupon()
    {
        $this->json('POST', $this->getAdminUri('coupon/generate'), [
            'name' => 'Test Coupon',
            'type' => 1,
            'value' => 100,
            'started_at' => time(),
            'ended_at' => time() + 86400
        ])->assertStatus(200);

        $this->assertDatabaseHas('v2_coupon', ['name' => 'Test Coupon']);
    }

    public function test_multi_generate_coupons_outputs_csv()
    {
        $this->withoutExceptionHandling();
        
        // Capture output for the multiGenerate method which uses echo
        ob_start();
        try {
            $this->json('POST', $this->getAdminUri('coupon/generate'), [
                'name' => 'Multi Test',
                'type' => 1,
                'value' => 100,
                'started_at' => time(),
                'ended_at' => time() + 86400,
                'generate_count' => 2
            ]);
        } finally {
            $output = ob_get_clean();
        }

        $this->assertStringContainsString('Multi Test', $output);
        $this->assertDatabaseHas('v2_coupon', ['name' => 'Multi Test']);
        $this->assertEquals(2, Coupon::where('name', 'Multi Test')->count());
    }

    public function test_drop_coupon_success()
    {
        $coupon = Coupon::factory()->create();

        $this->json('POST', $this->getAdminUri('coupon/drop'), ['id' => $coupon->id])
            ->assertStatus(200);

        $this->assertDatabaseMissing('v2_coupon', ['id' => $coupon->id]);
    }
}
