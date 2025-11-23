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
        // Get current columns
        $columns = Schema::getColumnListing('testimonials');
        
        // Add missing columns if they don't exist
        if (!in_array('customer_position', $columns)) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->string('customer_position')->nullable();
            });
        }
        
        if (!in_array('customer_company', $columns)) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->string('customer_company')->nullable();
            });
        }
        
        // Rename columns if they still have old names
        if (in_array('patient_name', $columns) && !in_array('customer_name', $columns)) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->renameColumn('patient_name', 'customer_name');
            });
        }
        
        if (in_array('patient_photo', $columns) && !in_array('customer_photo', $columns)) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->renameColumn('patient_photo', 'customer_photo');
            });
        }
        
        if (in_array('testimonial', $columns) && !in_array('review_text', $columns)) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->renameColumn('testimonial', 'review_text');
            });
        }
        
        // Drop unused columns if they still exist
        $columnsToCheck = ['treatment_type', 'treatment_date', 'is_approved'];
        $currentColumns = Schema::getColumnListing('testimonials');
        $columnsToDrop = array_intersect($columnsToCheck, $currentColumns);
        
        if (!empty($columnsToDrop)) {
            Schema::table('testimonials', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is intentionally empty as we don't want to reverse the fix
    }
};
