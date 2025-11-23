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
        // Check if table exists before proceeding
        if (!Schema::hasTable('medical_record_attachments')) {
            return; // Table doesn't exist yet, skip this migration
        }

        // Check if column exists before proceeding
        if (!Schema::hasColumn('medical_record_attachments', 'file_category')) {
            return; // Column doesn't exist yet, skip this migration
        }

        // Update existing data to map old values to new values
        DB::table('medical_record_attachments')
            ->where('file_category', 'notes')
            ->update(['file_category' => 'documents']);
        
        DB::table('medical_record_attachments')
            ->where('file_category', 'pre_consult')
            ->update(['file_category' => 'documents']);
        
        DB::table('medical_record_attachments')
            ->where('file_category', 'reference')
            ->update(['file_category' => 'documents']);

        // Modify the enum column
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE medical_record_attachments MODIFY COLUMN file_category ENUM('photo', 'results', 'documents', 'other') DEFAULT 'documents'");
        } elseif (DB::getDriverName() === 'pgsql') {
            // For PostgreSQL, we need to drop and recreate the constraint
            DB::statement("ALTER TABLE medical_record_attachments DROP CONSTRAINT IF EXISTS medical_record_attachments_file_category_check");
            DB::statement("ALTER TABLE medical_record_attachments ADD CONSTRAINT medical_record_attachments_file_category_check CHECK (file_category IN ('photo', 'results', 'documents', 'other'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if table exists before proceeding
        if (!Schema::hasTable('medical_record_attachments')) {
            return; // Table doesn't exist, skip this migration
        }

        // Check if column exists before proceeding
        if (!Schema::hasColumn('medical_record_attachments', 'file_category')) {
            return; // Column doesn't exist, skip this migration
        }

        // Map new values back to old values
        DB::table('medical_record_attachments')
            ->where('file_category', 'documents')
            ->update(['file_category' => 'notes']);
        
        DB::table('medical_record_attachments')
            ->where('file_category', 'photo')
            ->update(['file_category' => 'other']);
        
        DB::table('medical_record_attachments')
            ->where('file_category', 'results')
            ->update(['file_category' => 'other']);

        // Revert the enum column
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE medical_record_attachments MODIFY COLUMN file_category ENUM('pre_consult', 'notes', 'reference', 'other') DEFAULT 'notes'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE medical_record_attachments DROP CONSTRAINT IF EXISTS medical_record_attachments_file_category_check");
            DB::statement("ALTER TABLE medical_record_attachments ADD CONSTRAINT medical_record_attachments_file_category_check CHECK (file_category IN ('pre_consult', 'notes', 'reference', 'other'))");
        }
    }
};

