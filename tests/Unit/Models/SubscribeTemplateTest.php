<?php

namespace Tests\Unit\Models;

use App\Models\SubscribeTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscribeTemplateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a subscribe template can be created.
     *
     * @return void
     */
    public function test_subscribe_template_creation_is_successful(): void
    {
        $template = new SubscribeTemplate();
        $template->name = 'clash_new'; // Use a unique name to avoid conflicts with seeded defaults
        $template->content = "mixed-port: 7890\nallow-lan: true\nmode: rule";
        $template->save();

        $this->assertModelExists($template);

        $retrieved = SubscribeTemplate::find($template->id);
        $this->assertEquals('clash_new', $retrieved->name);
        $this->assertEquals("mixed-port: 7890\nallow-lan: true\nmode: rule", $retrieved->content);
    }

    /**
     * Test that a subscribe template can be updated.
     *
     * @return void
     */
    public function test_subscribe_template_can_be_updated(): void
    {
        $template = new SubscribeTemplate();
        $template->name = 'v2ray';
        $template->save();

        $template->name = 'v2ray_pro';
        $template->save();

        $this->assertEquals('v2ray_pro', $template->fresh()->name);
    }

    /**
     * Test that a subscribe template can be deleted.
     *
     * @return void
     */
    public function test_subscribe_template_can_be_deleted(): void
    {
        $template = new SubscribeTemplate();
        $template->name = 'Delete Me';
        $template->save();

        $template->delete();

        $this->assertModelMissing($template);
    }
}
