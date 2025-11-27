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
        // Use raw SQL to handle MySQL constraints properly
        DB::statement('ALTER TABLE `payment_gateways` MODIFY COLUMN `credentials` TEXT NOT NULL');
        
        // Drop any CHECK constraints on credentials column (MySQL 8.0+)
        try {
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'payment_gateways' 
                AND CONSTRAINT_TYPE = 'CHECK'
                AND CONSTRAINT_NAME LIKE '%credentials%'
            ");
            
            foreach ($constraints as $constraint) {
                DB::statement("ALTER TABLE `payment_gateways` DROP CHECK `{$constraint->CONSTRAINT_NAME}`");
            }
        } catch (\Exception $e) {
            // Ignore if no constraints exist or if MySQL version doesn't support this
            \Log::info('No CHECK constraints found on credentials column or MySQL version issue: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to json (note: this may fail if data is encrypted)
        DB::statement('ALTER TABLE `payment_gateways` MODIFY COLUMN `credentials` JSON NOT NULL');
    }
};
