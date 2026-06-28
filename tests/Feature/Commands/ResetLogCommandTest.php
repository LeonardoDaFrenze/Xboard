<?php

namespace Tests\Feature\Commands;

use App\Models\AdminAuditLog;
use App\Models\StatServer;
use App\Models\StatUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetLogCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_log_cleans_up_old_records()
    {
        // 1. Setup Data
        // StatUser & StatServer (Threshold: 2 months)
        $keepStatUser = StatUser::factory()->create(['record_at' => strtotime('-1 month')]);
        $deleteStatUser = StatUser::factory()->create(['record_at' => strtotime('-3 month')]);

        $keepStatServer = StatServer::factory()->create(['record_at' => strtotime('-1 month')]);
        $deleteStatServer = StatServer::factory()->create(['record_at' => strtotime('-3 month')]);

        // AdminAuditLog (Threshold: 3 months)
        $keepAdminLog = AdminAuditLog::factory()->create(['created_at' => date('Y-m-d H:i:s', strtotime('-2 month'))]);
        $deleteAdminLog = AdminAuditLog::factory()->create(['created_at' => date('Y-m-d H:i:s', strtotime('-4 month'))]);

        // 2. Run Command
        $this->artisan('reset:log')->assertExitCode(0);

        // 3. Assertions
        $this->assertDatabaseHas('v2_stat_user', ['id' => $keepStatUser->id]);
        $this->assertDatabaseMissing('v2_stat_user', ['id' => $deleteStatUser->id]);

        $this->assertDatabaseHas('v2_stat_server', ['id' => $keepStatServer->id]);
        $this->assertDatabaseMissing('v2_stat_server', ['id' => $deleteStatServer->id]);

        $this->assertDatabaseHas('v2_admin_audit_log', ['id' => $keepAdminLog->id]);
        $this->assertDatabaseMissing('v2_admin_audit_log', ['id' => $deleteAdminLog->id]);
    }
}
