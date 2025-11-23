<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrate existing department_id values to the doctor_department pivot table
     */
    public function up(): void
    {
        // Check if pivot table exists
        if (!Schema::hasTable('doctor_department')) {
            return; // Pivot table not created yet, skip
        }

        // Check if doctors table has department_id column
        if (!Schema::hasColumn('doctors', 'department_id')) {
            return; // No department_id column, skip
        }

        // Migrate existing doctor-department relationships
        $doctors = DB::table('doctors')
            ->whereNotNull('department_id')
            ->where('department_id', '!=', 0)
            ->get();

        foreach ($doctors as $doctor) {
            // Check if relationship already exists in pivot table
            $exists = DB::table('doctor_department')
                ->where('doctor_id', $doctor->id)
                ->where('department_id', $doctor->department_id)
                ->exists();

            if (!$exists) {
                DB::table('doctor_department')->insert([
                    'doctor_id' => $doctor->id,
                    'department_id' => $doctor->department_id,
                    'is_primary' => true, // Mark as primary since it was the only department
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove all entries from pivot table
        DB::table('doctor_department')->truncate();
    }
};
