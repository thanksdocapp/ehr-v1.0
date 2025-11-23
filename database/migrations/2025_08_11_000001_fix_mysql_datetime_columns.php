<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('database.default');
        
        // Fix patient_notifications table scheduled_at column
        if (Schema::hasTable('patient_notifications')) {
            if ($connection === 'mysql') {
                DB::statement('ALTER TABLE patient_notifications MODIFY COLUMN scheduled_at TIMESTAMP NULL DEFAULT NULL');
            } elseif ($connection === 'sqlite') {
                // SQLite handles this differently - we'll update any invalid values first
                DB::statement('UPDATE patient_notifications SET scheduled_at = NULL WHERE scheduled_at = "0000-00-00 00:00:00"');
            } elseif ($connection === 'pgsql') {
                DB::statement('ALTER TABLE patient_notifications ALTER COLUMN scheduled_at DROP DEFAULT');
                DB::statement('ALTER TABLE patient_notifications ALTER COLUMN scheduled_at SET DEFAULT NULL');
            }
        }
        
        // Fix prescriptions table dispensed_at column
        if (Schema::hasTable('prescriptions')) {
            if ($connection === 'mysql') {
                DB::statement('ALTER TABLE prescriptions MODIFY COLUMN dispensed_at TIMESTAMP NULL DEFAULT NULL');
            } elseif ($connection === 'sqlite') {
                // SQLite handles this differently - we'll update any invalid values first
                DB::statement('UPDATE prescriptions SET dispensed_at = NULL WHERE dispensed_at = "0000-00-00 00:00:00"');
            } elseif ($connection === 'pgsql') {
                DB::statement('ALTER TABLE prescriptions ALTER COLUMN dispensed_at DROP DEFAULT');
                DB::statement('ALTER TABLE prescriptions ALTER COLUMN dispensed_at SET DEFAULT NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to reverse this fix as it's correcting an invalid state
    }
};
