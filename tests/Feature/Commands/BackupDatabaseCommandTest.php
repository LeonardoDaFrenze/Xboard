<?php

namespace Tests\Feature\Commands;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupDatabaseCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        if (!File::exists(storage_path('backup'))) {
            File::makeDirectory(storage_path('backup'), 0755, true);
        }
    }

    public function test_upload_fails_without_required_configs()
    {
        Config::set('database.connections.mysql', null);

        $this->artisan('backup:database', ['upload' => 'true'])
            ->expectsOutputToContain('缺少必要配置项')
            ->assertExitCode(0);
    }

    public function test_fails_with_unsupported_database_driver()
    {
        Config::set('database.default', 'pgsql');

        $this->artisan('backup:database')
            ->expectsOutput('备份失败，你的数据库不是sqlite或者mysql')
            ->assertExitCode(0);
    }
    
    public function test_backup_handles_exception_during_dump()
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql', [
            'host' => 'invalid_host',
            'port' => '3306',
            'database' => 'invalid_db',
            'username' => 'invalid_user',
            'password' => 'invalid_pass',
            'dump' => [
                'dump_binary_path' => '/invalid/path/to/dump',
            ]
        ]);

        $this->artisan('backup:database')
            ->expectsOutputToContain('1️⃣：开始备份Mysql')
            ->assertExitCode(0);
    }
}
