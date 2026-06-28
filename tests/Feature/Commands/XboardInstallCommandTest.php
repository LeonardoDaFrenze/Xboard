<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class XboardInstallCommandTest extends TestCase
{
    use RefreshDatabase;

    protected ?string $originalDefaultConnection = null;
    protected array $originalMysqlConfig = [];

    protected function setUp(): void
    {
        parent::setUp();
        if (File::exists(base_path('.env'))) {
            File::delete(base_path('.env'));
        }
        $this->originalDefaultConnection = config('database.default');
        $this->originalMysqlConfig = config('database.connections.mysql', []);
    }

    protected function tearDown(): void
    {
        try {
            if (File::exists(base_path('.env'))) {
                File::delete(base_path('.env'));
            }
            if ($this->originalDefaultConnection !== null) {
                config(['database.default' => $this->originalDefaultConnection]);
            }
            if (!empty($this->originalMysqlConfig)) {
                config(['database.connections.mysql' => $this->originalMysqlConfig]);
            }
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    public function test_xboard_install_already_installed()
    {
        File::put(base_path('.env'), "INSTALLED=true");

        $this->artisan('xboard:install')
            ->expectsOutputToContain('Visit http(s)://your-site')
            ->assertExitCode(0);
    }

    public function test_xboard_install_mysql_connection_fail()
    {
        File::put(base_path('.env.example'), "INSTALLED=false");

        \Laravel\Prompts\Prompt::fake([
            \Laravel\Prompts\Key::DOWN,
            \Laravel\Prompts\Key::ENTER,
            "invalid_host_that_does_not_exist\n",
            "3306\n",
            "xboard\n",
            "root\n",
            "\n",
        ]);

        // Mock DB connection to fail immediately
        $connectionMock = Mockery::mock(\Illuminate\Database\Connection::class);
        $connectionMock->shouldReceive('getPdo')
            ->once()
            ->andThrow(new \PDOException("Mocked connection failure"));

        \Illuminate\Support\Facades\DB::shouldReceive('purge')->once()->with('mysql');
        \Illuminate\Support\Facades\DB::shouldReceive('connection')->once()->with('mysql')->andReturn($connectionMock);

        $this->artisan('xboard:install')
            ->expectsOutputToContain('MySQL connection failed')
            ->assertExitCode(0);
    }
}
