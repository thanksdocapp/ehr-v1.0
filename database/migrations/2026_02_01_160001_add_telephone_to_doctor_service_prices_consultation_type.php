<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'telephone' to consultation_type enum: in_person, online (video), telephone.
     */
    public function up(): void
    {
        if (!Schema::hasTable('doctor_service_prices') || !Schema::hasColumn('doctor_service_prices', 'consultation_type')) {
            return;
        }
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE doctor_service_prices MODIFY COLUMN consultation_type ENUM('in_person', 'online', 'telephone') NOT NULL DEFAULT 'in_person'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('doctor_service_prices') || !Schema::hasColumn('doctor_service_prices', 'consultation_type')) {
            return;
        }
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // Revert to original enum; existing 'telephone' values become 'in_person'
            DB::statement("UPDATE doctor_service_prices SET consultation_type = 'in_person' WHERE consultation_type = 'telephone'");
            DB::statement("ALTER TABLE doctor_service_prices MODIFY COLUMN consultation_type ENUM('in_person', 'online') NOT NULL DEFAULT 'in_person'");
        }
    }
};
