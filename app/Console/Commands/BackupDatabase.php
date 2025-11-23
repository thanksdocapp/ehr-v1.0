<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup 
                            {--path= : Custom path for backup file}
                            {--compress : Compress the backup file}
                            {--keep= : Number of backups to keep (default: 7)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connection = DB::getDefaultConnection();
        $config = DB::connection()->getConfig();
        $driver = $config['driver'];

        $this->info('🔄 Creating database backup...');

        try {
            // Determine backup path
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = Carbon::now()->format('Y-m-d_His');
            $dbName = $config['database'] ?? 'database';
            $backupFilename = "{$dbName}_{$timestamp}.sql";
            
            if ($customPath = $this->option('path')) {
                $backupPath = $customPath;
            } else {
                $backupPath = $backupDir . '/' . $backupFilename;
            }

            // Create backup based on driver
            switch ($driver) {
                case 'mysql':
                    $this->backupMySQL($config, $backupPath);
                    break;
                case 'pgsql':
                    $this->backupPostgreSQL($config, $backupPath);
                    break;
                case 'sqlite':
                    $this->backupSQLite($config, $backupPath);
                    break;
                default:
                    $this->error("Unsupported database driver: {$driver}");
                    return 1;
            }

            // Compress if requested
            if ($this->option('compress')) {
                $backupPath = $this->compressBackup($backupPath);
            }

            // Get file size
            $fileSize = filesize($backupPath);
            $fileSizeFormatted = $this->formatBytes($fileSize);

            $this->info("✅ Backup created successfully!");
            $this->line("  File: {$backupPath}");
            $this->line("  Size: {$fileSizeFormatted}");

            // Clean up old backups
            $keepCount = (int) ($this->option('keep') ?? 7);
            $this->cleanupOldBackups($backupDir, $keepCount);

            // Log backup creation
            $this->logBackup($backupPath, $fileSize);

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Backup failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Backup MySQL database
     */
    protected function backupMySQL(array $config, string $backupPath)
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );

        $this->info("  Executing mysqldump...");
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("mysqldump failed with return code: {$returnVar}");
        }

        if (!file_exists($backupPath)) {
            throw new \Exception("Backup file was not created");
        }
    }

    /**
     * Backup PostgreSQL database
     */
    protected function backupPostgreSQL(array $config, string $backupPath)
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 5432;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        // Set PGPASSWORD environment variable
        putenv("PGPASSWORD={$password}");

        $command = sprintf(
            'pg_dump --host=%s --port=%s --username=%s --dbname=%s --format=plain --file=%s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );

        $this->info("  Executing pg_dump...");
        
        exec($command, $output, $returnVar);

        // Clear password from environment
        putenv('PGPASSWORD');

        if ($returnVar !== 0) {
            throw new \Exception("pg_dump failed with return code: {$returnVar}");
        }

        if (!file_exists($backupPath)) {
            throw new \Exception("Backup file was not created");
        }
    }

    /**
     * Backup SQLite database
     */
    protected function backupSQLite(array $config, string $backupPath)
    {
        $databasePath = $config['database'];
        
        if (!file_exists($databasePath)) {
            throw new \Exception("SQLite database file not found: {$databasePath}");
        }

        $this->info("  Copying SQLite database file...");
        
        if (!copy($databasePath, $backupPath)) {
            throw new \Exception("Failed to copy SQLite database file");
        }
    }

    /**
     * Compress backup file
     */
    protected function compressBackup(string $backupPath): string
    {
        $this->info("  Compressing backup...");
        
        $compressedPath = $backupPath . '.gz';
        
        $fp_in = fopen($backupPath, 'rb');
        $fp_out = gzopen($compressedPath, 'wb9');

        if (!$fp_in || !$fp_out) {
            throw new \Exception("Failed to open files for compression");
        }

        while (!feof($fp_in)) {
            gzwrite($fp_out, fread($fp_in, 1024 * 512));
        }

        fclose($fp_in);
        gzclose($fp_out);

        // Delete original uncompressed file
        unlink($backupPath);

        return $compressedPath;
    }

    /**
     * Clean up old backups
     */
    protected function cleanupOldBackups(string $backupDir, int $keepCount)
    {
        $files = glob($backupDir . '/*.{sql,sql.gz}', GLOB_BRACE);
        
        if (count($files) <= $keepCount) {
            return;
        }

        // Sort by modification time (newest first)
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Delete oldest backups
        $filesToDelete = array_slice($files, $keepCount);
        $deletedCount = 0;

        foreach ($filesToDelete as $file) {
            if (unlink($file)) {
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->info("  🗑️  Deleted {$deletedCount} old backup(s)");
        }
    }

    /**
     * Log backup creation
     */
    protected function logBackup(string $backupPath, int $fileSize)
    {
        $logPath = storage_path('app/backups/backup_log.json');
        $logs = [];

        if (file_exists($logPath)) {
            $logs = json_decode(file_get_contents($logPath), true) ?? [];
        }

        $logs[] = [
            'file' => $backupPath,
            'size' => $fileSize,
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        // Keep only last 100 log entries
        $logs = array_slice($logs, -100);

        file_put_contents($logPath, json_encode($logs, JSON_PRETTY_PRINT));
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

