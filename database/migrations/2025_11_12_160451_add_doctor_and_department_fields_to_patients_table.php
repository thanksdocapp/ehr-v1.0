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
        Schema::table('patients', function (Blueprint $table) {
            // Add department_id to track which department the patient belongs to
            $table->foreignId('department_id')->nullable()->after('is_active')->constrained('departments')->onDelete('set null');
            
            // Add created_by_doctor_id to track which doctor created the patient
            $table->foreignId('created_by_doctor_id')->nullable()->after('department_id')->constrained('doctors')->onDelete('set null');
            
            // Add assigned_doctor_id to track which doctor is currently assigned to the patient
            $table->foreignId('assigned_doctor_id')->nullable()->after('created_by_doctor_id')->constrained('doctors')->onDelete('set null');
            
            // Add indexes for performance
            $table->index('department_id');
            $table->index('created_by_doctor_id');
            $table->index('assigned_doctor_id');
            $table->index(['department_id', 'created_by_doctor_id']);
            $table->index(['department_id', 'assigned_doctor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['created_by_doctor_id']);
            $table->dropForeign(['assigned_doctor_id']);
            $table->dropIndex(['department_id', 'assigned_doctor_id']);
            $table->dropIndex(['department_id', 'created_by_doctor_id']);
            $table->dropIndex(['assigned_doctor_id']);
            $table->dropIndex(['created_by_doctor_id']);
            $table->dropIndex(['department_id']);
            $table->dropColumn(['department_id', 'created_by_doctor_id', 'assigned_doctor_id']);
        });
    }
};
