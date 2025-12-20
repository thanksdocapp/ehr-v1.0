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
        // Check if table exists
        if (!Schema::hasTable('email_logs')) {
            return;
        }

        // Check if email_type column exists
        if (!Schema::hasColumn('email_logs', 'email_type')) {
            return;
        }

        // Modify the ENUM to include 'patient_communication'
        // Include all existing enum values: general, medical_record, appointment, prescription, lab_result, two_factor, password_reset
        // MySQL doesn't support ALTER ENUM directly, so we need to use MODIFY COLUMN with all values
        try {
            DB::statement("ALTER TABLE `email_logs` MODIFY COLUMN `email_type` ENUM('general', 'medical_record', 'appointment', 'prescription', 'lab_result', 'two_factor', 'password_reset', 'patient_communication') NOT NULL DEFAULT 'general'");
        } catch (\Exception $e) {
            // If the column is already updated or doesn't use ENUM, log and continue
            \Log::warning('Failed to modify email_type enum', [
                'error' => $e->getMessage()
            ]);
            // Don't throw - allow migration to continue even if enum modification fails
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if table exists
        if (!Schema::hasTable('email_logs')) {
            return;
        }

        // Check if email_type column exists
        if (!Schema::hasColumn('email_logs', 'email_type')) {
            return;
        }

        // Revert the ENUM back to original values (excluding patient_communication but keeping two_factor and password_reset)
        // Note: This will fail if there are existing records with 'patient_communication'
        // In that case, you would need to update those records first
        try {
            DB::statement("ALTER TABLE `email_logs` MODIFY COLUMN `email_type` ENUM('general', 'medical_record', 'appointment', 'prescription', 'lab_result', 'two_factor', 'password_reset') NOT NULL DEFAULT 'general'");
        } catch (\Exception $e) {
            \Log::warning('Failed to revert email_type enum', [
                'error' => $e->getMessage()
            ]);
        }
    }
};

