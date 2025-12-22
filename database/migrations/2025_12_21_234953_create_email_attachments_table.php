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
        Schema::create('email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_log_id')->constrained('email_logs')->onDelete('cascade');
            $table->string('file_name'); // Original file name
            $table->string('file_path'); // Storage path
            $table->string('file_type'); // MIME type
            $table->string('file_extension'); // File extension
            $table->unsignedBigInteger('file_size'); // File size in bytes
            $table->string('storage_disk')->default('public'); // Storage disk
            $table->enum('attachment_type', ['file', 'letter', 'form'])->default('file'); // Type of attachment
            $table->unsignedBigInteger('source_id')->nullable(); // ID of source document (template, generated doc, etc.)
            $table->text('description')->nullable(); // Optional description
            $table->timestamps();
            
            // Indexes
            $table->index(['email_log_id', 'created_at']);
            $table->index('attachment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_attachments');
    }
};
