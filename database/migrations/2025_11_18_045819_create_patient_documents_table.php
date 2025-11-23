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
        Schema::create('patient_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('document_templates')->onDelete('set null');
            $table->enum('type', ['letter', 'form']); // letter or form
            $table->string('title'); // Instance title (default from template, editable)
            $table->enum('status', ['draft', 'final', 'void'])->default('draft');
            $table->text('content')->nullable(); // Final HTML/text for letters
            $table->json('form_data')->nullable(); // Answers for forms
            $table->string('pdf_path')->nullable(); // Stored PDF path
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('signed_by_patient')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('patient_id');
            $table->index('template_id');
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_documents');
    }
};
