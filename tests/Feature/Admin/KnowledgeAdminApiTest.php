<?php

namespace Tests\Feature\Admin;

use App\Models\Knowledge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    public function test_fetch_knowledge_list()
    {
        Knowledge::factory()->count(3)->create();
        $this->json('GET', $this->getAdminUri('knowledge/fetch'))
             ->assertStatus(200)
             ->assertJsonCount(3, 'data');
    }

    public function test_fetch_knowledge_by_id()
    {
        $knowledge = Knowledge::factory()->create();
        $this->json('GET', $this->getAdminUri('knowledge/fetch'), ['id' => $knowledge->id])
             ->assertStatus(200)
             ->assertJsonPath('data.id', $knowledge->id);
    }

    public function test_get_categories()
    {
        Knowledge::factory()->create(['category' => 'Test Cat']);
        $this->json('GET', $this->getAdminUri('knowledge/getCategory'))
             ->assertStatus(200)
             ->assertJsonPath('data.0', 'Test Cat');
    }

    public function test_save_knowledge_create()
    {
        $this->json('POST', $this->getAdminUri('knowledge/save'), [
            'title' => 'New Knowledge',
            'category' => 'Test',
            'body' => 'Content',
            'language' => 'en-US',
            'show' => 1,
            'sort' => 1
        ])->assertStatus(200);

        $this->assertDatabaseHas('v2_knowledge', ['title' => 'New Knowledge']);
    }

    public function test_save_knowledge_update()
    {
        $knowledge = Knowledge::factory()->create();
        $this->json('POST', $this->getAdminUri('knowledge/save'), [
            'id' => $knowledge->id,
            'title' => 'Updated Title',
            'category' => $knowledge->category,
            'body' => $knowledge->body,
            'language' => $knowledge->language,
            'show' => $knowledge->show,
            'sort' => $knowledge->sort
        ])->assertStatus(200);

        $this->assertEquals('Updated Title', $knowledge->fresh()->title);
    }

    public function test_show_toggle()
    {
        $knowledge = Knowledge::factory()->create(['show' => 0]);
        $this->json('POST', $this->getAdminUri('knowledge/show'), ['id' => $knowledge->id])
             ->assertStatus(200);

        $this->assertEquals(1, $knowledge->fresh()->show);
    }

    public function test_sort_knowledge()
    {
        $k1 = Knowledge::factory()->create(['sort' => 1]);
        $k2 = Knowledge::factory()->create(['sort' => 2]);
        $this->json('POST', $this->getAdminUri('knowledge/sort'), ['ids' => [$k2->id, $k1->id]])
             ->assertStatus(200);

        $this->assertEquals(1, $k2->fresh()->sort);
        $this->assertEquals(2, $k1->fresh()->sort);
    }

    public function test_drop_knowledge()
    {
        $knowledge = Knowledge::factory()->create();
        $this->json('POST', $this->getAdminUri('knowledge/drop'), ['id' => $knowledge->id])
             ->assertStatus(200);
        $this->assertDatabaseMissing('v2_knowledge', ['id' => $knowledge->id]);
    }
}
