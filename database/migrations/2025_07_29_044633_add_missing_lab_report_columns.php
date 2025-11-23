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
        Schema::table('lab_reports', function (Blueprint $table) {
            // Add only the columns that don't exist yet
            // Based on the schema, these columns are missing:
            $table->text('technician_notes')->after('notes')->nullable();
            $table->string('report_file')->after('file_path')->nullable();
            $table->unsignedBigInteger('technician_id')->after('technician_name')->nullable();
            $table->unsignedBigInteger('created_by')->after('technician_id')->nullable();
            $table->unsignedBigInteger('updated_by')->after('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_reports', function (Blueprint $table) {
            // Drop the added columns
            $table->dropColumn([
                'technician_notes',
                'report_file',
                'technician_id',
                'created_by',
                'updated_by'
            ]);
        });
    }
};
