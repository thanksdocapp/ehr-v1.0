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
        Schema::create('form_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_document_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->string('token', 64)->unique(); // Unique token for the form link
            $table->string('recipient_email');
            $table->string('status')->default('pending'); // pending, opened, completed, expired
            $table->json('form_data')->nullable(); // Submitted form data
            $table->text('rendered_content')->nullable(); // Pre-filled template content
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('token');
            $table->index('status');
            $table->index('recipient_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_requests');
    }
};
