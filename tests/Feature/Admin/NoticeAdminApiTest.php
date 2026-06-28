<?php

namespace Tests\Feature\Admin;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_fetch_notices()
    {
        Notice::factory()->count(3)->create();
        $this->json('GET', $this->getAdminUri('notice/fetch'))
             ->assertStatus(200)
             ->assertJsonCount(3, 'data');
    }

    public function test_save_notice_create()
    {
        $this->json('POST', $this->getAdminUri('notice/save'), [
            'title' => 'Test Notice',
            'content' => 'Notice Content',
            'show' => 1,
            'popup' => 0
        ])->assertStatus(200);

        $this->assertDatabaseHas('v2_notice', ['title' => 'Test Notice']);
    }

    public function test_save_notice_update()
    {
        $notice = Notice::factory()->create();
        $this->json('POST', $this->getAdminUri('notice/save'), [
            'id' => $notice->id,
            'title' => 'Updated Notice',
            'content' => 'Content'
        ])->assertStatus(200);

        $this->assertEquals('Updated Notice', $notice->fresh()->title);
    }

    public function test_show_toggle()
    {
        $notice = Notice::factory()->create(['show' => 0]);
        $this->json('POST', $this->getAdminUri('notice/show'), ['id' => $notice->id])
             ->assertStatus(200);

        $this->assertEquals(1, $notice->fresh()->show);
    }

    public function test_sort_notices()
    {
        $n1 = Notice::factory()->create(['sort' => 1]);
        $n2 = Notice::factory()->create(['sort' => 2]);
        $this->json('POST', $this->getAdminUri('notice/sort'), ['ids' => [$n2->id, $n1->id]])
             ->assertStatus(200);

        $this->assertEquals(1, $n2->fresh()->sort);
        $this->assertEquals(2, $n1->fresh()->sort);
    }

    public function test_drop_notice()
    {
        $notice = Notice::factory()->create();
        $this->json('POST', $this->getAdminUri('notice/drop'), ['id' => $notice->id])
             ->assertStatus(200);
        $this->assertDatabaseMissing('v2_notice', ['id' => $notice->id]);
    }
}
