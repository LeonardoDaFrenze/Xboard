<?php

namespace Tests\Unit\Services;

use App\Models\GiftCardCode;
use App\Models\GiftCardTemplate;
use App\Models\User;
use App\Services\GiftCardService;
use App\Exceptions\ApiException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_constructor_throws_for_nonexistent_code(): void
    {
        $this->expectException(ApiException::class);

        new GiftCardService('NONEXISTENT');
    }

    public function test_validate_fails_for_disabled_template(): void
    {
        $template = GiftCardTemplate::factory()->create([
            'status' => 0,
            'type' => GiftCardTemplate::TYPE_GENERAL,
        ]);
        $code = GiftCardCode::factory()->create([
            'template_id' => $template->id,
            'status' => GiftCardCode::STATUS_UNUSED,
        ]);

        $service = new GiftCardService($code->code);

        $this->expectException(ApiException::class);
        $service->validateIsActive();
    }

    public function test_validate_fails_for_disabled_code(): void
    {
        $template = GiftCardTemplate::factory()->create([
            'status' => 1,
            'type' => GiftCardTemplate::TYPE_GENERAL,
        ]);
        $code = GiftCardCode::factory()->create([
            'template_id' => $template->id,
            'status' => GiftCardCode::STATUS_DISABLED,
        ]);

        $service = new GiftCardService($code->code);

        $this->expectException(ApiException::class);
        $service->validateIsActive();
    }

    public function test_valid_gift_card_passes_validation(): void
    {
        $template = GiftCardTemplate::factory()->create([
            'status' => 1,
            'type' => GiftCardTemplate::TYPE_GENERAL,
        ]);
        $code = GiftCardCode::factory()->create([
            'template_id' => $template->id,
            'status' => GiftCardCode::STATUS_UNUSED,
        ]);

        $service = new GiftCardService($code->code);

        $service->validateIsActive();
        $this->assertTrue(true);
    }

    public function test_check_user_eligibility_returns_false_without_user(): void
    {
        $template = GiftCardTemplate::factory()->create([
            'status' => 1,
            'type' => GiftCardTemplate::TYPE_GENERAL,
        ]);
        $code = GiftCardCode::factory()->create([
            'template_id' => $template->id,
            'status' => GiftCardCode::STATUS_UNUSED,
        ]);

        $service = new GiftCardService($code->code);
        $result = $service->checkUserEligibility();

        $this->assertFalse($result['can_redeem']);
    }

    public function test_redeem_without_user_throws(): void
    {
        $template = GiftCardTemplate::factory()->create([
            'status' => 1,
            'type' => GiftCardTemplate::TYPE_GENERAL,
        ]);
        $code = GiftCardCode::factory()->create([
            'template_id' => $template->id,
            'status' => GiftCardCode::STATUS_UNUSED,
        ]);

        $service = new GiftCardService($code->code);

        $this->expectException(ApiException::class);
        $service->redeem();
    }
}
