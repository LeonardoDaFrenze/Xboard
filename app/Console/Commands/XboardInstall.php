<?php

namespace App\Console\Commands;

use App\Services\Plugin\PluginManager;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use App\Models\User;
use App\Utils\Helper;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use function Laravel\Prompts\note;
use function Laravel\Prompts\select;

class XboardInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xboard:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XXXBoard initial installation wizard';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $isDocker = file_exists('/.dockerenv');
            $enableSqlite = getenv('ENABLE_SQLITE', false);
            $enableRedis = getenv('ENABLE_REDIS', false);
            $adminAccount = getenv('ADMIN_ACCOUNT', false);
            $this->info("__    __ ____                      _  ");
            $this->info("\ \  / /| __ )  ___   __ _ _ __ __| | ");
            $this->info(" \ \/ / | __ \ / _ \ / _` | '__/ _` | ");
            $this->info(" / /\ \ | |_) | (_) | (_| | | | (_| | ");
            $this->info("/_/  \_\|____/ \___/ \__,_|_|  \__,_| ");
            if (
                (File::exists(base_path() . '/.env') && $this->getEnvValue('INSTALLED'))
                || (getenv('INSTALLED', false) && $isDocker)
            ) {
                $securePath = admin_setting('secure_path', admin_setting('frontend_admin_path', hash('crc32b', config('app.key') ?? '')));
                $this->info("Visit http(s)://your-site/{$securePath} to access the admin panel. You can change your password in the user center.");
                $this->warn("To reinstall, clear the contents of the .env file (Docker users: do not delete the file).");
                $this->warn("Quick clear command:");
                note('rm .env && touch .env');
                return;
            }
            if (is_dir(base_path() . '/.env')) {
                $this->error('Installation failed — in Docker environments, keep an empty .env file instead of a directory.');
                return;
            }
            // Select database type
            $dbType = $enableSqlite ? 'sqlite' : select(
                label: 'Select database type',
                options: [
                    'sqlite' => 'SQLite (no extra setup required)',
                    'mysql' => 'MySQL',
                    'postgresql' => 'PostgreSQL'
                ],
                default: 'sqlite'
            );

            $envConfig = match ($dbType) {
                'sqlite' => $this->configureSqlite(),
                'mysql' => $this->configureMysql(),
                'postgresql' => $this->configurePostgresql(),
                default => throw new \InvalidArgumentException("Unsupported database type: {$dbType}")
            };

            if (is_null($envConfig)) {
                return;
            }
            $envConfig['APP_KEY'] = 'base64:' . base64_encode(Encrypter::generateKey('AES-256-CBC'));
            $isReidsValid = false;
            while (!$isReidsValid) {
                $useBuiltinRedis = $isDocker && ($enableRedis || confirm(label: 'Use the built-in Docker Redis?', default: true, yes: 'Yes', no: 'No'));
                if ($useBuiltinRedis) {
                    $envConfig['REDIS_HOST'] = '/data/redis.sock';
                    $envConfig['REDIS_PORT'] = 0;
                    $envConfig['REDIS_PASSWORD'] = null;
                    $isReidsValid = true;
                    break;
                }
                $envConfig['REDIS_HOST'] = text(label: 'Redis host', default: '127.0.0.1', required: true);
                $envConfig['REDIS_PORT'] = text(label: 'Redis port', default: '6379', required: true);
                $envConfig['REDIS_PASSWORD'] = text(label: 'Redis password (leave blank for none)', default: '');
                $redisConfig = [
                    'client' => 'phpredis',
                    'default' => [
                        'host' => $envConfig['REDIS_HOST'],
                        'password' => $envConfig['REDIS_PASSWORD'],
                        'port' => $envConfig['REDIS_PORT'],
                        'database' => 0,
                    ],
                ];
                try {
                    $redis = new \Illuminate\Redis\RedisManager(app(), 'phpredis', $redisConfig);
                    $redis->ping();
                    $isReidsValid = true;
                } catch (\Exception $e) {
                    $this->error("Redis connection failed: " . $e->getMessage());
                    $this->info("Please re-enter Redis configuration.");
                    $enableRedis = false;
                    sleep(1);
                }
            }

            if (!copy(base_path() . '/.env.example', base_path() . '/.env')) {
                abort(500, 'Failed to copy environment file — check directory permissions.');
            }
            ;
            $email = !empty($adminAccount) ? $adminAccount : text(
                label: 'Admin email address',
                default: 'admin@demo.com',
                required: true,
                validate: fn(string $email): ?string => match (true) {
                    !filter_var($email, FILTER_VALIDATE_EMAIL) => 'Please enter a valid email address.',
                    default => null,
                }
            );
            $password = Helper::guid(false);
            $this->saveToEnv($envConfig);

            $installDriverOverrides = [
                'CACHE_DRIVER' => 'array',
                'QUEUE_CONNECTION' => 'sync',
                'SESSION_DRIVER' => 'array',
            ];
            foreach ($installDriverOverrides as $key => $value) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
            Config::set('cache.default', 'array');
            Config::set('queue.default', 'sync');
            Config::set('session.driver', 'array');

            $this->call('config:cache');
            Artisan::call('cache:clear');
            $this->info('Importing database, please wait...');
            Artisan::call("migrate", ['--force' => true]);
            $this->info(Artisan::output());
            $this->info('Database import complete.');
            $this->info('Registering admin account...');
            if (!self::registerAdmin($email, $password)) {
                abort(500, 'Admin account registration failed — please try again.');
            }
            $this->info('Installing default plugins...');
            PluginManager::installDefaultPlugins();
            $this->info('Default plugins installed.');

            $this->info('Installation complete.');
            $this->info("Admin email: {$email}");
            $this->info("Admin password: {$password}");

            $defaultSecurePath = hash('crc32b', config('app.key') ?? '');
            $this->info("Visit http(s)://your-site/{$defaultSecurePath} to access the admin panel. You can change your password in the user center.");
            $envConfig['INSTALLED'] = true;
            $this->saveToEnv($envConfig);
            foreach (array_keys($installDriverOverrides) as $key) {
                putenv($key);
                unset($_ENV[$key], $_SERVER[$key]);
            }
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            $this->error($e);
        }
    }

    public static function registerAdmin($email, $password)
    {
        $user = new User();
        $user->email = $email;
        if (strlen($password) < 8) {
            abort(500, 'Admin password must be at least 8 characters.');
        }
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->uuid = Helper::guid(true);
        $user->token = Helper::guid();
        $user->is_admin = 1;
        return $user->save();
    }

    private function set_env_var($key, $value)
    {
        $value = !strpos($value, ' ') ? $value : '"' . $value . '"';
        $key = strtoupper($key);

        $envPath = app()->environmentFilePath();
        $contents = file_get_contents($envPath);

        if (preg_match("/^{$key}=[^\r\n]*/m", $contents, $matches)) {
            $contents = str_replace($matches[0], "{$key}={$value}", $contents);
        } else {
            $contents .= "\n{$key}={$value}\n";
        }

        return file_put_contents($envPath, $contents) !== false;
    }

    private function saveToEnv($data = [])
    {
        foreach ($data as $key => $value) {
            self::set_env_var($key, $value);
        }
        return true;
    }

    function getEnvValue($key, $default = null)
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(base_path());
        $dotenv->load();

        return Env::get($key, $default);
    }

    /**
     * Configure SQLite database
     *
     * @return array|null
     */
    private function configureSqlite(): ?array
    {
        $sqliteFile = '.docker/.data/database.sqlite';
        if (!file_exists(base_path($sqliteFile))) {
            if (!touch(base_path($sqliteFile))) {
                $this->info("SQLite file created: $sqliteFile");
            }
        }

        $envConfig = [
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $sqliteFile,
            'DB_HOST' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ];

        try {
            Config::set("database.default", 'sqlite');
            Config::set("database.connections.sqlite.database", base_path($envConfig['DB_DATABASE']));
            DB::purge('sqlite');
            DB::connection('sqlite')->getPdo();

            if (!blank(DB::connection('sqlite')->getPdo()->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(\PDO::FETCH_COLUMN))) {
                if (confirm(label: 'Existing data detected in the database. Clear it to proceed with installation?', default: false, yes: 'Clear', no: 'Cancel')) {
                    $this->info('Clearing database...');
                    $this->call('db:wipe', ['--force' => true]);
                    $this->info('Database cleared.');
                } else {
                    return null;
                }
            }
        } catch (\Exception $e) {
            $this->error("SQLite connection failed: " . $e->getMessage());
            return null;
        }

        return $envConfig;
    }

    /**
     * Configure MySQL database
     *
     * @return array
     */
    private function configureMysql(): array
    {
        while (true) {
            $envConfig = [
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => text(label: "MySQL host", default: '127.0.0.1', required: true),
                'DB_PORT' => text(label: 'MySQL port', default: '3306', required: true),
                'DB_DATABASE' => text(label: 'MySQL database name', default: 'xboard', required: true),
                'DB_USERNAME' => text(label: 'MySQL username', default: 'root', required: true),
                'DB_PASSWORD' => text(label: 'MySQL password', required: false),
            ];

            try {
                Config::set("database.default", 'mysql');
                Config::set("database.connections.mysql.host", $envConfig['DB_HOST']);
                Config::set("database.connections.mysql.port", $envConfig['DB_PORT']);
                Config::set("database.connections.mysql.database", $envConfig['DB_DATABASE']);
                Config::set("database.connections.mysql.username", $envConfig['DB_USERNAME']);
                Config::set("database.connections.mysql.password", $envConfig['DB_PASSWORD']);
                DB::purge('mysql');
                DB::connection('mysql')->getPdo();

                if (!blank(DB::connection('mysql')->select('SHOW TABLES'))) {
                    if (confirm(label: 'Existing data detected in the database. Clear it to proceed with installation?', default: false, yes: 'Clear', no: 'Cancel')) {
                        $this->info('Clearing database...');
                        $this->call('db:wipe', ['--force' => true]);
                        $this->info('Database cleared.');
                        return $envConfig;
                    } else {
                        continue;
                    }
                }

                return $envConfig;
            } catch (\Exception $e) {
                $this->error("MySQL connection failed: " . $e->getMessage());
                $this->info("Please re-enter MySQL configuration.");
            }
        }
    }

    /**
     * Configure PostgreSQL database
     *
     * @return array
     */
    private function configurePostgresql(): array
    {
        while (true) {
            $envConfig = [
                'DB_CONNECTION' => 'pgsql',
                'DB_HOST' => text(label: "PostgreSQL host", default: '127.0.0.1', required: true),
                'DB_PORT' => text(label: 'PostgreSQL port', default: '5432', required: true),
                'DB_DATABASE' => text(label: 'PostgreSQL database name', default: 'xboard', required: true),
                'DB_USERNAME' => text(label: 'PostgreSQL username', default: 'postgres', required: true),
                'DB_PASSWORD' => text(label: 'PostgreSQL password', required: false),
            ];

            try {
                Config::set("database.default", 'pgsql');
                Config::set("database.connections.pgsql.host", $envConfig['DB_HOST']);
                Config::set("database.connections.pgsql.port", $envConfig['DB_PORT']);
                Config::set("database.connections.pgsql.database", $envConfig['DB_DATABASE']);
                Config::set("database.connections.pgsql.username", $envConfig['DB_USERNAME']);
                Config::set("database.connections.pgsql.password", $envConfig['DB_PASSWORD']);
                DB::purge('pgsql');
                DB::connection('pgsql')->getPdo();

                // Check if database already has tables
                $tables = DB::connection('pgsql')->select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                if (!blank($tables)) {
                    if (confirm(label: 'Existing data detected in the database. Clear it to proceed with installation?', default: false, yes: 'Clear', no: 'Cancel')) {
                        $this->info('Clearing database...');
                        $this->call('db:wipe', ['--force' => true]);
                        $this->info('Database cleared.');
                        return $envConfig;
                    } else {
                        continue;
                    }
                }

                return $envConfig;
            } catch (\Exception $e) {
                $this->error("PostgreSQL connection failed: " . $e->getMessage());
                $this->info("Please re-enter PostgreSQL configuration.");
            }
        }
    }
}
