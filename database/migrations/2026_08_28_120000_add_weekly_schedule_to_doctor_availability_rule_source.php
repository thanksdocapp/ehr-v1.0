<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctor_availability_rules')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `doctor_availability_rules` MODIFY COLUMN `source` ENUM('backfill', 'default', 'manual', 'weekly_schedule') NOT NULL DEFAULT 'manual'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('doctor_availability_rules')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('doctor_availability_rules')
            ->where('source', 'weekly_schedule')
            ->update(['source' => 'backfill']);

        DB::statement("ALTER TABLE `doctor_availability_rules` MODIFY COLUMN `source` ENUM('backfill', 'default', 'manual') NOT NULL DEFAULT 'manual'");
    }
};
