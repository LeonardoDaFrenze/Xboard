<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateFromV2b extends Command
{
    protected $signature = 'migrateFromV2b {version?}';
    protected $description = 'Script for migrating different versions of V2b to this project';

    public function handle()
    {
        $version = $this->argument('version');
        if($version === 'config'){
            $this->MigrateV2ConfigToV2Settings();
            return;
        }

        // Define your SQL commands based on versions
        $sqlCommands = [
            'dev231027' => [
                // SQL commands for version Dev 2023/10/27
                'ALTER TABLE v2_order ADD COLUMN surplus_order_ids TEXT NULL;',
                'ALTER TABLE v2_plan DROP COLUMN daily_unit_price, DROP COLUMN transfer_unit_price;',
                'ALTER TABLE v2_server_hysteria DROP COLUMN ignore_client_bandwidth, DROP COLUMN obfs_type;'
            ],            
            '1.7.4' => [
                'CREATE TABLE `v2_server_vless` ( 
                    `id` INT AUTO_INCREMENT PRIMARY KEY, 
                    `group_id` TEXT NOT NULL, 
                    `route_id` TEXT NULL, 
                    `name` VARCHAR(255) NOT NULL,
                    `parent_id` INT NULL, 
                    `host` VARCHAR(255) NOT NULL, 
                    `port` INT NOT NULL, 
                    `server_port` INT NOT NULL, 
                    `tls` BOOLEAN NOT NULL, 
                    `tls_settings` TEXT NULL, 
                    `flow` VARCHAR(64) NULL, 
                    `network` VARCHAR(11) NOT NULL, 
                    `network_settings` TEXT NULL, 
                    `tags` TEXT NULL, 
                    `rate` VARCHAR(11) NOT NULL, 
                    `show` BOOLEAN DEFAULT 0, 
                    `sort` INT NULL, 
                    `created_at` INT NOT NULL, 
                    `updated_at` INT NOT NULL
                );'
            ],
            '1.7.3' => [
                'ALTER TABLE `v2_stat_order` RENAME TO `v2_stat`;',
                "ALTER TABLE `v2_stat` CHANGE COLUMN order_amount paid_total INT COMMENT 'Order Total';",
                "ALTER TABLE `v2_stat` CHANGE COLUMN order_count paid_count INT COMMENT 'Invitation Commission';",
                "ALTER TABLE `v2_stat` CHANGE COLUMN commission_amount commission_total INT COMMENT 'Commission Total';",
                "ALTER TABLE `v2_stat`
                    ADD COLUMN order_count INT NULL,
                    ADD COLUMN order_total INT NULL,
                    ADD COLUMN register_count INT NULL,
                    ADD COLUMN invite_count INT NULL,
                    ADD COLUMN transfer_used_total VARCHAR(32) NULL;
                ",  
                "CREATE TABLE `v2_log` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` TEXT NOT NULL,
                    `level` VARCHAR(11) NULL,
                    `host` VARCHAR(255) NULL,
                    `uri` VARCHAR(255) NOT NULL,
                    `method` VARCHAR(11) NOT NULL,
                    `data` TEXT NULL,
                    `ip` VARCHAR(128) NULL,
                    `context` TEXT NULL,
                    `created_at` INT NOT NULL,
                    `updated_at` INT NOT NULL
                );",
                'CREATE TABLE `v2_server_hysteria` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `group_id` VARCHAR(255) NOT NULL,
                    `route_id` VARCHAR(255) NULL,
                    `name` VARCHAR(255) NOT NULL,
                    `parent_id` INT NULL,
                    `host` VARCHAR(255) NOT NULL,
                    `port` VARCHAR(11) NOT NULL,
                    `server_port` INT NOT NULL,
                    `tags` VARCHAR(255) NULL,
                    `rate` VARCHAR(11) NOT NULL,
                    `show` BOOLEAN DEFAULT FALSE,
                    `sort` INT NULL,
                    `up_mbps` INT NOT NULL,
                    `down_mbps` INT NOT NULL,
                    `server_name` VARCHAR(64) NULL,
                    `insecure` BOOLEAN DEFAULT FALSE,
                    `created_at` INT NOT NULL,
                    `updated_at` INT NOT NULL
                );',
                "CREATE TABLE `v2_server_vless` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY, 
                    `group_id` TEXT NOT NULL, 
                    `route_id` TEXT NULL, 
                    `name` VARCHAR(255) NOT NULL, 
                    `parent_id` INT NULL, 
                    `host` VARCHAR(255) NOT NULL, 
                    `port` INT NOT NULL, 
                    `server_port` INT NOT NULL, 
                    `tls` BOOLEAN NOT NULL, 
                    `tls_settings` TEXT NULL, 
                    `flow` VARCHAR(64) NULL, 
                    `network` VARCHAR(11) NOT NULL, 
                    `network_settings` TEXT NULL, 
                    `tags` TEXT NULL, 
                    `rate` VARCHAR(11) NOT NULL, 
                    `show` BOOLEAN DEFAULT FALSE, 
                    `sort` INT NULL, 
                    `created_at` INT NOT NULL, 
                    `updated_at` INT NOT NULL
                );",
            ],
            'wyx2685' => [
                "ALTER TABLE `v2_plan` DROP COLUMN `device_limit`;",
                "ALTER TABLE `v2_server_hysteria` DROP COLUMN `version`, DROP COLUMN `obfs`, DROP COLUMN `obfs_password`;",
                "ALTER TABLE `v2_server_trojan` DROP COLUMN `network`, DROP COLUMN `network_settings`;",
                "ALTER TABLE `v2_user` DROP COLUMN `device_limit`;"
            ]
        ];

        if (!$version) {
            $version = $this->choice('Please select the V2board version you are migrating from:', array_keys($sqlCommands));
        }

        if (array_key_exists($version, $sqlCommands)) {
            
            try {
                foreach ($sqlCommands[$version] as $sqlCommand) {
                    // Execute SQL command
                    DB::statement($sqlCommand);
                }
                
                $this->info('1️⃣, Database difference correction successful');

// Initialize database migration
                $this->call('db:seed', ['--class' => 'OriginV2bMigrationsTableSeeder']);
                $this->info('2️⃣, Database migration record initialization successful');

                $this->call('xboard:update');
                $this->info('3️⃣, Update successful');

                $this->info("🎉: Successfully migrated from $version to Xboard");
            } catch (\Exception $e) {
                // An error occurred, rollback the transaction
                $this->error('Migration failed'. $e->getMessage() );
            }


        } else {
            $this->error("The version you entered was not found");
        }
    }

    public function MigrateV2ConfigToV2Settings()
    {
        Artisan::call('config:clear');
        $configValue = config('v2board') ?? [];

        foreach ($configValue as $k => $v) {
// Check if the record already exists
            $existingSetting = Setting::where('name', $k)->first();
            
// If the record does not exist, insert it
            if ($existingSetting) {
                $this->warn("Configuration {$k} already exists in the database, ignoring");
                continue;
            }
            Setting::create([
                'name' => $k,
                'value' => is_array($v)? json_encode($v) : $v,
            ]);
            $this->info("Configuration {$k} migration successful");
        }
        Artisan::call('config:cache');

        $this->info('All configurations have been migrated');
    }
}
