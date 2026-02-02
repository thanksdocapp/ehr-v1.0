<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'doctor_welcome' to email_logs.email_type enum so doctor welcome emails can be logged.
     */
    public function up(): void
    {
        if (! Schema::hasTable('email_logs') || ! Schema::hasColumn('email_logs', 'email_type')) {
            return;
        }

        try {
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
                    'password_reset',
                    'doctor_welcome'
                ) NOT NULL DEFAULT 'general'
            ");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Could not add doctor_welcome to email_type enum', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('email_logs') || ! Schema::hasColumn('email_logs', 'email_type')) {
            return;
        }

        try {
            // Revert to previous enum (without doctor_welcome)
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Could not revert email_type enum', ['error' => $e->getMessage()]);
        }
    }
};
