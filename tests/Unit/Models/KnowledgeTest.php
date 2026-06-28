<?php

namespace Tests\Unit\Models;

use App\Models\Knowledge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a knowledge article can be created successfully.
     *
     * @return void
     */
    public function test_knowledge_creation_is_successful(): void
    {
        $knowledge = Knowledge::factory()->create([
            'language' => 'en-US',
            'category' => 'Tutorials',
            'title' => 'How to connect on Windows',
            'body' => 'This is the tutorial content.',
            'sort' => 1,
            'show' => 1,
        ]);

        $this->assertDatabaseHas('v2_knowledge', [
            'id' => $knowledge->id,
            'title' => 'How to connect on Windows',
            'show' => 1,
        ]);

        $this->assertInstanceOf(Knowledge::class, $knowledge);
    }

    /**
     * Test that a knowledge article can be updated.
     *
     * @return void
     */
    public function test_knowledge_can_be_updated(): void
    {
        $knowledge = Knowledge::factory()->create([
            'show' => 1,
        ]);

        $knowledge->update([
            'show' => 0,
            'title' => 'Updated Title',
        ]);

        $this->assertDatabaseHas('v2_knowledge', [
            'id' => $knowledge->id,
            'show' => 0,
            'title' => 'Updated Title',
        ]);
    }

    /**
     * Test that a knowledge article can be deleted.
     *
     * @return void
     */
    public function test_knowledge_can_be_deleted(): void
    {
        $knowledge = Knowledge::factory()->create();
        $knowledgeId = $knowledge->id;

        $knowledge->delete();

        $this->assertDatabaseMissing('v2_knowledge', [
            'id' => $knowledgeId,
        ]);
    }
}
