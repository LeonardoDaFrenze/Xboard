<?php

namespace Tests\Unit\Models;

use App\Models\GiftCardCode;
use App\Models\GiftCardTemplate;
use App\Models\GiftCardUsage;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_gift_card_usage_creation(): void
    {
        $template = GiftCardTemplate::factory()->create();
        $code = GiftCardCode::factory()->create(['template_id' => $template->id]);
        $user = User::factory()->create();

        $usage = GiftCardUsage::create([
            'code_id' => $code->id,
            'template_id' => $template->id,
            'user_id' => $user->id,
            'rewards_given' => ['balance' => 1000],
            'multiplier_applied' => 1.0,
            'created_at' => time(),
        ]);

        $this->assertDatabaseHas('v2_gift_card_usage', [
            'id' => $usage->id,
            'code_id' => $code->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_gift_card_usage_belongs_to_code(): void
    {
        $template = GiftCardTemplate::factory()->create();
        $code = GiftCardCode::factory()->create(['template_id' => $template->id]);
        $user = User::factory()->create();

        $usage = GiftCardUsage::create([
            'code_id' => $code->id,
            'template_id' => $template->id,
            'user_id' => $user->id,
            'rewards_given' => ['balance' => 1000],
            'multiplier_applied' => 1.0,
            'created_at' => time(),
        ]);

        $this->assertEquals($code->id, $usage->code->id);
    }

    public function test_gift_card_usage_belongs_to_template(): void
    {
        $template = GiftCardTemplate::factory()->create();
        $code = GiftCardCode::factory()->create(['template_id' => $template->id]);
        $user = User::factory()->create();

        $usage = GiftCardUsage::create([
            'code_id' => $code->id,
            'template_id' => $template->id,
            'user_id' => $user->id,
            'rewards_given' => ['balance' => 1000],
            'multiplier_applied' => 1.0,
            'created_at' => time(),
        ]);

        $this->assertEquals($template->id, $usage->template->id);
    }

    public function test_gift_card_usage_rewards_given_is_array(): void
    {
        $template = GiftCardTemplate::factory()->create();
        $code = GiftCardCode::factory()->create(['template_id' => $template->id]);
        $user = User::factory()->create();

        $usage = GiftCardUsage::create([
            'code_id' => $code->id,
            'template_id' => $template->id,
            'user_id' => $user->id,
            'rewards_given' => ['balance' => 500, 'traffic' => 1073741824],
            'multiplier_applied' => 1.5,
            'created_at' => time(),
        ]);

        $this->assertIsArray($usage->rewards_given);
        $this->assertEquals(500, $usage->rewards_given['balance']);
        $this->assertEquals(1.5, $usage->multiplier_applied);
    }

    public function test_create_record_static_method(): void
    {
        $template = GiftCardTemplate::factory()->create();
        $code = GiftCardCode::factory()->create(['template_id' => $template->id]);
        $user = User::factory()->create();
        $plan = Plan::factory()->create();
        $user->update(['plan_id' => $plan->id, 'invite_user_id' => null]);

        $usage = GiftCardUsage::createRecord($code, $user, ['balance' => 1000], [
            'notes' => 'test record',
        ]);

        $this->assertDatabaseHas('v2_gift_card_usage', [
            'id' => $usage->id,
            'code_id' => $code->id,
            'user_id' => $user->id,
        ]);
        $this->assertEquals('test record', $usage->notes);
    }
}
