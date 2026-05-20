<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
                    'doctor_welcome',
                    'service_order'
                ) NOT NULL DEFAULT 'general'
            ");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Could not add service_order to email_type enum', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function down(): void
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
            \Illuminate\Support\Facades\Log::warning('Could not revert service_order from email_type enum', [
                'error' => $e->getMessage(),
            ]);
        }
    }
};
