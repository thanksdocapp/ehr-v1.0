<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add new columns
        Schema::table('lab_reports', function (Blueprint $table) {
            $table->text('interpretation')->nullable()->after('notes');
            $table->enum('priority', ['normal', 'urgent', 'stat'])->default('normal')->after('status');
        });
        
        // Step 2: Add lab_technician column (we'll keep both for now)
        Schema::table('lab_reports', function (Blueprint $table) {
            $table->string('lab_technician')->nullable()->after('technician_name');
        });
        
        // Step 3: Copy data from technician_name to lab_technician
        \DB::statement('UPDATE lab_reports SET lab_technician = technician_name');
        
        // Step 4: Add reference_range column (we'll keep both for now)
        Schema::table('lab_reports', function (Blueprint $table) {
            $table->text('reference_range')->nullable()->after('reference_values');
        });
        
        // Step 5: Copy data from reference_values to reference_range (convert JSON to text)
        \DB::statement("UPDATE lab_reports SET reference_range = CASE WHEN reference_values IS NOT NULL THEN reference_values ELSE NULL END");
        
        // Step 6: Change results from json to text
        Schema::table('lab_reports', function (Blueprint $table) {
            $table->text('results')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Change results back to json
        Schema::table('lab_reports', function (Blueprint $table) {
            $table->json('results')->nullable()->change();
        });
        
        // Step 2: Remove added columns one by one
        Schema::table('lab_reports', function (Blueprint $table) {
            $table->dropColumn('reference_range');
        });
        
        Schema::table('lab_reports', function (Blueprint $table) {
            $table->dropColumn('lab_technician');
        });
        
        Schema::table('lab_reports', function (Blueprint $table) {
            $table->dropColumn(['interpretation', 'priority']);
        });
    }
};
