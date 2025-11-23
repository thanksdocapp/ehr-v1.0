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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Template name (e.g. "Referral Letter", "Consent Form")
            $table->enum('type', ['letter', 'form']); // letter or form
            $table->string('slug')->unique(); // Unique identifier
            $table->json('builder_config')->nullable(); // Drag-and-drop block layout
            $table->string('render_mode')->default('builder'); // Default builder
            $table->text('content')->nullable(); // Optional compiled HTML snapshot (letters)
            $table->json('schema')->nullable(); // Compiled structure for form templates
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index('slug');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
