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
        Schema::create('department_patient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false); // Mark primary department
            $table->timestamps();
            
            // Ensure unique combination of patient and department
            $table->unique(['patient_id', 'department_id']);
            
            // Index for performance
            $table->index(['patient_id', 'is_primary']);
            $table->index('department_id');
        });
        
        // Backfill existing data from patients.department_id to pivot table
        // This ensures existing patients with department_id are migrated to the new many-to-many relationship
        if (Schema::hasColumn('patients', 'department_id')) {
            DB::statement("
                INSERT INTO department_patient (patient_id, department_id, is_primary, created_at, updated_at)
                SELECT id, department_id, true, created_at, updated_at
                FROM patients
                WHERE department_id IS NOT NULL
                ON DUPLICATE KEY UPDATE updated_at = NOW()
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_patient');
    }
};
