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
        // Use raw SQL to modify columns since Laravel Schema doesn't support changing ENUM columns directly
        
        // Make date_of_birth nullable for guest patients
        if (Schema::hasColumn('patients', 'date_of_birth')) {
            DB::statement('ALTER TABLE `patients` MODIFY COLUMN `date_of_birth` DATE NULL');
        }
        
        // Make gender nullable for guest patients (using raw SQL for ENUM)
        if (Schema::hasColumn('patients', 'gender')) {
            DB::statement("ALTER TABLE `patients` MODIFY COLUMN `gender` ENUM('male', 'female', 'other') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to NOT NULL (but this might fail if there are NULL values)
        // You may need to update existing NULL values first
        
        if (Schema::hasColumn('patients', 'date_of_birth')) {
            // First, update any NULL values to a default
            DB::statement("UPDATE `patients` SET `date_of_birth` = '1900-01-01' WHERE `date_of_birth` IS NULL");
            DB::statement('ALTER TABLE `patients` MODIFY COLUMN `date_of_birth` DATE NOT NULL');
        }
        
        if (Schema::hasColumn('patients', 'gender')) {
            // First, update any NULL values to a default
            DB::statement("UPDATE `patients` SET `gender` = 'other' WHERE `gender` IS NULL");
            DB::statement("ALTER TABLE `patients` MODIFY COLUMN `gender` ENUM('male', 'female', 'other') NOT NULL");
        }
    }
};

