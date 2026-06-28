<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Knowledge;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KnowledgeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_knowledge_list()
    {
        $user = User::factory()->create();
        
        Knowledge::factory()->create([
            'title' => 'How to setup Windows client',
            'show' => 1
        ]);

        Knowledge::factory()->create([
            'title' => 'Hidden Secret Document',
            'show' => 0
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/user/knowledge/fetch?language=en-US');

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'How to setup Windows client'])
                 ->assertJsonMissing(['title' => 'Hidden Secret Document']);
    }
}
