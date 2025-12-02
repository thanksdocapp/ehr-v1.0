<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('email_logs')) {
            return;
        }

        // Check if email_type column exists
        if (!Schema::hasColumn('email_logs', 'email_type')) {
            // Determine where to place the column
            $afterColumn = 'metadata';
            if (Schema::hasColumn('email_logs', 'sent_by')) {
                $afterColumn = 'sent_by';
            } elseif (Schema::hasColumn('email_logs', 'medical_record_id')) {
                $afterColumn = 'medical_record_id';
            } elseif (Schema::hasColumn('email_logs', 'patient_id')) {
                $afterColumn = 'patient_id';
            }

            Schema::table('email_logs', function (Blueprint $table) use ($afterColumn) {
                $table->enum('email_type', [
                    'general', 
                    'medical_record', 
                    'appointment', 
                    'prescription', 
                    'lab_result',
                    'two_factor',
                    'password_reset'
                ])->default('general')->after($afterColumn);
            });

            // Add index
            try {
                Schema::table('email_logs', function (Blueprint $table) {
                    $table->index('email_type');
                });
            } catch (\Exception $e) {
                // Index might already exist, ignore
            }
        } else {
            // Column exists, but we might need to update the enum values
            // Check if 'two_factor' is in the enum
            try {
                $columnInfo = DB::select("SHOW COLUMNS FROM `email_logs` WHERE Field = 'email_type'");
                if (!empty($columnInfo)) {
                    $columnType = $columnInfo[0]->Type;
                    // If it's an enum and doesn't include 'two_factor', we need to alter it
                    if (strpos($columnType, 'two_factor') === false) {
                        // Use raw SQL to modify enum
                        DB::statement("ALTER TABLE `email_logs` MODIFY COLUMN `email_type` ENUM('general', 'medical_record', 'appointment', 'prescription', 'lab_result', 'two_factor', 'password_reset') DEFAULT 'general'");
                    }
                }
            } catch (\Exception $e) {
                // If we can't check or update, that's okay - the column exists
                \Log::warning('Could not update email_type enum', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('email_logs')) {
            return;
        }

        if (Schema::hasColumn('email_logs', 'email_type')) {
            Schema::table('email_logs', function (Blueprint $table) {
                $table->dropIndex(['email_type']);
                $table->dropColumn('email_type');
            });
        }
    }
};
