<?php

namespace Tests\Unit\Models;

use App\Models\GiftCardTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardTemplateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a gift card template can be created.
     *
     * @return void
     */
    public function test_gift_card_template_creation_is_successful(): void
    {
        $template = new GiftCardTemplate();
        $template->name = '100$ Card';
        $template->type = GiftCardTemplate::TYPE_GENERAL;
        $template->status = 1;
        $template->rewards = ['balance' => 10000];
        $template->admin_id = 1;
        $template->save();

        $this->assertModelExists($template);

        $retrieved = GiftCardTemplate::find($template->id);
        $this->assertEquals('100$ Card', $retrieved->name);
        $this->assertEquals(['balance' => 10000], $retrieved->rewards);
    }
}
