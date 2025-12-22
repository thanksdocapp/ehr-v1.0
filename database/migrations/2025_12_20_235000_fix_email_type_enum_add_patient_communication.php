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

        // Modify the ENUM to include 'patient_communication', 'two_factor', and 'password_reset'
        // First, check if there are any values that don't match the new enum
        // MySQL requires MODIFY COLUMN with full enum definition
        try {
            // For MySQL, we need to update any invalid values first, or include all possible values
            // Let's include all values that might exist: 'two_factor' and 'password_reset'
            DB::statement("
                ALTER TABLE `email_logs` 
                MODIFY COLUMN `email_type` ENUM(
                    'general', 
                    'medical_record', 
                    'appointment', 
                    'prescription', 
                    'lab_result', 
                    'patient_communication',
                    'two_factor',
                    'password_reset'
                ) NOT NULL DEFAULT 'general'
            ");
            
            \Log::info('Successfully modified email_type enum to include patient_communication');
        } catch (\Exception $e) {
            \Log::error('Failed to modify email_type enum', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
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

        // Revert the ENUM back to original values
        // Note: This will fail if there are existing records with new enum values
        try {
            DB::statement("
                ALTER TABLE `email_logs` 
                MODIFY COLUMN `email_type` ENUM(
                    'general', 
                    'medical_record', 
                    'appointment', 
                    'prescription', 
                    'lab_result'
                ) NOT NULL DEFAULT 'general'
            ");
        } catch (\Exception $e) {
            \Log::warning('Failed to revert email_type enum', [
                'error' => $e->getMessage()
            ]);
        }
    }
};

