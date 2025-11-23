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
        Schema::create('medical_record_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('file_name'); // Original file name
            $table->string('file_path'); // Storage path
            $table->string('file_type'); // MIME type
            $table->string('file_extension'); // File extension
            $table->unsignedBigInteger('file_size'); // File size in bytes
            $table->string('storage_disk')->default('private'); // Storage disk (private/public)
            $table->enum('file_category', ['pre_consult', 'notes', 'reference', 'other'])->default('notes');
            $table->text('description')->nullable(); // Optional description
            $table->boolean('is_private')->default(true); // Access control
            $table->string('virus_scan_status')->default('pending'); // pending, clean, infected, error
            $table->timestamp('virus_scan_at')->nullable();
            $table->text('virus_scan_result')->nullable();
            $table->timestamp('expires_at')->nullable(); // Retention policy expiration
            $table->timestamps();
            
            // Indexes
            $table->index(['medical_record_id', 'created_at']);
            $table->index('uploaded_by');
            $table->index('file_category');
            $table->index('virus_scan_status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_record_attachments');
    }
};
