<?php

namespace Tests\Unit\Models;

use App\Models\StatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user statistics can be logged correctly.
     *
     * @return void
     */
    public function test_stat_user_creation_is_successful(): void
    {
        $user = User::factory()->create();

        $stat = new StatUser();
        $stat->user_id = $user->id;
        $stat->server_rate = 1.0;
        $stat->u = 5000;
        $stat->d = 10000;
        $stat->record_type = 'd';
        $stat->record_at = time();
        $stat->save();

        $this->assertModelExists($stat);

        $retrieved = StatUser::find($stat->id);
        $this->assertEquals($user->id, $retrieved->user_id);
        $this->assertEquals(5000, $retrieved->u);
        $this->assertEquals(10000, $retrieved->d);
        $this->assertEquals('d', $retrieved->record_type);
    }

    /**
     * Test relations or properties if user is deleted (depends on cascading rules, testing integrity).
     *
     * @return void
     */
    public function test_stat_user_belongs_to_user_conceptually(): void
    {
        $user = User::factory()->create();

        $stat = new StatUser();
        $stat->user_id = $user->id;
        $stat->server_rate = 1.5;
        $stat->u = 100;
        $stat->d = 200;
        $stat->record_type = 'm';
        $stat->record_at = time();
        $stat->save();

        $this->assertEquals($user->id, $stat->user_id);
        
        $stat->delete();
        $this->assertModelMissing($stat);
    }
}
