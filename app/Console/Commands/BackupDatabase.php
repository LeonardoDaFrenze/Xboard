<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {upload?}';
    protected $description = 'Backup the database and upload it to Google Cloud Storage';

    public function handle()
    {
        $isUpload = $this->argument('upload');
// If uploading to the cloud, check if necessary configuration exists
        if($isUpload){
            $requiredConfigs = ['database.connections.mysql', 'cloud_storage.google_cloud.key_file', 'cloud_storage.google_cloud.storage_bucket'];
            foreach ($requiredConfigs as $config) {
                if (blank(config($config))) {
                    $this->error("❌: Missing necessary configuration item: $config , cancel backup");
                    return;
                }
            }
        }

// Database backup logic
        $compressedBackupPath = null;
        try{
            if (config('database.default') === 'mysql'){
                $databaseBackupPath = storage_path('backup/' .  now()->format('Y-m-d_H-i-s') . '_' . config('database.connections.mysql.database') . '_database_backup.sql');
                $this->info("1️⃣: Start backing up MySQL");
                \Spatie\DbDumper\Databases\MySql::create()
                    ->setHost(config('database.connections.mysql.host'))
                    ->setPort(config('database.connections.mysql.port'))
                    ->setDbName(config('database.connections.mysql.database'))
                    ->setUserName(config('database.connections.mysql.username'))
                    ->setPassword(config('database.connections.mysql.password'))
                    ->dumpToFile($databaseBackupPath);
                $this->info("2️⃣: MySQL backup completed");
            }elseif(config('database.default') === 'sqlite'){
                $databaseBackupPath = storage_path('backup/' .  now()->format('Y-m-d_H-i-s') . '_sqlite'  . '_database_backup.sql');
                $this->info("1️⃣: Start backing up SQLite");
                \Spatie\DbDumper\Databases\Sqlite::create()
                    ->setDbName(config('database.connections.sqlite.database'))
                    ->dumpToFile($databaseBackupPath);
                $this->info("2️⃣: SQLite backup completed");
            }else{
                $this->error('Backup failed, your database is not SQLite or MySQL');
                return;
            }
            $this->info('3️⃣: Start compressing backup file');
// Use gzip to compress the backup file
            $compressedBackupPath = $databaseBackupPath . '.gz';
            $gzipCommand = new Process(["gzip", "-c", $databaseBackupPath]);
            $gzipCommand->run();

// Check if compression was successful
            if ($gzipCommand->isSuccessful()) {
// Compression successful, you can delete the original backup file
                file_put_contents($compressedBackupPath, $gzipCommand->getOutput());
                $this->info('4️⃣: File compression successful');
                unlink($databaseBackupPath);
            } else {
// Compression failed, handle error
                echo $gzipCommand->getErrorOutput();
                $this->error('😔: File compression failed');
                unlink($databaseBackupPath);
                return;
            }
            if (!$isUpload){
                $this->info("🎉: Database successfully backed up to: $compressedBackupPath");
            }else{
// Upload to cloud storage
                $this->info("5️⃣: Start uploading backup to Google Cloud");
// Google Cloud Storage configuration
                $storage = new StorageClient([
                    'keyFilePath' => config('cloud_storage.google_cloud.key_file'),
                ]);
                $bucket = $storage->bucket(config('cloud_storage.google_cloud.storage_bucket'));
                $objectName = 'backup/' . now()->format('Y-m-d_H-i-s') . '_database_backup.sql.gz';
// Upload file
                $bucket->upload(fopen($compressedBackupPath, 'r'), [
                    'name' => $objectName,
                ]);
        
// Output file link
                Log::channel('backup')->info("🎉: Database backup uploaded to Google Cloud Storage: $objectName");
                $this->info("🎉: Database backup uploaded to Google Cloud Storage: $objectName");
                File::delete($compressedBackupPath);
            }
        }catch(\Exception $e){
            Log::channel('backup')->error("😔: Database backup failed \n" . $e);
            $this->error("😔: Database backup failed\n" . $e);
            File::delete($compressedBackupPath);
        }
    }
}
