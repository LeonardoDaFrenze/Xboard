<?php

namespace Tests\Unit\Models;

use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a notice can be created successfully.
     *
     * @return void
     */
    public function test_notice_creation_is_successful(): void
    {
        $notice = Notice::factory()->create([
            'title' => 'System Maintenance',
            'content' => 'The system will be down for maintenance.',
            'show' => true,
            'tags' => ['maintenance', 'downtime'],
        ]);

        $this->assertDatabaseHas('v2_notice', [
            'id' => $notice->id,
            'title' => 'System Maintenance',
        ]);

        $this->assertInstanceOf(Notice::class, $notice);
    }

    /**
     * Test that notice attributes are casted correctly.
     *
     * @return void
     */
    public function test_notice_casts_are_applied(): void
    {
        $notice = Notice::factory()->create([
            'show' => 1,
            'tags' => ['important', 'news'],
        ]);

        $this->assertIsArray($notice->tags);
        $this->assertContains('important', $notice->tags);
        $this->assertIsBool($notice->show);
        $this->assertTrue($notice->show);
    }

    /**
     * Test that a notice can be updated.
     *
     * @return void
     */
    public function test_notice_can_be_updated(): void
    {
        $notice = Notice::factory()->create([
            'title' => 'Old Title',
            'show' => true,
        ]);

        $notice->update([
            'title' => 'New Title',
            'show' => false,
        ]);

        $this->assertDatabaseHas('v2_notice', [
            'id' => $notice->id,
            'title' => 'New Title',
            'show' => false,
        ]);
    }

    /**
     * Test that a notice can be deleted.
     *
     * @return void
     */
    public function test_notice_can_be_deleted(): void
    {
        $notice = Notice::factory()->create();

        $noticeId = $notice->id;

        $notice->delete();

        $this->assertDatabaseMissing('v2_notice', [
            'id' => $noticeId,
        ]);
    }
}
