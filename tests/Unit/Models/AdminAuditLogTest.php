<?php

namespace Tests\Unit\Models;

use App\Models\AdminAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an admin audit log can be created.
     *
     * @return void
     */
    public function test_admin_audit_log_creation_is_successful(): void
    {
        $log = new AdminAuditLog();
        $log->admin_id = 1;
        $log->action = 'server.update';
        $log->method = 'POST';
        $log->uri = '/admin/server';
        $log->request_data = json_encode(['name' => 'New Name']);
        $log->ip = '127.0.0.1';
        $log->save();

        $this->assertModelExists($log);

        $retrieved = AdminAuditLog::find($log->id);
        $this->assertEquals(1, $retrieved->admin_id);
        $this->assertEquals('server.update', $retrieved->action);
    }
}
// Hash shift 1
