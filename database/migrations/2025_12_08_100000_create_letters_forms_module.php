<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This creates a clean Letters & Forms module with:
     * - templates: Letter/form templates with WYSIWYG content
     * - generated_documents: PDFs generated from templates for patients
     */
    public function up(): void
    {
        // Create the templates table (fresh, clean design)
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['letter', 'form']);
            $table->longText('content'); // HTML content with placeholders like {{patient_name}}
            $table->json('placeholders')->nullable(); // List of available placeholders
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // Admin-created templates visible to all
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
            $table->index('created_by');
            $table->index('is_system');
        });

        // Create generated_documents table
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('templates')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('file_path'); // Path to stored PDF
            $table->string('file_name'); // Original filename
            $table->string('title'); // Document title
            $table->longText('rendered_content')->nullable(); // Final HTML before PDF
            $table->enum('status', ['draft', 'final', 'sent', 'void'])->default('draft');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('sent_at')->nullable();
            $table->string('sent_to')->nullable(); // Email address if sent
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('template_id');
            $table->index('patient_id');
            $table->index('generated_by');
            $table->index('status');
            $table->index(['patient_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_documents');
        Schema::dropIfExists('templates');
    }
};
