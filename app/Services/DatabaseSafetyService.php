<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class DatabaseSafetyService
{
    /**
     * Create a backup before destructive operations
     */
    public static function createBackupBeforeDestructiveOperation(string $operation = 'migration'): ?string
    {
        try {
            // Create backup
            $result = Artisan::call('db:backup', [
                '--keep' => 10, // Keep more backups during operations
            ]);

            if ($result === 0) {
                $output = Artisan::output();
                Log::info("Backup created before {$operation}", ['output' => $output]);
                return $output;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning("Failed to create backup before {$operation}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if database has data
     */
    public static function hasData(string $table = null): bool
    {
        if ($table) {
            return DB::table($table)->count() > 0;
        }

        // Check main tables
        $tables = ['users', 'patients', 'appointments', 'medical_records'];
        
        foreach ($tables as $tableName) {
            if (DB::getSchemaBuilder()->hasTable($tableName)) {
                if (DB::table($tableName)->count() > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get database statistics
     */
    public static function getDatabaseStats(): array
    {
        $stats = [];

        $tables = [
            'users' => 'Users',
            'patients' => 'Patients',
            'appointments' => 'Appointments',
            'medical_records' => 'Medical Records',
            'prescriptions' => 'Prescriptions',
            'lab_reports' => 'Lab Reports',
        ];

        foreach ($tables as $table => $label) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $count = DB::table($table)->count();
                $stats[$label] = $count;
            }
        }

        return $stats;
    }

    /**
     * Warn about destructive operation
     */
    public static function warnDestructiveOperation(string $operation): void
    {
        $hasData = self::hasData();
        
        if ($hasData) {
            $stats = self::getDatabaseStats();
            $message = "⚠️  WARNING: {$operation} may delete or modify existing data!\n\n";
            $message .= "Current database statistics:\n";
            
            foreach ($stats as $label => $count) {
                $message .= "  - {$label}: {$count}\n";
            }
            
            $message .= "\nThis operation cannot be undone!";
            
            Log::warning($message);
        }
    }
}

