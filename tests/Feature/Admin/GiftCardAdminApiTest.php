<?php

namespace Tests\Feature\Admin;

use App\Models\GiftCardCode;
use App\Models\GiftCardTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_template_crud_workflow()
    {
        // Create
        $response = $this->json('POST', $this->getAdminUri('gift-card/create-template'), [
            'name' => 'Test Template',
            'type' => 1,
            'rewards' => ['balance' => 100],
            'theme_color' => '#123456'
        ]);
        $response->assertStatus(200);
        $templateId = $response->json('data.id');

        // List
        $this->json('GET', $this->getAdminUri('gift-card/templates'))
            ->assertStatus(200)
            ->assertJsonPath('total', 1);

        // Update
        $this->json('POST', $this->getAdminUri('gift-card/update-template'), [
            'id' => $templateId,
            'name' => 'Updated Name'
        ])->assertStatus(200);
        $this->assertEquals('Updated Name', GiftCardTemplate::find($templateId)->name);

        // Delete (should fail if code existed, but empty is fine)
        $this->json('POST', $this->getAdminUri('gift-card/delete-template'), ['id' => $templateId])
            ->assertStatus(200);
    }

    public function test_generate_and_list_codes()
    {
        $template = GiftCardTemplate::factory()->create(['type' => 1]);

        // Generate
        $this->json('POST', $this->getAdminUri('gift-card/generate-codes'), [
            'template_id' => $template->id,
            'count' => 5
        ])->assertStatus(200);

        // Fetch Codes
        $this->json('GET', $this->getAdminUri('gift-card/codes'), ['template_id' => $template->id])
            ->assertStatus(200)
            ->assertJsonPath('total', 5);
    }

    public function test_toggle_and_delete_code()
    {
        $code = GiftCardCode::factory()->create();

        // Toggle
        $this->json('POST', $this->getAdminUri('gift-card/toggle-code'), [
            'id' => $code->id,
            'action' => 'disable'
        ])->assertStatus(200);
        $this->assertEquals(GiftCardCode::STATUS_DISABLED, $code->fresh()->status);

        // Delete
        $this->json('POST', $this->getAdminUri('gift-card/delete-code'), ['id' => $code->id])
            ->assertStatus(200);
        $this->assertDatabaseMissing('v2_gift_card_code', ['id' => $code->id]);
    }

    public function test_statistics_endpoint()
    {
        $this->json('GET', $this->getAdminUri('gift-card/statistics'))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['total_stats', 'daily_usages', 'type_stats']]);
    }

    public function test_types_endpoint()
    {
        $this->json('GET', $this->getAdminUri('gift-card/types'))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
