<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XboardRollbackCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_xboard_rollback_outputs_message()
    {
        $this->artisan('xboard:rollback')
            ->expectsOutput('正在回滚数据库请稍等...')
            ->assertExitCode(0);
    }
}
